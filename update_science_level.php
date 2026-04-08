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

// Check if required parameters are provided
if (!isset($_POST['username']) || empty($_POST['username'])) {
    echo json_encode(['success' => false, 'message' => 'Username is required']);
    exit;
}

$username = trim($_POST['username']);
$science_biology_level       = isset($_POST['science_biology_level'])       ? (int)$_POST['science_biology_level']       : 0;
$science_chemistry_level     = isset($_POST['science_chemistry_level'])     ? (int)$_POST['science_chemistry_level']     : 0;
$science_physics_level       = isset($_POST['science_physics_level'])       ? (int)$_POST['science_physics_level']       : 0;
$science_earthscience_level  = isset($_POST['science_earthscience_level'])  ? (int)$_POST['science_earthscience_level']  : 0;
$science_investigation_level = isset($_POST['science_investigation_level']) ? (int)$_POST['science_investigation_level'] : 0;

try {
    $checkStmt = $conn->prepare("SELECT username FROM users WHERE username = :username");
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET
        science_biology_level       = GREATEST(science_biology_level,       :biology),
        science_chemistry_level     = GREATEST(science_chemistry_level,     :chemistry),
        science_physics_level       = GREATEST(science_physics_level,       :physics),
        science_earthscience_level  = GREATEST(science_earthscience_level,  :earthscience),
        science_investigation_level = GREATEST(science_investigation_level, :investigation)
        WHERE username = :username");
    $stmt->bindParam(':biology',       $science_biology_level,       PDO::PARAM_INT);
    $stmt->bindParam(':chemistry',     $science_chemistry_level,     PDO::PARAM_INT);
    $stmt->bindParam(':physics',       $science_physics_level,       PDO::PARAM_INT);
    $stmt->bindParam(':earthscience',  $science_earthscience_level,  PDO::PARAM_INT);
    $stmt->bindParam(':investigation', $science_investigation_level, PDO::PARAM_INT);
    $stmt->bindParam(':username',      $username);
    
    if ($stmt->execute()) {
        $response = [
            'success' => true, 
            'message' => 'Level updated successfully'
        ];
        echo json_encode($response);
    } else {
        $response = ['success' => false, 'message' => 'Failed to update level'];
        echo json_encode($response);
    }
    
} catch (Exception $e) {
    error_log("Database error in update_science_level.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    echo json_encode($response);
}
?>