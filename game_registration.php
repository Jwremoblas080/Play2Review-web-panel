<?php
include('configurations/configurations.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if JSON decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit();
}

// Validate required fields
$required_fields = ['playerName', 'gradeLevel', 'username', 'password'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Missing required field: $field"
        ]);
        exit();
    }
}

// Sanitize input data
$playerName = trim($data['playerName']);
$gradeLevel = trim($data['gradeLevel']);
$username = trim($data['username']);
$password = $data['password'];

// Validate password length
if (strlen($password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be at least 6 characters'
    ]);
    exit();
}

try {
    // Check if username already exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username already exists'
        ]);
        exit();
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (player_name, student_id, username, password) 
                           VALUES (:playerName, :gradeLevel, :username, :password)");
    $stmt->bindParam(':playerName', $playerName);
    $stmt->bindParam(':gradeLevel', $gradeLevel);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashedPassword);
    
    if ($stmt->execute()) {
        // Get the ID of the newly inserted user
        $new_user_id = $conn->lastInsertId();
        
        // Insert into activity log
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity_description) VALUES (:user_id, :activity_description)");
        $log_stmt->bindParam(':user_id', $new_user_id);
        $activity_description = "New user registered";
        $log_stmt->bindParam(':activity_description', $activity_description);
        $log_stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed'
        ]);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>