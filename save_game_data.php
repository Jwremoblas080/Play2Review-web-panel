<?php
include('configurations/configurations.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get raw JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Debug: Log received data
error_log("Received raw JSON: " . $json);
error_log("Decoded data: " . print_r($data, true));

// Check if data is valid
if (!$data || !isset($data['username'])) {
    $response = array(
        "success" => false,
        "message" => "Invalid or missing data",
        "received_data" => $data
    );
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// TRIM ALL VALUES to remove spaces and invisible characters
$username = trim($data['username']);
$lives = isset($data['lives']) ? intval(trim($data['lives'])) : 3;
$feathers = isset($data['feathers']) ? intval(trim($data['feathers'])) : 0;
$selected_character = isset($data['selected_character']) ? trim($data['selected_character']) : 'Akio';
$volume = isset($data['volume']) ? floatval(trim($data['volume'])) : 1.0; // Get volume, default 1.0

// Debug: Log the trimmed values
error_log("Trimmed values - username: '$username', lives: $lives, feathers: $feathers, character: '$selected_character', volume: $volume");

// Check if username is empty after trimming
if (empty($username)) {
    $response = array(
        "success" => false,
        "message" => "Username is empty after trimming",
        "original_username" => $data['username']
    );
    echo json_encode($response);
    exit;
}

try {
    // Check if user exists first (PDO version)
    $check_sql = "SELECT username FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$username]);
    
    if ($check_stmt->rowCount() === 0) {
        $response = array(
            "success" => false,
            "message" => "User does not exist in database",
            "username" => $username
        );
        echo json_encode($response);
        exit;
    }

    // Update user data in database (PDO version) - ADD VOLUME COLUMN
    $sql = "UPDATE users SET lives = ?, feathers = ?, selected_character = ?, volume = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    
    // Execute with parameters as array (ADD VOLUME)
    $success = $stmt->execute([$lives, $feathers, $selected_character, $volume, $username]);
    
    if ($success) {
        // Check if any rows were actually updated
        if ($stmt->rowCount() > 0) {
            $response = array(
                "success" => true,
                "message" => "Game data saved successfully",
                "rows_affected" => $stmt->rowCount(),
                "data" => array(
                    "username" => $username,
                    "lives" => $lives,
                    "feathers" => $feathers,
                    "selected_character" => $selected_character,
                    "volume" => $volume
                )
            );
        } else {
            // Check if data is already the same
            $check_current_sql = "SELECT lives, feathers, selected_character, volume FROM users WHERE username = ?";
            $check_current_stmt = $conn->prepare($check_current_sql);
            $check_current_stmt->execute([$username]);
            $current_data = $check_current_stmt->fetch(PDO::FETCH_ASSOC);
            
            $response = array(
                "success" => false,
                "message" => "No rows updated. Data may already be the same.",
                "current_data" => $current_data,
                "new_data" => array(
                    "lives" => $lives,
                    "feathers" => $feathers,
                    "selected_character" => $selected_character,
                    "volume" => $volume
                ),
                "username" => $username
            );
        }
    } else {
        $response = array(
            "success" => false,
            "message" => "Error saving game data"
        );
    }
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    $response = array(
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    );
    echo json_encode($response);
}

// PDO doesn't need manual closing like MySQLi, but you can nullify the connection
$conn = null;
?>