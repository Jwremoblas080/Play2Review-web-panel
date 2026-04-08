<?php
require_once('../configurations/configurations.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'educator') {
    header("Location: logout.php");
    exit();
}

// Subject filter
$subject_filter = isset($_GET['subject']) ? $_GET['subject'] : 'overall';
$valid_subjects = ['overall', 'english', 'ap', 'filipino', 'math', 'science'];

// Validate subject filter
if(!in_array($subject_filter, $valid_subjects)) {
    $subject_filter = 'overall';
}

// Calculate points for each student
$students_query = "SELECT id, player_name, student_id, username, 
                  english_completed_level, ap_completed_level, filipino_completed_level, 
                  math_completed_level, science_completed_level, feathers, lives, potion,
                  selected_character, created_at
                  FROM users ORDER BY created_at DESC";
$students_result = mysqli_query($con, $students_query);
$students = array();

while($student = mysqli_fetch_assoc($students_result)) {
    // Calculate points (each level = 100 points)
    $english_points = $student['english_completed_level'] * 100;
    $ap_points = $student['ap_completed_level'] * 100;
    $filipino_points = $student['filipino_completed_level'] * 100;
    $math_points = $student['math_completed_level'] * 100;
    $science_points = $student['science_completed_level'] * 100;
    
    $total_points = $english_points + $ap_points + $filipino_points + $math_points + $science_points;
    
    $students[] = array(
        'id' => $student['id'],
        'player_name' => $student['player_name'],
        'student_id' => $student['student_id'],
        'username' => $student['username'],
        'english_points' => $english_points,
        'ap_points' => $ap_points,
        'filipino_points' => $filipino_points,
        'math_points' => $math_points,
        'science_points' => $science_points,
        'total_points' => $total_points,
        'feathers' => $student['feathers'],
        'lives' => $student['lives'],
        'potion' => $student['potion'],
        'selected_character' => $student['selected_character'],
        'created_at' => $student['created_at']
    );
}

// Sort students based on selected filter
if($subject_filter == 'overall') {
    usort($students, function($a, $b) {
        return $b['total_points'] - $a['total_points'];
    });
} else {
    $points_field = $subject_filter . '_points';
    usort($students, function($a, $b) use ($points_field) {
        return $b[$points_field] - $a[$points_field];
    });
}

// Get statistics
$total_students = count($students);
$active_students = 0;
$total_points_all = 0;
$average_points = 0;

$subject_totals = [
    'english' => 0,
    'ap' => 0,
    'filipino' => 0,
    'math' => 0,
    'science' => 0
];

foreach($students as $student) {
    $total_points_all += $student['total_points'];
    if($student['total_points'] > 0) $active_students++;
    
    $subject_totals['english'] += $student['english_points'];
    $subject_totals['ap'] += $student['ap_points'];
    $subject_totals['filipino'] += $student['filipino_points'];
    $subject_totals['math'] += $student['math_points'];
    $subject_totals['science'] += $student['science_points'];
}

if($total_students > 0) {
    $average_points = round($total_points_all / $total_students);
}

// Get top 3 students for badges
$top_students = array_slice($students, 0, 3);

// Subject names mapping
$subject_names = [
    'overall' => 'Overall Ranking',
    'english' => 'English',
    'ap' => 'Araling Panlipunan',
    'filipino' => 'Filipino',
    'math' => 'Mathematics',
    'science' => 'Science'
];

$current_subject_name = $subject_names[$subject_filter];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Game Session Leaderboard - Play2Review Admin</title>
    
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
        
        .header {
            background: linear-gradient(135deg, var(--primary) 0%, #064527 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
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
            background: linear-gradient(135deg, var(--secondary) 0%, #0A5F38 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: bold;
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
        
        .table th {
            background: linear-gradient(135deg, var(--primary) 0%, #064527 100%);
            color: white;
            border: none;
        }
        
        .student-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary) 0%, #0A5F38 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .rank-badge {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            margin-right: 10px;
        }
        
        .rank-1 { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); }
        .rank-2 { background: linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%); }
        .rank-3 { background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%); }
        .rank-other { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
        
        .points-badge {
            background: linear-gradient(135deg, var(--success) 0%, #17a673 100%);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .subject-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 15px;
            margin: 2px;
            display: inline-block;
        }
        
        .badge-english { background: #0A5F38; color: white; }
        .badge-ap { background: #1cc88a; color: white; }
        .badge-filipino { background: #f6c23e; color: #212529; }
        .badge-math { background: #36b9cc; color: white; }
        .badge-science { background: #1E7D4E; color: white; }
        
        .search-box {
            border-radius: 25px;
            border: 2px solid var(--primary);
            padding: 8px 20px;
        }
        
        .filter-select {
            border-radius: 25px;
            border: 2px solid var(--secondary);
            padding: 8px 15px;
        }
        
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #064527 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #064527 0%, var(--primary) 100%);
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
        
        .subject-filter-active {
            background: linear-gradient(135deg, var(--secondary) 0%, #0A5F38 100%) !important;
            color: white !important;
        }
        
        .top-student-card {
            text-align: center;
            padding: 20px;
            border-radius: 10px;
            background: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .top-student-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
        }
        
        .top-1::before { background: linear-gradient(90deg, #FFD700, #FFA500); }
        .top-2::before { background: linear-gradient(90deg, #C0C0C0, #A9A9A9); }
        .top-3::before { background: linear-gradient(90deg, #CD7F32, #8B4513); }
        
        .medal {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .medal-1 { color: #FFD700; }
        .medal-2 { color: #C0C0C0; }
        .medal-3 { color: #CD7F32; }
        
        .subject-points {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .points-breakdown {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
        }
    </style></head>

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
                        <h1 class="m-0 dashboard-title">Students Leaderboard</h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-trophy mr-1"></i>
                            Current Ranking: <?php echo $current_subject_name; ?>
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Leaderboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Subject Filter -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Rank Students By</h6>
                                <div class="btn-group" role="group" style="margin-left:2vh;">
                                    <a href="?subject=overall" 
                                       class="btn btn-outline-primary <?php echo $subject_filter == 'overall' ? 'subject-filter-active' : ''; ?>">
                                        <i class="fas fa-trophy"></i> Overall Points
                                    </a>
                                    <a href="?subject=english" 
                                       class="btn btn-outline-primary <?php echo $subject_filter == 'english' ? 'subject-filter-active' : ''; ?>">
                                        <span class="badge badge-english">E</span> English
                                    </a>
                                    <a href="?subject=ap" 
                                       class="btn btn-outline-primary <?php echo $subject_filter == 'ap' ? 'subject-filter-active' : ''; ?>">
                                        <span class="badge badge-ap">AP</span> Araling Panlipunan
                                    </a>
                                    <a href="?subject=filipino" 
                                       class="btn btn-outline-primary <?php echo $subject_filter == 'filipino' ? 'subject-filter-active' : ''; ?>">
                                        <span class="badge badge-filipino">F</span> Filipino
                                    </a>
                                    <a href="?subject=science" 
                                       class="btn btn-outline-primary <?php echo $subject_filter == 'science' ? 'subject-filter-active' : ''; ?>">
                                        <span class="badge badge-science">S</span> Science
                                    </a>
                                    <a href="?subject=math" 
                                       class="btn btn-outline-primary <?php echo $subject_filter == 'math' ? 'subject-filter-active' : ''; ?>">
                                        <span class="badge badge-math">M</span> Mathematics
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                            <div class="stats-number"><?php echo $active_students; ?></div>
                            <div class="stats-label">Active Students</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo number_format($total_points_all); ?></div>
                            <div class="stats-label">Total Points</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo number_format($average_points); ?></div>
                            <div class="stats-label">Average Points</div>
                        </div>
                    </div>
                </div>

                <!-- Top 3 Students -->
                <?php if(!empty($top_students)): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h4>Top Performers</h4>
                    </div>
                    <?php foreach($top_students as $index => $student): 
                        $rank = $index + 1;
                        $points_field = $subject_filter == 'overall' ? 'total_points' : $subject_filter . '_points';
                        $points = $student[$points_field];
                    ?>
                    <div class="col-lg-4 col-md-4">
                        <div class="top-student-card top-<?php echo $rank; ?>">
                            <div class="medal medal-<?php echo $rank; ?>">
                                <i class="fas fa-medal"></i>
                            </div>
                            <h5>#<?php echo $rank; ?> Rank</h5>
                            <div class="student-avatar" style="width: 60px; height: 60px; font-size: 1.5rem; margin: 0 auto 15px;">
                                <?php echo strtoupper(substr($student['player_name'], 0, 1)); ?>
                            </div>
                            <h6 class="mb-1"><?php echo htmlspecialchars($student['player_name']); ?></h6>
                            <p class="text-muted mb-2"><?php echo htmlspecialchars($student['student_id']); ?></p>
                            <div class="points-badge mb-3">
                                <?php echo number_format($points); ?> Points
                            </div>
                            <?php if($subject_filter == 'overall'): ?>
                            <div class="points-breakdown">
                                <small class="d-block">
                                    <span class="badge badge-english">English: <?php echo number_format($student['english_points']); ?></span>
                                </small>
                                <small class="d-block mt-1">
                                    <span class="badge badge-ap">AP: <?php echo number_format($student['ap_points']); ?></span>
                                </small>
                                <small class="d-block mt-1">
                                    <span class="badge badge-filipino">Filipino: <?php echo number_format($student['filipino_points']); ?></span>
                                </small>
                                <small class="d-block mt-1">
                                    <span class="badge badge-science">Science: <?php echo number_format($student['science_points']); ?></span>
                                </small>
                                <small class="d-block mt-1">
                                    <span class="badge badge-math">Math: <?php echo number_format($student['math_points']); ?></span>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Leaderboard Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            Students Leaderboard 
                            <small class="text-muted">- <?php echo $current_subject_name; ?></small>
                        </h3>
                        <div class="card-tools">
                            <div class="input-group input-group-sm" style="width: 200px;">
                                <input type="text" class="form-control search-box" placeholder="Search students...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="leaderboardTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">Rank</th>
                                        <th>Student</th>
                                        <th style="width: 150px;">Points</th>
                                        <th style="width: 200px;">Subject Points</th>
                                        <th style="width: 100px;">Potions</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($students)): ?>
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <i class="fas fa-trophy"></i>
                                                    <h4>No Students Found</h4>
                                                    <p>No student data available for ranking.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($students as $index => $student): 
                                            $rank = $index + 1;
                                            $points_field = $subject_filter == 'overall' ? 'total_points' : $subject_filter . '_points';
                                            $points = $student[$points_field];
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="rank-badge <?php echo $rank <= 3 ? 'rank-' . $rank : 'rank-other'; ?>">
                                                        <?php echo $rank; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="student-avatar">
                                                        <?php echo strtoupper(substr($student['player_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($student['player_name']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($student['student_id']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="points-badge">
                                                    <?php echo number_format($points); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap">
                                                    <?php if($subject_filter == 'overall'): ?>
                                                        <span class="subject-badge badge-english" title="English: <?php echo number_format($student['english_points']); ?>">E:<?php echo number_format($student['english_points']); ?></span>
                                                        <span class="subject-badge badge-ap" title="AP: <?php echo number_format($student['ap_points']); ?>">AP:<?php echo number_format($student['ap_points']); ?></span>
                                                        <span class="subject-badge badge-filipino" title="Filipino: <?php echo number_format($student['filipino_points']); ?>">F:<?php echo number_format($student['filipino_points']); ?></span>
                                                        <span class="subject-badge badge-science" title="Science: <?php echo number_format($student['science_points']); ?>">S:<?php echo number_format($student['science_points']); ?></span>
                                                        <span class="subject-badge badge-math" title="Math: <?php echo number_format($student['math_points']); ?>">M:<?php echo number_format($student['math_points']); ?></span>
                                                    <?php else: ?>
                                                        <span class="subject-badge badge-<?php echo $subject_filter; ?>">
                                                            <?php echo ucfirst($subject_filter); ?>: <?php echo number_format($points); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-feather"></i> <?php echo $student['feathers']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-info btn-sm btn-action view-student" 
                                                        data-id="<?php echo $student['id']; ?>"
                                                        data-toggle="modal" data-target="#viewStudentModal">
                                                    <i class="fas fa-eye"></i> View
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
    <?php include('includes/educ_modals.php'); ?>
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

<script>
$(document).ready(function() {
    $('#leaderboardTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [[1, 'desc']],
        "columnDefs": [{ "orderable": false, "targets": [-1] }]
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