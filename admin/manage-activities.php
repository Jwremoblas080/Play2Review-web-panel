<?php
require_once('../configurations/configurations.php');
require_once('category-config.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Fetch all students with their progress
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($con, $query);
$students = array();
while($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}

// Function to determine student's current status and next subject
function getStudentProgress($student) {
    $subjects_flow = ['english', 'ap', 'filipino', 'science', 'math'];
    $completed_subjects = [];
    $current_subject = null;
    $next_subject = null;
    $progress_percentage = 0;
    
    foreach($subjects_flow as $subject) {
        $level = $student[$subject . '_completed_level'];
        
        if($level == 10) {
            $completed_subjects[] = $subject;
        } else {
            if($current_subject === null) {
                $current_subject = $subject;
                $progress_percentage = ($level / 10) * 100;
            }
        }
    }
    
    // Determine next subject
    if($current_subject) {
        $current_index = array_search($current_subject, $subjects_flow);
        $next_subject = isset($subjects_flow[$current_index + 1]) ? $subjects_flow[$current_index + 1] : null;
    } else {
        // All subjects completed
        $current_subject = 'completed';
        $next_subject = null;
        $progress_percentage = 100;
    }
    
    return [
        'completed_subjects' => $completed_subjects,
        'current_subject' => $current_subject,
        'next_subject' => $next_subject,
        'progress_percentage' => $progress_percentage,
        'total_completed' => count($completed_subjects),
        'total_subjects' => count($subjects_flow)
    ];
}

// Function to get category-level progress for a student
function getCategoryProgress($student_id, $subject) {
    global $con, $CATEGORY_CONFIG;
    
    $categories = getCategoriesBySubject($subject);
    $category_stats = [];
    
    foreach ($categories as $category) {
        // Count total questions in this category
        $total_query = "SELECT COUNT(*) as total FROM quizes 
                       WHERE subject_name = '$subject' 
                       AND category = '" . mysqli_real_escape_string($con, $category) . "'";
        $total_result = mysqli_query($con, $total_query);
        $total_row = mysqli_fetch_assoc($total_result);
        $total_questions = $total_row['total'];
        
        // For now, we'll show potential - in future, track actual answered questions
        // This requires a new table to track student answers per question
        $category_stats[] = [
            'name' => $category,
            'total_questions' => $total_questions,
            'completed' => 0, // Placeholder - implement answer tracking
            'percentage' => 0  // Placeholder
        ];
    }
    
    return $category_stats;
}

// Function to get overall category statistics across all students
function getCategoryStatistics() {
    global $con;
    
    $subjects = ['english', 'math', 'filipino', 'ap', 'science'];
    $stats = [];
    
    foreach ($subjects as $subject) {
        $categories = getCategoriesBySubject($subject);
        $subject_stats = [];
        
        foreach ($categories as $category) {
            $query = "SELECT COUNT(*) as count FROM quizes 
                     WHERE subject_name = '$subject' 
                     AND category = '" . mysqli_real_escape_string($con, $category) . "'";
            $result = mysqli_query($con, $query);
            $row = mysqli_fetch_assoc($result);
            
            $subject_stats[$category] = $row['count'];
        }
        
        $stats[$subject] = $subject_stats;
    }
    
    return $stats;
}

// Calculate overall statistics
$total_students = count($students);
$students_completed_all = 0;
$subject_completion_stats = [
    'english' => 0,
    'ap' => 0,
    'filipino' => 0,
    'science' => 0,
    'math' => 0
];

$progress_distribution = [
    'just_started' => 0,      // 0-20%
    'beginner' => 0,          // 21-40%
    'intermediate' => 0,      // 41-60%
    'advanced' => 0,          // 61-80%
    'near_completion' => 0,   // 81-99%
    'completed' => 0          // 100%
];

foreach($students as $student) {
    $progress = getStudentProgress($student);
    
    // Count students who completed all subjects
    if($progress['current_subject'] == 'completed') {
        $students_completed_all++;
    }
    
    // Count subject completions
    foreach($progress['completed_subjects'] as $subject) {
        $subject_completion_stats[$subject]++;
    }
    
    // Categorize progress
    $total_progress = ($progress['total_completed'] / $progress['total_subjects']) * 100;
    if($total_progress == 100) {
        $progress_distribution['completed']++;
    } elseif($total_progress >= 81) {
        $progress_distribution['near_completion']++;
    } elseif($total_progress >= 61) {
        $progress_distribution['advanced']++;
    } elseif($total_progress >= 41) {
        $progress_distribution['intermediate']++;
    } elseif($total_progress >= 21) {
        $progress_distribution['beginner']++;
    } else {
        $progress_distribution['just_started']++;
    }
}

// Get recent activities (last 7 days)
$seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
$query = "SELECT * FROM users WHERE created_at >= '$seven_days_ago' OR updated_at >= '$seven_days_ago' ORDER BY updated_at DESC LIMIT 10";
$result = mysqli_query($con, $query);
$recent_activities = array();
while($row = mysqli_fetch_assoc($result)) {
    $recent_activities[] = $row;
}

// Get category statistics
$category_statistics = getCategoryStatistics();
$subject_names_full = [
    'english' => 'English',
    'ap' => 'Araling Panlipunan',
    'filipino' => 'Filipino',
    'math' => 'Mathematics',
    'science' => 'Science'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Progress Monitoring - Play2Review Admin</title>
    
    <?php include('includes/header.php'); ?>
    <style>
        :root {
    --primary: #0A5F38;
    --secondary: #1E7D4E;
    --success: #1cc88a;
    --warning: #f6c23e;
    --info: #36b9cc;
    --dark: #2c3e50;
    --light: #f8f9fa;
}

.progress-tracker {
    background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
    border-radius: 15px;
    padding: 20px;
    color: white;
    margin-bottom: 30px;
}

.subject-node {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    position: relative;
}

.subject-node:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 25px;
    top: 50px;
    width: 2px;
    height: 20px;
    background: rgba(255,255,255,0.5);
}

.node-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 1.2rem;
    border: 2px solid white;
}

.node-completed {
    background: var(--success);
}

.node-current {
    background: var(--warning);
    animation: pulse 2s infinite;
}

.node-upcoming {
    background: rgba(255,255,255,0.1);
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.node-info {
    flex: 1;
}

.node-title {
    font-weight: bold;
    margin-bottom: 5px;
}

.node-status {
    font-size: 0.9rem;
    opacity: 0.9;
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

.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
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

.progress-bar-container {
    background: #e9ecef;
    border-radius: 10px;
    height: 20px;
    overflow: hidden;
    margin: 5px 0;
}

.progress-bar-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 0.6s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: white;
    font-weight: bold;
}

.bg-english { background: linear-gradient(45deg, #0A5F38, #1E7D4E); }
.bg-ap { background: linear-gradient(45deg, #1cc88a, #2ecc71); }
.bg-filipino { background: linear-gradient(45deg, #f6c23e, #f39c12); }
.bg-science { background: linear-gradient(45deg, #e74a3b, #e74c3c); }
.bg-math { background: linear-gradient(45deg, #36b9cc, #3498db); }

.status-badge {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
}

.badge-completed { background: var(--success); color: white; }
.badge-in-progress { background: var(--warning); color: #212529; }
.badge-not-started { background: #6c757d; color: white; }

.activity-item {
    border-left: 3px solid var(--secondary);
    padding-left: 15px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
}

.activity-item:hover {
    background-color: #e9ecef;
    transform: translateX(5px);
}

.subject-mini-card {
    background: white;
    border-radius: 8px;
    padding: 10px;
    margin: 5px 0;
    border-left: 4px solid;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.mini-english { border-left-color: #0A5F38; }
.mini-ap { border-left-color: #1cc88a; }
.mini-filipino { border-left-color: #f6c23e; }
.mini-science { border-left-color: #e74a3b; }
.mini-math { border-left-color: #36b9cc; }

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

.progress-distribution-bar {
    height: 30px;
    border-radius: 15px;
    overflow: hidden;
    background: #e9ecef;
    margin: 10px 0;
}

.distribution-segment {
    height: 100%;
    display: inline-block;
    text-align: center;
    color: white;
    font-weight: bold;
    font-size: 0.8rem;
    line-height: 30px;
}

.subject-category-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    border-left: 4px solid var(--primary);
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.category-list {
    max-height: 400px;
    overflow-y: auto;
}

.category-item {
    background: white;
    padding: 12px;
    border-radius: 8px;
    border-left: 3px solid var(--info);
    transition: all 0.3s ease;
}

.category-item:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.category-name {
    flex: 1;
    font-size: 0.95rem;
}

.category-count {
    margin-left: 10px;
}

.badge-primary {
    background-color: var(--primary) !important;
}

/* Scrollbar styling for category list */
.category-list::-webkit-scrollbar {
    width: 6px;
}

.category-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.category-list::-webkit-scrollbar-thumb {
    background: var(--primary);
    border-radius: 10px;
}

.category-list::-webkit-scrollbar-thumb:hover {
    background: var(--secondary);
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
                        <h1 class="m-0 dashboard-title">Student Progress Monitoring</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Progress Monitoring</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Overall Statistics -->
                <div class="row mb-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $total_students; ?></div>
                            <div class="stats-label">Total Students</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $students_completed_all; ?></div>
                            <div class="stats-label">Completed All Subjects</div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo round(($students_completed_all / max(1, $total_students)) * 100, 1); ?>%</div>
                            <div class="stats-label">Overall Completion Rate</div>
                        </div>
                    </div>
                </div>

                <!-- Progress Flow Visualization -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="progress-tracker">
                            <h4 class="text-center mb-4"><i class="fas fa-road"></i> Learning Journey Flow</h4>
                            <div class="row">
                                <?php
                                $subjects_flow = [
                                    'english' => ['icon' => 'fas fa-language', 'name' => 'English'],
                                    'ap' => ['icon' => 'fas fa-globe-asia', 'name' => 'Araling Panlipunan'],
                                    'filipino' => ['icon' => 'fas fa-book', 'name' => 'Filipino'],
                                    'science' => ['icon' => 'fas fa-flask', 'name' => 'Science'],
                                    'math' => ['icon' => 'fas fa-calculator', 'name' => 'Mathematics']
                                ];
                                
                                foreach($subjects_flow as $subject => $info): 
                                    $completion_count = $subject_completion_stats[$subject];
                                    $completion_rate = round(($completion_count / max(1, $total_students)) * 100, 1);
                                ?>
                                <div class="col-md-2 col-4 text-center mb-3">
                                    <div class="node-icon <?php echo $completion_count > 0 ? 'node-completed' : 'node-upcoming'; ?>">
                                        <i class="<?php echo $info['icon']; ?>"></i>
                                    </div>
                                    <div class="node-info text-center" style="margin-top:-5vh;">
                                        <div class="node-title"><?php echo $info['name']; ?></div>
                                        <div class="node-status"><?php echo $completion_rate; ?>% Complete</div>
                                        <small><?php echo $completion_count; ?> students</small>
                                    </div>
                                </div>
                                <?php if($subject !== 'math'): ?>
                                <div class="col-md-1 col-1 text-center mb-3 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-arrow-right text-white"></i>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Students Progress Table -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Student Progress Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="activitiesTable" class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Current Status</th>
                                                <th>Progress</th>
                                                <th>Completed</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($students)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                                                        <h5>No Students Found</h5>
                                                        <p class="text-muted">No student data available for progress tracking.</p>
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach($students as $student): 
                                                    $progress = getStudentProgress($student);
                                                    $subject_names = [
                                                        'english' => 'English',
                                                        'ap' => 'AP',
                                                        'filipino' => 'Filipino',
                                                        'science' => 'Science',
                                                        'math' => 'Math'
                                                    ];
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="student-name-cell">
                                                            <div class="student-avatar">
                                                                <?php echo strtoupper(substr($student['player_name'], 0, 1)); ?>
                                                            </div>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($student['player_name']); ?></strong>
                                                                <br>
                                                                <small class="text-muted">ID: <?php echo htmlspecialchars($student['student_id']); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if($progress['current_subject'] == 'completed'): ?>
                                                            <span class="badge-completed status-badge">
                                                                <i class="fas fa-trophy"></i> All Completed
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge-in-progress status-badge">
                                                                <i class="fas fa-spinner"></i> 
                                                                <?php echo $subject_names[$progress['current_subject']]; ?> 
                                                                (Level <?php echo $student[$progress['current_subject'] . '_completed_level']; ?>/10)
                                                            </span>
                                                            <?php if($progress['next_subject']): ?>
                                                                <br>
                                                                <small class="text-muted">Next: <?php echo $subject_names[$progress['next_subject']]; ?></small>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="progress-bar-container">
                                                            <div class="progress-bar-fill bg-<?php echo $progress['current_subject'] != 'completed' ? $progress['current_subject'] : 'math'; ?>" 
                                                                 style="width: <?php echo $progress['progress_percentage']; ?>%">
                                                                <?php echo round($progress['progress_percentage']); ?>%
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?php echo $progress['total_completed']; ?>/<?php echo $progress['total_subjects']; ?> subjects
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <?php foreach($progress['completed_subjects'] as $subject): ?>
                                                            <span class="badge bg-<?php echo $subject; ?> mr-1 mb-1">
                                                                <?php echo ucfirst($subject); ?>
                                                            </span>
                                                        <?php endforeach; ?>
                                                        <?php if(empty($progress['completed_subjects'])): ?>
                                                            <span class="text-muted">None yet</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-info btn-sm view-progress" 
                                                                data-id="<?php echo $student['id']; ?>"
                                                                data-toggle="modal" data-target="#progressDetailModal">
                                                            <i class="fas fa-chart-line"></i> Details
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

                    <!-- Progress Distribution & Recent Activities -->
                    <div class="col-lg-4">
                        <!-- Progress Distribution -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">Progress Distribution</h3>
                            </div>
                            <div class="card-body">
                                <?php
                                $distribution_colors = [
                                    'just_started' => '#e74a3b',
                                    'beginner' => '#f6c23e',
                                    'intermediate' => '#36b9cc',
                                    'advanced' => '#4e73df',
                                    'near_completion' => '#1cc88a',
                                    'completed' => '#2e8b57'
                                ];
                                
                                $distribution_labels = [
                                    'just_started' => 'Just Started (0-20%)',
                                    'beginner' => 'Beginner (21-40%)',
                                    'intermediate' => 'Intermediate (41-60%)',
                                    'advanced' => 'Advanced (61-80%)',
                                    'near_completion' => 'Near Completion (81-99%)',
                                    'completed' => 'Completed (100%)'
                                ];
                                ?>
                                <div class="progress-distribution-bar">
                                    <?php foreach($progress_distribution as $key => $count): 
                                        $percentage = ($count / max(1, $total_students)) * 100;
                                        if($percentage > 0):
                                    ?>
                                    <div class="distribution-segment" style="margin-top:-0.5vh; width: <?php echo $percentage; ?>%; background: <?php echo $distribution_colors[$key]; ?>;"
                                         title="<?php echo $distribution_labels[$key]; ?>: <?php echo $count; ?> students">
                                        <?php if($percentage > 10): ?>
                                            <?php echo round($percentage); ?>%
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; endforeach; ?>
                                </div>
                                <div class="mt-3">
                                    <?php foreach($progress_distribution as $key => $count): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span>
                                            <span class="badge mr-2" style="background: <?php echo $distribution_colors[$key]; ?>;">&nbsp;&nbsp;&nbsp;</span>
                                            <?php echo $distribution_labels[$key]; ?>
                                        </span>
                                        <strong><?php echo $count; ?> students</strong>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activities -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Activities</h3>
                            </div>
                            <div class="card-body">
                                <?php if(empty($recent_activities)): ?>
                                    <p class="text-muted text-center">No recent activities</p>
                                <?php else: ?>
                                    <?php foreach($recent_activities as $activity): 
                                        $progress = getStudentProgress($activity);
                                    ?>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($activity['player_name']); ?></h6>
                                            <small><?php echo date('M j', strtotime($activity['updated_at'])); ?></small>
                                        </div>
                                        <p class="mb-1">
                                            <?php if($progress['current_subject'] == 'completed'): ?>
                                                <i class="fas fa-trophy text-success"></i> Completed all subjects
                                            <?php else: ?>
                                                <i class="fas fa-spinner text-warning"></i> 
                                                Working on <?php echo ucfirst($progress['current_subject']); ?> 
                                                (Level <?php echo $activity[$progress['current_subject'] . '_completed_level']; ?>)
                                            <?php endif; ?>
                                        </p>
                                        <small class="text-muted">
                                            Progress: <?php echo $progress['total_completed']; ?>/5 subjects
                                        </small>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category-Level Statistics -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-tags"></i> Question Distribution by Category</h3>
                                <p class="mb-0 text-white-50 small">See how many questions are available in each category across all subjects</p>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($category_statistics as $subject => $categories): ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="subject-category-card">
                                            <h5 class="mb-3">
                                                <span class="badge bg-<?php echo $subject; ?> mr-2">
                                                    <?php echo $subject_names_full[$subject]; ?>
                                                </span>
                                            </h5>
                                            
                                            <?php 
                                            $total_subject_questions = array_sum($categories);
                                            ?>
                                            
                                            <?php if (empty($categories) || $total_subject_questions == 0): ?>
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle"></i> No questions with categories yet
                                                </div>
                                            <?php else: ?>
                                                <div class="category-list">
                                                    <?php foreach ($categories as $category => $count): ?>
                                                    <div class="category-item mb-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="category-name">
                                                                <i class="fas fa-tag text-muted"></i>
                                                                <strong><?php echo htmlspecialchars($category); ?></strong>
                                                            </div>
                                                            <div class="category-count">
                                                                <span class="badge badge-primary">
                                                                    <?php echo $count; ?> question<?php echo $count != 1 ? 's' : ''; ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <?php if ($total_subject_questions > 0): ?>
                                                        <div class="progress mt-1" style="height: 5px;">
                                                            <div class="progress-bar bg-<?php echo $subject; ?>" 
                                                                 style="width: <?php echo ($count / $total_subject_questions) * 100; ?>%">
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?php echo round(($count / $total_subject_questions) * 100, 1); ?>% of <?php echo $subject_names_full[$subject]; ?> questions
                                                        </small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                
                                                <div class="mt-3 pt-3 border-top">
                                                    <strong>Total: <?php echo $total_subject_questions; ?> questions</strong>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
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

<!-- Progress Detail Modal -->
<div class="modal fade" id="progressDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Progress Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="progressDetailContent">
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
    $('#activitiesTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [],
        "columnDefs": [{ "orderable": false, "targets": [-1] }]
    });

    // View Progress Details
    $('.view-progress').click(function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: 'get_student_progress_details.php',
            type: 'POST',
            data: {id: id},
            success: function(response) {
                $('#progressDetailContent').html(response);
            }
        });
    });

    // Animate progress bars on page load
    $('.progress-bar-fill').each(function() {
        var width = $(this).css('width');
        $(this).css('width', '0').animate({
            width: width
        }, 1000);
    });
});
</script>
</body>
</html>