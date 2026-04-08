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
$ap_ekonomiks_level      = isset($_POST['ap_ekonomiks_level'])      ? (int)$_POST['ap_ekonomiks_level']      : 0;
$ap_kasaysayan_level     = isset($_POST['ap_kasaysayan_level'])     ? (int)$_POST['ap_kasaysayan_level']     : 0;
$ap_kontemporaryo_level  = isset($_POST['ap_kontemporaryo_level'])  ? (int)$_POST['ap_kontemporaryo_level']  : 0;
$ap_heograpiya_level     = isset($_POST['ap_heograpiya_level'])     ? (int)$_POST['ap_heograpiya_level']     : 0;
$ap_pamahalaan_level     = isset($_POST['ap_pamahalaan_level'])     ? (int)$_POST['ap_pamahalaan_level']     : 0;

try {
    $checkStmt = $conn->prepare("SELECT username FROM users WHERE username = :username");
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET
        ap_ekonomiks_level     = GREATEST(ap_ekonomiks_level,     :ekonomiks),
        ap_kasaysayan_level    = GREATEST(ap_kasaysayan_level,    :kasaysayan),
        ap_kontemporaryo_level = GREATEST(ap_kontemporaryo_level, :kontemporaryo),
        ap_heograpiya_level    = GREATEST(ap_heograpiya_level,    :heograpiya),
        ap_pamahalaan_level    = GREATEST(ap_pamahalaan_level,    :pamahalaan)
        WHERE username = :username");
    $stmt->bindParam(':ekonomiks',      $ap_ekonomiks_level,     PDO::PARAM_INT);
    $stmt->bindParam(':kasaysayan',     $ap_kasaysayan_level,    PDO::PARAM_INT);
    $stmt->bindParam(':kontemporaryo',  $ap_kontemporaryo_level, PDO::PARAM_INT);
    $stmt->bindParam(':heograpiya',     $ap_heograpiya_level,    PDO::PARAM_INT);
    $stmt->bindParam(':pamahalaan',     $ap_pamahalaan_level,    PDO::PARAM_INT);
    $stmt->bindParam(':username',       $username);
    
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
    error_log("Database error in update_english_level.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    echo json_encode($response);
}
?>