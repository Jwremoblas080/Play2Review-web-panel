<?php
/**
 * GET SAVE SLOTS API
 * Returns all save slots for a specific user
 */

include('../configurations/configurations.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Get username from POST
$username = $_POST['username'] ?? '';

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
    
    // Get all save slots for this user
    $stmt = $conn->prepare("
        SELECT 
            id, slot_number, slot_name, is_active,
            lives, feathers, potion, selected_character,
            last_subject_played, last_category_played,
            total_play_time_minutes,
            created_at, updated_at,
            -- Calculate total progress percentage
            (
                COALESCE(english_grammar_level, 0) + COALESCE(english_vocabulary_level, 0) + 
                COALESCE(english_reading_level, 0) + COALESCE(english_literature_level, 0) + 
                COALESCE(english_writing_level, 0) +
                COALESCE(math_algebra_level, 0) + COALESCE(math_geometry_level, 0) + 
                COALESCE(math_statistics_level, 0) + COALESCE(math_probability_level, 0) + 
                COALESCE(math_functions_level, 0) + COALESCE(math_wordproblems_level, 0) +
                COALESCE(science_biology_level, 0) + COALESCE(science_chemistry_level, 0) + 
                COALESCE(science_physics_level, 0) + COALESCE(science_earthscience_level, 0) + 
                COALESCE(science_investigation_level, 0) +
                COALESCE(filipino_gramatika_level, 0) + COALESCE(filipino_panitikan_level, 0) + 
                COALESCE(filipino_paguunawa_level, 0) + COALESCE(filipino_talasalitaan_level, 0) + 
                COALESCE(filipino_wika_level, 0) +
                COALESCE(ap_ekonomiks_level, 0) + COALESCE(ap_kasaysayan_level, 0) + 
                COALESCE(ap_kontemporaryo_level, 0) + COALESCE(ap_heograpiya_level, 0) + 
                COALESCE(ap_pamahalaan_level, 0)
            ) as total_levels_completed
        FROM save_slots
        WHERE user_id = :user_id
        ORDER BY slot_number ASC
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    $save_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $formatted_slots = [];
    foreach ($save_slots as $slot) {
        $total_possible_levels = 26 * 10; // 26 categories × 10 levels each = 260
        $progress_percentage = round(($slot['total_levels_completed'] / $total_possible_levels) * 100, 1);
        
        $formatted_slots[] = [
            'slot_id' => (int)$slot['id'],
            'slot_number' => (int)$slot['slot_number'],
            'slot_name' => $slot['slot_name'] ?? "Save Slot " . $slot['slot_number'],
            'is_active' => (bool)$slot['is_active'],
            'lives' => (int)$slot['lives'],
            'feathers' => (int)$slot['feathers'],
            'potion' => (int)$slot['potion'],
            'selected_character' => $slot['selected_character'],
            'last_subject' => $slot['last_subject_played'],
            'last_category' => $slot['last_category_played'],
            'play_time_minutes' => (int)$slot['total_play_time_minutes'],
            'progress_percentage' => $progress_percentage,
            'total_levels_completed' => (int)$slot['total_levels_completed'],
            'created_at' => $slot['created_at'],
            'updated_at' => $slot['updated_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Save slots retrieved successfully',
        'save_slots' => $formatted_slots,
        'total_slots' => count($formatted_slots),
        'max_slots' => 5
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_save_slots.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
