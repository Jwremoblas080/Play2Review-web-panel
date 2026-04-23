<?php
// Detect environment
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    // Localhost config
    $servername = "localhost";
    $dbname = "play2review_db";
    $dbusername = "root";
    $dbpassword = "";
} else {
    // Hostinger config
    $servername = "localhost";
    $dbname = "u551482737_play2review_db";
    $dbusername = "u551482737_play2review_db";
    $dbpassword = "4!noXC/l:D";
}

// Create ONE connection only (choose mysqli OR PDO)

// ✅ Option 1: mysqli
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    $urlconnection = "http://localhost/play2review/";
} else {
    $urlconnection = "https://blanchedalmond-shrew-238703.hostingersite.com/";
}

ob_start();
?>