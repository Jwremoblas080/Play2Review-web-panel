<?php
/**
 * DELETE SAVE SLOT API
 * Deletes a save slot (cannot delete if it's the only slot or if it's active)
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

if (empty($username) || empty($slot_id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username and slot_id are required'
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
    
    // Check if slot exists and belongs to user
    $stmt = $conn->prepare("
        SELECT is_active, slot_name FROM save_slots 
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
    
    $slot = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if it's the active slot
    if ($slot['is_active']) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete active save slot. Please load another slot first.'
        ]);
        exit;
    }
    
    // Check if it's the only slot
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM save_slots WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] <= 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete the only save slot'
        ]);
        exit;
    }
    
    // Log before deleting
    $stmt = $conn->prepare("
        INSERT INTO save_slot_logs (user_id, save_slot_id, action_type, action_description)
        VALUES (:user_id, :save_slot_id, 'DELETE', :description)
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':save_slot_id', $slot_id);
    $description = "Deleted save slot: " . $slot['slot_name'];
    $stmt->bindParam(':description', $description);
    $stmt->execute();
    
    // Delete the slot
    $stmt = $conn->prepare("DELETE FROM save_slots WHERE id = :slot_id");
    $stmt->bindParam(':slot_id', $slot_id);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Save slot deleted successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Error in delete_save_slot.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
