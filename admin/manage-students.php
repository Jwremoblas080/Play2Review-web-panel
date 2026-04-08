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
        case 'add_student':
            $player_name = mysqli_real_escape_string($con, $_POST['player_name']);
            $student_id = mysqli_real_escape_string($con, $_POST['student_id']);
            $username = mysqli_real_escape_string($con, $_POST['username']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (player_name, student_id, username, password, created_at) 
                     VALUES ('$player_name', '$student_id', '$username', '$password', NOW())";
            
            if(mysqli_query($con, $query)) {
                $_SESSION['success'] = "Student added successfully!";
            } else {
                $_SESSION['error'] = "Error adding student: " . mysqli_error($con);
            }
            break;
            
        case 'edit_student':
            $id = mysqli_real_escape_string($con, $_POST['id']);
            $player_name = mysqli_real_escape_string($con, $_POST['player_name']);
            $student_id = mysqli_real_escape_string($con, $_POST['student_id']);
            $username = mysqli_real_escape_string($con, $_POST['username']);
            
            $query = "UPDATE users SET 
                     player_name = '$player_name', 
                     student_id = '$student_id', 
                     username = '$username' 
                     WHERE id = '$id'";
            
            if(mysqli_query($con, $query)) {
                $_SESSION['success'] = "Student updated successfully!";
            } else {
                $_SESSION['error'] = "Error updating student: " . mysqli_error($con);
            }
            break;
            
        case 'delete_student':
            $id = mysqli_real_escape_string($con, $_POST['id']);
            
            $query = "DELETE FROM users WHERE id = '$id'";
            
            if(mysqli_query($con, $query)) {
                $_SESSION['success'] = "Student deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting student: " . mysqli_error($con);
            }
            break;
    }
    
    header("Location: manage-students.php");
    exit();
}

// Fetch all students
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($con, $query);
$students = array();
while($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}

// Get total students count
$total_students = count($students);

// Get students with completed levels
$completed_english = 0;
$completed_math = 0;
$completed_ap = 0;
$completed_filipino = 0;
$completed_science = 0;
foreach($students as $student) {
    if($student['english_completed_level'] == 10) $completed_english++;
    if($student['math_completed_level'] == 10) $completed_math++;
    if($student['ap_completed_level'] == 10) $completed_ap++;
    if($student['filipino_completed_level'] == 10) $completed_filipino++;
    if($student['science_completed_level'] == 10) $completed_science++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Students - Play2Review Admin</title>
    
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
    
    .student-avatar {
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
    
    .student-name-cell {
        display: flex;
        align-items: center;
    }
    
    .grade-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        background: #0A5F38;
        color: white;
    }
    
    .id-badge {
        font-size: 0.8rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        background: #6c757d;
        color: white;
    }
    
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
    
    .progress-sm {
        height: 8px;
    }
    
    .subject-progress {
        margin-bottom: 10px;
    }
    
    .modal-header {
        background: linear-gradient(135deg, #0A5F38 0%, #0D7A47 100%);
        color: white;
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
                        <h1 class="m-0 dashboard-title">Manage Students</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manage Students</li>
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
                            <div class="stats-number"><?php echo $total_students; ?></div>
                            <div class="stats-label">Total Students</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $completed_english; ?></div>
                            <div class="stats-label">Completed English</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $completed_math; ?></div>
                            <div class="stats-label">Completed Math</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo number_format(array_sum(array_column($students, 'feathers'))); ?></div>
                            <div class="stats-label">Total POTIONS</div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons and Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button class="btn add-new-btn" data-toggle="modal" data-target="#addStudentModal">
                            <i class="fas fa-plus-circle"></i> Add New Student
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control search-box" placeholder="Search students...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Students List</h3>
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
                            <table id="studentsTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Student ID</th>
                                        <th>Username</th>
                                        <th>Progress</th>
                                        <th>Feathers</th>
                                        <th>Lives</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($students)): ?>
                                        <tr>
                                            <td colspan="8">
                                                <div class="empty-state">
                                                    <i class="fas fa-user-graduate"></i>
                                                    <h4>No Students Found</h4>
                                                    <p>Get started by adding your first student.</p>
                                                    <button class="btn add-new-btn" data-toggle="modal" data-target="#addStudentModal">
                                                        Add Student
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($students as $student): ?>
                                        <tr>
                                            <td>
                                                <div class="student-name-cell">
                                                    <div class="student-avatar">
                                                        <?php echo strtoupper(substr($student['player_name'], 0, 1)); ?>
                                                    </div>
                                                    <?php echo htmlspecialchars($student['player_name']); ?>
                                                </div>
                                            </td>
                                            <td><span class="id-badge"><?php echo htmlspecialchars($student['student_id']); ?></span></td>
                                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                                            <td>
                                                <?php
                                                $subjects_table = [
                                                    'english'  => [
                                                        'label' => 'English',  'color' => '#0A5F38',
                                                        'cats'  => ['english_grammar_level','english_vocabulary_level','english_reading_level','english_literature_level','english_writing_level'],
                                                    ],
                                                    'ap'       => [
                                                        'label' => 'AP',       'color' => '#1565C0',
                                                        'cats'  => ['ap_ekonomiks_level','ap_kasaysayan_level','ap_kontemporaryo_level','ap_heograpiya_level','ap_pamahalaan_level'],
                                                    ],
                                                    'filipino' => [
                                                        'label' => 'Filipino', 'color' => '#6A1B9A',
                                                        'cats'  => ['filipino_gramatika_level','filipino_panitikan_level','filipino_paguunawa_level','filipino_talasalitaan_level','filipino_wika_level'],
                                                    ],
                                                    'science'  => [
                                                        'label' => 'Science',  'color' => '#00838F',
                                                        'cats'  => ['science_biology_level','science_chemistry_level','science_physics_level','science_earthscience_level','science_investigation_level'],
                                                    ],
                                                    'math'     => [
                                                        'label' => 'Math',     'color' => '#E65100',
                                                        'cats'  => ['math_algebra_level','math_geometry_level','math_statistics_level','math_probability_level','math_functions_level','math_wordproblems_level'],
                                                    ],
                                                ];
                                                foreach($subjects_table as $key => $info):
                                                    $sum = 0;
                                                    foreach($info['cats'] as $col) {
                                                        $sum += isset($student[$col]) ? (int)$student[$col] : 0;
                                                    }
                                                    $max = count($info['cats']) * 10;
                                                    $pct = round(($sum / $max) * 100);
                                                ?>
                                                <div class="subject-progress mb-1">
                                                    <div class="d-flex justify-content-between">
                                                        <small><?php echo $info['label']; ?>: <?php echo $sum; ?>/<?php echo $max; ?></small>
                                                        <small><?php echo $pct; ?>%</small>
                                                    </div>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar" style="width:<?php echo $pct; ?>%;background-color:<?php echo $info['color']; ?>;"></div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </td>
                                            <td><span class="badge badge-warning"><?php echo $student['feathers']; ?></span></td>
                                            <td><span class="badge badge-danger"><?php echo $student['lives']; ?></span></td>
                                            <td><?php echo date('M j, Y', strtotime($student['created_at'])); ?></td>
                                            <td class="action-buttons">
                                                <button class="btn btn-info btn-sm btn-action view-student" 
                                                        data-id="<?php echo $student['id']; ?>"
                                                        data-toggle="modal" data-target="#viewStudentModal">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-warning btn-sm btn-action edit-student" 
                                                        data-id="<?php echo $student['id']; ?>"
                                                        data-toggle="modal" data-target="#editStudentModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm btn-action delete-student" 
                                                        data-id="<?php echo $student['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($student['player_name']); ?>">
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

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_student">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="player_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn add-new-btn">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_student">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="player_name" id="edit_player_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" id="edit_student_id" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Student Modal -->
<div class="modal fade" id="viewStudentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewStudentContent">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteStudentModal" tabindex="-1" role="dialog">
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
                    <input type="hidden" name="action" value="delete_student">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Are you sure you want to delete student: <strong id="delete_student_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Edit Student
    $('.edit-student').click(function() {
        var id = $(this).data('id');
        var row = $(this).closest('tr');
        
        $('#edit_id').val(id);
        $('#edit_player_name').val(row.find('td:eq(0)').text().trim());
        $('#edit_student_id').val(row.find('.id-badge').text().trim());
        $('#edit_username').val(row.find('td:eq(2)').text().trim());
    });

    // View Student
    $('.view-student').click(function() {
        var id = $(this).data('id');
        $('#viewStudentContent').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading...</p></div>');
        
        $.ajax({
            url: 'get_student_progress_details.php',
            type: 'POST',
            data: {id: id},
            success: function(response) {
                $('#viewStudentContent').html(response);
            },
            error: function() {
                $('#viewStudentContent').html('<div class="alert alert-danger">Failed to load student details.</div>');
            }
        });
    });

    // Delete Student
    $('.delete-student').click(function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        
        $('#delete_id').val(id);
        $('#delete_student_name').text(name);
        $('#deleteStudentModal').modal('show');
    });

    // Search functionality handled by DataTables
    $('#studentsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [],
        "columnDefs": [{ "orderable": false, "targets": [3, 7] }]
    });
});
</script>
</body>
</html>