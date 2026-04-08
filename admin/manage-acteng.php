<?php
require_once('../configurations/configurations.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Date range filtering
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Fetch activity logs with user information
$query = "SELECT al.*, u.player_name, u.username, u.student_id 
          FROM activity_logs al 
          JOIN users u ON al.user_id = u.id 
          WHERE DATE(al.created_at) BETWEEN '$start_date' AND '$end_date'
          ORDER BY al.created_at DESC";
$result = mysqli_query($con, $query);
$activity_logs = array();
while($row = mysqli_fetch_assoc($result)) {
    $activity_logs[] = $row;
}

// Engagement Statistics
$total_activities = count($activity_logs);

// Daily activity count
$daily_activity_query = "SELECT DATE(created_at) as date, COUNT(*) as count 
                         FROM activity_logs 
                         WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                         GROUP BY DATE(created_at) 
                         ORDER BY date";
$daily_result = mysqli_query($con, $daily_activity_query);
$daily_activities = array();
while($row = mysqli_fetch_assoc($daily_result)) {
    $daily_activities[$row['date']] = $row['count'];
}

// Most active users
$active_users_query = "SELECT u.id, u.player_name, u.username, COUNT(al.id) as activity_count 
                       FROM activity_logs al 
                       JOIN users u ON al.user_id = u.id 
                       WHERE DATE(al.created_at) BETWEEN '$start_date' AND '$end_date'
                       GROUP BY u.id 
                       ORDER BY activity_count DESC 
                       LIMIT 10";
$active_result = mysqli_query($con, $active_users_query);
$active_users = array();
while($row = mysqli_fetch_assoc($active_result)) {
    $active_users[] = $row;
}

// Activity type distribution
$activity_types_query = "SELECT 
                         SUM(CASE WHEN activity_description LIKE '%logged in%' THEN 1 ELSE 0 END) as logins,
                         SUM(CASE WHEN activity_description LIKE '%Started Playing%' THEN 1 ELSE 0 END) as game_starts,
                         SUM(CASE WHEN activity_description LIKE '%completed%' THEN 1 ELSE 0 END) as completions,
                         SUM(CASE WHEN activity_description LIKE '%registered%' THEN 1 ELSE 0 END) as registrations
                         FROM activity_logs 
                         WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
$types_result = mysqli_query($con, $activity_types_query);
$activity_types = mysqli_fetch_assoc($types_result);

// Peak hours analysis
$peak_hours_query = "SELECT HOUR(created_at) as hour, COUNT(*) as count 
                     FROM activity_logs 
                     WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                     GROUP BY HOUR(created_at) 
                     ORDER BY hour";
$peak_result = mysqli_query($con, $peak_hours_query);
$peak_hours = array_fill(0, 24, 0);
while($row = mysqli_fetch_assoc($peak_result)) {
    $peak_hours[$row['hour']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activity Logs & Engagement - Play2Review Admin</title>
    <?php include('includes/header.php'); ?>
    <style>.btn-primary {
    color: #fff;
    background-color: #0A5F38;
    border-color: #0A5F38;
    box-shadow: none;
}
        .activity-log-item {
            border-left: 4px solid #0A5F38;
            margin-bottom: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .activity-log-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .log-type-badge {
            font-size: 0.7rem;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
        }
        
        .badge-login { background: #0A5F38; color: white; }
        .badge-game { background: #1E7D4E; color: white; }
        .badge-completion { background: #28a745; color: white; }
        .badge-registration { background: #17a2b8; color: white; }
        .badge-other { background: #6c757d; color: white; }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
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
            color: #0A5F38;
        }
        
        .stats-label {
            font-size: 1rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .peak-hour-bar {
            height: 30px;
            background: linear-gradient(90deg, #0A5F38, #1E7D4E);
            border-radius: 5px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }
        
        .peak-hour-bar:hover {
            transform: scale(1.02);
        }
        
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
    <!-- Chart.js for graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <h1 class="m-0">Activity Logs & Engagement Tracking</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- Date Filter -->
                <div class="filter-section">
                    <form method="GET" class="row">
                        <div class="col-md-4">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">Apply Filter</button>
                        </div>
                    </form>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $total_activities; ?></div>
                            <div class="stats-label">Total Activities</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $activity_types['logins']; ?></div>
                            <div class="stats-label">User Logins</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $activity_types['game_starts']; ?></div>
                            <div class="stats-label">Game Sessions</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo $activity_types['completions']; ?></div>
                            <div class="stats-label">Level Completions</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Left Column: Charts -->
                    <div class="col-lg-8">
                        <!-- Daily Activity Chart -->
                        <div class="chart-container">
                            <h5>Daily Activity Trend</h5>
                            <canvas id="dailyActivityChart" height="100"></canvas>
                        </div>

                        <!-- Peak Hours Chart -->
                        <div class="chart-container">
                            <h5>Peak Activity Hours</h5>
                            <div class="row">
                                <?php for($hour = 0; $hour < 24; $hour++): 
                                    $count = $peak_hours[$hour];
                                    $percentage = $count > 0 ? ($count / max($peak_hours)) * 100 : 0;
                                    $hour_label = sprintf("%02d:00", $hour);
                                ?>
                                <div class="col-md-4 mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small><?php echo $hour_label; ?></small>
                                        <small><?php echo $count; ?> activities</small>
                                    </div>
                                    <div class="peak-hour-bar" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Active Users -->
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <h5>Most Active Users</h5>
                            <?php if(empty($active_users)): ?>
                                <p class="text-muted text-center">No active users in this period</p>
                            <?php else: ?>
                                <?php foreach($active_users as $index => $user): ?>
                                <div class="d-flex align-items-center mb-3 p-2 border rounded">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user['player_name'], 0, 1)); ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <strong><?php echo htmlspecialchars($user['player_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($user['username']); ?></small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-primary"><?php echo $user['activity_count']; ?> activities</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Activity Type Distribution -->
                        <div class="chart-container">
                            <h5>Activity Type Distribution</h5>
                            <canvas id="activityTypeChart" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Activity Logs Table -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity Logs</h3>
                    </div>
                    <div class="card-body">
                        <?php if(empty($activity_logs)): ?>
                            <p class="text-center text-muted">No activity logs found for the selected period.</p>
                        <?php else: ?>
                            <?php foreach($activity_logs as $log): ?>
                            <div class="activity-log-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($log['player_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($log['player_name']); ?></strong>
                                                <small class="text-muted ml-2">(<?php echo htmlspecialchars($log['username']); ?>)</small>
                                            </div>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($log['activity_description']); ?></p>
                                        <div class="d-flex align-items-center">
                                            <span class="log-type-badge badge-<?php echo getActivityBadgeClass($log['activity_description']); ?>">
                                                <?php echo getActivityType($log['activity_description']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Daily Activity Chart
    const dailyCtx = document.getElementById('dailyActivityChart').getContext('2d');
    const dailyChart = new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: [<?php echo implode(',', array_map(function($date) { return "'$date'"; }, array_keys($daily_activities))); ?>],
            datasets: [{
                label: 'Activities',
                data: [<?php echo implode(',', array_values($daily_activities)); ?>],
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Activities'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });

    // Activity Type Chart
    const typeCtx = document.getElementById('activityTypeChart').getContext('2d');
    const typeChart = new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Logins', 'Game Starts', 'Completions', 'Registrations'],
            datasets: [{
                data: [
                    <?php echo $activity_types['logins']; ?>,
                    <?php echo $activity_types['game_starts']; ?>,
                    <?php echo $activity_types['completions']; ?>,
                    <?php echo $activity_types['registrations']; ?>
                ],
                backgroundColor: [
                    '#28a745',
                    '#007bff',
                    '#17a2b8',
                    '#6f42c1'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

<?php
function getActivityBadgeClass($description) {
    if (stripos($description, 'logged in') !== false) return 'login';
    if (stripos($description, 'Started Playing') !== false) return 'game';
    if (stripos($description, 'completed') !== false) return 'completion';
    if (stripos($description, 'registered') !== false) return 'registration';
    return 'other';
}

function getActivityType($description) {
    if (stripos($description, 'logged in') !== false) return 'Login';
    if (stripos($description, 'Started Playing') !== false) return 'Game Start';
    if (stripos($description, 'completed') !== false) return 'Completion';
    if (stripos($description, 'registered') !== false) return 'Registration';
    return 'Other';
}
?>
</body>
</html>