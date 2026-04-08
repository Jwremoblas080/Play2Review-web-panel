<?php
// Database configuration
$servername = "localhost";
$dbname = "play2review_db";
$dbusername = "root";
$dbpassword = "";

$host = $servername;
$username = $dbusername;
$password = $dbpassword;
$database = $dbname;

// Database configuration
define('DB_HOST', $servername);
define('DB_USER', $dbusername);
define('DB_PASS', $dbpassword);
define('DB_NAME', $dbname);

$SERVER = $servername;
$USERNAME = $dbusername;
$PASSWORD = $dbpassword;
$DB_NAME = $dbname;

// Create connection
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connsi = new mysqli($host, $username, $password, $database);
if ($connsi->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$DB = mysqli_connect($SERVER, $USERNAME, $PASSWORD, $DB_NAME);
    
if (!$DB) {
    die("Connection failed: " . mysqli_connect_error());
}

$urlconnection = "localhost/play2review/";

ob_start();
?>
    