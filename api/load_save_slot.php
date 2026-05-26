<?php
/**
 * LOAD SAVE SLOT API
 * Loads a specific save slot and makes it active
 * Returns all game data from that slot
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
    
    // Verify the slot belongs to this user
    $stmt = $conn->prepare("
        SELECT * FROM save_slots 
        WHERE id = :slot_id AND user_id = :user_id
    ");
    $stmt->bindParam(':slot_id', $slot_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Save slot not found or does not belong to this user'
        ]);
        exit;
    }
    
    $save_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Deactivate all other slots for this user
    $stmt = $conn->prepare("
        UPDATE save_slots 
        SET is_active = 0 
        WHERE user_id = :user_id
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    // Activate the selected slot
    $stmt = $conn->prepare("
        UPDATE save_slots 
        SET is_active = 1 
        WHERE id = :slot_id
    ");
    $stmt->bindParam(':slot_id', $slot_id);
    $stmt->execute();
    
    // Update user's current_save_slot_id
    $stmt = $conn->prepare("
        UPDATE users 
        SET current_save_slot_id = :slot_id 
        WHERE id = :user_id
    ");
    $stmt->bindParam(':slot_id', $slot_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    // Log the action
    $stmt = $conn->prepare("
        INSERT INTO save_slot_logs (user_id, save_slot_id, action_type, action_description)
        VALUES (:user_id, :save_slot_id, 'LOAD', :description)
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':save_slot_id', $slot_id);
    $description = "Loaded save slot: " . $save_data['slot_name'];
    $stmt->bindParam(':description', $description);
    $stmt->execute();
    
    // Return all game data
    $response = [
        'success' => true,
        'message' => 'Save slot loaded successfully',
        'slot_info' => [
            'slot_id' => (int)$save_data['id'],
            'slot_number' => (int)$save_data['slot_number'],
            'slot_name' => $save_data['slot_name']
        ],
        'game_data' => [
            'lives' => (int)$save_data['lives'],
            'feathers' => (int)$save_data['feathers'],
            'potion' => (int)$save_data['potion'],
            'selected_character' => $save_data['selected_character'],
            'volume' => (float)$save_data['volume']
        ],
        'english_levels' => [
            'grammar' => (int)$save_data['english_grammar_level'],
            'vocabulary' => (int)$save_data['english_vocabulary_level'],
            'reading' => (int)$save_data['english_reading_level'],
            'literature' => (int)$save_data['english_literature_level'],
            'writing' => (int)$save_data['english_writing_level']
        ],
        'math_levels' => [
            'algebra' => (int)$save_data['math_algebra_level'],
            'geometry' => (int)$save_data['math_geometry_level'],
            'statistics' => (int)$save_data['math_statistics_level'],
            'probability' => (int)$save_data['math_probability_level'],
            'functions' => (int)$save_data['math_functions_level'],
            'wordproblems' => (int)$save_data['math_wordproblems_level']
        ],
        'science_levels' => [
            'biology' => (int)$save_data['science_biology_level'],
            'chemistry' => (int)$save_data['science_chemistry_level'],
            'physics' => (int)$save_data['science_physics_level'],
            'earthscience' => (int)$save_data['science_earthscience_level'],
            'investigation' => (int)$save_data['science_investigation_level']
        ],
        'filipino_levels' => [
            'gramatika' => (int)$save_data['filipino_gramatika_level'],
            'panitikan' => (int)$save_data['filipino_panitikan_level'],
            'paguunawa' => (int)$save_data['filipino_paguunawa_level'],
            'talasalitaan' => (int)$save_data['filipino_talasalitaan_level'],
            'wika' => (int)$save_data['filipino_wika_level']
        ],
        'ap_levels' => [
            'ekonomiks' => (int)$save_data['ap_ekonomiks_level'],
            'kasaysayan' => (int)$save_data['ap_kasaysayan_level'],
            'kontemporaryo' => (int)$save_data['ap_kontemporaryo_level'],
            'heograpiya' => (int)$save_data['ap_heograpiya_level'],
            'pamahalaan' => (int)$save_data['ap_pamahalaan_level']
        ],
        'metadata' => [
            'last_subject' => $save_data['last_subject_played'],
            'last_category' => $save_data['last_category_played'],
            'play_time_minutes' => (int)$save_data['total_play_time_minutes']
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error in load_save_slot.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
