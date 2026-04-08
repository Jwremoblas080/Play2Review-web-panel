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
$english_grammar_level       = isset($_POST['english_grammar_level'])    ? (int)$_POST['english_grammar_level']    : 0;
$english_vocabulary_level    = isset($_POST['english_vocabulary_level']) ? (int)$_POST['english_vocabulary_level'] : 0;
$english_reading_level       = isset($_POST['english_reading_level'])    ? (int)$_POST['english_reading_level']    : 0;
$english_literature_level    = isset($_POST['english_literature_level']) ? (int)$_POST['english_literature_level'] : 0;
$english_writing_level       = isset($_POST['english_writing_level'])    ? (int)$_POST['english_writing_level']    : 0;
$english_completed_level     = isset($_POST['english_completed_level'])  ? (int)$_POST['english_completed_level']  : 0;

try {
    $checkStmt = $conn->prepare("SELECT username FROM users WHERE username = :username");
    $checkStmt->bindParam(':username', $username);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET
        english_grammar_level    = GREATEST(english_grammar_level,    :grammar),
        english_vocabulary_level = GREATEST(english_vocabulary_level, :vocabulary),
        english_reading_level    = GREATEST(english_reading_level,    :reading),
        english_literature_level = GREATEST(english_literature_level, :literature),
        english_writing_level    = GREATEST(english_writing_level,    :writing),
        english_completed_level  = GREATEST(english_completed_level,  :completed)
        WHERE username = :username");
    $stmt->bindParam(':grammar',     $english_grammar_level,    PDO::PARAM_INT);
    $stmt->bindParam(':vocabulary',  $english_vocabulary_level, PDO::PARAM_INT);
    $stmt->bindParam(':reading',     $english_reading_level,    PDO::PARAM_INT);
    $stmt->bindParam(':literature',  $english_literature_level, PDO::PARAM_INT);
    $stmt->bindParam(':writing',     $english_writing_level,    PDO::PARAM_INT);
    $stmt->bindParam(':completed',   $english_completed_level,  PDO::PARAM_INT);
    $stmt->bindParam(':username',    $username);
    
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