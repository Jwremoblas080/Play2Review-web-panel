<?php
require_once('../includes/config.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Get student data
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$student = null;

if($id > 0) {
    $query = "SELECT * FROM students WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);
}

if(!$student) {
    $_SESSION['message'] = "Student not found!";
    $_SESSION['message_type'] = "error";
    header("Location: manage-students.php");
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
        // Check if student ID already exists (excluding current student)
        $checkQuery = "SELECT id FROM students WHERE studentid = ? AND id != ?";
        $checkStmt = mysqli_prepare($con, $checkQuery);
        mysqli_stmt_bind_param($checkStmt, "si", $studentid, $id);
        mysqli_stmt_execute($checkStmt);
        mysqli_stmt_store_result($checkStmt);
        
        if(mysqli_stmt_num_rows($checkStmt) > 0) {
            $error = "Student ID already exists!";
        } else {
            // Update database
            $query = "UPDATE students SET fullname = ?, gradelevel = ?, studentid = ? WHERE id = ?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "sssi", $fullname, $gradelevel, $studentid, $id);
            
            if(mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Student updated successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: manage-students.php");
                exit();
            } else {
                $error = "Error updating student: " . mysqli_error($con);
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
    <title>Edit Student - Play2review Admin</title>
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
                                <h3 class="card-title">Edit Student</h3>
                            </div>
                            <div class="card-body">
                                <?php if(isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                
                                <form method="POST">
                                    <div class="form-group">
                                        <label>Full Name *</label>
                                        <input type="text" name="fullname" class="form-control" required 
                                               value="<?php echo htmlspecialchars($student['fullname']); ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Grade Level *</label>
                                        <select name="gradelevel" class="form-control" required>
                                            <option value="">Select Grade Level</option>
                                            <?php foreach($gradeLevels as $grade): ?>
                                                <option value="<?php echo $grade; ?>" 
                                                    <?php echo ($student['gradelevel'] == $grade) ? 'selected' : ''; ?>>
                                                    <?php echo $grade; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Student ID *</label>
                                        <input type="text" name="studentid" class="form-control" required 
                                               value="<?php echo htmlspecialchars($student['studentid']); ?>">
                                        <small class="form-text text-muted">Unique identifier for the student</small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Update Student</button>
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