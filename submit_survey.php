<?php
require_once 'configurations/configurations.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$response = ['success' => false, 'message' => 'Unknown error'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['username']) || empty($_POST['username'])) {
        $response['message'] = 'Username is required';
        echo json_encode($response);
        exit;
    }
    
    $username = mysqli_real_escape_string($con, $_POST['username']);
    
    try {
        // First get user_id and student_id
        $userQuery = "SELECT id, student_id FROM users WHERE username = '$username' LIMIT 1";
        $userResult = mysqli_query($con, $userQuery);
        
        if ($userResult && mysqli_num_rows($userResult) > 0) {
            $user = mysqli_fetch_assoc($userResult);
            $user_id = $user['id'];
            $student_id = $user['student_id'];
            
            // Check if survey already exists for this user
            $checkQuery = "SELECT id FROM surveys WHERE user_id = '$user_id' LIMIT 1";
            $checkResult = mysqli_query($con, $checkQuery);
            
            if ($checkResult && mysqli_num_rows($checkResult) > 0) {
                $response['message'] = 'Survey already submitted for this user';
                echo json_encode($response);
                exit;
            }
            
            // Get questions and answers
            $questions_data = [];
            $total_yes = 0;
            $total_no = 0;
            
            // Check for questions (up to 10)
            for ($i = 1; $i <= 10; $i++) {
                $answer_field = "question_" . $i;
                $question_text_field = "question_text_" . $i;
                
                if (isset($_POST[$answer_field]) && isset($_POST[$question_text_field])) {
                    $answer = $_POST[$answer_field] == 1 ? 'yes' : 'no';
                    $question_text = mysqli_real_escape_string($con, $_POST[$question_text_field]);
                    
                    if ($answer === 'yes' || $answer === 'no') {
                        $questions_data[] = [
                            'question_number' => $i,
                            'question_text' => $question_text,
                            'answer' => $answer
                        ];
                        
                        if ($answer === 'yes') {
                            $total_yes++;
                        } else {
                            $total_no++;
                        }
                    }
                }
            }
            
            // Validate that we have questions
            if (empty($questions_data)) {
                $response['message'] = 'No valid questions received';
                echo json_encode($response);
                exit;
            }
            
            $feedback = isset($_POST['feedback']) ? mysqli_real_escape_string($con, $_POST['feedback']) : 'Survey completed via game';
            
            // Insert each question separately
            $success_count = 0;
            foreach ($questions_data as $question) {
                $query = "INSERT INTO surveys (
                    user_id, student_id, 
                    question_text, answer, question_number, feedback
                ) VALUES (
                    '$user_id', '$student_id',
                    '{$question['question_text']}', '{$question['answer']}', '{$question['question_number']}', '$feedback'
                )";
                
                if (mysqli_query($con, $query)) {
                    $success_count++;
                } else {
                    $response['message'] = 'Database error inserting question ' . $question['question_number'] . ': ' . mysqli_error($con);
                    echo json_encode($response);
                    exit;
                }
            }
            
            if ($success_count == count($questions_data)) {
                $response['success'] = true;
                $response['message'] = 'Survey submitted successfully with ' . $success_count . ' questions';
                $response['total_yes'] = $total_yes;
                $response['total_no'] = $total_no;
            }
            
        } else {
            $response['message'] = 'User not found';
        }
    } catch (Exception $e) {
        $response['message'] = 'Server error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>