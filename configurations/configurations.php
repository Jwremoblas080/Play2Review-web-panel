<?php

// Auto-detect environment (localhost vs Hostinger)
$is_localhost = (
    $_SERVER['SERVER_NAME'] == 'localhost' || 
    $_SERVER['SERVER_NAME'] == '127.0.0.1' ||
    strpos($_SERVER['SERVER_NAME'], 'localhost') !== false
);

// Database configuration based on environment
if ($is_localhost) {
    // Localhost configuration
    $servername = "localhost";
    $dbname = "play2review_db";  // Your local database name
    $dbusername = "root";         // Default localhost username
    $dbpassword = "";             // Default localhost password (empty for XAMPP/WAMP)
    $urlconnection = "localhost/play2review/";
} else {
    // Hostinger/Production configuration
    $servername = "localhost";
    $dbname = "u551482737_play2review_db";
    $dbusername = "u551482737_play2review_db";
    $dbpassword = "4!noXC/l:D";
    $urlconnection = $_SERVER['SERVER_NAME'] . "/";
}

// Set database variables
$host = $servername;
$username = $dbusername;
$password = $dbpassword;
$database = $dbname;

// Define constants
define('DB_HOST', $servername);
define('DB_USER', $dbusername);
define('DB_PASS', $dbpassword);
define('DB_NAME', $dbname);

$SERVER = $servername;
$USERNAME = $dbusername;
$PASSWORD = $dbpassword;
$DB_NAME = $dbname;

// Create PDO connection
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

// Create mysqli connection (connsi)
$connsi = new mysqli($host, $username, $password, $database);
if ($connsi->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $connsi->connect_error]));
}

// Create mysqli connection (con)
$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($con, "utf8");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Create mysqli connection (DB)
$DB = mysqli_connect($SERVER, $USERNAME, $PASSWORD, $DB_NAME);
    
if (!$DB) {
    die("Connection failed: " . mysqli_connect_error());
}

// Optional: Display environment info (remove in production)
// echo "<!-- Environment: " . ($is_localhost ? "Localhost" : "Production") . " -->";

ob_start();
?>
    