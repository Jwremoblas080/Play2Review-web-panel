<?php
include('configurations/configurations.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Check if username is provided
if (!isset($_POST['username']) || empty($_POST['username'])) {
    echo json_encode(['success' => false, 'message' => 'Username is required']);
    exit;
}

$username = trim($_POST['username']);

try {
    // Use PDO syntax instead of MySQLi
    $stmt = $conn->prepare("SELECT id,
            science_biology_level, science_chemistry_level, science_physics_level,
            science_earthscience_level, science_investigation_level
            FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $user_id = $row['id'];
        
        // Insert activity log
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity_description) VALUES (:user_id, :activity_description)");
        $log_stmt->bindParam(':user_id', $user_id);
        $activity_description = "Started Playing Subject Science";
        $log_stmt->bindParam(':activity_description', $activity_description);
        $log_stmt->execute();

        $response = [
            'success' => true, 
            'message' => 'Level loaded successfully',
            'science_biology_level'      => (int)$row['science_biology_level'],
            'science_chemistry_level'    => (int)$row['science_chemistry_level'],
            'science_physics_level'      => (int)$row['science_physics_level'],
            'science_earthscience_level' => (int)$row['science_earthscience_level'],
            'science_investigation_level'=> (int)$row['science_investigation_level'],
        ];
        echo json_encode($response);
    } else {
        $response = ['success' => false, 'message' => 'User not found'];
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    error_log("Database error in load_science_level.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    echo json_encode($response);
}

// PDO doesn't need close statements or connection like MySQLi
?>