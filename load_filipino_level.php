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
            filipino_gramatika_level, filipino_panitikan_level, filipino_paguunawa_level,
            filipino_talasalitaan_level, filipino_wika_level
            FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $user_id = $row['id'];
        
        // Insert activity log
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity_description) VALUES (:user_id, :activity_description)");
        $log_stmt->bindParam(':user_id', $user_id);
        $activity_description = "Started Playing Subject Filipino";
        $log_stmt->bindParam(':activity_description', $activity_description);
        $log_stmt->execute();

        $response = [
            'success' => true, 
            'message' => 'Level loaded successfully',
            'filipino_gramatika_level'    => (int)$row['filipino_gramatika_level'],
            'filipino_panitikan_level'    => (int)$row['filipino_panitikan_level'],
            'filipino_paguunawa_level'    => (int)$row['filipino_paguunawa_level'],
            'filipino_talasalitaan_level' => (int)$row['filipino_talasalitaan_level'],
            'filipino_wika_level'         => (int)$row['filipino_wika_level'],
        ];
        echo json_encode($response);
    } else {
        $response = ['success' => false, 'message' => 'User not found'];
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    error_log("Database error in load_filipino_level.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    echo json_encode($response);
}

// PDO doesn't need close statements or connection like MySQLi
?>