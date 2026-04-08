<?php
require_once('../includes/config.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $gradelevel = trim($_POST['gradelevel']);
    $studentid = trim($_POST['studentid']);
    
    // Validate inputs
    if(empty($fullname) || empty($gradelevel) || empty($studentid)) {
        $error = "All fields are required!";
    } else {
        // Check if student ID already exists
        $checkQuery = "SELECT id FROM students WHERE studentid = ?";
        $checkStmt = mysqli_prepare($con, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "s", $studentid);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        
        if(mysqli_stmt_num_rows($checkStmt) > 0) {
            $error = "Student ID already exists!";
        } else {
            // Insert into database
            $query = "INSERT INTO students (fullname, gradelevel, studentid) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "sss", $fullname, $gradelevel, $studentid);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Student added successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: manage-students.php");
                exit();
            } else {
                $error = "Error adding student: " . mysqli_error($con);
            }
        }
    }
}

// Available grade levels
$gradeLevels = ['Kindergarten', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Student - Play2Review Admin</title>
    <?php include('includes/header.php'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <?php include('includes/topbar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Add New Student</h3>
                            </div>
                            <div class="card-body">
                                <?php if(isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                
                                <form method="POST">
                                    <div class="form-group">
                                        <label>Full Name *</label>
                                        <input type="text" name="fullname" class="form-control" required 
                                               placeholder="e.g., Juan Dela Cruz">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Grade Level *</label>
                                        <select name="gradelevel" class="form-control" required>
                                            <option value="">Select Grade Level</option>
                                            <?php foreach($gradeLevels as $grade): ?>
                                                <option value="<?php echo $grade; ?>">
                                                    <?php echo $grade; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Student ID *</label>
                                        <input type="text" name="studentid" class="form-control" required 
                                               placeholder="e.g., 10001">
                                        <small class="form-text text-muted">Unique identifier for the student</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success">Add Student</button>
                                        <a href="manage-students.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
</body>
</html>