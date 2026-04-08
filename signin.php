<?php
include('configurations/configurations.php');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Check if JSON decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data'
    ]);
    exit();
}

// Validate required fields
if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Username and password are required'
    ]);
    exit();
}

// Sanitize input data
$username = trim($data['username']);
$password = $data['password'];


try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, player_name, student_id, password, username,
            lives, feathers, selected_character, volume, potion,
            english_grammar_level, english_vocabulary_level, english_reading_level, english_literature_level, english_writing_level,
            math_algebra_level, math_geometry_level, math_statistics_level, math_probability_level, math_functions_level, math_wordproblems_level,
            filipino_gramatika_level, filipino_panitikan_level, filipino_paguunawa_level, filipino_talasalitaan_level, filipino_wika_level,
            ap_ekonomiks_level, ap_kasaysayan_level, ap_kontemporaryo_level, ap_heograpiya_level, ap_pamahalaan_level,
            science_biology_level, science_chemistry_level, science_physics_level, science_earthscience_level, science_investigation_level
            FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($password, $user['password'])) {

            // SIMPLE ACTIVITY LOGGING - Insert user login activity
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity_description, created_at) VALUES (:user_id, :activity_description, NOW())");
            $log_stmt->bindParam(':user_id', $user['id']);
            $activity_description = "User logged into the game";
            $log_stmt->bindParam(':activity_description', $activity_description);
            $log_stmt->execute();
            
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'playerName'       => $user['player_name'],
                'gradeLevel'       => $user['student_id'],
                'lives'            => (int)$user['lives'],
                'feathers'         => (int)$user['feathers'],
                'selected_character' => $user['selected_character'],
                'volume'           => (float)$user['volume'],
                'potion'           => (int)$user['potion'],
                // English
                'english_grammar_level'       => (int)$user['english_grammar_level'],
                'english_vocabulary_level'    => (int)$user['english_vocabulary_level'],
                'english_reading_level'       => (int)$user['english_reading_level'],
                'english_literature_level'    => (int)$user['english_literature_level'],
                'english_writing_level'       => (int)$user['english_writing_level'],
                // Math
                'math_algebra_level'          => (int)$user['math_algebra_level'],
                'math_geometry_level'         => (int)$user['math_geometry_level'],
                'math_statistics_level'       => (int)$user['math_statistics_level'],
                'math_probability_level'      => (int)$user['math_probability_level'],
                'math_functions_level'        => (int)$user['math_functions_level'],
                'math_wordproblems_level'     => (int)$user['math_wordproblems_level'],
                // Filipino
                'filipino_gramatika_level'    => (int)$user['filipino_gramatika_level'],
                'filipino_panitikan_level'    => (int)$user['filipino_panitikan_level'],
                'filipino_paguunawa_level'    => (int)$user['filipino_paguunawa_level'],
                'filipino_talasalitaan_level' => (int)$user['filipino_talasalitaan_level'],
                'filipino_wika_level'         => (int)$user['filipino_wika_level'],
                // AP
                'ap_ekonomiks_level'          => (int)$user['ap_ekonomiks_level'],
                'ap_kasaysayan_level'         => (int)$user['ap_kasaysayan_level'],
                'ap_kontemporaryo_level'      => (int)$user['ap_kontemporaryo_level'],
                'ap_heograpiya_level'         => (int)$user['ap_heograpiya_level'],
                'ap_pamahalaan_level'         => (int)$user['ap_pamahalaan_level'],
                // Science
                'science_biology_level'       => (int)$user['science_biology_level'],
                'science_chemistry_level'     => (int)$user['science_chemistry_level'],
                'science_physics_level'       => (int)$user['science_physics_level'],
                'science_earthscience_level'  => (int)$user['science_earthscience_level'],
                'science_investigation_level' => (int)$user['science_investigation_level'],
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid password'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>