<?php
/**
 * API Endpoint: Log Student Answer
 * 
 * This endpoint records when a student answers a question
 * Enables category-level progress tracking
 * 
 * POST Parameters:
 * - user_id: Student's user ID
 * - quiz_id: Question ID from quizes table
 * - is_correct: 1 for correct, 0 for incorrect
 * 
 * Returns JSON:
 * - success: true/false
 * - message: Status message
 * - data: Additional info (optional)
 */

header('Content-Type: application/json');
require_once('configurations/configurations.php');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST requests are allowed'
    ]);
    exit;
}

// Get POST data
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
$is_correct = isset($_POST['is_correct']) ? intval($_POST['is_correct']) : 0;

// Validate inputs
if ($user_id <= 0 || $quiz_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user_id or quiz_id'
    ]);
    exit;
}

// Check if student_answers table exists
$table_check = mysqli_query($con, "SHOW TABLES LIKE 'student_answers'");
if (mysqli_num_rows($table_check) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Student answers tracking not enabled. Please run migration first.'
    ]);
    exit;
}

// Get quiz details (subject, category, level)
$quiz_query = "SELECT subject_name, category, level FROM quizes WHERE id = $quiz_id";
$quiz_result = mysqli_query($con, $quiz_query);

if (!$quiz_result || mysqli_num_rows($quiz_result) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Quiz not found'
    ]);
    exit;
}

$quiz = mysqli_fetch_assoc($quiz_result);

// Insert or update answer record
$insert_query = "INSERT INTO student_answers 
                 (user_id, quiz_id, subject_name, category, level, is_correct, answered_at) 
                 VALUES 
                 ($user_id, $quiz_id, '" . mysqli_real_escape_string($con, $quiz['subject_name']) . "', 
                  '" . mysqli_real_escape_string($con, $quiz['category']) . "', 
                  " . intval($quiz['level']) . ", 
                  $is_correct, 
                  NOW())
                 ON DUPLICATE KEY UPDATE 
                 is_correct = $is_correct,
                 answered_at = NOW()";

if (mysqli_query($con, $insert_query)) {
    // Get category progress stats
    $stats_query = "SELECT 
                        COUNT(*) as total_answered,
                        SUM(is_correct) as total_correct
                    FROM student_answers 
                    WHERE user_id = $user_id 
                    AND subject_name = '" . mysqli_real_escape_string($con, $quiz['subject_name']) . "'
                    AND category = '" . mysqli_real_escape_string($con, $quiz['category']) . "'";
    
    $stats_result = mysqli_query($con, $stats_query);
    $stats = mysqli_fetch_assoc($stats_result);
    
    echo json_encode([
        'success' => true,
        'message' => 'Answer logged successfully',
        'data' => [
            'subject' => $quiz['subject_name'],
            'category' => $quiz['category'],
            'level' => $quiz['level'],
            'is_correct' => $is_correct == 1,
            'category_stats' => [
                'total_answered' => intval($stats['total_answered']),
                'total_correct' => intval($stats['total_correct']),
                'accuracy' => $stats['total_answered'] > 0 
                    ? round(($stats['total_correct'] / $stats['total_answered']) * 100, 1) 
                    : 0
            ]
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to log answer: ' . mysqli_error($con)
    ]);
}

mysqli_close($con);
?>
