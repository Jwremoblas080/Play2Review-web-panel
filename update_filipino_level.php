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
$filipino_gramatika_level    = isset($_POST['filipino_gramatika_level'])    ? (int)$_POST['filipino_gramatika_level']    : 0;
$filipino_panitikan_level    = isset($_POST['filipino_panitikan_level'])    ? (int)$_POST['filipino_panitikan_level']    : 0;
$filipino_paguunawa_level    = isset($_POST['filipino_paguunawa_level'])    ? (int)$_POST['filipino_paguunawa_level']    : 0;
$filipino_talasalitaan_level = isset($_POST['filipino_talasalitaan_level']) ? (int)$_POST['filipino_talasalitaan_level'] : 0;
$filipino_wika_level         = isset($_POST['filipino_wika_level'])         ? (int)$_POST['filipino_wika_level']         : 0;

try {
    $checkStmt = $conn->prepare("SELECT username FROM users WHERE username = :username");
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET
        filipino_gramatika_level    = GREATEST(filipino_gramatika_level,    :gramatika),
        filipino_panitikan_level    = GREATEST(filipino_panitikan_level,    :panitikan),
        filipino_paguunawa_level    = GREATEST(filipino_paguunawa_level,    :paguunawa),
        filipino_talasalitaan_level = GREATEST(filipino_talasalitaan_level, :talasalitaan),
        filipino_wika_level         = GREATEST(filipino_wika_level,         :wika)
        WHERE username = :username");
    $stmt->bindParam(':gramatika',    $filipino_gramatika_level,    PDO::PARAM_INT);
    $stmt->bindParam(':panitikan',    $filipino_panitikan_level,    PDO::PARAM_INT);
    $stmt->bindParam(':paguunawa',    $filipino_paguunawa_level,    PDO::PARAM_INT);
    $stmt->bindParam(':talasalitaan', $filipino_talasalitaan_level, PDO::PARAM_INT);
    $stmt->bindParam(':wika',         $filipino_wika_level,         PDO::PARAM_INT);
    $stmt->bindParam(':username',     $username);
    
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
    error_log("Database error in update_filipino_level.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    echo json_encode($response);
}
?>