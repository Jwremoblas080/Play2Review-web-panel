<?php
/**
 * RENAME SAVE SLOT API
 * Renames a save slot
 */

include('../configurations/configurations.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$username = $_POST['username'] ?? '';
$slot_id = $_POST['slot_id'] ?? 0;
$new_name = $_POST['new_name'] ?? '';

if (empty($username) || empty($slot_id) || empty($new_name)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username, slot_id, and new_name are required'
    ]);
    exit;
}

try {
    // Get user ID
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $user['id'];
    
    // Verify slot belongs to user
    $stmt = $conn->prepare("
        SELECT slot_name FROM save_slots 
        WHERE id = :slot_id AND user_id = :user_id
    ");
    $stmt->bindParam(':slot_id', $slot_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Save slot not found'
        ]);
        exit;
    }
    
    $old_slot = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Update slot name
    $stmt = $conn->prepare("
        UPDATE save_slots 
        SET slot_name = :new_name 
        WHERE id = :slot_id
    ");
    $stmt->bindParam(':new_name', $new_name);
    $stmt->bindParam(':slot_id', $slot_id);
    $stmt->execute();
    
    // Log the action
    $stmt = $conn->prepare("
        INSERT INTO save_slot_logs (user_id, save_slot_id, action_type, action_description)
        VALUES (:user_id, :save_slot_id, 'RENAME', :description)
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':save_slot_id', $slot_id);
    $description = "Renamed from '" . $old_slot['slot_name'] . "' to '" . $new_name . "'";
    $stmt->bindParam(':description', $description);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Save slot renamed successfully',
        'new_name' => $new_name
    ]);
    
} catch (Exception $e) {
    error_log("Error in rename_save_slot.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
