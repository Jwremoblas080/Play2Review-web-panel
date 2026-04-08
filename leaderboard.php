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
                   COALESCE(english_completed_level, 0) as english_completed_level, 
                   COALESCE(ap_completed_level, 0) as ap_completed_level, 
                   COALESCE(filipino_completed_level, 0) as filipino_completed_level, 
                   COALESCE(math_completed_level, 0) as math_completed_level, 
                   COALESCE(science_completed_level, 0) as science_completed_level 
            FROM users 
            ORDER BY (COALESCE(english_completed_level, 0) + 
                     COALESCE(ap_completed_level, 0) + 
                     COALESCE(filipino_completed_level, 0) + 
                     COALESCE(math_completed_level, 0) + 
                     COALESCE(science_completed_level, 0)) DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $leaderboard = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $english = (int)$row['english_completed_level'];
        $ap = (int)$row['ap_completed_level'];
        $filipino = (int)$row['filipino_completed_level'];
        $math = (int)$row['math_completed_level'];
        $science = (int)$row['science_completed_level'];
        
        $totalScore = ($english + $ap + $filipino + $math + $science) * 100;
        
        $entry = new stdClass();
        $entry->username = $row['username'] ?? '';
        $entry->playerName = $row['player_name'] ?? '';
        $entry->englishScore = $english * 100;
        $entry->apScore = $ap * 100;
        $entry->filipinoScore = $filipino * 100;
        $entry->mathScore = $math * 100;
        $entry->scienceScore = $science * 100;
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