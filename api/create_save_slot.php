<?php
/**
 * CREATE SAVE SLOT API
 * Creates a new save slot for a user
 */

include('../configurations/configurations.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Get parameters
$username = $_POST['username'] ?? '';
$slot_name = $_POST['slot_name'] ?? '';

if (empty($username)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username is required'
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
    
    // Check how many slots user already has
    $stmt = $conn->prepare("SELECT COUNT(*) as slot_count FROM save_slots WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['slot_count'] >= 5) {
        echo json_encode([
            'success' => false,
            'message' => 'Maximum save slots (5) reached'
        ]);
        exit;
    }
    
    // Find next available slot number
    $stmt = $conn->prepare("
        SELECT slot_number FROM save_slots 
        WHERE user_id = :user_id 
        ORDER BY slot_number ASC
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $existing_slots = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $next_slot_number = 1;
    for ($i = 1; $i <= 5; $i++) {
        if (!in_array($i, $existing_slots)) {
            $next_slot_number = $i;
            break;
        }
    }
    
    // Set default slot name if not provided
    if (empty($slot_name)) {
        $slot_name = "Save Slot " . $next_slot_number;
    }
    
    // Create new save slot with default values
    $stmt = $conn->prepare("
        INSERT INTO save_slots (
            user_id, slot_number, slot_name, is_active,
            lives, feathers, potion, selected_character, volume
        ) VALUES (
            :user_id, :slot_number, :slot_name, 0,
            3, 0, 0, 'Akio', 1.0
        )
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':slot_number', $next_slot_number);
    $stmt->bindParam(':slot_name', $slot_name);
    $stmt->execute();
    
    $new_slot_id = $conn->lastInsertId();
    
    // Log the action
    $stmt = $conn->prepare("
        INSERT INTO save_slot_logs (user_id, save_slot_id, action_type, action_description)
        VALUES (:user_id, :save_slot_id, 'CREATE', :description)
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':save_slot_id', $new_slot_id);
    $description = "Created new save slot: " . $slot_name;
    $stmt->bindParam(':description', $description);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Save slot created successfully',
        'slot_id' => (int)$new_slot_id,
        'slot_number' => (int)$next_slot_number,
        'slot_name' => $slot_name
    ]);
    
} catch (Exception $e) {
    error_log("Error in create_save_slot.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
