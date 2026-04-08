<?php
include('configurations/configurations.php');

// Set headers FIRST - no output before this
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Simple response first
$response = new stdClass();
$response->success = false;
$response->message = "Initializing";
$response->questions = array();

try {
    // Check if connection exists
    if (!isset($conn)) {
        throw new Exception("Database connection failed");
    }
    
    // Get parameters
    $subject_name = $_POST['subject_name'] ?? '';
    $quiz_level = $_POST['quiz_level'] ?? 1;
    $category = $_POST['category'] ?? ''; // NEW: Category filter
    
    if (empty($subject_name)) {
        throw new Exception("Subject name is required");
    }
    
    // Validate subject name
    $valid_subjects = ['english', 'ap', 'filipino', 'math', 'science'];
    if (!in_array(strtolower($subject_name), $valid_subjects)) {
        throw new Exception("Invalid subject name");
    }
    
    // Prepare SQL query with optional category filter
    $sql = "SELECT id, teacher_id, subject_name, quiz_level, question, 
                   answer_a, answer_b, answer_c, answer_d, correct_answer_number, category 
            FROM quizes 
            WHERE subject_name = :subject_name 
            AND quiz_level = :quiz_level";
    
    // Add category filter if provided
    if (!empty($category)) {
        $sql .= " AND category = :category";
    }
    
    $sql .= " ORDER BY RAND()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':subject_name', $subject_name);
    $stmt->bindParam(':quiz_level', $quiz_level, PDO::PARAM_INT);
    
    // Bind category parameter if provided
    if (!empty($category)) {
        $stmt->bindParam(':category', $category);
    }
    
    $stmt->execute();
    
    $questions = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $question = new stdClass();
        $question->id = (int)$row['id'];
        $question->teacher_id = (int)$row['teacher_id'];
        $question->subject_name = $row['subject_name'];
        $question->quiz_level = (int)$row['quiz_level'];
        $question->question = $row['question'];
        $question->answer_a = $row['answer_a'];
        $question->answer_b = $row['answer_b'];
        $question->answer_c = $row['answer_c'];
        $question->answer_d = $row['answer_d'];
        $question->correct_answer_number = (int)$row['correct_answer_number'];
        $question->category = $row['category'] ?? ''; // NEW: Include category in response
        
        $questions[] = $question;
    }
    
    if (count($questions) > 0) {
        $response->success = true;
        $response->questions = $questions;
        $response->message = "Successfully loaded " . count($questions) . " questions";
        $response->category = $category; // NEW: Echo back the category filter
    } else {
        $response->success = false;
        $categoryMsg = !empty($category) ? " in category '{$category}'" : "";
        $response->message = "No questions found for " . $subject_name . " level " . $quiz_level . $categoryMsg;
    }
    
} catch (Exception $e) {
    $response->success = false;
    $response->message = $e->getMessage();
}

// Clean any output and send JSON
if (ob_get_length()) ob_clean();
echo json_encode($response);
exit();
?>