<?php
include('configurations/configurations.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if data is valid
if (!$data || !isset($data['current_username'])) {
    $response = array(
        "success" => false,
        "message" => "Invalid or missing data"
    );
    echo json_encode($response);
    exit;
}

$current_username = trim($data['current_username']);
$full_name = isset($data['full_name']) ? trim($data['full_name']) : '';
$student_id = isset($data['grade_level']) ? trim($data['grade_level']) : '';
$new_password = isset($data['new_password']) ? trim($data['new_password']) : '';

try {
    // Build SQL query based on what fields are provided
    if (!empty($new_password)) {
        // Update with password (hash the password)
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET player_name = ?, student_id = ?, password = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$full_name, $student_id, $hashed_password, $current_username]);
    } else {
        // Update without password
        $sql = "UPDATE users SET player_name = ?, student_id = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$full_name, $student_id, $current_username]);
    }

    if ($stmt->rowCount() > 0) {
        $response = array(
            "success" => true,
            "message" => "Profile updated successfully"
        );
    } else {
        $response = array(
            "success" => false,
            "message" => "No changes made or user not found"
        );
    }
    
} catch (PDOException $e) {
    $response = array(
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    );
}

echo json_encode($response);
$conn = null;
?>