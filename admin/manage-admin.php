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
        case 'add_admin':
            $firstName = mysqli_real_escape_string($con, $_POST['firstName']);
            $middleName = mysqli_real_escape_string($con, $_POST['middleName']);
            $lastName = mysqli_real_escape_string($con, $_POST['lastName']);
            $username = mysqli_real_escape_string($con, $_POST['username']);
            $password = mysqli_real_escape_string($con, $_POST['password']);
            $position = mysqli_real_escape_string($con, $_POST['position']);
            $contact_number = mysqli_real_escape_string($con, $_POST['contact_number']);
            $is_active = mysqli_real_escape_string($con, $_POST['is_active']);
            
            // Hash the password
            $hashed_password = md5($password);
            
            // Handle profile image upload
            $profileImage = '';
            if(isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
                $target_dir = "uploads/profiles/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $file_extension = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
                $profileImage = 'profile-' . time() . '-' . basename($_FILES['profileImage']['name']);
                $target_file = $target_dir . $profileImage;
                move_uploaded_file($_FILES['profileImage']['tmp_name'], $target_file);
            }
            
            $query = "INSERT INTO admin (firstName, middleName, lastName, username, password, profileImage, position, contact_number, is_active, created_at) 
                     VALUES ('$firstName', '$middleName', '$lastName', '$username', '$hashed_password', '$profileImage', '$position', '$contact_number', '$is_active', NOW())";
            
            if(mysqli_query($con, $query)) {
                $_SESSION['success'] = "Admin added successfully!";
            } else {
                $_SESSION['error'] = "Error adding admin: " . mysqli_error($con);
            }
            break;
            
        case 'edit_admin':
            $id = mysqli_real_escape_string($con, $_POST['id']);
            $firstName = mysqli_real_escape_string($con, $_POST['firstName']);
            $middleName = mysqli_real_escape_string($con, $_POST['middleName']);
            $lastName = mysqli_real_escape_string($con, $_POST['lastName']);
            $username = mysqli_real_escape_string($con, $_POST['username']);
            $position = mysqli_real_escape_string($con, $_POST['position']);
            $contact_number = mysqli_real_escape_string($con, $_POST['contact_number']);
            $is_active = mysqli_real_escape_string($con, $_POST['is_active']);
            
            // Handle profile image upload
            $profileImageUpdate = '';
            if(isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] == 0) {
                $target_dir = "uploads/profiles/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $file_extension = pathinfo($_FILES['profileImage']['name'], PATHINFO_EXTENSION);
                $profileImage = 'profile-' . time() . '-' . basename($_FILES['profileImage']['name']);
                $target_file = $target_dir . $profileImage;
                move_uploaded_file($_FILES['profileImage']['tmp_name'], $target_file);
                $profileImageUpdate = ", profileImage = '$profileImage'";
            }
            
            // Handle password update
            $passwordUpdate = '';
            if(!empty($_POST['password'])) {
                $password = mysqli_real_escape_string($con, $_POST['password']);
                $hashed_password = md5($password);
                $passwordUpdate = ", password = '$hashed_password'";
            }
            
            $query = "UPDATE admin SET 
                     firstName = '$firstName', 
                     middleName = '$middleName', 
                     lastName = '$lastName', 
                     username = '$username', 
                     position = '$position', 
                     contact_number = '$contact_number', 
                     is_active = '$is_active'
                     $passwordUpdate
                     $profileImageUpdate,
                     updated_at = NOW()
                     WHERE admin_ID = '$id'";
            
            if(mysqli_query($con, $query)) {
                $_SESSION['success'] = "Admin updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating admin: " . mysqli_error($con);
            }
            break;
            
        case 'delete_admin':
            $id = mysqli_real_escape_string($con, $_POST['id']);
            
            // Prevent deleting yourself
            if($id == $_SESSION['admin_ID']) {
                $_SESSION['error'] = "You cannot delete your own account!";
            } else {
                $query = "DELETE FROM admin WHERE admin_ID = '$id'";
                
                if(mysqli_query($con, $query)) {
                    $_SESSION['success'] = "Admin deleted successfully!";
                } else {
                    $_SESSION['error'] = "Error deleting admin: " . mysqli_error($con);
                }
            }
            break;
    }
    
    header("Location: manage-admin.php");
    exit();
}

// Fetch all admins
$query = "SELECT * FROM admin ORDER BY created_at DESC";
$result = mysqli_query($con, $query);
$admins = array();
while($row = mysqli_fetch_assoc($result)) {
    $admins[] = $row;
}

// Get statistics
$total_admins = count($admins);
$active_admins = 0;
$inactive_admins = 0;
$position_counts = [
    'Administrator' => 0,
    'Moderator' => 0,
    'Staff' => 0
];

foreach($admins as $admin) {
    if($admin['is_active'] == 1) $active_admins++;
    if($admin['is_active'] == 0) $inactive_admins++;
    
    $position = $admin['position'];
    if(isset($position_counts[$position])) {
        $position_counts[$position]++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Admins - Play2Review Admin</title>
    
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
    
    .admin-avatar {
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
    
    .admin-name-cell {
        display: flex;
        align-items: center;
    }
    
    .role-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        color: white;
    }
    
    .badge-Administrator { background-color: #0A5F38; }
    .badge-Moderator { background-color: #0D7A47; }
    .badge-Staff { background-color: #0C6B3F; }
    
    .status-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
    }
    
    .badge-1 { background-color: #0A5F38; color: white; }
    .badge-0 { background-color: #6c757d; color: white; }
    
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
    
    .admin-card-header {
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
                        <h1 class="m-0 dashboard-title">Manage Admins</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manage Admins</li>
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
                            <div class="stats-number"><?php echo $total_admins; ?></div>
                            <div class="stats-label">Total Admins</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $active_admins; ?></div>
                            <div class="stats-label">Active Admins</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $inactive_admins; ?></div>
                            <div class="stats-label">Inactive Admins</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $position_counts['Administrator']; ?></div>
                            <div class="stats-label">Administrators</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons and Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button class="btn add-new-btn" data-toggle="modal" data-target="#addAdminModal">
                            <i class="fas fa-plus-circle"></i> Add New Admin
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control search-box" placeholder="Search admins...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admins Table -->
                <div class="card">
                    <div class="card-header admin-card-header">
                        <h3 class="card-title">Administrators List</h3>
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
                            <table id="adminsTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Profile</th>
                                        <th>Admin Name</th>
                                        <th>Username</th>
                                        <th>Contact</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($admins)): ?>
                                        <tr>
                                            <td colspan="8">
                                                <div class="empty-state">
                                                    <i class="fas fa-user-shield"></i>
                                                    <h4>No Admins Found</h4>
                                                    <p>Get started by adding your first admin.</p>
                                                    <button class="btn add-new-btn" data-toggle="modal" data-target="#addAdminModal">
                                                        Add Admin
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($admins as $admin): ?>
                                        <tr>
                                            <td>
                                                <?php if(!empty($admin['profileImage'])): ?>
                                                    <img src="uploads/profiles/<?php echo htmlspecialchars($admin['profileImage']); ?>" 
                                                         alt="Profile" 
                                                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="admin-avatar">
                                                        <?php echo strtoupper(substr($admin['firstName'], 0, 1)); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($admin['firstName'] . ' ' . $admin['middleName'] . ' ' . $admin['lastName']); ?></strong>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo htmlspecialchars($admin['username']); ?></small>
                                            </td>
                                            <td>
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($admin['contact_number']); ?>
                                            </td>
                                            <td>
                                                <span class="role-badge badge-<?php echo str_replace(' ', '', $admin['position']); ?>">
                                                    <?php echo strtoupper($admin['position']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge badge-<?php echo $admin['is_active']; ?>">
                                                    <?php echo $admin['is_active'] == 1 ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($admin['created_at'])); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-info btn-sm btn-action view-admin" 
                                                        data-id="<?php echo $admin['admin_ID']; ?>"
                                                        data-toggle="modal" data-target="#viewAdminModal">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning btn-sm btn-action edit-admin" 
                                                        data-id="<?php echo $admin['admin_ID']; ?>"
                                                        data-toggle="modal" data-target="#editAdminModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if($admin['admin_ID'] != $_SESSION['admin_ID']): ?>
                                                <button class="btn btn-danger btn-sm btn-action delete-admin" 
                                                        data-id="<?php echo $admin['admin_ID']; ?>"
                                                        data-name="<?php echo htmlspecialchars($admin['firstName'] . ' ' . $admin['lastName']); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
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

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Admin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_admin">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="firstName" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="middleName" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="lastName" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username (Email) *</label>
                                <input type="email" name="username" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number *</label>
                                <input type="text" name="contact_number" class="form-control" required>
                            </div>
                        </div>
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
                                <label>Position *</label>
                                <select name="position" class="form-control" required>
                                    <option value="">Select Position</option>
                                    <option value="Administrator">Administrator</option>
                                    <option value="Moderator">Moderator</option>
                                    <option value="Staff">Staff</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Profile Image</label>
                                <input type="file" name="profileImage" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status *</label>
                                <select name="is_active" class="form-control" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn add-new-btn">Add Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Admin</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_admin">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>First Name *</label>
                                <input type="text" name="firstName" id="edit_firstName" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="middleName" id="edit_middleName" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Last Name *</label>
                                <input type="text" name="lastName" id="edit_lastName" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Username (Email) *</label>
                                <input type="email" name="username" id="edit_username" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Number *</label>
                                <input type="text" name="contact_number" id="edit_contact_number" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password (Leave blank to keep current)</label>
                                <input type="password" name="password" id="edit_password" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Position *</label>
                                <select name="position" id="edit_position" class="form-control" required>
                                    <option value="Administrator">Administrator</option>
                                    <option value="Moderator">Moderator</option>
                                    <option value="Staff">Staff</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Profile Image</label>
                                <input type="file" name="profileImage" class="form-control" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status *</label>
                                <select name="is_active" id="edit_is_active" class="form-control" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>m>
        </div>
    </div>
</div>

<!-- View Admin Modal -->
<div class="modal fade" id="viewAdminModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Admin Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewAdminContent">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAdminModal" tabindex="-1" role="dialog">
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
                    <input type="hidden" name="action" value="delete_admin">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Are you sure you want to delete admin: <strong id="delete_admin_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#adminsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [],
        "columnDefs": [{ "orderable": false, "targets": [-1] }]
    });

    // Edit Admin - Fetch data via AJAX
    $('.edit-admin').click(function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: 'get_admin_data.php',
            type: 'POST',
            data: {id: id},
            dataType: 'json',
            success: function(data) {
                $('#edit_id').val(data.admin_ID);
                $('#edit_firstName').val(data.firstName);
                $('#edit_middleName').val(data.middleName);
                $('#edit_lastName').val(data.lastName);
                $('#edit_username').val(data.username);
                $('#edit_contact_number').val(data.contact_number);
                $('#edit_position').val(data.position);
                $('#edit_is_active').val(data.is_active);
            }
        });
    });

    // View Admin
    $('.view-admin').click(function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: 'get_admin_details.php',
            type: 'POST',
            data: {id: id},
            success: function(response) {
                $('#viewAdminContent').html(response);
            }
        });
    });

    // Delete Admin
    $('.delete-admin').click(function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        $('#delete_id').val(id);
        $('#delete_admin_name').text(name);
        $('#deleteAdminModal').modal('show');
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