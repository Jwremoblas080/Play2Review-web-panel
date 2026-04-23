<?php
require_once('../configurations/configurations.php');
require_once('category-config.php');

// Check educator privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'educator') {
    header("Location: logout.php");
    exit();
}

// Add logging function for educators
function logQuizAction($con, $action_type, $subject_name, $quiz_level, $quiz_id, $question_text, $old_values = null, $new_values = null, $changed_fields = null) {
    $action_type = mysqli_real_escape_string($con, $action_type);
    $subject_name = mysqli_real_escape_string($con, $subject_name);
    $quiz_level = $quiz_level ? mysqli_real_escape_string($con, $quiz_level) : 'NULL';
    $quiz_id = $quiz_id ? mysqli_real_escape_string($con, $quiz_id) : 'NULL';
    $question_text = mysqli_real_escape_string($con, $question_text);
    $old_values = $old_values ? "'" . mysqli_real_escape_string($con, json_encode($old_values, JSON_UNESCAPED_UNICODE)) . "'" : 'NULL';
    $new_values = $new_values ? "'" . mysqli_real_escape_string($con, json_encode($new_values, JSON_UNESCAPED_UNICODE)) . "'" : 'NULL';
    $changed_fields = $changed_fields ? "'" . mysqli_real_escape_string($con, json_encode($changed_fields)) . "'" : 'NULL';
    
    // Get user info (educator)
    $performed_by_type = 'teacher';
    $performed_by_id = $_SESSION['user_id'] ?? 0;
    $performed_by_name = mysqli_real_escape_string($con, $_SESSION['name'] ?? 'Teacher');
    $ip_address = mysqli_real_escape_string($con, $_SERVER['REMOTE_ADDR']);
    
    $query = "INSERT INTO quiz_audit_logs 
              (action_type, subject_name, quiz_level, quiz_id, question_text, old_values, new_values, changed_fields, performed_by_type, performed_by_id, performed_by_name, ip_address) 
              VALUES 
              ('$action_type', '$subject_name', $quiz_level, $quiz_id, '$question_text', $old_values, $new_values, $changed_fields, '$performed_by_type', '$performed_by_id', '$performed_by_name', '$ip_address')";
    
    return mysqli_query($con, $query);
}

// Get educator's handled subjects
$educator_id = $_SESSION['user_id'];
$educator_query = "SELECT * FROM educators WHERE id = '$educator_id'";
$educator_result = mysqli_query($con, $educator_query);
$educator_data = mysqli_fetch_assoc($educator_result);

// Parse handled subjects (comma-separated string)
$handled_subjects = explode(',', $educator_data['handled_subject']);
$handled_subjects = array_filter($handled_subjects); // Remove empty values

// If no subjects are assigned, show empty array
if(empty($handled_subjects)) {
    $handled_subjects = [];
}

// Get current subject from URL or default to first subject
$current_subject = $_GET['subject'] ?? ($handled_subjects[0] ?? '');

// Get current level filter from URL
$current_level = $_GET['level'] ?? 'all';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ========================= EXCEL TEMPLATE DOWNLOAD =========================
    if (isset($_POST['download_template'])) {
        $subject_name   = $_POST['subject_name'] ?? ($handled_subjects[0] ?? 'english');
        $subject_labels = ['english'=>'English','ap'=>'Araling Panlipunan','filipino'=>'Filipino','math'=>'Mathematics','science'=>'Science'];
        $subject_label  = $subject_labels[$subject_name] ?? ucfirst($subject_name);
        $categories     = getCategoriesBySubject($subject_name);

        require_once('includes/generate_quiz_xlsx.php');
        generateQuizTemplateXlsx($subject_name, $categories, $subject_label);
        exit();
    }

    // ========================= CSV BULK IMPORT =========================
    if (isset($_POST['import_csv'])) {
        $subject_name = mysqli_real_escape_string($con, $_POST['import_subject']);

        // Verify teacher handles this subject
        if (!in_array($subject_name, $handled_subjects)) {
            $_SESSION['error'] = "You are not assigned to that subject.";
            header("Location: educ-quizes.php?subject=$subject_name");
            exit();
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Please select a valid CSV file.";
            header("Location: educ-quizes.php?subject=$subject_name");
            exit();
        }

        $file   = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');

        if (!$handle) {
            $_SESSION['error'] = "Could not open the uploaded file.";
            header("Location: educ-quizes.php?subject=$subject_name");
            exit();
        }

        // Strip UTF-8 BOM from the start of the file if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle); // not a BOM, go back to start
        }

        // Skip rows until we find the actual header row (contains 'subject_name')
        // Strip BOM and normalize — Excel CSV exports often include a UTF-8 BOM
        $found_header = false;
        while (($row = fgetcsv($handle)) !== false) {
            $first = trim($row[0] ?? '');
            // Strip UTF-8 BOM if present
            $first = ltrim($first, "\xEF\xBB\xBF");
            // Strip any non-printable/invisible characters
            $first = preg_replace('/[\x00-\x1F\x7F\xC2\xA0]/u', '', $first);
            $first = strtolower($first);
            // Match exact or partial (handles extra whitespace/encoding quirks)
            if ($first === 'subject_name' || strpos($first, 'subject_name') !== false) {
                $found_header = true;
                break;
            }
        }

        if (!$found_header) {
            fclose($handle);
            $_SESSION['error'] = "Could not find the header row. Make sure you saved the file as CSV (comma-separated) from Excel.";
            header("Location: educ-quizes.php?subject=$subject_name");
            exit();
        }

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 9) { $skipped++; continue; }

            $first_cell = strtolower(trim($row[0]));
            // Skip hint row and empty rows
            if (empty($first_cell) || strpos($first_cell, 'e.g') !== false || strpos($first_cell, 'pick') !== false) {
                continue;
            }

            [$row_subject, $row_level, $row_category, $row_question, $row_a, $row_b, $row_c, $row_d, $row_correct] = $row;

            $row_subject  = mysqli_real_escape_string($con, trim($row_subject));
            $row_level    = (int) trim($row_level);
            $row_category = mysqli_real_escape_string($con, trim($row_category));
            $row_question = mysqli_real_escape_string($con, trim($row_question));
            $row_a        = mysqli_real_escape_string($con, trim($row_a));
            $row_b        = mysqli_real_escape_string($con, trim($row_b));
            $row_c        = mysqli_real_escape_string($con, trim($row_c));
            $row_d        = mysqli_real_escape_string($con, trim($row_d));
            $row_correct  = (int) trim($row_correct);

            if (empty($row_question) || $row_level < 1 || $row_level > 10 || $row_correct < 1 || $row_correct > 4) {
                $skipped++;
                continue;
            }

            $q = "INSERT INTO quizes (teacher_id, subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number)
                  VALUES ('$educator_id', '$row_subject', '$row_level', '$row_category', '$row_question', '$row_a', '$row_b', '$row_c', '$row_d', '$row_correct')";

            if (mysqli_query($con, $q)) {
                $new_id = mysqli_insert_id($con);
                logQuizAction($con, 'ADD', $row_subject, $row_level, $new_id, $row_question, null,
                    ['subject_name'=>$row_subject,'quiz_level'=>$row_level,'category'=>$row_category,'question'=>$row_question,
                     'answer_a'=>$row_a,'answer_b'=>$row_b,'answer_c'=>$row_c,'answer_d'=>$row_d,'correct_answer_number'=>$row_correct,'teacher_id'=>$educator_id]);
                $imported++;
            } else {
                $skipped++;
            }
        }
        fclose($handle);

        $_SESSION['message'] = "CSV Import complete: $imported question(s) added" . ($skipped > 0 ? ", $skipped row(s) skipped." : ".");
        header("Location: educ-quizes.php?subject=$subject_name");
        exit();
    }

    if (isset($_POST['add_quiz'])) {
        $subject_name = mysqli_real_escape_string($con, $_POST['subject_name']);
        $quiz_level = mysqli_real_escape_string($con, $_POST['quiz_level']);
        $category = mysqli_real_escape_string($con, $_POST['category']);
        $question = mysqli_real_escape_string($con, $_POST['question']);
        $answer_a = mysqli_real_escape_string($con, $_POST['answer_a']);
        $answer_b = mysqli_real_escape_string($con, $_POST['answer_b']);
        $answer_c = mysqli_real_escape_string($con, $_POST['answer_c']);
        $answer_d = mysqli_real_escape_string($con, $_POST['answer_d']);
        $correct_answer = mysqli_real_escape_string($con, $_POST['correct_answer']);

        $query = "INSERT INTO quizes (teacher_id, subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) 
                  VALUES ('$educator_id', '$subject_name', '$quiz_level', '$category', '$question', '$answer_a', '$answer_b', '$answer_c', '$answer_d', '$correct_answer')";
        
        if(mysqli_query($con, $query)) {
            $new_quiz_id = mysqli_insert_id($con);
            
            // Log the ADD action
            $new_values = [
                'subject_name' => $subject_name,
                'quiz_level' => $quiz_level,
                'category' => $category,
                'question' => $question,
                'answer_a' => $answer_a,
                'answer_b' => $answer_b,
                'answer_c' => $answer_c,
                'answer_d' => $answer_d,
                'correct_answer_number' => $correct_answer,
                'teacher_id' => $educator_id
            ];
            
            logQuizAction(
                $con, 
                'ADD', 
                $subject_name, 
                $quiz_level, 
                $new_quiz_id, 
                $question, 
                null, 
                $new_values
            );
            
            $_SESSION['message'] = "Quiz question added successfully!";
        } else {
            $_SESSION['error'] = "Error adding quiz question: " . mysqli_error($con);
        }
        
        header("Location: educ-quizes.php?subject=" . $subject_name . "&level=" . $current_level);
        exit();
    }
    
    if (isset($_POST['edit_quiz'])) {
        $quiz_id = mysqli_real_escape_string($con, $_POST['quiz_id']);
        $subject_name = mysqli_real_escape_string($con, $_POST['subject_name']);
        $quiz_level = mysqli_real_escape_string($con, $_POST['quiz_level']);
        $category = mysqli_real_escape_string($con, $_POST['category']);
        $question = mysqli_real_escape_string($con, $_POST['question']);
        $answer_a = mysqli_real_escape_string($con, $_POST['answer_a']);
        $answer_b = mysqli_real_escape_string($con, $_POST['answer_b']);
        $answer_c = mysqli_real_escape_string($con, $_POST['answer_c']);
        $answer_d = mysqli_real_escape_string($con, $_POST['answer_d']);
        $correct_answer = mysqli_real_escape_string($con, $_POST['correct_answer']);

        // Get old values for logging
        $old_query = "SELECT * FROM quizes WHERE id = '$quiz_id' AND teacher_id = '$educator_id'";
        $old_result = mysqli_query($con, $old_query);
        $old_data = mysqli_fetch_assoc($old_result);

        $query = "UPDATE quizes SET 
                  subject_name = '$subject_name', 
                  quiz_level = '$quiz_level', 
                  category = '$category',
                  question = '$question', 
                  answer_a = '$answer_a', 
                  answer_b = '$answer_b', 
                  answer_c = '$answer_c', 
                  answer_d = '$answer_d', 
                  correct_answer_number = '$correct_answer' 
                  WHERE id = '$quiz_id' AND teacher_id = '$educator_id'";
        
        if(mysqli_query($con, $query)) {
            // Determine what changed
            $changed_fields = [];
            $new_values = [
                'subject_name' => $subject_name,
                'quiz_level' => $quiz_level,
                'category' => $category,
                'question' => $question,
                'answer_a' => $answer_a,
                'answer_b' => $answer_b,
                'answer_c' => $answer_c,
                'answer_d' => $answer_d,
                'correct_answer_number' => $correct_answer
            ];
            
            $old_values = [
                'subject_name' => $old_data['subject_name'],
                'quiz_level' => $old_data['quiz_level'],
                'category' => $old_data['category'] ?? '',
                'question' => $old_data['question'],
                'answer_a' => $old_data['answer_a'],
                'answer_b' => $old_data['answer_b'],
                'answer_c' => $old_data['answer_c'],
                'answer_d' => $old_data['answer_d'],
                'correct_answer_number' => $old_data['correct_answer_number']
            ];
            
            // Compare and find changed fields
            foreach ($new_values as $key => $value) {
                if ($old_values[$key] != $value) {
                    $changed_fields[] = $key;
                }
            }
            
            // Log the EDIT action if there were changes
            if (!empty($changed_fields)) {
                logQuizAction(
                    $con, 
                    'EDIT', 
                    $subject_name, 
                    $quiz_level, 
                    $quiz_id, 
                    $question, 
                    $old_values, 
                    $new_values, 
                    $changed_fields
                );
            }
            
            $_SESSION['message'] = "Quiz question updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating quiz question: " . mysqli_error($con);
        }
        
        header("Location: educ-quizes.php?subject=" . $subject_name . "&level=" . $current_level);
        exit();
    }
    
    if (isset($_POST['delete_quiz'])) {
        $quiz_id = mysqli_real_escape_string($con, $_POST['quiz_id']);
        $subject_name = mysqli_real_escape_string($con, $_POST['subject_name']);

        // Get the quiz data before deleting for logging
        $old_query = "SELECT * FROM quizes WHERE id = '$quiz_id' AND teacher_id = '$educator_id'";
        $old_result = mysqli_query($con, $old_query);
        $old_data = mysqli_fetch_assoc($old_result);

        if ($old_data) {
            $query = "DELETE FROM quizes WHERE id = '$quiz_id' AND teacher_id = '$educator_id'";
            
            if(mysqli_query($con, $query)) {
                // Log the DELETE action
                $old_values = [
                    'subject_name' => $old_data['subject_name'],
                    'quiz_level' => $old_data['quiz_level'],
                    'question' => $old_data['question'],
                    'answer_a' => $old_data['answer_a'],
                    'answer_b' => $old_data['answer_b'],
                    'answer_c' => $old_data['answer_c'],
                    'answer_d' => $old_data['answer_d'],
                    'correct_answer_number' => $old_data['correct_answer_number']
                ];
                
                logQuizAction(
                    $con, 
                    'DELETE', 
                    $old_data['subject_name'], 
                    $old_data['quiz_level'], 
                    $quiz_id, 
                    $old_data['question'], 
                    $old_values, 
                    null
                );
                
                $_SESSION['message'] = "Quiz question deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting quiz question: " . mysqli_error($con);
            }
        }
        
        header("Location: educ-quizes.php?subject=" . $subject_name . "&level=" . $current_level);
        exit();
    }
}

// Fetch quizzes for current subject with level filter
$quizzes = [];
$available_levels = [];
if ($current_subject && in_array($current_subject, $handled_subjects)) {
    // Build query with level filter
    $query = "SELECT * FROM quizes WHERE teacher_id = '$educator_id' AND subject_name = '$current_subject'";
    
    if ($current_level !== 'all') {
        $query .= " AND quiz_level = '$current_level'";
    }
    
    $query .= " ORDER BY quiz_level, id";
    
    $result = mysqli_query($con, $query);
    while($row = mysqli_fetch_assoc($result)) {
        $quizzes[] = $row;
    }
    
    // Get available levels for this subject
    $level_query = "SELECT DISTINCT quiz_level FROM quizes WHERE teacher_id = '$educator_id' AND subject_name = '$current_subject' ORDER BY quiz_level";
    $level_result = mysqli_query($con, $level_query);
    while($level_row = mysqli_fetch_assoc($level_result)) {
        $available_levels[] = $level_row['quiz_level'];
    }
}

// Subject names mapping
$subject_names = [
    'english' => 'English',
    'ap' => 'Araling Panlipunan',
    'filipino' => 'Filipino', 
    'math' => 'Mathematics',
    'science' => 'Science'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Educator Dashboard - Manage Quizzes - Play2Review</title>
    
    <?php include('includes/educ_header.php'); ?>
    <style>
        .stat-card {
            transition: all 0.3s ease;
            border-left: 4px solid;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .stat-card-students { border-left-color: #0A5F38; }
        .stat-card-teachers { border-left-color: #1E7D4E; }
        .stat-card-feathers { border-left-color: #28a745; }
        .stat-card-potion { border-left-color: #17a2b8; }
        .stat-card-lives { border-left-color: #20c997; }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        
        .activity-item {
            border-left: 3px solid #0A5F38;
            padding-left: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
        }
        
        .teacher-activity-item {
            border-left: 3px solid #1E7D4E;
            padding-left: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: #f8f9fc;
            border-radius: 8px;
            padding: 12px;
        }
        
        .activity-item:hover, .teacher-activity-item:hover {
            transform: translateX(5px);
        }
        
        .activity-item:hover {
            background-color: #e9ecef;
        }
        
        .teacher-activity-item:hover {
            background-color: #e8f5e8;
        }
        
        .progress {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 5px;
            border: 1px solid #ddd;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.6s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
            font-weight: bold;
            min-width: 30px;
        }
        
        .bg-english { background-color: #0A5F38 !important; }
        .bg-ap { background-color: #1cc88a !important; }
        .bg-filipino { background-color: #f6c23e !important; }
        .bg-math { background-color: #36b9cc !important; }
        .bg-science { background-color: #1E7D4E !important; }
        
        .card-header {
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            color: white;
            font-weight: bold;
        }
        
        .teacher-card-header {
            background: linear-gradient(135deg, #1E7D4E 0%, #0A5F38 100%);
            color: white;
            font-weight: bold;
        }
        
        .dashboard-title {
            color: #0A5F38;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .subject-progress {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background: #f8f9fc;
        }
        
        .player-rank {
            font-weight: bold;
            color: #0A5F38;
        }
        
        .character-progress {
            margin-bottom: 15px;
        }
        
        .teacher-stats {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fc;
            border-radius: 8px;
            border-left: 4px solid #1E7D4E;
        }
        
        /* Custom small box styling */
        .small-box {
            border-radius: 0.25rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: block;
            margin-bottom: 20px;
            position: relative;
        }
        
        .small-box > .inner {
            padding: 10px;
        }
        
        .small-box > .small-box-footer {
            background: rgba(0, 0, 0, 0.1);
            color: rgba(255, 255, 255, 0.8);
            display: block;
            padding: 3px 0;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10;
        }
        
        .small-box h3 {
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0 0 10px 0;
            padding: 0;
            white-space: nowrap;
        }
        
        .small-box p {
            font-size: 1rem;
        }
        
        .small-box .icon {
            color: rgba(0, 0, 0, 0.15);
            z-index: 0;
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .small-box:hover .icon {
            font-size: 2.7rem;
            transition: all 0.3s ease;
        }

        .text-english { color: #0A5F38; }
        .text-ap { color: #1cc88a; }
        .text-filipino { color: #f6c23e; }
        .text-math { color: #36b9cc; }
        .text-science { color: #1E7D4E; }
        
        .status-active { color: #28a745; }
        .status-inactive { color: #6c757d; }
        .status-pending { color: #ffc107; }
        
        .badge-active { background-color: #28a745; }
        .badge-inactive { background-color: #6c757d; }
        .badge-pending { background-color: #ffc107; }

        .subject-tab {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .subject-tab:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
        }
        .subject-tab.active {
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .level-tab {
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 6px;
            margin-right: 3px;
            margin-bottom: 3px;
            font-size: 0.85rem;
        }
        .level-tab:hover {
            background-color: #e9ecef;
            transform: translateY(-1px);
        }
        .level-tab.active {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .quiz-card {
            border-left: 4px solid #0A5F38;
            transition: transform 0.2s ease;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        .quiz-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .level-badge {
            font-size: 0.8em;
        }
        .correct-answer {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .btn-primary {
            color: #fff;
            background-color: #0A5F38;
            border-color: #0A5F38;
            box-shadow: none;
        }
        .btn-outline-primary {
            color: #0A5F38;
            border-color: #0A5F38;
        }
        .table th {
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            color: white;
        }
        .bg-english { background-color: #0A5F38 !important; }
        .bg-ap { background-color: #1cc88a !important; }
        .bg-filipino { background-color: #f6c23e !important; }
        .bg-math { background-color: #36b9cc !important; }
        .bg-science { background-color: #1E7D4E !important; }
        .no-subjects-message {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }
        .no-subjects-message i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .bg-primary{
            background: #0A5F38 !important;
        }
        .card-header {
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            color: white;
            font-weight: bold;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #6c757d;
        }
        .teacher-badge {
            background: linear-gradient(135deg, #1E7D4E 0%, #0F4F2E 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            display: inline-block;
            margin-left: 5px;
        }
        .activity-log-link {
            margin-bottom: 15px;
            text-align: right;
        }
        .btn-view-logs {
            background: linear-gradient(135deg, #1E7D4E 0%, #0A5F38 100%);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .btn-view-logs:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            color: white;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include('includes/educ_topbar.php'); ?>
    <?php include('includes/educ_sidebar.php'); ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 dashboard-title">
                            Manage In-game Quizzes
                            <span class="teacher-badge">TEACHER</span>
                        </h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-tasks mr-1"></i>
                            Based on your handled subjects
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Manage Quizzes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Activity Log Link -->
                <div class="activity-log-link">
                    <a href="educ_audit_logs.php" class="btn-view-logs">
                        <i class="fas fa-history"></i> View My Activity Logs
                    </a>
                </div>

                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <?php if(!empty($handled_subjects)): ?>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                            <i class="fas fa-plus"></i> Add New Question
                        </button>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importCsvModal">
                            <i class="fas fa-file-csv"></i> Import CSV
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="subject_name" value="<?php echo $current_subject; ?>">
                            <button type="submit" name="download_template" class="btn btn-outline-secondary">
                                <i class="fas fa-download"></i> Download Template
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Success/Error Messages -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Subject Tabs -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0" style="color:white;"><i class="fas fa-book"></i> My Subjects</h5>
                    </div>
                    <div class="card-body">
                        <?php if(empty($handled_subjects)): ?>
                            <div class="no-subjects-message">
                                <i class="fas fa-book-open"></i>
                                <h4>No Subjects Assigned</h4>
                                <p>You haven't been assigned any subjects to manage yet.</p>
                                <p class="text-muted">Please contact the administrator to get subjects assigned to your account.</p>
                            </div>
                        <?php else: ?>
                            <!-- Level Filter Section -->
                            <div class="filter-section">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h6 class="mb-2"><i class="fas fa-filter"></i> Filter by Subject:</h6>
                                            <?php foreach ($handled_subjects as $subject): ?>
                                                <a href="?subject=<?php echo urlencode($subject); ?>&level=<?php echo $current_level; ?>" 
                                                   class="btn subject-tab <?php echo ($subject === $current_subject) ? 'active' : 'btn-outline-primary'; ?>">
                                                    <i class="fas fa-book"></i>
                                                    <?php echo $subject_names[$subject]; ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-2"><i class="fas fa-filter"></i> Filter by Level:</h6>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a href="?subject=<?php echo $current_subject; ?>&level=all" 
                                               class="btn level-tab <?php echo ($current_level === 'all') ? 'active' : 'btn-outline-secondary'; ?>">
                                                All Levels
                                            </a>
                                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                                <a href="?subject=<?php echo $current_subject; ?>&level=<?php echo $i; ?>" 
                                                   class="btn level-tab <?php echo ($current_level == $i) ? 'active' : 'btn-outline-info'; ?>">
                                                    Level <?php echo $i; ?>
                                                    <?php if (in_array($i, $available_levels)): ?>
                                                        <span class="badge bg-light text-dark ms-1">✓</span>
                                                    <?php endif; ?>
                                                </a>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Level Filter and Quiz Questions Table -->
                <?php if ($current_subject && !empty($handled_subjects)): ?>

                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0" style="color:white;">
                            <i class="fas fa-list"></i>
                            <?php echo $subject_names[$current_subject]; ?> Questions
                            <span class="badge bg-secondary"><?php echo count($quizzes); ?> questions</span>
                        </h5>
                        <div>
                            <span class="text-muted"  style="color:white !important;">Levels: </span>
                            <?php
                            $levels = array_unique(array_column($quizzes, 'quiz_level'));
                            sort($levels);
                            foreach ($levels as $level): ?>
                                <span class="badge bg-info level-badge">Level <?php echo $level; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($quizzes)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5>No questions found</h5>
                                <p class="text-muted">
                                    <?php if ($current_level === 'all'): ?>
                                        No questions found for <?php echo $subject_names[$current_subject]; ?>.
                                    <?php else: ?>
                                        No questions found for <?php echo $subject_names[$current_subject]; ?> Level <?php echo $current_level; ?>.
                                    <?php endif; ?>
                                </p>
                                <p class="text-muted">Click "Add New Question" to create your first quiz question.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table id="educQuizesTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Question</th>
                                            <th>Level</th>
                                            <th>Category</th>
                                            <th>Answers</th>
                                            <th>Correct</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($quizzes as $quiz): ?>
                                        <tr>
                                            <td><?php echo $quiz['id']; ?></td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars(substr($quiz['question'], 0, 80)); ?><?php echo strlen($quiz['question']) > 80 ? '...' : ''; ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">Level <?php echo $quiz['quiz_level']; ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($quiz['category'])): ?>
                                                    <span class="badge bg-info text-dark" style="font-size: 0.75rem; white-space: normal; max-width: 150px; display: inline-block;">
                                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($quiz['category']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary" style="font-size: 0.75rem;">
                                                        <i class="fas fa-question"></i> Not Set
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <div><strong>A:</strong> <?php echo htmlspecialchars(substr($quiz['answer_a'], 0, 30)); ?></div>
                                                    <div><strong>B:</strong> <?php echo htmlspecialchars(substr($quiz['answer_b'], 0, 30)); ?></div>
                                                    <div><strong>C:</strong> <?php echo htmlspecialchars(substr($quiz['answer_c'], 0, 30)); ?></div>
                                                    <div><strong>D:</strong> <?php echo htmlspecialchars(substr($quiz['answer_d'], 0, 30)); ?></div>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">Option <?php echo $quiz['correct_answer_number']; ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-warning btn-edit" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editQuizModal"
                                                            data-quiz='<?php echo htmlspecialchars(json_encode($quiz), ENT_QUOTES, 'UTF-8'); ?>'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-delete" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteQuizModal"
                                                            data-quiz-id="<?php echo $quiz['id']; ?>"
                                                            data-subject="<?php echo $current_subject; ?>"
                                                            data-question="<?php echo htmlspecialchars(substr($quiz['question'], 0, 50)); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Add Quiz Modal -->
        <?php if(!empty($handled_subjects)): ?>
        <div class="modal fade" id="addQuizModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Add New Quiz Question</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select name="subject_name" id="add-subject-select" class="form-control" required>
                                            <option value="">Select Subject</option>
                                            <?php foreach ($handled_subjects as $subject): ?>
                                                <option value="<?php echo $subject; ?>" <?php echo ($subject === $current_subject) ? 'selected' : ''; ?>>
                                                    <?php echo $subject_names[$subject]; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Quiz Level <span class="text-danger">*</span></label>
                                        <select name="quiz_level" class="form-control" required>
                                            <option value="">Select Level</option>
                                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                                <option value="<?php echo $i; ?>" <?php echo ($current_level == $i && $current_level !== 'all') ? 'selected' : ''; ?>>
                                                    Level <?php echo $i; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dynamic Category Containers -->
                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                
                                <!-- English Categories -->
                                <div id="add-english-category" class="category-container" style="display: none;">
                                    <select name="category" class="form-control category-select" disabled>
                                        <option value="">Select Category</option>
                                        <?php foreach (getCategoriesBySubject('english') as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Math Categories -->
                                <div id="add-math-category" class="category-container" style="display: none;">
                                    <select name="category" class="form-control category-select" disabled>
                                        <option value="">Select Category</option>
                                        <?php foreach (getCategoriesBySubject('math') as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Filipino Categories -->
                                <div id="add-filipino-category" class="category-container" style="display: none;">
                                    <select name="category" class="form-control category-select" disabled>
                                        <option value="">Select Category</option>
                                        <?php foreach (getCategoriesBySubject('filipino') as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- AP Categories -->
                                <div id="add-ap-category" class="category-container" style="display: none;">
                                    <select name="category" class="form-control category-select" disabled>
                                        <option value="">Select Category</option>
                                        <?php foreach (getCategoriesBySubject('ap') as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Science Categories -->
                                <div id="add-science-category" class="category-container" style="display: none;">
                                    <select name="category" class="form-control category-select" disabled>
                                        <option value="">Select Category</option>
                                        <?php foreach (getCategoriesBySubject('science') as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Question <span class="text-danger">*</span></label>
                                <textarea name="question" class="form-control" rows="3" placeholder="Enter the question..." required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Option A</label>
                                        <input type="text" name="answer_a" class="form-control" placeholder="Enter option A" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Option B</label>
                                        <input type="text" name="answer_b" class="form-control" placeholder="Enter option B" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Option C</label>
                                        <input type="text" name="answer_c" class="form-control" placeholder="Enter option C" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Option D</label>
                                        <input type="text" name="answer_d" class="form-control" placeholder="Enter option D" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Correct Answer</label>
                                <select name="correct_answer" class="form-control" required>
                                    <option value="">Select Correct Option</option>
                                    <option value="1">Option A</option>
                                    <option value="2">Option B</option>
                                    <option value="3">Option C</option>
                                    <option value="4">Option D</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_quiz" class="btn btn-primary">Add Question</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Quiz Modal -->
        <div class="modal fade" id="editQuizModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">Edit Quiz Question</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="quiz_id" id="edit_quiz_id">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select name="subject_name" id="edit-subject-select" class="form-control" required>
                                            <?php foreach ($handled_subjects as $subject): ?>
                                                <option value="<?php echo $subject; ?>"><?php echo $subject_names[$subject]; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Quiz Level <span class="text-danger">*</span></label>
                                        <select name="quiz_level" id="edit_quiz_level" class="form-control" required>
                                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                                <option value="<?php echo $i; ?>">Level <?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dynamic Category Containers for Edit -->
                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                
                                <!-- English Categories -->
                                <div id="edit-english-category" class="category-container" style="display: none;">
                                    <select name="category" class="form-control category-select" disabled>
                                        <option value="">Select Category</option>
                                        <?php foreach (getCategoriesBySubject('english') as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Math Categories -->
                                <div id="edit-math-category" class="category-container" style="display: none;">
                                    <select name="category" class="form-control category-select" disabled>
                                        <option value="">Select Category</option>
                                        <?php foreach (getCategoriesBySubject('math') as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Filipino Categories -->
                                <div id="edit-filipino-category" class="category-container" style="display: none;">
                                    <select name="category" class="form-control category-select" disabled>
                                        <option value="">Select Category</option>
                                        <?php foreach (getCategoriesBySubject('filipino') as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- AP Categories -->
                                <div id="edit-ap-category" class="category-container" style="display: none;">
                                    <select name="category" class="form-control category-select" disabled>
                                        <option value="">Select Category</option>
                                        <?php foreach (getCategoriesBySubject('ap') as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Science Categories -->
                                <div id="edit-science-category" class="category-container" style="display: none;">
                                    <select name="category" class="form-control category-select" disabled>
                                        <option value="">Select Category</option>
                                        <?php foreach (getCategoriesBySubject('science') as $key => $label): ?>
                                            <option value="<?php echo htmlspecialchars($key); ?>">
                                                <?php echo htmlspecialchars($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Question <span class="text-danger">*</span></label>
                                <textarea name="question" id="edit_question" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Option A</label>
                                        <input type="text" name="answer_a" id="edit_answer_a" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Option B</label>
                                        <input type="text" name="answer_b" id="edit_answer_b" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Option C</label>
                                        <input type="text" name="answer_c" id="edit_answer_c" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Option D</label>
                                        <input type="text" name="answer_d" id="edit_answer_d" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Correct Answer</label>
                                <select name="correct_answer" id="edit_correct_answer" class="form-control" required>
                                    <option value="1">Option A</option>
                                    <option value="2">Option B</option>
                                    <option value="3">Option C</option>
                                    <option value="4">Option D</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="edit_quiz" class="btn btn-warning">Update Question</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Import CSV Modal -->
        <div class="modal fade" id="importCsvModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-file-csv"></i> Import Questions from CSV</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <strong>How to use:</strong>
                                <ol class="mb-0 mt-1">
                                    <li>Click <strong>Download Template</strong> to get the Excel file (.xlsx)</li>
                                    <li>Fill in your questions — use the dropdowns for Category, Level, and Correct Answer</li>
                                    <li>In Excel: <strong>File → Save As → CSV UTF-8 (Comma delimited) (.csv)</strong></li>
                                    <li>Upload the saved CSV file here</li>
                                </ol>
                                <div class="mt-2 text-warning"><i class="fas fa-exclamation-triangle"></i> <strong>Do NOT upload the .xlsx file directly — save as CSV first.</strong></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <select name="import_subject" class="form-control" required>
                                    <?php foreach ($handled_subjects as $subject): ?>
                                        <option value="<?php echo $subject; ?>" <?php echo ($subject === $current_subject) ? 'selected' : ''; ?>>
                                            <?php echo $subject_names[$subject]; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">CSV File <span class="text-danger">*</span></label>
                                <input type="file" name="csv_file" class="form-control" accept=".csv,.xlsx,.xls" required>
                                <small class="text-muted">Upload the CSV file saved from the Excel template. In Excel: File → Save As → CSV UTF-8 (Comma delimited)</small>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Rows with missing questions, invalid levels (1–10), or invalid correct answer (1–4) will be skipped.
                            </small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="import_csv" class="btn btn-success">
                                <i class="fas fa-upload"></i> Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteQuizModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Confirm Delete</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="quiz_id" id="delete_quiz_id">
                            <input type="hidden" name="subject_name" id="delete_subject_name">
                            <p>Are you sure you want to delete this quiz question?</p>
                            <p class="fw-bold" id="delete_question_text"></p>
                            <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="delete_quiz" class="btn btn-danger">Delete Question</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include('includes/footer.php'); ?>
    <?php include('includes/educ_modals.php'); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ==================== CATEGORY DROPDOWN SWITCHING ====================
        // Function to show/hide category dropdowns based on selected subject
        function updateCategoryDropdown(subjectSelectId, containerPrefix) {
            const subjectSelect = document.getElementById(subjectSelectId);
            if (!subjectSelect) return;
            
            const selectedSubject = subjectSelect.value;
            
            // Hide all category containers and disable their selects
            document.querySelectorAll('.category-container').forEach(container => {
                container.style.display = 'none';
                const select = container.querySelector('.category-select');
                if (select) {
                    select.disabled = true;
                    select.removeAttribute('required');
                }
            });
            
            // Show the selected subject's category container and enable its select
            if (selectedSubject) {
                const targetContainer = document.getElementById(`${containerPrefix}-${selectedSubject}-category`);
                if (targetContainer) {
                    targetContainer.style.display = 'block';
                    const select = targetContainer.querySelector('.category-select');
                    if (select) {
                        select.disabled = false;
                        select.setAttribute('required', 'required');
                    }
                }
            }
        }
        
        // Add event listeners for subject dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            // Add Quiz Modal - subject change
            const addSubjectSelect = document.getElementById('add-subject-select');
            if (addSubjectSelect) {
                addSubjectSelect.addEventListener('change', function() {
                    updateCategoryDropdown('add-subject-select', 'add');
                });
                // Trigger on page load if subject is pre-selected
                updateCategoryDropdown('add-subject-select', 'add');
            }
            
            // Edit Quiz Modal - subject change
            const editSubjectSelect = document.getElementById('edit-subject-select');
            if (editSubjectSelect) {
                editSubjectSelect.addEventListener('change', function() {
                    updateCategoryDropdown('edit-subject-select', 'edit');
                });
            }
        });
        
        // ==================== EDIT BUTTON FUNCTIONALITY ====================
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const quiz = JSON.parse(this.dataset.quiz);
                document.getElementById('edit_quiz_id').value = quiz.id;
                document.getElementById('edit-subject-select').value = quiz.subject_name;
                document.getElementById('edit_quiz_level').value = quiz.quiz_level;
                document.getElementById('edit_question').value = quiz.question;
                document.getElementById('edit_answer_a').value = quiz.answer_a;
                document.getElementById('edit_answer_b').value = quiz.answer_b;
                document.getElementById('edit_answer_c').value = quiz.answer_c;
                document.getElementById('edit_answer_d').value = quiz.answer_d;
                document.getElementById('edit_correct_answer').value = quiz.correct_answer_number;
                
                // Update category dropdown for the selected subject
                updateCategoryDropdown('edit-subject-select', 'edit');
                
                // Set the category value if it exists
                setTimeout(() => {
                    const categoryContainer = document.getElementById(`edit-${quiz.subject_name}-category`);
                    if (categoryContainer) {
                        const categorySelect = categoryContainer.querySelector('.category-select');
                        if (categorySelect && quiz.category) {
                            categorySelect.value = quiz.category;
                        }
                    }
                }, 50);
            });
        });

        // ==================== DELETE BUTTON FUNCTIONALITY ====================
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_quiz_id').value = this.dataset.quizId;
                document.getElementById('delete_subject_name').value = this.dataset.subject;
                document.getElementById('delete_question_text').textContent = this.dataset.question;
            });
        });
    </script>
</body>
</html>        // Auto-select current level in add modal
        document.addEventListener('DOMContentLoaded', function() {
            const currentLevel = '<?php echo $current_level; ?>';
            if (currentLevel !== 'all') {
                const levelSelect = document.querySelector('select[name="quiz_level"]');
                if (levelSelect) {
                    levelSelect.value = currentLevel;
                }
            }
        });
    </script>
</div>
<script>
$(document).ready(function() {
    $('#educQuizesTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [],
        "columnDefs": [{ "orderable": false, "targets": [-1] }]
    });
});
</script>
</body>
</html>