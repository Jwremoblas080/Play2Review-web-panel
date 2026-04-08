<?php
require_once 'configurations/configurations.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$response = ['success' => false, 'message' => 'Unknown error', 'already_submitted' => false];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['username']) || empty($_POST['username'])) {
        $response['message'] = 'Username is required';
        echo json_encode($response);
        exit;
    }
    
    $username = mysqli_real_escape_string($con, $_POST['username']);
    
    try {
        // First get user_id
        $userQuery = "SELECT id, student_id FROM users WHERE username = '$username'";
        $userResult = mysqli_query($con, $userQuery);
        
        if ($userResult && mysqli_num_rows($userResult) > 0) {
            $user = mysqli_fetch_assoc($userResult);
            $user_id = $user['id'];
            
            // Check if survey already submitted
            $query = "SELECT id FROM surveys WHERE user_id = '$user_id' LIMIT 1";
            $result = mysqli_query($con, $query);
            
            if ($result) {
                $response['success'] = true;
                $response['already_submitted'] = (mysqli_num_rows($result) > 0);
                $response['message'] = $response['already_submitted'] ? 'Survey already submitted' : 'Survey not submitted yet';
            } else {
                $response['message'] = 'Database query failed: ' . mysqli_error($con);
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