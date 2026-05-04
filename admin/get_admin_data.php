<?php
require_once('../configurations/configurations.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if(isset($_POST['id'])) {
    $id = mysqli_real_escape_string($con, $_POST['id']);
    
    $query = "SELECT * FROM admin WHERE admin_ID = '$id'";
    $result = mysqli_query($con, $query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $admin = mysqli_fetch_assoc($result);
        echo json_encode($admin);
    } else {
        echo json_encode(['error' => 'Admin not found']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
