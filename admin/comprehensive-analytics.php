<?php
require_once('../configurations/configurations.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Initialize stats array
$stats = array();

// ==================== GENERAL STATISTICS ====================
$query = "SELECT COUNT(*) as total FROM users";
$result = mysqli_query($con, $query);
$stats['total_users'] = mysqli_fetch_assoc($result)['total'];

// Active users based on recent registration (last 30 days)
$query = "SELECT COUNT(*) as active FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
$result = mysqli_query($con, $query);
$stats['active_users'] = mysqli_fetch_assoc($result)['active'] ?? 0;

$query = "SELECT COUNT(*) as new_users FROM users WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$result = mysqli_query($con, $query);
$stats['new_users'] = mysqli_fetch_assoc($result)['new_users'];

$query = "SELECT COUNT(*) as total FROM educators";
$result = mysqli_query($con, $query);
$stats['total_educators'] = mysqli_fetch_assoc($result)['total'] ?? 0;

$query = "SELECT SUM(feathers) as total_feathers, SUM(potion) as total_potion, AVG(lives) as avg_lives FROM users";
$result = mysqli_query($con, $query);
$game_stats = mysqli_fetch_assoc($result);
$stats['total_feathers'] = $game_stats['total_feathers'] ?? 0;
$stats['total_potion'] = $game_stats['total_potion'] ?? 0;
$stats['avg_lives'] = round($game_stats['avg_lives'] ?? 0, 2);

// ==================== SUBJECT STATISTICS ====================
$category_columns = [
    'english' => ['english_grammar_level', 'english_vocabulary_level', 'english_reading_level', 'english_literature_level', 'english_writing_level'],
    'math' => ['math_algebra_level', 'math_geometry_level', 'math_statistics_level', 'math_probability_level', 'math_functions_level', 'math_wordproblems_level'],
    'science' => ['science_biology_level', 'science_chemistry_level', 'science_physics_level', 'science_earthscience_level', 'science_investigation_level'],
    'filipino' => ['filipino_gramatika_level', 'filipino_panitikan_level', 'filipino_paguunawa_level', 'filipino_talasalitaan_level', 'filipino_wika_level'],
    'ap' => ['ap_ekonomiks_level', 'ap_kasaysayan_level', 'ap_kontemporaryo_level', 'ap_heograpiya_level', 'ap_pamahalaan_level']
];

$subjects = ['english', 'ap', 'filipino', 'math', 'science'];

foreach($subjects as $subject) {
    $columns = $category_columns[$subject];
    $sum_columns = implode(' + ', array_map(function($col) {
        return "COALESCE($col, 0)";
    }, $columns));
    
    $query = "SELECT AVG($sum_columns) as avg_level FROM users";
    $result = mysqli_query($con, $query);
    $avg_level = mysqli_fetch_assoc($result)['avg_level'] ?? 0;
    $stats["avg_{$subject}_level"] = round($avg_level, 2);
    
    $max_level = count($columns) * 10;
    $stats["avg_{$subject}_percentage"] = round(($avg_level / $max_level) * 100, 2);
    
    $query = "SELECT COUNT(*) as completed FROM users WHERE ($sum_columns) >= $max_level";
    $result = mysqli_query($con, $query);
    $stats["{$subject}_completed"] = mysqli_fetch_assoc($result)['completed'] ?? 0;
}

// ==================== CHARACTER DISTRIBUTION ====================
$query = "SELECT selected_character, COUNT(*) as count FROM users GROUP BY selected_character ORDER BY count DESC";
$result = mysqli_query($con, $query);
$character_stats = array();
while($row = mysqli_fetch_assoc($result)) {
    $character_stats[$row['selected_character']] = $row['count'];
}

// ==================== TOP PERFORMERS ====================
$english_sum = implode(' + ', array_map(function($col) { return "COALESCE($col, 0)"; }, $category_columns['english']));
$math_sum = implode(' + ', array_map(function($col) { return "COALESCE($col, 0)"; }, $category_columns['math']));
$science_sum = implode(' + ', array_map(function($col) { return "COALESCE($col, 0)"; }, $category_columns['science']));
$filipino_sum = implode(' + ', array_map(function($col) { return "COALESCE($col, 0)"; }, $category_columns['filipino']));
$ap_sum = implode(' + ', array_map(function($col) { return "COALESCE($col, 0)"; }, $category_columns['ap']));

$query = "SELECT player_name, 
          (($english_sum) + ($math_sum) + ($science_sum) + ($filipino_sum) + ($ap_sum)) * 10 as total_score,
          feathers, potion, lives
          FROM users 
          ORDER BY total_score DESC 
          LIMIT 10";
$result = mysqli_query($con, $query);
$top_performers = array();
while($row = mysqli_fetch_assoc($result)) {
    $top_performers[] = $row;
}

// ==================== TEACHER STATISTICS ====================
$query = "SELECT status, COUNT(*) as count FROM educators GROUP BY status";
$result = mysqli_query($con, $query);
$teacher_status_stats = array();
while($row = mysqli_fetch_assoc($result)) {
    $teacher_status_stats[$row['status']] = $row['count'];
}

$query = "SELECT handled_subject, COUNT(*) as count FROM educators GROUP BY handled_subject";
$result = mysqli_query($con, $query);
$teacher_subject_stats = array();
while($row = mysqli_fetch_assoc($result)) {
    $teacher_subject_stats[$row['handled_subject']] = $row['count'];
}

// ==================== RECENT ACTIVITIES ====================
$query = "SELECT player_name, created_at FROM users ORDER BY created_at DESC LIMIT 10";
$result = mysqli_query($con, $query);
$recent_students = array();
while($row = mysqli_fetch_assoc($result)) {
    $recent_students[] = $row;
}

$query = "SELECT teacher_name, email, created_at FROM educators ORDER BY created_at DESC LIMIT 5";
$result = mysqli_query($con, $query);
$recent_teachers = array();
while($row = mysqli_fetch_assoc($result)) {
    $recent_teachers[] = $row;
}

// ==================== QUIZ STATISTICS ====================
$quiz_stats = array();
foreach($subjects as $subject) {
    $query = "SELECT COUNT(*) as total FROM quizes WHERE subject_name = '$subject'";
    $result = mysqli_query($con, $query);
    $quiz_stats[$subject] = mysqli_fetch_assoc($result)['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comprehensive Analytics - Play2Review Admin</title>
    
    <?php include('includes/header.php'); ?>
    <style>
    .stat-box {
        background: linear-gradient(135deg, #0A5F38 0%, #0D7A47 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        margin-bottom: 20px;
        transition: transform 0.3s;
    }
    .stat-box:hover { transform: translateY(-5px); }
    .stat-box h3 { font-size: 2.5rem; margin: 0; }
    .stat-box p { margin: 5px 0 0 0; opacity: 0.9; }
    
    .nav-tabs .nav-link { color: #0A5F38; font-weight: 600; }
    .nav-tabs .nav-link.active { background-color: #0A5F38; color: white; }
    
    .progress { height: 20px; border-radius: 10px; }
    .progress-bar { font-weight: bold; }
    
    .card { border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin-bottom: 20px; }
    .card-header { background: linear-gradient(135deg, #0A5F38 0%, #0D7A47 100%); color: white; font-weight: bold; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <?php include('includes/topbar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Comprehensive Analytics Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Comprehensive Analytics</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                    <form method="GET" class="form-inline">
                                        <label class="mr-2">Date Range:</label>
                                        <input type="date" name="start_date" class="form-control mr-2" value="<?php echo $start_date; ?>">
                                        <label class="mr-2">to</label>
                                        <input type="date" name="end_date" class="form-control mr-2" value="<?php echo $end_date; ?>">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                    </form>
                                    <button onclick="printAnalytics()" class="btn btn-secondary">
                                        <i class="fas fa-print"></i> Print Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" id="analyticsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab">
                            <i class="fas fa-chart-line"></i> Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="students-tab" data-toggle="tab" href="#students" role="tab">
                            <i class="fas fa-user-graduate"></i> Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="educators-tab" data-toggle="tab" href="#educators" role="tab">
                            <i class="fas fa-chalkboard-teacher"></i> Educators
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="subjects-tab" data-toggle="tab" href="#subjects" role="tab">
                            <i class="fas fa-book"></i> Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="game-tab" data-toggle="tab" href="#game" role="tab">
                            <i class="fas fa-gamepad"></i> Game Stats
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="quizzes-tab" data-toggle="tab" href="#quizzes" role="tab">
                            <i class="fas fa-question-circle"></i> Quizzes
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="analyticsTabContent">
                    
                    <!-- OVERVIEW TAB -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-lg-3 col-6">
                                <div class="stat-box">
                                    <h3><?php echo $stats['total_users']; ?></h3>
                                    <p><i class="fas fa-users"></i> Total Students</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="stat-box" style="background: linear-gradient(135deg, #1E88E5 0%, #1976D2 100%);">
                                    <h3><?php echo $stats['active_users']; ?></h3>
                                    <p><i class="fas fa-user-check"></i> Active Students (30 days)</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="stat-box" style="background: linear-gradient(135deg, #43A047 0%, #388E3C 100%);">
                                    <h3><?php echo $stats['new_users']; ?></h3>
                                    <p><i class="fas fa-user-plus"></i> New Students (Period)</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="stat-box" style="background: linear-gradient(135deg, #FB8C00 0%, #F57C00 100%);">
                                    <h3><?php echo $stats['total_educators']; ?></h3>
                                    <p><i class="fas fa-chalkboard-teacher"></i> Total Educators</p>
                                </div>
                            </div>
                        </div>

                        <!-- Subject Progress Overview -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Subject Completion Progress</h3>
                                    </div>
                                    <div class="card-body">
                                        <?php foreach($subjects as $subject): 
                                            $percentage = $stats["avg_{$subject}_percentage"];
                                            $avg_level = $stats["avg_{$subject}_level"];
                                            $completed_count = $stats["{$subject}_completed"];
                                            $max_level = count($category_columns[$subject]) * 10;
                                        ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="font-weight-bold text-uppercase"><?php echo ucfirst($subject); ?></span>
                                                <span class="badge badge-secondary"><?php echo round($avg_level, 1); ?>/<?php echo $max_level; ?> Levels</span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: <?php echo $percentage; ?>%"
                                                     aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <?php echo round($percentage, 1); ?>%
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-check-circle text-success"></i> 
                                                <?php echo $completed_count; ?> students completed all levels
                                            </small>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Top Performers -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Top 10 Performers</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Player Name</th>
                                                    <th>Total Score</th>
                                                    <th>Feathers</th>
                                                    <th>Potions</th>
                                                    <th>Lives</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $rank = 1;
                                                foreach($top_performers as $performer): 
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php if($rank == 1): ?>
                                                            <i class="fas fa-trophy text-warning"></i>
                                                        <?php elseif($rank == 2): ?>
                                                            <i class="fas fa-medal text-secondary"></i>
                                                        <?php elseif($rank == 3): ?>
                                                            <i class="fas fa-medal text-danger"></i>
                                                        <?php else: ?>
                                                            <?php echo $rank; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($performer['player_name']); ?></td>
                                                    <td><strong><?php echo number_format($performer['total_score']); ?></strong></td>
                                                    <td><?php echo number_format($performer['feathers']); ?></td>
                                                    <td><?php echo number_format($performer['potion']); ?></td>
                                                    <td><?php echo $performer['lives']; ?></td>
                                                </tr>
                                                <?php 
                                                $rank++;
                                                endforeach; 
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STUDENTS TAB -->
                    <div class="tab-pane fade" id="students" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Student Statistics</h3>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Total Students</span>
                                                <strong><?php echo $stats['total_users']; ?></strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Active (30 days)</span>
                                                <strong><?php echo $stats['active_users']; ?></strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>New (Period)</span>
                                                <strong><?php echo $stats['new_users']; ?></strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Activity Rate</span>
                                                <strong><?php echo $stats['total_users'] > 0 ? round(($stats['active_users'] / $stats['total_users']) * 100, 1) : 0; ?>%</strong>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Character Distribution</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Character</th>
                                                    <th>Count</th>
                                                    <th>Percentage</th>
                                                    <th>Visual</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($character_stats as $character => $count): 
                                                    $percentage = ($count / $stats['total_users']) * 100;
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($character); ?></td>
                                                    <td><?php echo $count; ?></td>
                                                    <td><?php echo round($percentage, 1); ?>%</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-info" style="width: <?php echo $percentage; ?>%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Students -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Recent Student Registrations</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Player Name</th>
                                                    <th>Registration Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($recent_students as $student): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['player_name']); ?></td>
                                                    <td><?php echo date('M d, Y h:i A', strtotime($student['created_at'])); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- EDUCATORS TAB -->
                    <div class="tab-pane fade" id="educators" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Educator Status Distribution</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Count</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total_teachers = array_sum($teacher_status_stats);
                                                foreach($teacher_status_stats as $status => $count): 
                                                    $percentage = $total_teachers > 0 ? ($count / $total_teachers) * 100 : 0;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge badge-<?php echo $status == 'active' ? 'success' : 'secondary'; ?>">
                                                            <?php echo ucfirst($status); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $count; ?></td>
                                                    <td><?php echo round($percentage, 1); ?>%</td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Educators by Subject</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Count</th>
                                                    <th>Visual</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total_by_subject = array_sum($teacher_subject_stats);
                                                foreach($teacher_subject_stats as $subject => $count): 
                                                    $percentage = $total_by_subject > 0 ? ($count / $total_by_subject) * 100 : 0;
                                                ?>
                                                <tr>
                                                    <td><?php echo ucfirst($subject); ?></td>
                                                    <td><?php echo $count; ?></td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-primary" style="width: <?php echo $percentage; ?>%">
                                                                <?php echo round($percentage, 1); ?>%
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Educators -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Recent Educator Registrations</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Teacher Name</th>
                                                    <th>Email</th>
                                                    <th>Registration Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($recent_teachers as $teacher): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($teacher['teacher_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                                                    <td><?php echo date('M d, Y h:i A', strtotime($teacher['created_at'])); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SUBJECTS TAB -->
                    <div class="tab-pane fade" id="subjects" role="tabpanel">
                        <div class="row mt-3">
                            <?php foreach($subjects as $subject): 
                                $avg_level = $stats["avg_{$subject}_level"];
                                $percentage = $stats["avg_{$subject}_percentage"];
                                $completed = $stats["{$subject}_completed"];
                                $max_level = count($category_columns[$subject]) * 10;
                            ?>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title text-uppercase"><?php echo $subject; ?> Analytics</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-6 text-center">
                                                <h4><?php echo round($avg_level, 1); ?></h4>
                                                <small class="text-muted">Average Level</small>
                                            </div>
                                            <div class="col-6 text-center">
                                                <h4><?php echo $completed; ?></h4>
                                                <small class="text-muted">Completed</small>
                                            </div>
                                        </div>
                                        <div class="progress mb-2" style="height: 25px;">
                                            <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%">
                                                <?php echo round($percentage, 1); ?>%
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-layer-group"></i> 
                                            <?php echo count($category_columns[$subject]); ?> categories, 
                                            Max Level: <?php echo $max_level; ?>
                                        </small>
                                        
                                        <hr>
                                        
                                        <h6 class="font-weight-bold">Categories:</h6>
                                        <ul class="list-unstyled">
                                            <?php foreach($category_columns[$subject] as $category): 
                                                $cat_name = str_replace($subject . '_', '', $category);
                                                $cat_name = str_replace('_level', '', $cat_name);
                                                $cat_name = ucwords(str_replace('_', ' ', $cat_name));
                                            ?>
                                            <li><i class="fas fa-check-circle text-success"></i> <?php echo $cat_name; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- GAME STATS TAB -->
                    <div class="tab-pane fade" id="game" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-lg-4 col-6">
                                <div class="stat-box" style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);">
                                    <h3><?php echo number_format($stats['total_feathers']); ?></h3>
                                    <p><i class="fas fa-feather"></i> Total Feathers Collected</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="stat-box" style="background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);">
                                    <h3><?php echo number_format($stats['total_potion']); ?></h3>
                                    <p><i class="fas fa-flask"></i> Total Potions Collected</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-6">
                                <div class="stat-box" style="background: linear-gradient(135deg, #E91E63 0%, #C2185B 100%);">
                                    <h3><?php echo $stats['avg_lives']; ?></h3>
                                    <p><i class="fas fa-heart"></i> Average Lives</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Game Resources Summary</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Resource</th>
                                                    <th>Total</th>
                                                    <th>Average per Student</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><i class="fas fa-feather text-warning"></i> Feathers</td>
                                                    <td><?php echo number_format($stats['total_feathers']); ?></td>
                                                    <td><?php echo $stats['total_users'] > 0 ? number_format($stats['total_feathers'] / $stats['total_users'], 2) : 0; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fas fa-flask text-purple"></i> Potions</td>
                                                    <td><?php echo number_format($stats['total_potion']); ?></td>
                                                    <td><?php echo $stats['total_users'] > 0 ? number_format($stats['total_potion'] / $stats['total_users'], 2) : 0; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><i class="fas fa-heart text-danger"></i> Lives</td>
                                                    <td>-</td>
                                                    <td><?php echo $stats['avg_lives']; ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QUIZZES TAB -->
                    <div class="tab-pane fade" id="quizzes" role="tabpanel">
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Quiz Distribution by Subject</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Total Quizzes</th>
                                                    <th>Percentage</th>
                                                    <th>Visual</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total_quizzes = array_sum($quiz_stats);
                                                foreach($quiz_stats as $subject => $count): 
                                                    $percentage = $total_quizzes > 0 ? ($count / $total_quizzes) * 100 : 0;
                                                ?>
                                                <tr>
                                                    <td class="text-uppercase font-weight-bold"><?php echo $subject; ?></td>
                                                    <td><?php echo $count; ?></td>
                                                    <td><?php echo round($percentage, 1); ?>%</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-info" style="width: <?php echo $percentage; ?>%"></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <tr class="font-weight-bold">
                                                    <td>TOTAL</td>
                                                    <td><?php echo $total_quizzes; ?></td>
                                                    <td>100%</td>
                                                    <td>-</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Quiz Statistics Summary</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <?php foreach($quiz_stats as $subject => $count): ?>
                                            <div class="col-md-4 col-sm-6">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-info"><i class="fas fa-question-circle"></i></span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text text-uppercase"><?php echo $subject; ?></span>
                                                        <span class="info-box-number"><?php echo $count; ?> Quizzes</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

    <?php include('includes/footer.php'); ?>
</div>

<script>
$(document).ready(function() {
    // Tab switching animation
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $($(e.target).attr('href')).addClass('animated fadeIn');
    });
    
    // Smooth scroll to top when switching tabs
    $('a[data-toggle="tab"]').on('click', function (e) {
        $('html, body').animate({ scrollTop: 0 }, 300);
    });
});

function printAnalytics() {
    const win = window.open('', '_blank', 'width=1000,height=750');
    const dateRange = '<?php echo date("M d, Y", strtotime($start_date)); ?>';
    const dateEnd   = '<?php echo date("M d, Y", strtotime($end_date)); ?>';
    const now       = '<?php echo date("F d, Y  h:i A"); ?>';
    const adminName = '<?php echo htmlspecialchars($_SESSION["name"] ?? "Admin"); ?>';
    const stats = <?php echo json_encode($stats); ?>;
    const topPerformers = <?php echo json_encode($top_performers); ?>;
    const characterStats = <?php echo json_encode($character_stats); ?>;
    const teacherStatusStats = <?php echo json_encode($teacher_status_stats); ?>;
    const teacherSubjectStats = <?php echo json_encode($teacher_subject_stats); ?>;
    const quizStats = <?php echo json_encode($quiz_stats); ?>;
    const recentStudents = <?php echo json_encode($recent_students); ?>;
    const recentTeachers = <?php echo json_encode($recent_teachers); ?>;
    const subjectNames = { english:'English', ap:'Araling Panlipunan', filipino:'Filipino', math:'Mathematics', science:'Science' };
    const subjectColors = { english:'#0A5F38', ap:'#1cc88a', filipino:'#e6a817', math:'#36b9cc', science:'#1E7D4E' };
    const catCounts = { english:5, ap:5, filipino:5, math:6, science:5 };

    function pct(val, max) { return max > 0 ? Math.min(100, Math.round((val/max)*100)) : 0; }
    function fmt(n) { return Number(n).toLocaleString(); }
    function bar(pctVal, color) {
        return `<div class="bar-wrap"><div class="bar" style="width:${pctVal}%;background:${color};"></div><span class="bar-label">${pctVal}%</span></div>`;
    }
    function secHeader(title, icon) {
        return `<div class="sec-header"><span>${icon}</span><span>${title}</span></div>`;
    }
    function kpi(value, label, color, icon) {
        return `<div class="kpi" style="border-top:4px solid ${color}"><div class="kpi-icon" style="color:${color}">${icon}</div><div class="kpi-val">${value}</div><div class="kpi-lbl">${label}</div></div>`;
    }

    // 1. Overview KPIs
    let overviewKpis = `<div class="kpi-grid">
        ${kpi(fmt(stats.total_users),    'Total Students',        '#0A5F38','&#128100;')}
        ${kpi(fmt(stats.active_users),   'Active (30 days)',      '#1E88E5','&#9989;')}
        ${kpi(fmt(stats.new_users),      'New (Period)',          '#43A047','&#10133;')}
        ${kpi(fmt(stats.total_educators),'Total Educators',       '#FB8C00','&#127979;')}
    </div>`;

    // 2. Subject progress
    let subRows = '';
    for (const [sub, name] of Object.entries(subjectNames)) {
        const p = stats[`avg_${sub}_percentage`] || 0;
        const avg = stats[`avg_${sub}_level`] || 0;
        const comp = stats[`${sub}_completed`] || 0;
        const max = catCounts[sub] * 10;
        subRows += `<tr><td><strong>${name}</strong></td><td>${avg} / ${max}</td><td>${bar(p, subjectColors[sub])}</td><td>${comp}</td></tr>`;
    }
    let subjectSection = `${secHeader('Subject Completion Progress','&#128218;')}
        <table><thead><tr><th>Subject</th><th>Avg Level</th><th style="width:40%">Progress</th><th>Completed</th></tr></thead><tbody>${subRows}</tbody></table>`;

    // 3. Top performers
    let perfRows = '';
    topPerformers.forEach((p,i) => {
        const medal = i===0?'🥇':i===1?'🥈':i===2?'🥉':`${i+1}`;
        perfRows += `<tr><td style="text-align:center">${medal}</td><td>${p.player_name}</td><td><strong>${fmt(p.total_score)}</strong></td><td>${fmt(p.feathers)}</td><td>${fmt(p.potion)}</td><td>${p.lives}</td></tr>`;
    });
    let perfSection = `${secHeader('Top 10 Performers','&#127942;')}
        <table><thead><tr><th>#</th><th>Player</th><th>Score</th><th>Feathers</th><th>Potions</th><th>Lives</th></tr></thead><tbody>${perfRows}</tbody></table>`;

    // 4. Students
    const totalUsers = stats.total_users || 1;
    let charRows = '';
    for (const [ch, cnt] of Object.entries(characterStats)) {
        const p = pct(cnt, totalUsers);
        charRows += `<tr><td>${ch||'None'}</td><td>${cnt}</td><td>${p}%</td><td>${bar(p,'#17a2b8')}</td></tr>`;
    }
    let studRows = '';
    recentStudents.forEach(s => {
        studRows += `<tr><td>${s.player_name}</td><td>${new Date(s.created_at).toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'})}</td></tr>`;
    });
    let studentsSection = `${secHeader('Student Statistics','&#127891;')}
        <div class="two-col">
            <div><h4>Summary</h4>
                <table>
                    <tr><td>Total Students</td><td><strong>${fmt(stats.total_users)}</strong></td></tr>
                    <tr><td>Active (30 days)</td><td><strong>${fmt(stats.active_users)}</strong></td></tr>
                    <tr><td>New (Period)</td><td><strong>${fmt(stats.new_users)}</strong></td></tr>
                    <tr><td>Activity Rate</td><td><strong>${pct(stats.active_users,stats.total_users)}%</strong></td></tr>
                </table>
            </div>
            <div><h4>Character Distribution</h4>
                <table><thead><tr><th>Character</th><th>Count</th><th>%</th><th>Visual</th></tr></thead><tbody>${charRows}</tbody></table>
            </div>
        </div>
        <h4>Recent Registrations</h4>
        <table><thead><tr><th>Player Name</th><th>Registration Date</th></tr></thead><tbody>${studRows}</tbody></table>`;

    // 5. Educators
    const totalTeachers = Object.values(teacherStatusStats).reduce((a,b)=>a+b,0)||1;
    let statusRows = '';
    for (const [st, cnt] of Object.entries(teacherStatusStats)) {
        const p = pct(cnt, totalTeachers);
        const dot = st==='active'?'#28a745':'#6c757d';
        statusRows += `<tr><td><span style="color:${dot}">&#9679;</span> ${st.charAt(0).toUpperCase()+st.slice(1)}</td><td>${cnt}</td><td>${p}%</td></tr>`;
    }
    const totalBySub = Object.values(teacherSubjectStats).reduce((a,b)=>a+b,0)||1;
    let subTeachRows = '';
    for (const [sub, cnt] of Object.entries(teacherSubjectStats)) {
        subTeachRows += `<tr><td>${sub.charAt(0).toUpperCase()+sub.slice(1)}</td><td>${cnt}</td><td>${bar(pct(cnt,totalBySub),'#007bff')}</td></tr>`;
    }
    let teachRows = '';
    recentTeachers.forEach(t => {
        teachRows += `<tr><td>${t.teacher_name}</td><td>${t.email}</td><td>${new Date(t.created_at).toLocaleDateString('en-US',{year:'numeric',month:'short',day:'numeric'})}</td></tr>`;
    });
    let educatorsSection = `${secHeader('Educator Statistics','&#128203;')}
        <div class="two-col">
            <div><h4>Status Distribution</h4>
                <table><thead><tr><th>Status</th><th>Count</th><th>%</th></tr></thead><tbody>${statusRows}</tbody></table>
            </div>
            <div><h4>By Subject</h4>
                <table><thead><tr><th>Subject</th><th>Count</th><th>Visual</th></tr></thead><tbody>${subTeachRows}</tbody></table>
            </div>
        </div>
        <h4>Recent Educator Registrations</h4>
        <table><thead><tr><th>Name</th><th>Email</th><th>Date</th></tr></thead><tbody>${teachRows}</tbody></table>`;

    // 6. Game Stats
    let gameSection = `${secHeader('Game Statistics','&#127918;')}
        <div class="kpi-grid">
            ${kpi(fmt(stats.total_feathers),'Total Feathers','#e6a817','&#129413;')}
            ${kpi(fmt(stats.total_potion),  'Total Potions', '#9C27B0','&#9879;')}
            ${kpi(stats.avg_lives,          'Avg Lives',     '#E91E63','&#10084;')}
        </div>
        <table><thead><tr><th>Resource</th><th>Total</th><th>Avg per Student</th></tr></thead>
        <tbody>
            <tr><td>&#129413; Feathers</td><td>${fmt(stats.total_feathers)}</td><td>${stats.total_users>0?(stats.total_feathers/stats.total_users).toFixed(2):0}</td></tr>
            <tr><td>&#9879; Potions</td><td>${fmt(stats.total_potion)}</td><td>${stats.total_users>0?(stats.total_potion/stats.total_users).toFixed(2):0}</td></tr>
            <tr><td>&#10084; Lives</td><td>—</td><td>${stats.avg_lives}</td></tr>
        </tbody></table>`;

    // 7. Quizzes
    const totalQ = Object.values(quizStats).reduce((a,b)=>a+Number(b),0)||1;
    let quizRows = '';
    for (const [sub, cnt] of Object.entries(quizStats)) {
        const p = pct(cnt, totalQ);
        quizRows += `<tr><td><strong>${subjectNames[sub]||sub}</strong></td><td>${cnt}</td><td>${p}%</td><td>${bar(p,subjectColors[sub]||'#0A5F38')}</td></tr>`;
    }
    let quizSection = `${secHeader('Quiz Distribution','&#10067;')}
        <table><thead><tr><th>Subject</th><th>Total Quizzes</th><th>%</th><th style="width:35%">Visual</th></tr></thead>
        <tbody>${quizRows}<tr style="font-weight:bold;background:#f0f0f0"><td>TOTAL</td><td>${totalQ}</td><td>100%</td><td></td></tr></tbody></table>`;

    win.document.write(`<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Play2Review Analytics Report</title>
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',Arial,sans-serif;font-size:12px;color:#222;background:#fff}
  .cover{background:linear-gradient(135deg,#0A5F38 0%,#1E7D4E 100%);color:white;padding:32px 40px 24px;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .cover-logo{font-size:1.9rem;font-weight:900;letter-spacing:1px;margin-bottom:4px}
  .cover-logo span{color:#a8f0c6}
  .cover-title{font-size:1.1rem;font-weight:600;opacity:.95;margin-bottom:14px}
  .cover-meta{display:flex;gap:24px;flex-wrap:wrap;font-size:.75rem;opacity:.85;border-top:1px solid rgba(255,255,255,.3);padding-top:10px}
  .cover-meta span strong{display:block;font-size:.65rem;text-transform:uppercase;letter-spacing:.5px;opacity:.7;margin-bottom:1px}
  .page-body{padding:24px 40px}
  .sec-header{display:flex;align-items:center;gap:10px;background:#0A5F38;color:white;padding:7px 14px;border-radius:6px;margin:22px 0 10px;font-size:.9rem;font-weight:700;-webkit-print-color-adjust:exact;print-color-adjust:exact;page-break-after:avoid}
  .sec-header:first-child{margin-top:0}
  .kpi-grid{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}
  .kpi{flex:1;min-width:120px;border:1px solid #e0e0e0;border-radius:8px;padding:12px 10px;text-align:center;page-break-inside:avoid}
  .kpi-icon{font-size:1.3rem;margin-bottom:5px}
  .kpi-val{font-size:1.5rem;font-weight:800;color:#0A5F38;line-height:1;margin-bottom:3px}
  .kpi-lbl{font-size:.68rem;color:#666;text-transform:uppercase;letter-spacing:.3px}
  table{width:100%;border-collapse:collapse;margin-bottom:12px;font-size:.8rem;page-break-inside:auto}
  thead tr{background:#0A5F38;color:white;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  th{padding:6px 9px;text-align:left;font-weight:600;font-size:.74rem;text-transform:uppercase;letter-spacing:.3px}
  td{padding:5px 9px;border-bottom:1px solid #eee;vertical-align:middle}
  tr:nth-child(even) td{background:#f8f9fa}
  tr:last-child td{border-bottom:none}
  .bar-wrap{position:relative;background:#e9ecef;border-radius:20px;height:13px;overflow:hidden}
  .bar{height:100%;border-radius:20px;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .bar-label{position:absolute;right:5px;top:0;line-height:13px;font-size:.65rem;font-weight:bold;color:#333}
  .two-col{display:flex;gap:18px;margin-bottom:12px}
  .two-col>div{flex:1;min-width:0}
  h4{font-size:.8rem;font-weight:700;color:#0A5F38;margin:8px 0 5px;text-transform:uppercase;letter-spacing:.3px}
  .rpt-footer{margin-top:24px;padding:10px 40px;background:#f8f9fa;border-top:2px solid #0A5F38;font-size:.68rem;color:#666;display:flex;justify-content:space-between;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  @page{margin:10mm 8mm;size:A4}
  @media print{.sec-header{page-break-after:avoid}tr{page-break-inside:avoid}.kpi{page-break-inside:avoid}}
</style></head>
<body>
  <div class="cover">
    <div class="cover-logo">Play2<span>Review</span></div>
    <div class="cover-title">Comprehensive Analytics Report</div>
    <div class="cover-meta">
      <span><strong>Date Range</strong>${dateRange} &mdash; ${dateEnd}</span>
      <span><strong>Generated</strong>${now}</span>
      <span><strong>Prepared by</strong>${adminName}</span>
      <span><strong>Total Students</strong>${fmt(stats.total_users)}</span>
      <span><strong>Total Educators</strong>${fmt(stats.total_educators)}</span>
    </div>
  </div>
  <div class="page-body">
    ${overviewKpis}
    ${subjectSection}
    ${perfSection}
    ${studentsSection}
    ${educatorsSection}
    ${gameSection}
    ${quizSection}
  </div>
  <div class="rpt-footer">
    <span>Play2Review &mdash; Admin Analytics Report</span>
    <span>Generated: ${now}</span>
  </div>
</body></html>`);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); win.close(); }, 800);
}
</script>

</body>
</html>
