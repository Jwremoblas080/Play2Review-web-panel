<?php
require_once 'configurations/configurations.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

$response = ['success' => false, 'message' => 'Unknown error', 'user_id' => -1];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['username']) || empty($_POST['username'])) {
        $response['message'] = 'Username is required';
        echo json_encode($response);
        exit;
    }
    
    $username = mysqli_real_escape_string($con, $_POST['username']);
    
    try {
        $query = "SELECT id, student_id FROM users WHERE username = '$username' LIMIT 1";
        $result = mysqli_query($con, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $response['success'] = true;
            $response['user_id'] = (int)$user['id'];
            $response['message'] = 'User ID retrieved successfully';
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