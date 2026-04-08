<?php
include('configurations/configurations.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$username = $_POST['Username'] ?? null;

if ($username) {
    try {
        $stmt = $conn->prepare("SELECT lives, potion FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $lives = (int)$user['lives'];
            $potion = (int)$user['potion'];

            if ($lives < 3 && $potion > 0) {
                $lives = min(3, $lives + 1);
                $potion--;

                $update = $conn->prepare("UPDATE users SET lives = ?, potion = ? WHERE username = ?");
                $update->execute([$lives, $potion, $username]);

                echo json_encode([
                    "success" => true,
                    "message" => "Potion used successfully",
                    "lives"   => $lives,
                    "potion"  => $potion
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Cannot use potion. Conditions not met.",
                    "lives"   => $lives,
                    "potion"  => $potion
                ]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "User not found"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No username received"]);
}

$conn = null;
?>
