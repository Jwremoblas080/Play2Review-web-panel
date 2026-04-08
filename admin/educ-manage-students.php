<?php
require_once('../configurations/configurations.php');

// Check educator privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'educator') {
    header("Location: logout.php");
    exit();
}

// Get educator's handled subjects
$educator_id = $_SESSION['user_id'];
$educator_query = "SELECT handled_subject FROM educators WHERE id = '$educator_id'";
$educator_result = mysqli_query($con, $educator_query);
$educator_data = mysqli_fetch_assoc($educator_result);

// Parse handled subjects (comma-separated string)
$handled_subjects = explode(',', $educator_data['handled_subject']);
$handled_subjects = array_filter($handled_subjects); // Remove empty values

// If no subjects are assigned, show empty array
if(empty($handled_subjects)) {
    $handled_subjects = [];
}

// Subject filter - ONLY ALLOW HANDLED SUBJECTS
$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : 'all';
$valid_subjects = array_merge(['all'], $handled_subjects); // Only allow handled subjects + 'all'

// Validate subject filter - only show subjects this educator handles
if(!in_array($subject_filter, $valid_subjects)) {
    $subject_filter = 'all';
}

// Subject names mapping
$subject_names = [
    'all' => 'All Students',
    'english' => 'English',
    'ap' => 'Araling Panlipunan',
    'filipino' => 'Filipino',
    'math' => 'Mathematics',
    'science' => 'Science'
];

$current_subject_name = $subject_names[$subject_filter] ?? 'All Students';

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
    
    header("Location: educ-manage-students.php?subject=" . $subject_filter);
    exit();
}

// Build query based on subject filter - ONLY FOR HANDLED SUBJECTS
if($subject_filter == 'all') {
    $query = "SELECT * FROM users ORDER BY created_at DESC";
} else {
    // Only filter by subjects this educator handles
    if(in_array($subject_filter, $handled_subjects)) {
        $query = "SELECT * FROM users WHERE {$subject_filter}_completed_level > 0 ORDER BY created_at DESC";
    } else {
        $query = "SELECT * FROM users ORDER BY created_at DESC";
    }
}

$result = mysqli_query($con, $query);
$students = array();
while($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}

// Get total students count
$total_students = count($students);

// Get students with completed levels - ONLY FOR HANDLED SUBJECTS
$completed_english = 0;
$completed_math = 0;
$completed_ap = 0;
$completed_filipino = 0;
$completed_science = 0;

// Get active students per subject - ONLY FOR HANDLED SUBJECTS
$active_english = 0;
$active_ap = 0;
$active_filipino = 0;
$active_math = 0;
$active_science = 0;

foreach($students as $student) {
    // Only count completion for handled subjects
    if(in_array('english', $handled_subjects) && $student['english_completed_level'] == 10) $completed_english++;
    if(in_array('math', $handled_subjects) && $student['math_completed_level'] == 10) $completed_math++;
    if(in_array('ap', $handled_subjects) && $student['ap_completed_level'] == 10) $completed_ap++;
    if(in_array('filipino', $handled_subjects) && $student['filipino_completed_level'] == 10) $completed_filipino++;
    if(in_array('science', $handled_subjects) && $student['science_completed_level'] == 10) $completed_science++;
    
    // Only count active students for handled subjects
    if(in_array('english', $handled_subjects) && $student['english_completed_level'] > 0) $active_english++;
    if(in_array('ap', $handled_subjects) && $student['ap_completed_level'] > 0) $active_ap++;
    if(in_array('filipino', $handled_subjects) && $student['filipino_completed_level'] > 0) $active_filipino++;
    if(in_array('math', $handled_subjects) && $student['math_completed_level'] > 0) $active_math++;
    if(in_array('science', $handled_subjects) && $student['science_completed_level'] > 0) $active_science++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Students - Play2Review Educator</title>
    
    <?php include('includes/educ_header.php'); ?>
    <style>
        /* Your existing CSS styles remain the same */
        :root {
            --primary: #0A5F38;
            --secondary: #1E7D4E;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --success: #1cc88a;
            --warning: #f6c23e;
            --info: #36b9cc;
        }
        
        .add-new-btn {
            background: linear-gradient(135deg, #0A5F38 0%, #064527 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        /* ... rest of your CSS styles ... */
        
        .no-subjects-message {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .no-subjects-message i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .subject-disabled {
            opacity: 0.5;
            pointer-events: none;
        }
        .progress-bar {
    display: -ms-flexbox;
    display: flex;
    -ms-flex-direction: column;
    flex-direction: column;
    -ms-flex-pack: center;
    justify-content: center;
    overflow: hidden;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    background-color: #0A5F38 !important;
    transition: width .6s 
ease;
}
        .handled-subject-indicator {
            background: #0A5F38;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            margin-left: 5px;
        }
        
        .stats-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .dashboard-title {
            color: #0A5F38;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include('includes/educ_topbar.php'); ?>
    <?php include('includes/educ_sidebar.php'); ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->

        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 dashboard-title">Manage Students </h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-chalkboard-teacher mr-1"></i>
                            My Subjects: 
                            <?php if(!empty($handled_subjects)): ?>
                                <?php 
                                $display_subjects = array_map(function($subject) use ($subject_names) {
                                    return $subject_names[$subject];
                                }, $handled_subjects);
                                echo implode(', ', $display_subjects);
                                ?>
                            <?php else: ?>
                                <span class="text-danger">No subjects assigned</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="educator_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manage Students</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <?php if(empty($handled_subjects)): ?>
                    <div class="no-subjects-message">
                        <i class="fas fa-book-open"></i>
                        <h4>No Subjects Assigned</h4>
                        <p>You haven't been assigned any subjects to monitor yet.</p>
                        <p class="text-muted">Please contact the administrator to get subjects assigned to your account.</p>
                    </div>
                <?php else: ?>
                
                <!-- Subject Filter -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Filter by Subject</h6>
                                <div class="btn-group" role="group" style="margin-left:2vh;">
                                    <a href="?subject=all" 
                                       class="btn btn-outline-primary <?php echo $subject_filter == 'all' ? 'subject-filter-active' : ''; ?>">
                                        All Students
                                    </a>
                                    <?php foreach(['english', 'ap', 'filipino', 'science', 'math'] as $subject): ?>
                                        <?php if(in_array($subject, $handled_subjects)): ?>
                                            <a href="?subject=<?php echo $subject; ?>" 
                                               class="btn btn-outline-primary <?php echo $subject_filter == $subject ? 'subject-filter-active' : ''; ?>">
                                                <span class="subject-indicator indicator-<?php echo $subject; ?>"></span> 
                                                <?php echo $subject_names[$subject]; ?>
                                                <span class="handled-subject-indicator">My Subject</span>
                                            </a>
                                        <?php else: ?>
                                            <a href="#" class="btn btn-outline-secondary subject-disabled" title="Not your assigned subject">
                                                <span class="subject-indicator indicator-<?php echo $subject; ?>"></span> 
                                                <?php echo $subject_names[$subject]; ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards - ONLY SHOW HANDLED SUBJECTS -->
                <div class="row mb-4">
                    <div class="col-lg-2 col-md-4">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $total_students; ?></div>
                            <div class="stats-label">
                                <?php echo $subject_filter == 'all' ? 'Total Students' : 'Active Students'; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php foreach($handled_subjects as $subject): ?>
                        <div class="col-lg-2 col-md-4">
                            <div class="stats-card">
                                <div class="stats-number">
                                    <?php 
                                    switch($subject) {
                                        case 'english': echo $active_english; break;
                                        case 'ap': echo $active_ap; break;
                                        case 'filipino': echo $active_filipino; break;
                                        case 'science': echo $active_science; break;
                                        case 'math': echo $active_math; break;
                                        default: echo '0';
                                    }
                                    ?>
                                </div>
                                <div class="stats-label">Taking <?php echo $subject_names[$subject]; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Fill remaining columns if needed -->
                    <?php 
                    $displayed_stats = 1 + count($handled_subjects);
                    $remaining_cols = 6 - $displayed_stats;
                    for($i = 0; $i < $remaining_cols; $i++): 
                    ?>
                        <div class="col-lg-2 col-md-4">
                            <div class="stats-card" style="opacity: 0.3;">
                                <div class="stats-number">-</div>
                                <div class="stats-label">No Subject</div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- Completion Statistics - ONLY HANDLED SUBJECTS -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">My Subjects Completion Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <?php foreach($handled_subjects as $subject): ?>
                                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                                            <div class="border rounded p-3">
                                                <div class="text-<?php echo $subject; ?> font-weight-bold" style="font-size: 1.5rem;">
                                                    <?php 
                                                    switch($subject) {
                                                        case 'english': echo $completed_english; break;
                                                        case 'ap': echo $completed_ap; break;
                                                        case 'filipino': echo $completed_filipino; break;
                                                        case 'science': echo $completed_science; break;
                                                        case 'math': echo $completed_math; break;
                                                        default: echo '0';
                                                    }
                                                    ?>
                                                </div>
                                                <small class="text-muted">Completed <?php echo $subject_names[$subject]; ?></small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- Total Potions -->
                                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <div class="font-weight-bold text-primary" style="font-size: 1.5rem;">
                                                <?php echo number_format(array_sum(array_column($students, 'feathers'))); ?>
                                            </div>
                                            <small class="text-muted">Total Feathers</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Fill remaining columns -->
                                    <?php 
                                    $displayed_completion = count($handled_subjects) + 1;
                                    $remaining_completion = 6 - $displayed_completion;
                                    for($i = 0; $i < $remaining_completion; $i++): 
                                    ?>
                                        <div class="col-lg-2 col-md-4 col-6 mb-3">
                                            <div class="border rounded p-3" style="opacity: 0.3;">
                                                <div class="font-weight-bold" style="font-size: 1.5rem;">-</div>
                                                <small class="text-muted">No Data</small>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
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
                        <h3 class="card-title">
                            Students List 
                            <?php if($subject_filter != 'all'): ?>
                                <small class="text-muted">- Showing students taking <?php echo $subject_names[$subject_filter]; ?></small>
                            <?php endif; ?>
                        </h3>
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
                            <table id="educStudentsTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Student ID</th>
                                        <th>Username</th>
                                        <th>Progress in My Subjects</th>
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
                                                    <h4>
                                                        <?php if($subject_filter == 'all'): ?>
                                                            No Students Found
                                                        <?php else: ?>
                                                            No Students Taking <?php echo $subject_names[$subject_filter]; ?>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <p>
                                                        <?php if($subject_filter == 'all'): ?>
                                                            Get started by adding your first student.
                                                        <?php else: ?>
                                                            No students have started <?php echo $subject_names[$subject_filter]; ?> yet.
                                                        <?php endif; ?>
                                                    </p>
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
                                                $displayed = 0;
                                                // Only show progress for handled subjects
                                                foreach($handled_subjects as $subject): 
                                                    $level = $student[$subject . '_completed_level'];
                                                    if($level > 0 || $subject_filter == 'all'): 
                                                        $displayed++;
                                                        if($displayed <= 3): // Show only first 3 subjects to save space
                                                ?>
                                                <div class="subject-progress">
                                                    <small>
                                                        <span class="subject-indicator indicator-<?php echo $subject; ?>"></span>
                                                        <?php echo $subject_names[$subject]; ?>: <?php echo $level; ?>/10
                                                    </small>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar bg-<?php echo $subject; ?>" style="width: <?php echo ($level / 10) * 100; ?>%"></div>
                                                    </div>
                                                </div>
                                                <?php 
                                                        endif;
                                                    endif;
                                                endforeach; 
                                                if($displayed > 3): 
                                                ?>
                                                <small class="text-muted">+<?php echo ($displayed - 3); ?> more of my subjects</small>
                                                <?php endif; 
                                                if($displayed == 0): 
                                                ?>
                                                <small class="text-muted">No progress in your subjects yet</small>
                                                <?php endif; ?>
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
                
                <?php endif; // End of if(!empty($handled_subjects)) ?>
            </div>
        </section>
    </div>

    <?php include('includes/footer.php'); ?>
</div>

<!-- Your existing modals remain the same -->
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
    $('#educStudentsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [],
        "columnDefs": [{ "orderable": false, "targets": [3, -1] }]
    });

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
        
        $.ajax({
            url: 'get_student_details.php',
            type: 'POST',
            data: {id: id},
            success: function(response) {
                $('#viewStudentContent').html(response);
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

    // Search functionality
    $('.search-box').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Update progress bar colors based on subject
    $('.progress-bar').each(function() {
        var progressBar = $(this);
        var width = progressBar.css('width');
        progressBar.css('width', '0').animate({
            width: width
        }, 800);
    });
});
</script>
</body>
</html>