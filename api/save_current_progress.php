<?php
/**
 * SAVE CURRENT PROGRESS API
 * Saves current game progress to the active save slot
 * This is called during gameplay to update the save slot
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
$subject = $_POST['subject'] ?? '';
$category = $_POST['category'] ?? '';

if (empty($username)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username is required'
    ]);
    exit;
}

try {
    // Get user ID and current active slot
    $stmt = $conn->prepare("
        SELECT u.id, u.current_save_slot_id 
        FROM users u
        WHERE u.username = :username
    ");
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
    $slot_id = $user['current_save_slot_id'];
    
    // If no active slot, create one
    if (empty($slot_id)) {
        $stmt = $conn->prepare("
            INSERT INTO save_slots (
                user_id, slot_number, slot_name, is_active,
                lives, feathers, potion, selected_character, volume
            ) VALUES (
                :user_id, 1, 'Main Save', 1,
                3, 0, 0, 'Akio', 1.0
            )
        ");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        $slot_id = $conn->lastInsertId();
        
        // Update user's current_save_slot_id
        $stmt = $conn->prepare("UPDATE users SET current_save_slot_id = :slot_id WHERE id = :user_id");
        $stmt->bindParam(':slot_id', $slot_id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }
    
    // Build update query dynamically based on what data is provided
    $update_fields = [];
    $params = [':slot_id' => $slot_id];
    
    // Game data
    if (isset($_POST['lives'])) {
        $update_fields[] = "lives = :lives";
        $params[':lives'] = (int)$_POST['lives'];
    }
    if (isset($_POST['feathers'])) {
        $update_fields[] = "feathers = :feathers";
        $params[':feathers'] = (int)$_POST['feathers'];
    }
    if (isset($_POST['potion'])) {
        $update_fields[] = "potion = :potion";
        $params[':potion'] = (int)$_POST['potion'];
    }
    if (isset($_POST['selected_character'])) {
        $update_fields[] = "selected_character = :selected_character";
        $params[':selected_character'] = $_POST['selected_character'];
    }
    if (isset($_POST['volume'])) {
        $update_fields[] = "volume = :volume";
        $params[':volume'] = (float)$_POST['volume'];
    }
    
    // Subject and category tracking
    if (!empty($subject)) {
        $update_fields[] = "last_subject_played = :subject";
        $params[':subject'] = $subject;
    }
    if (!empty($category)) {
        $update_fields[] = "last_category_played = :category";
        $params[':category'] = $category;
    }
    
    // Category levels - English
    if (isset($_POST['english_grammar_level'])) {
        $update_fields[] = "english_grammar_level = :english_grammar_level";
        $params[':english_grammar_level'] = (int)$_POST['english_grammar_level'];
    }
    if (isset($_POST['english_vocabulary_level'])) {
        $update_fields[] = "english_vocabulary_level = :english_vocabulary_level";
        $params[':english_vocabulary_level'] = (int)$_POST['english_vocabulary_level'];
    }
    if (isset($_POST['english_reading_level'])) {
        $update_fields[] = "english_reading_level = :english_reading_level";
        $params[':english_reading_level'] = (int)$_POST['english_reading_level'];
    }
    if (isset($_POST['english_literature_level'])) {
        $update_fields[] = "english_literature_level = :english_literature_level";
        $params[':english_literature_level'] = (int)$_POST['english_literature_level'];
    }
    if (isset($_POST['english_writing_level'])) {
        $update_fields[] = "english_writing_level = :english_writing_level";
        $params[':english_writing_level'] = (int)$_POST['english_writing_level'];
    }
    
    // Category levels - Math
    if (isset($_POST['math_algebra_level'])) {
        $update_fields[] = "math_algebra_level = :math_algebra_level";
        $params[':math_algebra_level'] = (int)$_POST['math_algebra_level'];
    }
    if (isset($_POST['math_geometry_level'])) {
        $update_fields[] = "math_geometry_level = :math_geometry_level";
        $params[':math_geometry_level'] = (int)$_POST['math_geometry_level'];
    }
    if (isset($_POST['math_statistics_level'])) {
        $update_fields[] = "math_statistics_level = :math_statistics_level";
        $params[':math_statistics_level'] = (int)$_POST['math_statistics_level'];
    }
    if (isset($_POST['math_probability_level'])) {
        $update_fields[] = "math_probability_level = :math_probability_level";
        $params[':math_probability_level'] = (int)$_POST['math_probability_level'];
    }
    if (isset($_POST['math_functions_level'])) {
        $update_fields[] = "math_functions_level = :math_functions_level";
        $params[':math_functions_level'] = (int)$_POST['math_functions_level'];
    }
    if (isset($_POST['math_wordproblems_level'])) {
        $update_fields[] = "math_wordproblems_level = :math_wordproblems_level";
        $params[':math_wordproblems_level'] = (int)$_POST['math_wordproblems_level'];
    }
    
    // Category levels - Science
    if (isset($_POST['science_biology_level'])) {
        $update_fields[] = "science_biology_level = :science_biology_level";
        $params[':science_biology_level'] = (int)$_POST['science_biology_level'];
    }
    if (isset($_POST['science_chemistry_level'])) {
        $update_fields[] = "science_chemistry_level = :science_chemistry_level";
        $params[':science_chemistry_level'] = (int)$_POST['science_chemistry_level'];
    }
    if (isset($_POST['science_physics_level'])) {
        $update_fields[] = "science_physics_level = :science_physics_level";
        $params[':science_physics_level'] = (int)$_POST['science_physics_level'];
    }
    if (isset($_POST['science_earthscience_level'])) {
        $update_fields[] = "science_earthscience_level = :science_earthscience_level";
        $params[':science_earthscience_level'] = (int)$_POST['science_earthscience_level'];
    }
    if (isset($_POST['science_investigation_level'])) {
        $update_fields[] = "science_investigation_level = :science_investigation_level";
        $params[':science_investigation_level'] = (int)$_POST['science_investigation_level'];
    }
    
    // Category levels - Filipino
    if (isset($_POST['filipino_gramatika_level'])) {
        $update_fields[] = "filipino_gramatika_level = :filipino_gramatika_level";
        $params[':filipino_gramatika_level'] = (int)$_POST['filipino_gramatika_level'];
    }
    if (isset($_POST['filipino_panitikan_level'])) {
        $update_fields[] = "filipino_panitikan_level = :filipino_panitikan_level";
        $params[':filipino_panitikan_level'] = (int)$_POST['filipino_panitikan_level'];
    }
    if (isset($_POST['filipino_paguunawa_level'])) {
        $update_fields[] = "filipino_paguunawa_level = :filipino_paguunawa_level";
        $params[':filipino_paguunawa_level'] = (int)$_POST['filipino_paguunawa_level'];
    }
    if (isset($_POST['filipino_talasalitaan_level'])) {
        $update_fields[] = "filipino_talasalitaan_level = :filipino_talasalitaan_level";
        $params[':filipino_talasalitaan_level'] = (int)$_POST['filipino_talasalitaan_level'];
    }
    if (isset($_POST['filipino_wika_level'])) {
        $update_fields[] = "filipino_wika_level = :filipino_wika_level";
        $params[':filipino_wika_level'] = (int)$_POST['filipino_wika_level'];
    }
    
    // Category levels - AP
    if (isset($_POST['ap_ekonomiks_level'])) {
        $update_fields[] = "ap_ekonomiks_level = :ap_ekonomiks_level";
        $params[':ap_ekonomiks_level'] = (int)$_POST['ap_ekonomiks_level'];
    }
    if (isset($_POST['ap_kasaysayan_level'])) {
        $update_fields[] = "ap_kasaysayan_level = :ap_kasaysayan_level";
        $params[':ap_kasaysayan_level'] = (int)$_POST['ap_kasaysayan_level'];
    }
    if (isset($_POST['ap_kontemporaryo_level'])) {
        $update_fields[] = "ap_kontemporaryo_level = :ap_kontemporaryo_level";
        $params[':ap_kontemporaryo_level'] = (int)$_POST['ap_kontemporaryo_level'];
    }
    if (isset($_POST['ap_heograpiya_level'])) {
        $update_fields[] = "ap_heograpiya_level = :ap_heograpiya_level";
        $params[':ap_heograpiya_level'] = (int)$_POST['ap_heograpiya_level'];
    }
    if (isset($_POST['ap_pamahalaan_level'])) {
        $update_fields[] = "ap_pamahalaan_level = :ap_pamahalaan_level";
        $params[':ap_pamahalaan_level'] = (int)$_POST['ap_pamahalaan_level'];
    }
    
    if (empty($update_fields)) {
        echo json_encode([
            'success' => false,
            'message' => 'No data provided to save'
        ]);
        exit;
    }
    
    // Execute update
    $sql = "UPDATE save_slots SET " . implode(', ', $update_fields) . " WHERE id = :slot_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    // Log the action
    $stmt = $conn->prepare("
        INSERT INTO save_slot_logs (user_id, save_slot_id, action_type, action_description)
        VALUES (:user_id, :save_slot_id, 'SAVE', :description)
    ");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':save_slot_id', $slot_id);
    $description = "Saved progress" . (!empty($subject) ? " in $subject" : "") . (!empty($category) ? " - $category" : "");
    $stmt->bindParam(':description', $description);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Progress saved successfully',
        'slot_id' => (int)$slot_id
    ]);
    
} catch (Exception $e) {
    error_log("Error in save_current_progress.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
