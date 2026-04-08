<?php
require_once('../configurations/configurations.php');

// Check admin privileges
if (!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    header("Location: logout.php");
    exit();
}

// Pagination settings
$records_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Filter parameters
$filter_action = isset($_GET['action']) ? mysqli_real_escape_string($con, $_GET['action']) : '';
$filter_subject = isset($_GET['subject']) ? mysqli_real_escape_string($con, $_GET['subject']) : '';
$filter_user_type = isset($_GET['user_type']) ? mysqli_real_escape_string($con, $_GET['user_type']) : '';
$date_from = isset($_GET['date_from']) ? mysqli_real_escape_string($con, $_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? mysqli_real_escape_string($con, $_GET['date_to']) : '';

// Build WHERE clause
$where_conditions = [];
if ($filter_action) {
    $where_conditions[] = "action_type = '$filter_action'";
}
if ($filter_subject) {
    $where_conditions[] = "subject_name = '$filter_subject'";
}
if ($filter_user_type) {
    $where_conditions[] = "performed_by_type = '$filter_user_type'";
}
if ($date_from) {
    $where_conditions[] = "DATE(created_at) >= '$date_from'";
}
if ($date_to) {
    $where_conditions[] = "DATE(created_at) <= '$date_to'";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total records for pagination
$count_query = "SELECT COUNT(*) as total FROM quiz_audit_logs $where_clause";
$count_result = mysqli_query($con, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Fetch audit logs with pagination and join with users tables to get emails
$query = "SELECT qal.*, 
          CASE 
            WHEN qal.performed_by_type = 'admin' THEN (SELECT username FROM admin WHERE id = qal.performed_by_id)
            WHEN qal.performed_by_type = 'teacher' THEN (SELECT email FROM educators WHERE id = qal.performed_by_id)
          END as user_email
          FROM quiz_audit_logs qal
          $where_clause 
          ORDER BY qal.created_at DESC 
          LIMIT $offset, $records_per_page";

$logs_result = mysqli_query($con, $query);

// Subject names mapping
$subject_names = [
    'english' => 'English',
    'ap' => 'Araling Panlipunan',
    'filipino' => 'Filipino',
    'math' => 'Mathematics',
    'science' => 'Science'
];

// Action types for filter
$action_types = ['ADD', 'EDIT', 'DELETE', 'DELETE_ALL'];

// Get unique subjects for filter dropdown
$subjects_query = "SELECT DISTINCT subject_name FROM quiz_audit_logs ORDER BY subject_name";
$subjects_result = mysqli_query($con, $subjects_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quiz Activity Logs - Play2Review</title>

    <?php include('includes/header.php'); ?>
    <style>
        .filter-card {
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
        }
        
        .small-box {
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .table th {
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            color: white;
            font-size: 0.9rem;
            white-space: nowrap;
        }
        
        .badge-action {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .badge-add { background-color: #28a745; color: white; }
        .badge-edit { background-color: #ffc107; color: black; }
        .badge-delete { background-color: #dc3545; color: white; }
        .badge-delete_all { background-color: #dc3545; color: white; }
        
        .admin-badge {
            background: linear-gradient(135deg, #0A5F38 0%, #064527 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
        }
        
        .teacher-badge {
            background: linear-gradient(135deg, #1E7D4E 0%, #0F4F2E 100%);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
        }
        
        .pagination {
            justify-content: center;
        }
        
        .question-preview {
            max-width: 250px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .btn-view {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .btn-view:hover {
            background-color: #138496;
            color: white;
        }
        
        .modal-detail-label {
            font-weight: bold;
            color: #0A5F38;
            min-width: 120px;
        }
        
        .change-item {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 3px solid #0A5F38;
        }
        
        .old-value {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 5px;
            border-radius: 3px;
            text-decoration: line-through;
        }
        
        .new-value {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 5px;
            border-radius: 3px;
        }
        
        .user-detail {
            background: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
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
                            <h1 class="m-0">Quiz Activity Logs</h1>
                            <p class="text-muted">Track all quiz management actions</p>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="admin-dashboard.php">Home</a></li>
                                <li class="breadcrumb-item"><a href="manage-quizes.php">Manage Quizzes</a></li>
                                <li class="breadcrumb-item active">Activity Logs</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                <div class="container-fluid">
                    <!-- Filter Section -->
                    <div class="filter-card">
                        <form method="GET" class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Action</label>
                                    <select name="action" class="form-control form-control-sm">
                                        <option value="">All Actions</option>
                                        <?php foreach ($action_types as $type): ?>
                                            <option value="<?php echo $type; ?>" <?php echo $filter_action == $type ? 'selected' : ''; ?>>
                                                <?php echo $type; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Subject</label>
                                    <select name="subject" class="form-control form-control-sm">
                                        <option value="">All Subjects</option>
                                        <?php 
                                        mysqli_data_seek($subjects_result, 0);
                                        while($subject_row = mysqli_fetch_assoc($subjects_result)): 
                                            $subj = $subject_row['subject_name'];
                                        ?>
                                            <option value="<?php echo $subj; ?>" <?php echo $filter_subject == $subj ? 'selected' : ''; ?>>
                                                <?php echo $subject_names[$subj] ?? ucfirst($subj); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>User Type</label>
                                    <select name="user_type" class="form-control form-control-sm">
                                        <option value="">All</option>
                                        <option value="admin" <?php echo $filter_user_type == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="teacher" <?php echo $filter_user_type == 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date From</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo $date_from; ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date To</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo $date_to; ?>">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-light btn-sm w-100">
                                        <i class="fas fa-filter"></i> Apply Filters
                                    </button>
                                    <a href="audit_logs.php" class="btn btn-outline-light btn-sm w-100 mt-1">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Summary Stats -->
                    <div class="row">
                        <?php
                        $stats_query = "SELECT 
                            COUNT(*) as total,
                            SUM(CASE WHEN action_type = 'ADD' THEN 1 ELSE 0 END) as total_adds,
                            SUM(CASE WHEN action_type = 'EDIT' THEN 1 ELSE 0 END) as total_edits,
                            SUM(CASE WHEN action_type IN ('DELETE', 'DELETE_ALL') THEN 1 ELSE 0 END) as total_deletes
                            FROM quiz_audit_logs";
                        $stats_result = mysqli_query($con, $stats_query);
                        $stats = mysqli_fetch_assoc($stats_result);
                        ?>
                        <div class="col-md-3">
                            <div class="small-box">
                                <div class="inner">
                                    <h3><?php echo $stats['total'] ?? 0; ?></h3>
                                    <p>Total Activities</p>
                                </div>
                                <div class="icon"><i class="fas fa-history"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box">
                                <div class="inner">
                                    <h3><?php echo $stats['total_adds'] ?? 0; ?></h3>
                                    <p>Total Adds</p>
                                </div>
                                <div class="icon"><i class="fas fa-plus-circle"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box">
                                <div class="inner">
                                    <h3><?php echo $stats['total_edits'] ?? 0; ?></h3>
                                    <p>Total Edits</p>
                                </div>
                                <div class="icon"><i class="fas fa-edit"></i></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="small-box">
                                <div class="inner">
                                    <h3><?php echo $stats['total_deletes'] ?? 0; ?></h3>
                                    <p>Total Deletes</p>
                                </div>
                                <div class="icon"><i class="fas fa-trash"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Logs Table -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Activity Logs</h3>
                            <div class="card-tools">
                                <span class="badge badge-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="auditTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Date & Time</th>
                                            <th>Action</th>
                                            <th>User</th>
                                            <th>Subject</th>
                                            <th>Level</th>
                                            <th>Question</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($logs_result) > 0): ?>
                                            <?php while ($log = mysqli_fetch_assoc($logs_result)): ?>
                                                <tr>
                                                    <td>#<?php echo $log['id']; ?></td>
                                                    <td>
                                                        <i class="far fa-clock"></i>
                                                        <?php echo date('M d, Y', strtotime($log['created_at'])); ?>
                                                        <br>
                                                        <small><?php echo date('g:i A', strtotime($log['created_at'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge-action badge-<?php echo strtolower($log['action_type']); ?>">
                                                            <?php echo $log['action_type']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($log['performed_by_type'] == 'admin'): ?>
                                                            <span class="admin-badge">ADMIN</span>
                                                        <?php else: ?>
                                                            <span class="teacher-badge">TEACHER</span>
                                                        <?php endif; ?>
                                                        <br>
                                                        <small><?php echo htmlspecialchars($log['performed_by_name']); ?></small>
                                                    </td>
                                                    <td>
                                                        <?php echo $subject_names[$log['subject_name']] ?? ucfirst($log['subject_name']); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $log['quiz_level'] ? 'Level ' . $log['quiz_level'] : '-'; ?>
                                                    </td>
                                                    <td>
                                                        <div class="question-preview" title="<?php echo htmlspecialchars($log['question_text']); ?>">
                                                            <?php echo htmlspecialchars(substr($log['question_text'], 0, 50)) . (strlen($log['question_text']) > 50 ? '...' : ''); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button class="btn-view" data-bs-toggle="modal" data-bs-target="#viewLogModal<?php echo $log['id']; ?>">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                                    <h5>No Activity Logs Found</h5>
                                                    <p class="text-muted">Try adjusting your filters</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&action=<?php echo $filter_action; ?>&subject=<?php echo $filter_subject; ?>&user_type=<?php echo $filter_user_type; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Modals for each log -->
        <?php if (mysqli_num_rows($logs_result) > 0): ?>
            <?php mysqli_data_seek($logs_result, 0); ?>
            <?php while ($log = mysqli_fetch_assoc($logs_result)): ?>
                <div class="modal fade" id="viewLogModal<?php echo $log['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%); color: white;">
                                <h5 class="modal-title">
                                    <i class="fas fa-history"></i> Activity Details #<?php echo $log['id']; ?>
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <!-- User Information -->
                                <div class="user-detail">
                                    <h6 class="text-success mb-3"><i class="fas fa-user"></i> User Information</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><span class="modal-detail-label">User Type:</span> 
                                                <?php if ($log['performed_by_type'] == 'admin'): ?>
                                                    <span class="admin-badge">ADMIN</span>
                                                <?php else: ?>
                                                    <span class="teacher-badge">TEACHER</span>
                                                <?php endif; ?>
                                            </p>
                                            <p><span class="modal-detail-label">Name:</span> <?php echo htmlspecialchars($log['performed_by_name']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><span class="modal-detail-label">Email:</span> <?php echo htmlspecialchars($log['user_email'] ?? 'N/A'); ?></p>
                                            <p><span class="modal-detail-label">User ID:</span> #<?php echo $log['performed_by_id']; ?></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Basic Information -->
                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <p><span class="modal-detail-label">Action Type:</span> 
                                            <span class="badge-action badge-<?php echo strtolower($log['action_type']); ?>">
                                                <?php echo $log['action_type']; ?>
                                            </span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><span class="modal-detail-label">Subject:</span> 
                                            <?php echo $subject_names[$log['subject_name']] ?? ucfirst($log['subject_name']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><span class="modal-detail-label">Level:</span> 
                                            <?php echo $log['quiz_level'] ? 'Level ' . $log['quiz_level'] : 'All Levels'; ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <p><span class="modal-detail-label">Question ID:</span> 
                                            <?php echo $log['quiz_id'] ? '#' . $log['quiz_id'] : 'N/A'; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><span class="modal-detail-label">Date & Time:</span> 
                                            <?php echo date('F j, Y', strtotime($log['created_at'])); ?> at 
                                            <?php echo date('g:i:s A', strtotime($log['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Question Text -->
                                <div class="form-group">
                                    <label class="modal-detail-label">Question:</label>
                                    <div class="p-3 bg-light rounded">
                                        <?php echo htmlspecialchars($log['question_text']); ?>
                                    </div>
                                </div>

                                <!-- Changes Details -->
                                <?php if ($log['action_type'] == 'EDIT' && !empty($log['changed_fields'])): ?>
                                    <?php 
                                    $changed_fields = json_decode($log['changed_fields'], true);
                                    $old_values = json_decode($log['old_values'], true);
                                    $new_values = json_decode($log['new_values'], true);
                                    ?>
                                    <div class="mt-3">
                                        <label class="modal-detail-label">Changes Made:</label>
                                        <?php foreach ($changed_fields as $field): ?>
                                            <?php if (isset($old_values[$field]) || isset($new_values[$field])): ?>
                                                <div class="change-item">
                                                    <strong class="text-primary">
                                                        <?php 
                                                        $field_labels = [
                                                            'question' => 'Question',
                                                            'quiz_level' => 'Level',
                                                            'answer_a' => 'Option A',
                                                            'answer_b' => 'Option B',
                                                            'answer_c' => 'Option C',
                                                            'answer_d' => 'Option D',
                                                            'correct_answer_number' => 'Correct Answer'
                                                        ];
                                                        echo $field_labels[$field] ?? ucfirst($field);
                                                        ?>:
                                                    </strong>
                                                    <div class="mt-2">
                                                        <?php if (isset($old_values[$field])): ?>
                                                            <span class="old-value">
                                                                <?php 
                                                                if ($field == 'correct_answer_number') {
                                                                    echo 'Option ' . $old_values[$field];
                                                                } else {
                                                                    echo htmlspecialchars($old_values[$field]);
                                                                }
                                                                ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        <i class="fas fa-arrow-right mx-3 text-muted"></i>
                                                        <?php if (isset($new_values[$field])): ?>
                                                            <span class="new-value">
                                                                <?php 
                                                                if ($field == 'correct_answer_number') {
                                                                    echo 'Option ' . $new_values[$field];
                                                                } else {
                                                                    echo htmlspecialchars($new_values[$field]);
                                                                }
                                                                ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($log['action_type'] == 'ADD' && !empty($log['new_values'])): ?>
                                    <?php $new_values = json_decode($log['new_values'], true); ?>
                                    <div class="mt-3">
                                        <label class="modal-detail-label">Added Question Details:</label>
                                        <div class="change-item">
                                            <div class="row">
                                                <div class="col-md-6"><strong>A:</strong> <?php echo htmlspecialchars($new_values['answer_a'] ?? ''); ?></div>
                                                <div class="col-md-6"><strong>B:</strong> <?php echo htmlspecialchars($new_values['answer_b'] ?? ''); ?></div>
                                                <div class="col-md-6"><strong>C:</strong> <?php echo htmlspecialchars($new_values['answer_c'] ?? ''); ?></div>
                                                <div class="col-md-6"><strong>D:</strong> <?php echo htmlspecialchars($new_values['answer_d'] ?? ''); ?></div>
                                                <div class="col-12 mt-2"><strong>Correct Answer:</strong> Option <?php echo $new_values['correct_answer_number'] ?? ''; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($log['action_type'] == 'DELETE' && !empty($log['old_values'])): ?>
                                    <?php $old_values = json_decode($log['old_values'], true); ?>
                                    <div class="mt-3">
                                        <label class="modal-detail-label text-danger">Deleted Question Details:</label>
                                        <div class="change-item">
                                            <div class="row">
                                                <div class="col-md-6"><strong>A:</strong> <?php echo htmlspecialchars($old_values['answer_a'] ?? ''); ?></div>
                                                <div class="col-md-6"><strong>B:</strong> <?php echo htmlspecialchars($old_values['answer_b'] ?? ''); ?></div>
                                                <div class="col-md-6"><strong>C:</strong> <?php echo htmlspecialchars($old_values['answer_c'] ?? ''); ?></div>
                                                <div class="col-md-6"><strong>D:</strong> <?php echo htmlspecialchars($old_values['answer_d'] ?? ''); ?></div>
                                                <div class="col-12 mt-2"><strong>Correct Answer:</strong> Option <?php echo $old_values['correct_answer_number'] ?? ''; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($log['action_type'] == 'DELETE_ALL'): ?>
                                    <div class="mt-3">
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Bulk Delete Action</strong>
                                            <p class="mb-0 mt-2">
                                                Deleted all questions for <?php echo $subject_names[$log['subject_name']] ?? $log['subject_name']; ?>
                                                <?php if ($log['quiz_level']): ?> 
                                                    (Level <?php echo $log['quiz_level']; ?>)
                                                <?php else: ?>
                                                    (All Levels)
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- IP Address -->
                                <div class="mt-3 text-muted small">
                                    <i class="fas fa-laptop"></i> IP Address: <?php echo $log['ip_address']; ?>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <?php include('includes/footer.php'); ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('#auditTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 15,
        "order": [[0, 'desc']]
    });
});
</script>
</body>
</html>