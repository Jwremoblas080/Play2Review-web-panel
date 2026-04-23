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

// Subject names mapping
$subject_names = [
    'english' => 'English',
    'ap' => 'Araling Panlipunan',
    'filipino' => 'Filipino', 
    'math' => 'Mathematics',
    'science' => 'Science'
];

// Fetch statistics from database - ONLY for handled subjects
$stats = array();

// Total users
$query = "SELECT COUNT(*) as total_users FROM users";
$result = mysqli_query($con, $query);
$stats['total_users'] = mysqli_fetch_assoc($result)['total_users'];

// Total teachers
$query = "SELECT COUNT(*) as total_teachers FROM educators";
$result = mysqli_query($con, $query);
$stats['total_teachers'] = mysqli_fetch_assoc($result)['total_teachers'] ?? 0;

// Total feathers
$query = "SELECT SUM(feathers) as total_feathers FROM users";
$result = mysqli_query($con, $query);
$stats['total_feathers'] = mysqli_fetch_assoc($result)['total_feathers'] ?? 0;

// Total potion
$query = "SELECT SUM(potion) as total_potion FROM users";
$result = mysqli_query($con, $query);
$stats['total_potion'] = mysqli_fetch_assoc($result)['total_potion'] ?? 0;

// Average lives
$query = "SELECT AVG(lives) as avg_lives FROM users";
$result = mysqli_query($con, $query);
$stats['avg_lives'] = round(mysqli_fetch_assoc($result)['avg_lives'] ?? 0, 1);

// Subject completion statistics - ONLY for handled subjects - Using category levels
// Category columns for each subject
$category_columns = [
    'english' => ['english_grammar_level', 'english_vocabulary_level', 'english_reading_level', 'english_literature_level', 'english_writing_level'],
    'math' => ['math_algebra_level', 'math_geometry_level', 'math_statistics_level', 'math_probability_level', 'math_functions_level', 'math_wordproblems_level'],
    'science' => ['science_biology_level', 'science_chemistry_level', 'science_physics_level', 'science_earthscience_level', 'science_investigation_level'],
    'filipino' => ['filipino_gramatika_level', 'filipino_panitikan_level', 'filipino_paguunawa_level', 'filipino_talasalitaan_level', 'filipino_wika_level'],
    'ap' => ['ap_ekonomiks_level', 'ap_kasaysayan_level', 'ap_kontemporaryo_level', 'ap_heograpiya_level', 'ap_pamahalaan_level']
];

foreach($handled_subjects as $subject) {
    $columns = $category_columns[$subject];
    $sum_columns = implode(' + ', array_map(function($col) {
        return "COALESCE($col, 0)";
    }, $columns));
    
    // Calculate average total category levels
    $query = "SELECT AVG($sum_columns) as avg_level FROM users";
    $result = mysqli_query($con, $query);
    $avg_level_result = mysqli_fetch_assoc($result);
    $avg_level = $avg_level_result["avg_level"] ?? 0;
    $stats["avg_{$subject}_level"] = round($avg_level, 1);
    
    // Max possible level is number of categories * 10
    $max_level = count($columns) * 10;
    $stats["avg_{$subject}_percentage"] = ($avg_level / $max_level) * 100;
    
    // Users who completed all categories (each category at level 10)
    $query = "SELECT COUNT(*) as completed FROM users WHERE ($sum_columns) >= $max_level";
    $result = mysqli_query($con, $query);
    $completed_result = mysqli_fetch_assoc($result);
    $stats["{$subject}_completed"] = $completed_result['completed'] ?? 0;
}

// Character popularity
$query = "SELECT selected_character, COUNT(*) as count FROM users GROUP BY selected_character ORDER BY count DESC";
$result = mysqli_query($con, $query);
$character_stats = array();
while($row = mysqli_fetch_assoc($result)) {
    $character_stats[$row['selected_character']] = $row['count'];
}

// Recent player activities (last 10 registered users)
$query = "SELECT player_name, created_at FROM users ORDER BY created_at DESC LIMIT 10";
$result = mysqli_query($con, $query);
$recent_activities = array();
while($row = mysqli_fetch_assoc($result)) {
    $recent_activities[] = $row;
}

// Top users by total score - ONLY for handled subjects - Using category levels (10 points per level)
$score_columns = [];
foreach($handled_subjects as $subject) {
    $columns = $category_columns[$subject];
    $sum_columns = implode(' + ', array_map(function($col) {
        return "COALESCE($col, 0)";
    }, $columns));
    $score_columns[] = "($sum_columns)";
}

if(!empty($score_columns)) {
    $score_query = implode(' + ', $score_columns);
    $query = "SELECT player_name, 
              ($score_query) * 10 as total_score,
              feathers, potion
              FROM users 
              ORDER BY total_score DESC 
              LIMIT 5";
} else {
    $query = "SELECT player_name, 0 as total_score, feathers, potion FROM users LIMIT 0";
}

$result = mysqli_query($con, $query);
$top_users = array();
while($row = mysqli_fetch_assoc($result)) {
    $top_users[] = $row;
}

// Students progress by handled subjects - Using category levels
$students_progress = [];
if(!empty($handled_subjects)) {
    // Build SELECT clause with category level sums for each handled subject
    $select_parts = ['player_name', 'student_id'];
    foreach($handled_subjects as $subject) {
        $columns = $category_columns[$subject];
        $sum_columns = implode(' + ', array_map(function($col) {
            return "COALESCE($col, 0)";
        }, $columns));
        $select_parts[] = "($sum_columns) as {$subject}_total_level";
    }
    $columns_query = implode(', ', $select_parts);
    
    $query = "SELECT $columns_query 
              FROM users 
              ORDER BY created_at DESC 
              LIMIT 20";
    $result = mysqli_query($con, $query);
    while($row = mysqli_fetch_assoc($result)) {
        $students_progress[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Educator Dashboard - Play2Review Game Analytics</title>
    
    <?php include('includes/educ_header.php'); ?>
    <style>
        .stat-card {
            transition: all 0.3s ease;
            border-left: 4px solid;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .stat-card-students { border-left-color: #0A5F38; }
        .stat-card-teachers { border-left-color: #1E7D4E; }
        .stat-card-feathers { border-left-color: #28a745; }
        .stat-card-potion { border-left-color: #17a2b8; }
        .stat-card-lives { border-left-color: #20c997; }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        
        .activity-item {
            border-left: 3px solid #0A5F38;
            padding-left: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
        }
        
        .teacher-activity-item {
            border-left: 3px solid #1E7D4E;
            padding-left: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: #f8f9fc;
            border-radius: 8px;
            padding: 12px;
        }
        
        .activity-item:hover, .teacher-activity-item:hover {
            transform: translateX(5px);
        }
        
        .activity-item:hover {
            background-color: #e9ecef;
        }
        
        .teacher-activity-item:hover {
            background-color: #e8f5e8;
        }
        
        .progress {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 5px;
            border: 1px solid #ddd;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 0.6s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: white;
            font-weight: bold;
            min-width: 30px;
        }
        
        .bg-english { background-color: #0A5F38 !important; }
        .bg-ap { background-color: #1cc88a !important; }
        .bg-filipino { background-color: #f6c23e !important; }
        .bg-math { background-color: #36b9cc !important; }
        .bg-science { background-color: #1E7D4E !important; }
        
        .card-header {
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            color: white;
            font-weight: bold;
        }
        
        .teacher-card-header {
            background: linear-gradient(135deg, #1E7D4E 0%, #0A5F38 100%);
            color: white;
            font-weight: bold;
        }
        
        .dashboard-title {
            color: #0A5F38;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .subject-progress {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background: #f8f9fc;
            position: relative;
        }
        
        .handled-subject-badge {
            position: absolute;
            top: -10px;
            right: 10px;
            background: #0A5F38;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .player-rank {
            font-weight: bold;
            color: #0A5F38;
        }
        
        .character-progress {
            margin-bottom: 15px;
        }
        
        .teacher-stats {
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fc;
            border-radius: 8px;
            border-left: 4px solid #1E7D4E;
        }
        
        /* Custom small box styling */
        .small-box {
            border-radius: 0.25rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            display: block;
            margin-bottom: 20px;
            position: relative;
        }
        
        .small-box > .inner {
            padding: 10px;
        }
        
        .small-box > .small-box-footer {
            background: rgba(0, 0, 0, 0.1);
            color: rgba(255, 255, 255, 0.8);
            display: block;
            padding: 3px 0;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10;
        }
        
        .small-box h3 {
            font-size: 2.2rem;
            font-weight: bold;
            margin: 0 0 10px 0;
            padding: 0;
            white-space: nowrap;
        }
        
        .small-box p {
            font-size: 1rem;
        }
        
        .small-box .icon {
            color: rgba(0, 0, 0, 0.15);
            z-index: 0;
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .small-box:hover .icon {
            font-size: 2.7rem;
            transition: all 0.3s ease;
        }
.bg-primary {
    background-color: #0A5F38 !important;
}
        .text-english { color: #0A5F38; }
        .text-ap { color: #1cc88a; }
        .text-filipino { color: #f6c23e; }
        .text-math { color: #36b9cc; }
        .text-science { color: #1E7D4E; }
        
        .status-active { color: #28a745; }
        .status-inactive { color: #6c757d; }
        .status-pending { color: #ffc107; }
        
        .badge-active { background-color: #28a745; }
        .badge-inactive { background-color: #6c757d; }
        .badge-pending { background-color: #ffc107; }
        
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
                        <h1 class="m-0 dashboard-title">Educator Dashboard</h1>
                        <p class="text-muted mb-0">
                            <i class="fas fa-chalkboard-teacher mr-1"></i>
                            Monitoring: 
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
                            <li class="breadcrumb-item"><a href="educator_dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Stats Row -->
                <div class="row">
                    <!-- Total Students -->
                    <div class="col-lg-3 col-md-6">
                        <div class="small-box bg-gradient-danger stat-card stat-card-students">
                            <div class="inner">
                                <h3><?php echo $stats['total_users']; ?></h3>
                                <p>Total Students</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users stat-icon"></i>
                            </div>
                            <a href="manage-users.php" class="small-box-footer">
                                View All <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Handled Subjects Count -->
                    <div class="col-lg-3 col-md-6">
                        <div class="small-box bg-gradient-primary stat-card stat-card-teachers">
                            <div class="inner">
                                <h3><?php echo count($handled_subjects); ?></h3>
                                <p>Subjects Handled</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-book stat-icon"></i>
                            </div>
                            <a href="#" class="small-box-footer">
                                View Details <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Total Potion -->
                    <div class="col-lg-3 col-md-6">
                        <div class="small-box bg-gradient-purple stat-card stat-card-potion">
                            <div class="inner">
                                <h3><?php echo number_format($stats['total_potion']); ?></h3>
                                <p>Total Potion</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-wine-bottle stat-icon"></i>
                            </div>
                            <a href="#" class="small-box-footer">
                                View Details <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Average Lives -->
                    <div class="col-lg-3 col-md-6">
                        <div class="small-box bg-gradient-info stat-card stat-card-lives">
                            <div class="inner">
                                <h3><?php echo $stats['avg_lives']; ?></h3>
                                <p>Average Lives</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-heart stat-icon"></i>
                            </div>
                            <a href="#" class="small-box-footer">
                                View Details <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Second Row: Subject Progress - ONLY HANDLED SUBJECTS -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">My Subjects Progress Monitoring</h3>
                                <div class="card-tools">
                                    <span class="badge badge-light">Max Level: 10</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if(empty($handled_subjects)): ?>
                                    <div class="no-subjects-message">
                                        <i class="fas fa-book-open"></i>
                                        <h4>No Subjects Assigned</h4>
                                        <p>You haven't been assigned any subjects to monitor yet.</p>
                                        <p class="text-muted">Please contact the administrator to get subjects assigned to your account.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach($handled_subjects as $subject): 
                                            $percentage = $stats["avg_{$subject}_percentage"] ?? 0;
                                            $avg_level = $stats["avg_{$subject}_level"] ?? 0;
                                            $completed_count = $stats["{$subject}_completed"] ?? 0;
                                        ?>
                                        <div class="col-md-4">
                                            <div class="subject-progress">
                                                <span class="handled-subject-badge">
                                                    <i class="fas fa-chalkboard-teacher"></i> Your Subject
                                                </span>
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-uppercase font-weight-bold text-<?php echo $subject; ?>">
                                                        <?php echo $subject_names[$subject]; ?>
                                                    </span>
                                                    <span class="badge badge-secondary"><?php echo $avg_level; ?>/10 Levels</span>
                                                </div>
                                                <div class="progress">
                                                    <div class="progress-bar bg-<?php echo $subject; ?>" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $percentage; ?>% !important;"
                                                         aria-valuenow="<?php echo $percentage; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <?php echo round($percentage, 1); ?>%
                                                    </div>
                                                </div>
                                                <small class="text-muted mt-1 d-block">
                                                    <i class="fas fa-check-circle text-success"></i>
                                                    <?php echo $completed_count; ?> students completed all levels
                                                </small>
                                                <small class="text-info">
                                                    Average Progress: Level <?php echo $avg_level; ?>
                                                </small>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Third Row: Student Progress & Recent Activities -->
                <div class="row">
                    <!-- Top Students in Handled Subjects -->
                    <div class="col-lg-6">
                        <?php if(!empty($handled_subjects)): ?>
                        <!-- Top Students -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Top Students in My Subjects</h3>
                            </div>
                            <div class="card-body p-0">
                                <?php if(!empty($top_users)): ?>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 10px">#</th>
                                            <th>Student</th>
                                            <th>Total Score</th>
                                            <th>Feathers</th>
                                            <th>Potion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $rank = 1; ?>
                                        <?php foreach($top_users as $player): ?>
                                        <tr>
                                            <td><span class="player-rank"><?php echo $rank++; ?></span></td>
                                            <td><?php echo htmlspecialchars($player['player_name']); ?></td>
                                            <td><span class="badge bg-success"><?php echo $player['total_score']; ?></span></td>
                                            <td><?php echo $player['feathers']; ?></td>
                                            <td><?php echo $player['potion']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <div class="text-center p-4">
                                    <i class="fas fa-users fa-2x text-muted mb-3"></i>
                                    <p class="text-muted">No student data available for your subjects.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Student Progress by Subject -->
                        <?php if(!empty($students_progress)): ?>
                        <div class="card mt-4">
                            <div class="card-header teacher-card-header">
                                <h3 class="card-title">Student Progress in My Subjects</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <?php foreach($handled_subjects as $subject): ?>
                                                <th class="text-center"><?php echo ucfirst($subject); ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($students_progress as $student): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($student['player_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo $student['student_id']; ?></small>
                                                </td>
                                                <?php foreach($handled_subjects as $subject): 
                                                    $level = $student["{$subject}_total_level"] ?? 0;
                                                    $max_level = count($category_columns[$subject]) * 10;
                                                    $percentage = ($level / $max_level) * 100;
                                                ?>
                                                <td class="text-center">
                                                    <div class="progress" style="height: 15px; width: 80px; margin: 0 auto;">
                                                        <div class="progress-bar bg-<?php echo $subject; ?>" 
                                                             style="width: <?php echo $percentage; ?>%"
                                                             title="Level <?php echo $level; ?>/<?php echo $max_level; ?>">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">Lvl <?php echo $level; ?></small>
                                                </td>
                                                <?php endforeach; ?>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Recent Activities & Character Popularity -->
                    <div class="col-lg-6">
                        <!-- Recent Student Registrations -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Student Registrations</h3>
                            </div>
                            <div class="card-body">
                                <?php foreach($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['player_name']); ?></h6>
                                        <small><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-0">New student registered</p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Character Popularity -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h3 class="card-title">Character Popularity</h3>
                            </div>
                            <div class="card-body">
                                <?php foreach($character_stats as $character => $count): 
                                    $char_percentage = ($count / $stats['total_users']) * 100;
                                ?>
                                <div class="character-progress">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span><?php echo $character ?: 'Not Set'; ?></span>
                                        <span><?php echo $count; ?> students (<?php echo round($char_percentage, 1); ?>%)</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-primary" 
                                             role="progressbar" 
                                             style="width: <?php echo $char_percentage; ?>% !important;"
                                             aria-valuenow="<?php echo $char_percentage; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo round($char_percentage, 1); ?>%
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include('includes/footer.php'); ?>
    <?php include('includes/educ_modals.php'); ?>
</div>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Simple progress bar animation
    $('.progress-bar').each(function() {
        var width = $(this).css('width');
        $(this).css('width', '0').animate({
            width: width
        }, 800);
    });
    
    // Auto-refresh dashboard every 5 minutes
    setInterval(function() {
        window.location.reload();
    }, 300000);
});
</script>
</body>
</html>