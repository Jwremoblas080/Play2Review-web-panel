<?php
include('configurations/configurations.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Get username from POST data
$username = $_POST['username'] ?? '';

if (empty($username)) {
    $response = array(
        "success" => false,
        "message" => "Username is required"
    );
    echo json_encode($response);
    exit;
}

try {
    // Get user data from database - ADD player_name and grade_level to the SELECT query
    $sql = "SELECT lives, feathers, selected_character, volume, player_name, student_id, username, potion FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $response = array(
            "success" => true,
            "message" => "Game data loaded successfully",
            "data" => array(
                "lives" => (int)$userData['lives'],
                "feathers" => (int)$userData['feathers'],
                "selected_character" => $userData['selected_character'],
                "volume" => (float)$userData['volume'],
                "player_name" => $userData['player_name'], // ADD THIS
                "grade_level" => $userData['student_id'], 
                "username" => $userData['username'], 
                "potion" => $userData['potion'] 
            )
        );
    } else {
        $response = array(
            "success" => false,
            "message" => "User not found"
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

$conn = null;
?>