<?php
/**
 * API Endpoint: Get Quiz Questions by Category
 * 
 * Fetches quiz questions filtered by subject and category KEY
 * 
 * POST Parameters:
 * - subject_name: Subject key (english, math, filipino, ap, science)
 * - category: Category KEY (e.g., "grammar", "algebra") ✅ NOT LABEL
 * - quiz_level: Level 1-10 (optional, if not provided returns all levels)
 * 
 * Returns JSON with questions array
 */

include('configurations/configurations.php');
require_once('admin/category-config.php'); // ✅ Include category validation

// Set headers
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Initialize response
$response = new stdClass();
$response->success = false;
$response->message = "Initializing";
$response->questions = array();
$response->category = "";
$response->subject = "";

try {
    // Check database connection
    if (!isset($conn)) {
        throw new Exception("Database connection failed");
    }
    
    // Get POST parameters
    $subject_name = $_POST['subject_name'] ?? '';
    $categoryKey = $_POST['category'] ?? ''; // ✅ This should be a KEY, not label
    $quiz_level = isset($_POST['quiz_level']) ? (int)$_POST['quiz_level'] : null;
    
    // Validate required parameters
    if (empty($subject_name)) {
        throw new Exception("Subject name is required");
    }
    
    if (empty($categoryKey)) {
        throw new Exception("Category is required");
    }
    
    // Validate subject name
    $valid_subjects = ['english', 'ap', 'filipino', 'math', 'science'];
    if (!in_array(strtolower($subject_name), $valid_subjects)) {
        throw new Exception("Invalid subject name: " . $subject_name);
    }
    
    // ✅ CRITICAL: Validate category KEY using config
    if (!isValidCategory($subject_name, $categoryKey)) {
        throw new Exception("Invalid category key: " . $categoryKey . " for subject: " . $subject_name);
    }
    
    // Build SQL query - use KEY for database lookup
    $sql = "SELECT id, teacher_id, subject_name, category, level as quiz_level, question, 
                   answer_a, answer_b, answer_c, answer_d, correct_answer_number 
            FROM quizes 
            WHERE subject_name = :subject_name 
            AND category = :category";
    
    // Add level filter if provided
    if ($quiz_level !== null) {
        $sql .= " AND level = :quiz_level";
    }
    
    // Randomize question order
    $sql .= " ORDER BY RAND()";
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':subject_name', $subject_name);
    $stmt->bindParam(':category', $categoryKey); // ✅ Bind the KEY
    
    if ($quiz_level !== null) {
        $stmt->bindParam(':quiz_level', $quiz_level, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    // Fetch questions
    $questions = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $question = new stdClass();
        $question->id = (int)$row['id'];
        $question->teacher_id = (int)$row['teacher_id'];
        $question->subject_name = $row['subject_name'];
        $question->category = $row['category'];
        $question->quiz_level = (int)$row['quiz_level'];
        $question->question = $row['question'];
        $question->answer_a = $row['answer_a'];
        $question->answer_b = $row['answer_b'];
        $question->answer_c = $row['answer_c'];
        $question->answer_d = $row['answer_d'];
        $question->correct_answer_number = (int)$row['correct_answer_number'];
        
        $questions[] = $question;
    }
    
    // Set response
    if (count($questions) > 0) {
        $response->success = true;
        $response->questions = $questions;
        $response->category_key = $categoryKey; // ✅ Return KEY
        $response->category_label = getCategoryLabel($subject_name, $categoryKey); // ✅ Return LABEL for UI
        $response->subject = $subject_name;
        $response->total_questions = count($questions);
        
        if ($quiz_level !== null) {
            $response->message = "Successfully loaded " . count($questions) . " questions for " . 
                               $subject_name . " - " . $categoryKey . " (Level " . $quiz_level . ")";
        } else {
            $response->message = "Successfully loaded " . count($questions) . " questions for " . 
                               $subject_name . " - " . $categoryKey . " (All levels)";
        }
    } else {
        $response->success = false;
        $response->category_key = $categoryKey;
        $response->category_label = getCategoryLabel($subject_name, $categoryKey);
        $response->subject = $subject_name;
        
        if ($quiz_level !== null) {
            $response->message = "No questions found for " . $subject_name . " - " . 
                               $categoryKey . " (Level " . $quiz_level . ")";
        } else {
            $response->message = "No questions found for " . $subject_name . " - " . $categoryKey;
        }
    }
    
} catch (Exception $e) {
    $response->success = false;
    $response->message = "Error: " . $e->getMessage();
}

// Clean output buffer and send JSON
if (ob_get_length()) ob_clean();
echo json_encode($response);
exit();
?>
