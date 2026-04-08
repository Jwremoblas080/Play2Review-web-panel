<?php
require_once('../configurations/configurations.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Handle delete action
if(isset($_POST['action']) && $_POST['action'] == 'delete_survey') {
    $survey_id = mysqli_real_escape_string($con, $_POST['survey_id']);
    
    $query = "DELETE FROM surveys WHERE id = '$survey_id'";
    
    if(mysqli_query($con, $query)) {
        $_SESSION['success'] = "Survey record deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting survey: " . mysqli_error($con);
    }
    
    header("Location: manage-survey.php");
    exit();
}

// Handle delete all surveys for a student
if(isset($_POST['action']) && $_POST['action'] == 'delete_all_surveys') {
    $user_id = mysqli_real_escape_string($con, $_POST['user_id']);
    $student_name = mysqli_real_escape_string($con, $_POST['student_name']);
    
    $query = "DELETE FROM surveys WHERE user_id = '$user_id'";
    
    if(mysqli_query($con, $query)) {
        $_SESSION['success'] = "All survey records for $student_name deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting surveys: " . mysqli_error($con);
    }
    
    header("Location: manage-survey.php");
    exit();
}

// Fetch all students with their survey counts
$query = "SELECT u.id, u.player_name, u.student_id, u.username, 
                 COUNT(s.id) as survey_count,
                 SUM(CASE WHEN s.answer = 'yes' THEN 1 ELSE 0 END) as yes_count,
                 SUM(CASE WHEN s.answer = 'no' THEN 1 ELSE 0 END) as no_count,
                 MAX(s.submission_date) as last_survey_date
          FROM users u 
          LEFT JOIN surveys s ON u.id = s.user_id 
          GROUP BY u.id, u.player_name, u.student_id, u.username
          HAVING survey_count > 0
          ORDER BY survey_count DESC, last_survey_date DESC";
$result = mysqli_query($con, $query);
$students = array();
while($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}

// Get survey statistics
$total_surveys = 0;
$yes_count = 0;
$no_count = 0;
foreach($students as $student) {
    $total_surveys += $student['survey_count'];
    $yes_count += $student['yes_count'];
    $no_count += $student['no_count'];
}

// Get unique questions and their statistics
$question_stats = array();
$question_query = "SELECT question_text, 
                  COUNT(*) as total_answers,
                  SUM(CASE WHEN answer = 'yes' THEN 1 ELSE 0 END) as yes_count,
                  SUM(CASE WHEN answer = 'no' THEN 1 ELSE 0 END) as no_count
                  FROM surveys 
                  GROUP BY question_text 
                  ORDER BY total_answers DESC";
$question_result = mysqli_query($con, $question_query);
while($row = mysqli_fetch_assoc($question_result)) {
    $question_stats[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Surveys - Play2Review Admin</title>
    
    <?php include('includes/header.php'); ?>
    <style>
        :root {
            --primary: #0A5F38;
            --secondary: #1E7D4E;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --success: #1cc88a;
            --warning: #f6c23e;
            --info: #36b9cc;
        }
        
        .stats-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
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
        
        .survey-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            font-size: 1.2rem;
        }
        
        .survey-name-cell {
            display: flex;
            align-items: center;
        }
        
        .answer-badge-yes {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .answer-badge-no {
            background: linear-gradient(135deg, #0A5F38 0%, #064527 100%);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .student-id-badge {
            background: #6c757d;
            color: white;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            font-size: 0.7rem;
        }
        
        .survey-count-badge {
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .table th {
            background: linear-gradient(135deg, #0A5F38 0%, #064527 100%);
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
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: bold;
        }
        
        .question-text {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .question-text-full {
            white-space: normal;
            word-wrap: break-word;
        }
        
        .progress-sm {
            height: 8px;
        }
        
        .question-stats {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fc;
            border-radius: 8px;
            border-left: 4px solid var(--info);
        }
        
        .survey-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            border-left: 4px solid var(--info);
        }
        
        .answer-row {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .answer-row:last-child {
            border-bottom: none;
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
                        <h1 class="m-0 dashboard-title">Manage Student Surveys</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Manage Surveys</li>
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
                            <div class="stats-number"><?php echo count($students); ?></div>
                            <div class="stats-label">Students with Surveys</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $total_surveys; ?></div>
                            <div class="stats-label">Total Surveys</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $yes_count; ?></div>
                            <div class="stats-label">Yes Answers</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $no_count; ?></div>
                            <div class="stats-label">No Answers</div>
                        </div>
                    </div>
                </div>

                <!-- Question Statistics -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Question Statistics</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach($question_stats as $stat): 
                                    $yes_percentage = ($stat['yes_count'] / $stat['total_answers']) * 100;
                                    $no_percentage = ($stat['no_count'] / $stat['total_answers']) * 100;
                                ?>
                                <div class="question-stats">
                                    <h6 class="font-weight-bold"><?php echo htmlspecialchars($stat['question_text']); ?></h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-success">
                                                <i class="fas fa-check-circle"></i> 
                                                Yes: <?php echo $stat['yes_count']; ?> (<?php echo round($yes_percentage, 1); ?>%)
                                            </small>
                                            <div class="progress progress-sm mt-1">
                                                <div class="progress-bar bg-success" style="width: <?php echo $yes_percentage; ?>%"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-danger">
                                                <i class="fas fa-times-circle"></i> 
                                                No: <?php echo $stat['no_count']; ?> (<?php echo round($no_percentage, 1); ?>%)
                                            </small>
                                            <div class="progress progress-sm mt-1">
                                                <div class="progress-bar bg-danger" style="width: <?php echo $no_percentage; ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted">Total answers: <?php echo $stat['total_answers']; ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students with Surveys Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Students with Surveys</h3>
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
                            <table id="surveyTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Survey Stats</th>
                                        <th>Last Survey</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($students)): ?>
                                        <tr>
                                            <td colspan="4">
                                                <div class="empty-state">
                                                    <i class="fas fa-clipboard-list"></i>
                                                    <h4>No Surveys Found</h4>
                                                    <p>No students have completed surveys yet.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($students as $student): ?>
                                        <tr>
                                            <td>
                                                <div class="survey-name-cell">
                                                    <div class="survey-avatar">
                                                        <?php echo strtoupper(substr($student['player_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($student['player_name']); ?></strong>
                                                        <br>
                                                        <span class="student-id-badge"><?php echo htmlspecialchars($student['student_id']); ?></span>
                                                        <small class="text-muted d-block">@<?php echo htmlspecialchars($student['username']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="survey-count-badge mr-3">
                                                        <?php echo $student['survey_count']; ?> Surveys
                                                    </span>
                                                    <div>
                                                        <small class="text-success d-block">
                                                            <i class="fas fa-check"></i> Yes: <?php echo $student['yes_count']; ?>
                                                        </small>
                                                        <small class="text-danger d-block">
                                                            <i class="fas fa-times"></i> No: <?php echo $student['no_count']; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($student['last_survey_date']): ?>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y g:i A', strtotime($student['last_survey_date'])); ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">No surveys</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="action-buttons">
                                                <button class="btn btn-info btn-sm btn-action view-student-surveys" 
                                                        data-userid="<?php echo $student['id']; ?>"
                                                        data-student="<?php echo htmlspecialchars($student['player_name']); ?>"
                                                        data-studentid="<?php echo htmlspecialchars($student['student_id']); ?>"
                                                        data-username="<?php echo htmlspecialchars($student['username']); ?>">
                                                    <i class="fas fa-eye"></i> View Surveys
                                                </button>
                                                <button class="btn btn-danger btn-sm btn-action delete-all-surveys" 
                                                        data-userid="<?php echo $student['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($student['player_name']); ?>">
                                                    <i class="fas fa-trash"></i> Delete All
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

<!-- View Student Surveys Modal -->
<div class="modal fade" id="viewStudentSurveysModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Survey Details - <span id="modalStudentName"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Student Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td id="modalStudentNameFull"></td>
                            </tr>
                            <tr>
                                <td><strong>Student ID:</strong></td>
                                <td id="modalStudentId"></td>
                            </tr>
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td id="modalUsername"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <h6>Survey Responses</h6>
                <div id="surveyResponses">
                    <!-- Survey responses will be loaded here via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete All Surveys Confirmation Modal -->
<div class="modal fade" id="deleteAllSurveysModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete All Surveys</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_all_surveys">
                    <input type="hidden" name="user_id" id="delete_all_user_id">
                    <input type="hidden" name="student_name" id="delete_all_student_name">
                    <p>Are you sure you want to delete ALL survey responses from student: <strong id="delete_all_student_display"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This will delete all <?php echo count($question_stats); ?> survey questions and cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete All Surveys</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#surveyTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [],
        "columnDefs": [{ "orderable": false, "targets": [-1] }]
    });

    // View Student Surveys functionality
    $('.view-student-surveys').on('click', function() {
        var userId = $(this).data('userid');
        var studentName = $(this).data('student');
        var studentId = $(this).data('studentid');
        var username = $(this).data('username');
        
        // Set modal header info
        $('#modalStudentName').text(studentName);
        $('#modalStudentNameFull').text(studentName);
        $('#modalStudentId').text(studentId);
        $('#modalUsername').text(username);
        
        // Load survey responses via AJAX
        $.ajax({
            url: 'get_student_surveys.php',
            type: 'POST',
            data: { user_id: userId },
            success: function(response) {
                $('#surveyResponses').html(response);
                $('#viewStudentSurveysModal').modal('show');
            },
            error: function() {
                $('#surveyResponses').html('<div class="alert alert-danger">Error loading survey data.</div>');
                $('#viewStudentSurveysModal').modal('show');
            }
        });
    });

    // Delete All Surveys functionality
    $('.delete-all-surveys').on('click', function() {
        var userId = $(this).data('userid');
        var studentName = $(this).data('name');
        
        $('#delete_all_user_id').val(userId);
        $('#delete_all_student_name').val(studentName);
        $('#delete_all_student_display').text(studentName);
        $('#deleteAllSurveysModal').modal('show');
    });
});
</script>
</body>
</html>