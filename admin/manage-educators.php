<?php
require_once('../configurations/configurations.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Handle form actions
if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch($action) {
        case 'add_teacher':
            $teacher_name = mysqli_real_escape_string($con, $_POST['teacher_name']);
            $age = mysqli_real_escape_string($con, $_POST['age']);
            $contact = mysqli_real_escape_string($con, $_POST['contact']);
            $address = mysqli_real_escape_string($con, $_POST['address']);
            $email = mysqli_real_escape_string($con, $_POST['email']);
            $password = mysqli_real_escape_string($con, $_POST['password']);
            $handled_subject = mysqli_real_escape_string($con, $_POST['handled_subject']);
            $status = mysqli_real_escape_string($con, $_POST['status']);
            
            // Hash the password
            $hashed_password = md5($password);
            
            $query = "INSERT INTO educators (teacher_name, age, contact, address, email, password, handled_subject, status, created_at) 
                     VALUES ('$teacher_name', '$age', '$contact', '$address', '$email', '$hashed_password', '$handled_subject', '$status', NOW())";
            
            if(mysqli_query($con, $query)) {
                $_SESSION['success'] = "Teacher added successfully!";
            } else {
                $_SESSION['error'] = "Error adding teacher: " . mysqli_error($con);
            }
            break;
            
        case 'edit_teacher':
            $id = mysqli_real_escape_string($con, $_POST['id']);
            $teacher_name = mysqli_real_escape_string($con, $_POST['teacher_name']);
            $age = mysqli_real_escape_string($con, $_POST['age']);
            $contact = mysqli_real_escape_string($con, $_POST['contact']);
            $address = mysqli_real_escape_string($con, $_POST['address']);
            $email = mysqli_real_escape_string($con, $_POST['email']);
            $handled_subject = mysqli_real_escape_string($con, $_POST['handled_subject']);
            $status = mysqli_real_escape_string($con, $_POST['status']);
            
            $query = "UPDATE educators SET 
                     teacher_name = '$teacher_name', 
                     age = '$age', 
                     contact = '$contact', 
                     address = '$address', 
                     email = '$email', 
                     handled_subject = '$handled_subject', 
                     status = '$status',
                     updated_at = NOW()
                     WHERE id = '$id'";
            
            if(mysqli_query($con, $query)) {
                $_SESSION['success'] = "Teacher updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating teacher: " . mysqli_error($con);
            }
            break;
            
        case 'delete_teacher':
            $id = mysqli_real_escape_string($con, $_POST['id']);
            
            $query = "DELETE FROM educators WHERE id = '$id'";
            
            if(mysqli_query($con, $query)) {
                $_SESSION['success'] = "Teacher deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting teacher: " . mysqli_error($con);
            }
            break;
    }
    
    header("Location: manage-educators.php");
    exit();
}

// Fetch all teachers
$query = "SELECT * FROM educators ORDER BY created_at DESC";
$result = mysqli_query($con, $query);
$teachers = array();
while($row = mysqli_fetch_assoc($result)) {
    $teachers[] = $row;
}

// Get statistics
$total_teachers = count($teachers);
$active_teachers = 0;
$pending_teachers = 0;
$subject_counts = [
    'english' => 0,
    'ap' => 0,
    'filipino' => 0,
    'math' => 0,
    'science' => 0
];

foreach($teachers as $teacher) {
    if($teacher['status'] == 'active') $active_teachers++;
    if($teacher['status'] == 'pending') $pending_teachers++;

    // Split multiple handled subjects by comma
    $subjects = explode(',', $teacher['handled_subject']);
    foreach($subjects as $subject) {
        $subject = trim(strtolower($subject)); // Clean whitespace & make lowercase
        if(isset($subject_counts[$subject])) {
            $subject_counts[$subject]++;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Educators - Play2Review Admin</title>
    
    <?php include('includes/header.php'); ?>
    <style>
    :root {
        --primary: #0A5F38;
        --secondary: #0D7A47;
        --dark: #08482B;
        --light: #F8FDF8;
        --success: #0F8A50;
        --warning: #FFA726;
        --info: #0C6B3F;
    }
    
    .add-new-btn {
        background: linear-gradient(135deg, #0A5F38 0%, #08482B 100%);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    
    .add-new-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(10, 95, 56, 0.3);
    }
    
    .search-box {
        border-radius: 25px;
        border: 2px solid #0A5F38;
        padding: 8px 20px;
    }
    
    .filter-select {
        border-radius: 25px;
        border: 2px solid #0D7A47;
    }
    
    .stats-card {
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        background: white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .stats-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--primary);
    }
    
    .stats-label {
        font-size: 1rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .teacher-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0A5F38 0%, #0D7A47 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 10px;
    }
    
    .teacher-name-cell {
        display: flex;
        align-items: center;
    }
    
    .subject-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        color: white;
    }
    
    .badge-english { background-color: #0A5F38; }
    .badge-ap { background-color: #0D7A47; }
    .badge-filipino { background-color: #0F8A50; }
    .badge-math { background-color: #0C6B3F; }
    .badge-science { background-color: #08482B; }
    
    .status-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
    }
    
    .badge-active { background-color: #0A5F38; color: white; }
    .badge-inactive { background-color: #6c757d; color: white; }
    .badge-pending { background-color: #FFA726; color: #212529; }
    
    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .table th {
        background: linear-gradient(135deg, #0A5F38 0%, #08482B 100%);
        color: white;
        border: none;
        font-weight: bold;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(10, 95, 56, 0.1);
    }
    
    .action-buttons {
        white-space: nowrap;
    }
    
    .btn-action {
        margin: 2px;
        font-size: 0.8rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 5rem;
        margin-bottom: 20px;
        color: #dee2e6;
    }
    
    .dashboard-title {
        color: #0A5F38;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    } 
    
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        background: linear-gradient(135deg, #0A5F38 0%, #0D7A47 100%);
        color: white;
        border-radius: 10px 10px 0 0 !important;
        font-weight: bold;
    }
    
    .teacher-card-header {
        background: linear-gradient(135deg, #0A5F38 0%, #0C6B3F 100%);
        color: white;
        font-weight: bold;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #0A5F38 0%, #0D7A47 100%);
        color: white;
    }
    
    .contact-info {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .age-badge {
        background: #E8F5E8;
        color: #0A5F38;
        padding: 0.3rem 0.6rem;
        border-radius: 15px;
        font-size: 0.8rem;
    }

    /* Additional green theme elements */
    .bg-gradient-primary { 
        background: linear-gradient(135deg, #0A5F38 0%, #0D7A47 100%) !important; 
        color: white; 
    }
    
    .bg-gradient-success { 
        background: linear-gradient(135deg, #0F8A50 0%, #0D7A47 100%) !important; 
        color: white; 
    }
    
    .bg-gradient-info { 
        background: linear-gradient(135deg, #0C6B3F 0%, #0A5F38 100%) !important; 
        color: white; 
    }
    
    .bg-gradient-warning { 
        background: linear-gradient(135deg, #FFA726 0%, #F57C00 100%) !important; 
        color: white; 
    }
</style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include('includes/topbar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 dashboard-title">Manage Educators</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manage Educators</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $total_teachers; ?></div>
                            <div class="stats-label">Total Teachers</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $active_teachers; ?></div>
                            <div class="stats-label">Active Teachers</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $pending_teachers; ?></div>
                            <div class="stats-label">Pending Approval</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo array_sum($subject_counts); ?></div>
                            <div class="stats-label">Subjects Covered</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons and Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button class="btn add-new-btn" data-toggle="modal" data-target="#addTeacherModal">
                            <i class="fas fa-plus-circle"></i> Add New Teacher
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control search-box" placeholder="Search teachers...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Teachers Table -->
                <div class="card">
                    <div class="card-header teacher-card-header">
                        <h3 class="card-title">Educators List</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="icon fas fa-check"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="icon fas fa-ban"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table id="educatorsTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Teacher</th>
                                        <th>Contact Info</th>
                                        <th>Subject</th>
                                        <th>Age</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($teachers)): ?>
                                        <tr>
                                            <td colspan="7">
                                                <div class="empty-state">
                                                    <i class="fas fa-chalkboard-teacher"></i>
                                                    <h4>No Teachers Found</h4>
                                                    <p>Get started by adding your first teacher.</p>
                                                    <button class="btn add-new-btn" data-toggle="modal" data-target="#addTeacherModal">
                                                        Add Teacher
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($teachers as $teacher): ?>
                                        <tr>
                                            <td>
                                                <div class="teacher-name-cell">
                                                    <div class="teacher-avatar">
                                                        <?php echo strtoupper(substr($teacher['teacher_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($teacher['teacher_name']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($teacher['email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($teacher['contact']); ?>
                                                    <br>
                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(substr($teacher['address'], 0, 30)) . '...'; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="subject-badge badge-<?php echo $teacher['handled_subject']; ?>">
                                                    <?php echo strtoupper($teacher['handled_subject']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="age-badge"><?php echo $teacher['age']; ?> years</span>
                                            </td>
                                            <td>
                                                <span class="status-badge badge-<?php echo $teacher['status']; ?>">
                                                    <?php echo ucfirst($teacher['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($teacher['created_at'])); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-info btn-sm btn-action view-teacher" 
                                                        data-id="<?php echo $teacher['id']; ?>"
                                                        data-toggle="modal" data-target="#viewTeacherModal">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning btn-sm btn-action edit-teacher" 
                                                        data-id="<?php echo $teacher['id']; ?>"
                                                        data-toggle="modal" data-target="#editTeacherModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm btn-action delete-teacher" 
                                                        data-id="<?php echo $teacher['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($teacher['teacher_name']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include('includes/footer.php'); ?>
</div>

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Teacher</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_teacher">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="teacher_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Age *</label>
                                <input type="number" name="age" class="form-control" min="20" max="70" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number *</label>
                                <input type="text" name="contact" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address *</label>
                        <textarea name="address" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Handled Subject *</label>
                                <select name="handled_subject" class="form-control" required>
                                    <option value="">Select Subject</option>
                                    <option value="english">English</option>
                                    <option value="ap">AP</option>
                                    <option value="filipino">Filipino</option>
                                    <option value="math">Math</option>
                                    <option value="science">Science</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn add-new-btn">Add Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Teacher Modal -->
<div class="modal fade" id="editTeacherModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Teacher</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_teacher">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" name="teacher_name" id="edit_teacher_name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Age *</label>
                                <input type="number" name="age" id="edit_age" class="form-control" min="20" max="70" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number *</label>
                                <input type="text" name="contact" id="edit_contact" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email Address *</label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Address *</label>
                        <textarea name="address" id="edit_address" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Handled Subject *</label>
                                <select name="handled_subject" id="edit_handled_subject" class="form-control" required>
                                    <option value="english">English</option>
                                    <option value="ap">AP</option>
                                    <option value="filipino">Filipino</option>
                                    <option value="math">Math</option>
                                    <option value="science">Science</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status *</label>
                                <select name="status" id="edit_status" class="form-control" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="pending">Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Teacher Modal -->
<div class="modal fade" id="viewTeacherModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Teacher Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewTeacherContent">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTeacherModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_teacher">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Are you sure you want to delete teacher: <strong id="delete_teacher_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#educatorsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [],
        "columnDefs": [{ "orderable": false, "targets": [-1] }]
    });

    // Edit Teacher
    $('.edit-teacher').click(function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        
        // You would typically fetch the full teacher data via AJAX here
        // For now, we'll use the data from the table row
        $('#edit_id').val(id);
        $('#edit_teacher_name').val(row.find('strong').text().trim());
        $('#edit_age').val(row.find('.age-badge').text().replace(' years', '').trim());
        $('#edit_contact').val(row.find('.contact-info').html().split('<br>')[0].replace('<i class="fas fa-phone"></i>', '').trim());
        $('#edit_email').val(row.find('small.text-muted').text().trim());
        $('#edit_address').val(row.find('.contact-info').html().split('<br>')[1].replace('<i class="fas fa-map-marker-alt"></i>', '').replace('...', '').trim());
        
        // Get subject from badge class
        var subjectClass = row.find('.subject-badge').attr('class').split(' ').find(cls => cls.startsWith('badge-'));
        $('#edit_handled_subject').val(subjectClass.replace('badge-', ''));
        
        // Get status from badge class
        var statusClass = row.find('.status-badge').attr('class').split(' ').find(cls => cls.startsWith('badge-'));
        $('#edit_status').val(statusClass.replace('badge-', ''));
    });

    // View Teacher
    $('.view-teacher').click(function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: 'get_teacher_details.php',
            type: 'POST',
            data: {id: id},
            success: function(response) {
                $('#viewTeacherContent').html(response);
            }
        });
    });

    // Delete Teacher
    $('.delete-teacher').click(function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        $('#delete_id').val(id);
        $('#delete_teacher_name').text(name);
        $('#deleteTeacherModal').modal('show');
    });

    // Search functionality
    $('.search-box').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>
</body>
</html>