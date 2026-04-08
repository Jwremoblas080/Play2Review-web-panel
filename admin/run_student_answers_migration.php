<?php
/**
 * Browser-based migration script for creating student_answers table
 * Run this once to set up category-level progress tracking
 * 
 * Access: http://localhost/play2review/admin/run_student_answers_migration.php
 */

require_once('../configurations/configurations.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    die('Access denied. Admin privileges required.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Answers Table Migration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0A5F38 0%, #1E7D4E 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .migration-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #0A5F38;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="migration-card p-5">
        <h2 class="text-center mb-4">📊 Student Answers Table Migration</h2>
        <p class="text-center text-muted mb-4">This will create the <code>student_answers</code> table for category-level progress tracking</p>
        
        <?php
        // Read SQL file
        $sql_file = 'create_student_answers_table.sql';
        
        if (!file_exists($sql_file)) {
            echo '<div class="alert alert-danger">';
            echo '<strong>Error:</strong> SQL file not found: ' . $sql_file;
            echo '</div>';
            exit;
        }
        
        $sql = file_get_contents($sql_file);
        
        // Execute migration
        echo '<h5>Migration Progress:</h5>';
        echo '<div class="border rounded p-3 mb-3">';
        
        try {
            // Check if table already exists
            $check_query = "SHOW TABLES LIKE 'student_answers'";
            $result = mysqli_query($con, $check_query);
            
            if (mysqli_num_rows($result) > 0) {
                echo '<p class="warning">⚠️ Table <code>student_answers</code> already exists!</p>';
                echo '<p>Migration skipped. If you want to recreate the table, drop it first:</p>';
                echo '<pre>DROP TABLE student_answers;</pre>';
            } else {
                // Execute the SQL
                if (mysqli_multi_query($con, $sql)) {
                    do {
                        // Store first result set
                        if ($result = mysqli_store_result($con)) {
                            mysqli_free_result($result);
                        }
                    } while (mysqli_next_result($con));
                    
                    echo '<p class="success">✅ Table <code>student_answers</code> created successfully!</p>';
                    
                    // Verify table structure
                    $verify_query = "DESCRIBE student_answers";
                    $verify_result = mysqli_query($con, $verify_query);
                    
                    if ($verify_result) {
                        echo '<p class="success">✅ Table structure verified</p>';
                        echo '<details class="mt-3">';
                        echo '<summary style="cursor: pointer;">View Table Structure</summary>';
                        echo '<pre class="mt-2">';
                        while ($row = mysqli_fetch_assoc($verify_result)) {
                            echo sprintf("%-20s %-20s %s\n", 
                                $row['Field'], 
                                $row['Type'], 
                                $row['Key'] ? "[$row[Key]]" : ""
                            );
                        }
                        echo '</pre>';
                        echo '</details>';
                    }
                    
                    echo '<div class="alert alert-success mt-3">';
                    echo '<strong>Migration Complete!</strong><br>';
                    echo 'The system is now ready to track student progress by category.';
                    echo '</div>';
                    
                } else {
                    throw new Exception(mysqli_error($con));
                }
            }
            
        } catch (Exception $e) {
            echo '<p class="error">❌ Migration failed!</p>';
            echo '<div class="alert alert-danger">';
            echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        
        echo '</div>';
        
        // Next steps
        echo '<h5>Next Steps:</h5>';
        echo '<ol>';
        echo '<li>The <code>student_answers</code> table is now ready</li>';
        echo '<li>Update your Unity game to log answers to this table</li>';
        echo '<li>View category progress in <a href="manage-activities.php">Manage Activities</a></li>';
        echo '<li>Check individual student details for category breakdown</li>';
        echo '</ol>';
        
        echo '<div class="text-center mt-4">';
        echo '<a href="manage-activities.php" class="btn btn-success">Go to Student Activities</a>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
