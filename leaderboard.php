<?php
include('configurations/configurations.php');

// Set headers FIRST - no output before this
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// Simple response first
$response = new stdClass();
$response->success = false;
$response->message = "Initializing";

try {
    // Check if connection exists
    if (!isset($conn)) {
        throw new Exception("Database connection failed");
    }
    
    $sql = "SELECT username, player_name, 
                   -- English categories
                   COALESCE(english_grammar_level, 0) as english_grammar_level,
                   COALESCE(english_vocabulary_level, 0) as english_vocabulary_level,
                   COALESCE(english_reading_level, 0) as english_reading_level,
                   COALESCE(english_literature_level, 0) as english_literature_level,
                   COALESCE(english_writing_level, 0) as english_writing_level,
                   -- Math categories
                   COALESCE(math_algebra_level, 0) as math_algebra_level,
                   COALESCE(math_geometry_level, 0) as math_geometry_level,
                   COALESCE(math_statistics_level, 0) as math_statistics_level,
                   COALESCE(math_probability_level, 0) as math_probability_level,
                   COALESCE(math_functions_level, 0) as math_functions_level,
                   COALESCE(math_wordproblems_level, 0) as math_wordproblems_level,
                   -- Science categories
                   COALESCE(science_biology_level, 0) as science_biology_level,
                   COALESCE(science_chemistry_level, 0) as science_chemistry_level,
                   COALESCE(science_physics_level, 0) as science_physics_level,
                   COALESCE(science_earthscience_level, 0) as science_earthscience_level,
                   COALESCE(science_investigation_level, 0) as science_investigation_level,
                   -- Filipino categories
                   COALESCE(filipino_gramatika_level, 0) as filipino_gramatika_level,
                   COALESCE(filipino_panitikan_level, 0) as filipino_panitikan_level,
                   COALESCE(filipino_paguunawa_level, 0) as filipino_paguunawa_level,
                   COALESCE(filipino_talasalitaan_level, 0) as filipino_talasalitaan_level,
                   COALESCE(filipino_wika_level, 0) as filipino_wika_level,
                   -- AP categories
                   COALESCE(ap_ekonomiks_level, 0) as ap_ekonomiks_level,
                   COALESCE(ap_kasaysayan_level, 0) as ap_kasaysayan_level,
                   COALESCE(ap_kontemporaryo_level, 0) as ap_kontemporaryo_level,
                   COALESCE(ap_heograpiya_level, 0) as ap_heograpiya_level,
                   COALESCE(ap_pamahalaan_level, 0) as ap_pamahalaan_level
            FROM users 
            ORDER BY (
                -- Sum all English categories
                COALESCE(english_grammar_level, 0) + COALESCE(english_vocabulary_level, 0) + 
                COALESCE(english_reading_level, 0) + COALESCE(english_literature_level, 0) + 
                COALESCE(english_writing_level, 0) +
                -- Sum all Math categories
                COALESCE(math_algebra_level, 0) + COALESCE(math_geometry_level, 0) + 
                COALESCE(math_statistics_level, 0) + COALESCE(math_probability_level, 0) + 
                COALESCE(math_functions_level, 0) + COALESCE(math_wordproblems_level, 0) +
                -- Sum all Science categories
                COALESCE(science_biology_level, 0) + COALESCE(science_chemistry_level, 0) + 
                COALESCE(science_physics_level, 0) + COALESCE(science_earthscience_level, 0) + 
                COALESCE(science_investigation_level, 0) +
                -- Sum all Filipino categories
                COALESCE(filipino_gramatika_level, 0) + COALESCE(filipino_panitikan_level, 0) + 
                COALESCE(filipino_paguunawa_level, 0) + COALESCE(filipino_talasalitaan_level, 0) + 
                COALESCE(filipino_wika_level, 0) +
                -- Sum all AP categories
                COALESCE(ap_ekonomiks_level, 0) + COALESCE(ap_kasaysayan_level, 0) + 
                COALESCE(ap_kontemporaryo_level, 0) + COALESCE(ap_heograpiya_level, 0) + 
                COALESCE(ap_pamahalaan_level, 0)
            ) DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $leaderboard = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Calculate English score from all English categories
        $englishScore = (
            (int)$row['english_grammar_level'] +
            (int)$row['english_vocabulary_level'] +
            (int)$row['english_reading_level'] +
            (int)$row['english_literature_level'] +
            (int)$row['english_writing_level']
        ) * 10;
        
        // Calculate Math score from all Math categories
        $mathScore = (
            (int)$row['math_algebra_level'] +
            (int)$row['math_geometry_level'] +
            (int)$row['math_statistics_level'] +
            (int)$row['math_probability_level'] +
            (int)$row['math_functions_level'] +
            (int)$row['math_wordproblems_level']
        ) * 10;
        
        // Calculate Science score from all Science categories
        $scienceScore = (
            (int)$row['science_biology_level'] +
            (int)$row['science_chemistry_level'] +
            (int)$row['science_physics_level'] +
            (int)$row['science_earthscience_level'] +
            (int)$row['science_investigation_level']
        ) * 10;
        
        // Calculate Filipino score from all Filipino categories
        $filipinoScore = (
            (int)$row['filipino_gramatika_level'] +
            (int)$row['filipino_panitikan_level'] +
            (int)$row['filipino_paguunawa_level'] +
            (int)$row['filipino_talasalitaan_level'] +
            (int)$row['filipino_wika_level']
        ) * 10;
        
        // Calculate AP score from all AP categories
        $apScore = (
            (int)$row['ap_ekonomiks_level'] +
            (int)$row['ap_kasaysayan_level'] +
            (int)$row['ap_kontemporaryo_level'] +
            (int)$row['ap_heograpiya_level'] +
            (int)$row['ap_pamahalaan_level']
        ) * 10;
        
        $totalScore = $englishScore + $mathScore + $scienceScore + $filipinoScore + $apScore;
        
        $entry = new stdClass();
        $entry->username = $row['username'] ?? '';
        $entry->playerName = $row['player_name'] ?? '';
        $entry->englishScore = $englishScore;
        $entry->mathScore = $mathScore;
        $entry->scienceScore = $scienceScore;
        $entry->filipinoScore = $filipinoScore;
        $entry->apScore = $apScore;
        $entry->totalScore = $totalScore;
        
        $leaderboard[] = $entry;
    }
    
    $response->success = true;
    $response->leaderboard = $leaderboard;
    $response->message = "Successfully loaded " . count($leaderboard) . " users";
    
} catch (Exception $e) {
    $response->success = false;
    $response->message = $e->getMessage();
}

// Clean any output and send JSON
if (ob_get_length()) ob_clean();
echo json_encode($response);
exit();
?>