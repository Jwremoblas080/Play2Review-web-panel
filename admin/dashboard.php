<?php
require_once('../configurations/configurations.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Fetch statistics from database
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

// Subject completion statistics
$subjects = ['english', 'ap', 'filipino', 'math', 'science'];
foreach($subjects as $subject) {
    $query = "SELECT AVG({$subject}_completed_level) as avg_level FROM users";
    $result = mysqli_query($con, $query);
    $avg_level_result = mysqli_fetch_assoc($result);
    $avg_level = $avg_level_result["avg_level"] ?? 0;
    $stats["avg_{$subject}_level"] = round($avg_level, 1);
    $stats["avg_{$subject}_percentage"] = ($avg_level / 10) * 100;
    
    // users who completed all levels (level 10)
    $query = "SELECT COUNT(*) as completed FROM users WHERE {$subject}_completed_level = 10";
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

// Recent teacher registrations
$query = "SELECT teacher_name, email, created_at FROM educators ORDER BY created_at DESC LIMIT 5";
$result = mysqli_query($con, $query);
$recent_teachers = array();
while($row = mysqli_fetch_assoc($result)) {
    $recent_teachers[] = $row;
}

// Teacher statistics by status
$query = "SELECT status, COUNT(*) as count FROM educators GROUP BY status";
$result = mysqli_query($con, $query);
$teacher_status_stats = array();
while($row = mysqli_fetch_assoc($result)) {
    $teacher_status_stats[$row['status']] = $row['count'];
}

// Teacher statistics by subject
$query = "SELECT handled_subject, COUNT(*) as count FROM educators GROUP BY handled_subject";
$result = mysqli_query($con, $query);
$teacher_subject_stats = array();
while($row = mysqli_fetch_assoc($result)) {
    $teacher_subject_stats[$row['handled_subject']] = $row['count'];
}

// Top users by total score (each level is 100 points)
$query = "SELECT player_name, 
          (english_completed_level + ap_completed_level + filipino_completed_level + 
           math_completed_level + science_completed_level) * 100 as total_score,
          feathers, potion
          FROM users 
          ORDER BY total_score DESC 
          LIMIT 5";
$result = mysqli_query($con, $query);
$top_users = array();
while($row = mysqli_fetch_assoc($result)) {
    $top_users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - Play2Review Game Analytics</title>
    
    <?php include('includes/header.php'); ?>
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
    .stat-card-teachers { border-left-color: #0D7A47; }
    .stat-card-feathers { border-left-color: #0F8A50; }
    .stat-card-potion { border-left-color: #0C6B3F; }
    .stat-card-lives { border-left-color: #08482B; }
    
    .stat-icon {
        font-size: 2.5rem;
        opacity: 0.7;
    }
    
    .activity-item {
        border-left: 3px solid #0A5F38;
        padding-left: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        background: #F0F8F0;
        border-radius: 8px;
        padding: 12px;
    }
    
    .teacher-activity-item {
        border-left: 3px solid #0D7A47;
        padding-left: 15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        background: #F5FBF5;
        border-radius: 8px;
        padding: 12px;
    }
    
    .activity-item:hover, .teacher-activity-item:hover {
        transform: translateX(5px);
    }
    
    .activity-item:hover {
        background-color: #E8F5E8;
    }
    
    .teacher-activity-item:hover {
        background-color: #EDF7ED;
    }
    
    .progress {
        height: 20px;
        background-color: #E8F5E8;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 5px;
        border: 1px solid #C8E6C9;
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
        background-color: #0A5F38 !important;
    }
    
    .bg-english { background-color: #0A5F38 !important; }
    .bg-ap { background-color: #0D7A47 !important; }
    .bg-filipino { background-color: #0F8A50 !important; }
    .bg-math { background-color: #0C6B3F !important; }
    .bg-science { background-color: #08482B !important; }
    
    .card-header {
        background: linear-gradient(135deg, #0A5F38 0%, #0D7A47 100%);
        color: white;
        font-weight: bold;
    }
    
    .teacher-card-header {
        background: linear-gradient(135deg, #0A5F38 0%, #0C6B3F 100%);
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
        border: 1px solid #C8E6C9;
        border-radius: 10px;
        background: #F8FDF8;
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
        background: #F8FDF8;
        border-radius: 8px;
        border-left: 4px solid #0D7A47;
    }
    
    /* Custom small box styling */
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(10, 95, 56, 0.15);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }
    
    .small-box > .inner {
        padding: 10px;
    }
    
    .small-box > .small-box-footer {
        background: rgba(10, 95, 56, 0.1);
        color: rgba(255, 255, 255, 0.9);
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
        color: rgba(10, 95, 56, 0.15);
        z-index: 0;
        position: absolute;
        top: 15px;
        right: 15px;
    }
    
    .small-box:hover .icon {
        font-size: 2.7rem;
        transition: all 0.3s ease;
    }

    .text-english { color: #0A5F38; }
    .text-ap { color: #0D7A47; }
    .text-filipino { color: #0F8A50; }
    .text-math { color: #0C6B3F; }
    .text-science { color: #08482B; }
    
    .status-active { color: #0A5F38; }
    .status-inactive { color: #6c757d; }
    .status-pending { color: #FFA726; }
    
    .badge-active { background-color: #0A5F38; }
    .badge-inactive { background-color: #6c757d; }
    .badge-pending { background-color: #FFA726; }

    /* Additional green theme elements */
    .bg-gradient-danger { background: linear-gradient(135deg, #0A5F38 0%, #08482B 100%) !important; color: white; }
    .bg-gradient-primary { background: linear-gradient(135deg, #0A5F38 0%, #0D7A47 100%) !important; color: white; }
    .bg-gradient-purple { background: linear-gradient(135deg, #0C6B3F 0%, #0A5F38 100%) !important; color: white; }
    .bg-gradient-info { background: linear-gradient(135deg, #0D7A47 0%, #0B5532 100%) !important; color: white; }
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
                        <h1 class="m-0 dashboard-title">Play2Review Game Analytics Dashboard</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
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
                    
                    <!-- Total Teachers -->
                    <div class="col-lg-3 col-md-6">
                        <div class="small-box bg-gradient-primary stat-card stat-card-teachers">
                            <div class="inner">
                                <h3><?php echo $stats['total_teachers']; ?></h3>
                                <p>Total Teachers</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chalkboard-teacher stat-icon"></i>
                            </div>
                            <a href="manage-teachers.php" class="small-box-footer">
                                View All <i class="fas fa-arrow-circle-right"></i>
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
                
                <!-- Second Row: Subject Progress -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Subject Completion Progress</h3>
                                <div class="card-tools">
                                    <span class="badge badge-light">Max Level: 10</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach($subjects as $subject): 
                                        $percentage = $stats["avg_{$subject}_percentage"];
                                        $avg_level = $stats["avg_{$subject}_level"];
                                        $completed_count = $stats["{$subject}_completed"];
                                    ?>
                                    <div class="col-md-4">
                                        <div class="subject-progress">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-uppercase font-weight-bold text-<?php echo $subject; ?>">
                                                    <?php echo ucfirst($subject); ?>
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
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Third Row: Top Students & Recent Activities -->
                <div class="row">
                    <!-- Top Students & Teacher Stats -->
                    <div class="col-lg-6">
                        <!-- Top Students -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Top Students by Total Score</h3>
                            </div>
                            <div class="card-body p-0">
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
                            </div>
                        </div>

                        <!-- Teacher Statistics -->
                        <div class="card mt-4">
                            <div class="card-header teacher-card-header">
                                <h3 class="card-title">Teacher Statistics</h3>
                            </div>
                            <div class="card-body">
                                <!-- Teacher Status Stats -->
                                <h6 class="font-weight-bold">Teacher Status Distribution</h6>
                                <?php foreach($teacher_status_stats as $status => $count): 
                                    $status_percentage = ($count / $stats['total_teachers']) * 100;
                                ?>
                                <div class="teacher-stats">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="status-<?php echo $status; ?>">
                                            <i class="fas fa-circle mr-1"></i><?php echo ucfirst($status); ?>
                                        </span>
                                        <span><?php echo $count; ?> teachers (<?php echo round($status_percentage, 1); ?>%)</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar badge-<?php echo $status; ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $status_percentage; ?>% !important;"
                                             aria-valuenow="<?php echo $status_percentage; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo round($status_percentage, 1); ?>%
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <!-- Teacher Subject Stats -->
                                <h6 class="font-weight-bold mt-4">Subjects Handled by Teachers</h6>
                                <?php foreach($teacher_subject_stats as $subject => $count): 
                                    $subject_percentage = ($count / $stats['total_teachers']) * 100;
                                ?>
                                <div class="teacher-stats">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-<?php echo strtolower($subject); ?>">
                                            <i class="fas fa-book mr-1"></i><?php echo ucfirst($subject); ?>
                                        </span>
                                        <span><?php echo $count; ?> teachers</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar bg-<?php echo strtolower($subject); ?>" 
                                             role="progressbar" 
                                             style="width: <?php echo $subject_percentage; ?>% !important;"
                                             aria-valuenow="<?php echo $subject_percentage; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo round($subject_percentage, 1); ?>%
                                        </div>
                                    </div>
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

                        <!-- Recent Teacher Registrations -->
                        <div class="card mt-4">
                            <div class="card-header teacher-card-header">
                                <h3 class="card-title">Recent Teacher Registrations</h3>
                            </div>
                            <div class="card-body">
                                <?php foreach($recent_teachers as $teacher): ?>
                                <div class="teacher-activity-item">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($teacher['teacher_name']); ?></h6>
                                        <small><?php echo date('M j, Y g:i A', strtotime($teacher['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-0"><?php echo htmlspecialchars($teacher['email']); ?></p>
                                    <small class="text-muted">New teacher registered</small>
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
    <?php include('includes/modals.php'); ?>
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