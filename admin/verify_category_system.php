<?php
/**
 * Category System Verification Script
 * Run this to check if everything is set up correctly
 * 
 * Access: http://localhost/play2review/admin/verify_category_system.php
 */

require_once('../configurations/configurations.php');

// Set content type
header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Category System Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .check-section {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .check-section h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #0A5F38;
            padding-bottom: 10px;
        }
        .status {
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 4px;
            font-weight: bold;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .status.warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th {
            background: #0A5F38;
            color: white;
            padding: 10px;
            text-align: left;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        table tr:hover {
            background: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .badge.success {
            background: #28a745;
            color: white;
        }
        .badge.error {
            background: #dc3545;
            color: white;
        }
        .code {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🔍 Category System Verification</h1>
    <p>This script checks if your category system is properly configured.</p>

    <?php
    $allChecks = [];
    
    // ========== CHECK 1: Database Connection ==========
    echo '<div class="check-section">';
    echo '<h2>1. Database Connection</h2>';
    
    if (isset($conn)) {
        echo '<div class="status success">✓ Database connection established (PDO)</div>';
        $allChecks['db_connection'] = true;
    } elseif (isset($con)) {
        echo '<div class="status warning">⚠ Database connection exists but using mysqli (should use PDO)</div>';
        $conn = $con; // Use mysqli for checks
        $allChecks['db_connection'] = true;
    } else {
        echo '<div class="status error">✗ No database connection found</div>';
        $allChecks['db_connection'] = false;
    }
    
    echo '</div>';
    
    // ========== CHECK 2: Category Column Exists ==========
    echo '<div class="check-section">';
    echo '<h2>2. Database Structure</h2>';
    
    try {
        if (isset($conn) && $conn instanceof PDO) {
            $stmt = $conn->query("SHOW COLUMNS FROM quizes LIKE 'category'");
            $categoryColumn = $stmt->fetch();
        } else {
            $result = mysqli_query($conn, "SHOW COLUMNS FROM quizes LIKE 'category'");
            $categoryColumn = mysqli_fetch_assoc($result);
        }
        
        if ($categoryColumn) {
            echo '<div class="status success">✓ Category column exists in quizes table</div>';
            echo '<div class="info">';
            echo '<strong>Column Details:</strong><br>';
            echo 'Type: ' . $categoryColumn['Type'] . '<br>';
            echo 'Null: ' . $categoryColumn['Null'] . '<br>';
            echo 'Default: ' . ($categoryColumn['Default'] ?? 'NULL');
            echo '</div>';
            $allChecks['category_column'] = true;
        } else {
            echo '<div class="status error">✗ Category column does NOT exist in quizes table</div>';
            echo '<div class="info">';
            echo '<strong>Fix:</strong> Run this SQL in phpMyAdmin:<br>';
            echo '<div class="code">ALTER TABLE `quizes` ADD COLUMN `category` VARCHAR(255) NULL AFTER `subject_name`;</div>';
            echo '</div>';
            $allChecks['category_column'] = false;
        }
    } catch (Exception $e) {
        echo '<div class="status error">✗ Error checking database structure: ' . $e->getMessage() . '</div>';
        $allChecks['category_column'] = false;
    }
    
    echo '</div>';
    
    // ========== CHECK 3: Questions with Categories ==========
    echo '<div class="check-section">';
    echo '<h2>3. Questions with Categories</h2>';
    
    try {
        if (isset($conn) && $conn instanceof PDO) {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM quizes");
            $totalQuestions = $stmt->fetch()['total'];
            
            $stmt = $conn->query("SELECT COUNT(*) as with_category FROM quizes WHERE category IS NOT NULL AND category != ''");
            $questionsWithCategory = $stmt->fetch()['with_category'];
        } else {
            $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM quizes");
            $totalQuestions = mysqli_fetch_assoc($result)['total'];
            
            $result = mysqli_query($conn, "SELECT COUNT(*) as with_category FROM quizes WHERE category IS NOT NULL AND category != ''");
            $questionsWithCategory = mysqli_fetch_assoc($result)['with_category'];
        }
        
        echo '<div class="info">';
        echo '<strong>Total Questions:</strong> ' . $totalQuestions . '<br>';
        echo '<strong>Questions with Category:</strong> ' . $questionsWithCategory . '<br>';
        echo '<strong>Questions without Category:</strong> ' . ($totalQuestions - $questionsWithCategory);
        echo '</div>';
        
        if ($questionsWithCategory > 0) {
            echo '<div class="status success">✓ Found ' . $questionsWithCategory . ' questions with categories</div>';
            $allChecks['questions_with_category'] = true;
        } else {
            echo '<div class="status warning">⚠ No questions have categories assigned yet</div>';
            echo '<div class="info">';
            echo '<strong>Fix:</strong> Add sample questions using the SQL in COMPLETE_CATEGORY_FIX.md';
            echo '</div>';
            $allChecks['questions_with_category'] = false;
        }
        
        // Show breakdown by subject
        if ($questionsWithCategory > 0) {
            echo '<h3>Questions by Subject & Category:</h3>';
            echo '<table>';
            echo '<tr><th>Subject</th><th>Category</th><th>Count</th></tr>';
            
            if (isset($conn) && $conn instanceof PDO) {
                $stmt = $conn->query("SELECT subject_name, category, COUNT(*) as count FROM quizes WHERE category IS NOT NULL AND category != '' GROUP BY subject_name, category ORDER BY subject_name, category");
                $breakdown = $stmt->fetchAll();
            } else {
                $result = mysqli_query($conn, "SELECT subject_name, category, COUNT(*) as count FROM quizes WHERE category IS NOT NULL AND category != '' GROUP BY subject_name, category ORDER BY subject_name, category");
                $breakdown = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $breakdown[] = $row;
                }
            }
            
            foreach ($breakdown as $row) {
                echo '<tr>';
                echo '<td><strong>' . ucfirst($row['subject_name']) . '</strong></td>';
                echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                echo '<td><span class="badge success">' . $row['count'] . '</span></td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        
    } catch (Exception $e) {
        echo '<div class="status error">✗ Error checking questions: ' . $e->getMessage() . '</div>';
        $allChecks['questions_with_category'] = false;
    }
    
    echo '</div>';
    
    // ========== CHECK 4: Category Config File ==========
    echo '<div class="check-section">';
    echo '<h2>4. Category Configuration File</h2>';
    
    if (file_exists('category-config.php')) {
        echo '<div class="status success">✓ category-config.php exists</div>';
        
        require_once('category-config.php');
        
        if (isset($CATEGORY_CONFIG)) {
            echo '<div class="info">';
            echo '<strong>Configured Subjects:</strong> ' . count($CATEGORY_CONFIG) . '<br>';
            
            $totalCategories = 0;
            foreach ($CATEGORY_CONFIG as $subject => $categories) {
                echo '<br><strong>' . ucfirst($subject) . ':</strong> ' . count($categories) . ' categories';
                $totalCategories += count($categories);
            }
            echo '<br><br><strong>Total Categories:</strong> ' . $totalCategories;
            echo '</div>';
            $allChecks['category_config'] = true;
        } else {
            echo '<div class="status error">✗ $CATEGORY_CONFIG variable not found in category-config.php</div>';
            $allChecks['category_config'] = false;
        }
    } else {
        echo '<div class="status error">✗ category-config.php file not found</div>';
        $allChecks['category_config'] = false;
    }
    
    echo '</div>';
    
    // ========== CHECK 5: JavaScript File ==========
    echo '<div class="check-section">';
    echo '<h2>5. JavaScript Files</h2>';
    
    if (file_exists('category-management.js')) {
        echo '<div class="status success">✓ category-management.js exists</div>';
        $fileSize = filesize('category-management.js');
        echo '<div class="info">File size: ' . number_format($fileSize) . ' bytes</div>';
        $allChecks['javascript_file'] = true;
    } else {
        echo '<div class="status warning">⚠ category-management.js not found (may be inline in manage-quizes.php)</div>';
        $allChecks['javascript_file'] = false;
    }
    
    echo '</div>';
    
    // ========== CHECK 6: PHP Endpoint ==========
    echo '<div class="check-section">';
    echo '<h2>6. PHP API Endpoint</h2>';
    
    if (file_exists('../get_quiz_questions.php')) {
        echo '<div class="status success">✓ get_quiz_questions.php exists</div>';
        
        // Check if it supports category filtering
        $content = file_get_contents('../get_quiz_questions.php');
        if (strpos($content, 'category') !== false) {
            echo '<div class="status success">✓ Endpoint supports category filtering</div>';
            $allChecks['php_endpoint'] = true;
        } else {
            echo '<div class="status error">✗ Endpoint does NOT support category filtering</div>';
            $allChecks['php_endpoint'] = false;
        }
    } else {
        echo '<div class="status error">✗ get_quiz_questions.php not found</div>';
        $allChecks['php_endpoint'] = false;
    }
    
    echo '</div>';
    
    // ========== FINAL SUMMARY ==========
    echo '<div class="check-section">';
    echo '<h2>📊 Summary</h2>';
    
    $passedChecks = array_filter($allChecks);
    $totalChecks = count($allChecks);
    $passedCount = count($passedChecks);
    $percentage = round(($passedCount / $totalChecks) * 100);
    
    echo '<div class="info">';
    echo '<strong>Checks Passed:</strong> ' . $passedCount . ' / ' . $totalChecks . ' (' . $percentage . '%)<br><br>';
    
    if ($percentage == 100) {
        echo '<div class="status success">';
        echo '🎉 <strong>ALL CHECKS PASSED!</strong><br>';
        echo 'Your category system is fully configured and ready to use.';
        echo '</div>';
    } elseif ($percentage >= 75) {
        echo '<div class="status warning">';
        echo '⚠ <strong>MOSTLY CONFIGURED</strong><br>';
        echo 'A few minor issues need attention. Review the failed checks above.';
        echo '</div>';
    } else {
        echo '<div class="status error">';
        echo '✗ <strong>CONFIGURATION INCOMPLETE</strong><br>';
        echo 'Several issues need to be fixed. Follow the COMPLETE_CATEGORY_FIX.md guide.';
        echo '</div>';
    }
    
    echo '</div>';
    
    echo '<h3>Next Steps:</h3>';
    echo '<ol>';
    if (!$allChecks['category_column']) {
        echo '<li>Add category column to database (see Check #2)</li>';
    }
    if (!$allChecks['questions_with_category']) {
        echo '<li>Add sample questions with categories (see COMPLETE_CATEGORY_FIX.md)</li>';
    }
    if (!$allChecks['category_config']) {
        echo '<li>Fix category-config.php file</li>';
    }
    if (!$allChecks['php_endpoint']) {
        echo '<li>Update get_quiz_questions.php to support category filtering</li>';
    }
    if ($percentage == 100) {
        echo '<li>Test in admin panel: <a href="manage-quizes.php">Manage Quizes</a></li>';
        echo '<li>Test in Unity game</li>';
    }
    echo '</ol>';
    
    echo '</div>';
    ?>
    
    <div class="check-section">
        <h2>📚 Documentation</h2>
        <ul>
            <li><a href="../COMPLETE_CATEGORY_FIX.md">COMPLETE_CATEGORY_FIX.md</a> - Complete implementation guide</li>
            <li><a href="CATEGORY_FIX_IMPLEMENTATION.md">CATEGORY_FIX_IMPLEMENTATION.md</a> - Technical details</li>
            <li><a href="manage-quizes.php">Manage Quizes</a> - Admin panel</li>
        </ul>
    </div>
    
    <div class="check-section">
        <p style="text-align: center; color: #666;">
            <small>Category System Verification Script v1.0 | <?php echo date('Y-m-d H:i:s'); ?></small>
        </p>
    </div>
</body>
</html>
