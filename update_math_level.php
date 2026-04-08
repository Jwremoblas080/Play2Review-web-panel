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
$math_algebra_level      = isset($_POST['math_algebra_level'])      ? (int)$_POST['math_algebra_level']      : 0;
$math_geometry_level     = isset($_POST['math_geometry_level'])     ? (int)$_POST['math_geometry_level']     : 0;
$math_statistics_level   = isset($_POST['math_statistics_level'])   ? (int)$_POST['math_statistics_level']   : 0;
$math_probability_level  = isset($_POST['math_probability_level'])  ? (int)$_POST['math_probability_level']  : 0;
$math_functions_level    = isset($_POST['math_functions_level'])    ? (int)$_POST['math_functions_level']    : 0;
$math_wordproblems_level = isset($_POST['math_wordproblems_level']) ? (int)$_POST['math_wordproblems_level'] : 0;

try {
    $checkStmt = $conn->prepare("SELECT username FROM users WHERE username = :username");
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET
        math_algebra_level      = GREATEST(math_algebra_level,      :algebra),
        math_geometry_level     = GREATEST(math_geometry_level,     :geometry),
        math_statistics_level   = GREATEST(math_statistics_level,   :statistics),
        math_probability_level  = GREATEST(math_probability_level,  :probability),
        math_functions_level    = GREATEST(math_functions_level,    :functions),
        math_wordproblems_level = GREATEST(math_wordproblems_level, :wordproblems)
        WHERE username = :username");
    $stmt->bindParam(':algebra',      $math_algebra_level,      PDO::PARAM_INT);
    $stmt->bindParam(':geometry',     $math_geometry_level,     PDO::PARAM_INT);
    $stmt->bindParam(':statistics',   $math_statistics_level,   PDO::PARAM_INT);
    $stmt->bindParam(':probability',  $math_probability_level,  PDO::PARAM_INT);
    $stmt->bindParam(':functions',    $math_functions_level,    PDO::PARAM_INT);
    $stmt->bindParam(':wordproblems', $math_wordproblems_level, PDO::PARAM_INT);
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
    error_log("Database error in update_math_level.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    echo json_encode($response);
}
?>