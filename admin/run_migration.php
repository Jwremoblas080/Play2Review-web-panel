<?php
/**
 * Simple Migration Runner
 * Run this file once in your browser: http://localhost/play2review/admin/run_migration.php
 */

require_once('../configurations/configurations.php');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Migration - Add Category Column</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: blue; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Database Migration Runner</h1>
    <p>Adding <code>category</code> column to <code>quizes</code> table...</p>
";

$errors = [];
$success = [];

// Step 1: Add category column
echo "<h2>Step 1: Adding category column</h2>";
$sql1 = "ALTER TABLE `quizes` ADD COLUMN `category` VARCHAR(255) NULL AFTER `subject_name`";
if (mysqli_query($con, $sql1)) {
    $success[] = "✅ Category column added successfully";
    echo "<div class='success'>✅ Category column added successfully</div>";
} else {
    $error = mysqli_error($con);
    if (strpos($error, 'Duplicate column name') !== false) {
        $success[] = "ℹ️ Category column already exists (skipped)";
        echo "<div class='info'>ℹ️ Category column already exists (skipped)</div>";
    } else {
        $errors[] = "❌ Error adding category column: " . $error;
        echo "<div class='error'>❌ Error: " . $error . "</div>";
    }
}

// Step 2: Add index on category
echo "<h2>Step 2: Adding index on category</h2>";
$sql2 = "ALTER TABLE `quizes` ADD INDEX `idx_category` (`category`)";
if (mysqli_query($con, $sql2)) {
    $success[] = "✅ Index on category added successfully";
    echo "<div class='success'>✅ Index on category added successfully</div>";
} else {
    $error = mysqli_error($con);
    if (strpos($error, 'Duplicate key name') !== false) {
        $success[] = "ℹ️ Index on category already exists (skipped)";
        echo "<div class='info'>ℹ️ Index on category already exists (skipped)</div>";
    } else {
        $errors[] = "❌ Error adding index: " . $error;
        echo "<div class='error'>❌ Error: " . $error . "</div>";
    }
}

// Step 3: Add composite index
echo "<h2>Step 3: Adding composite index (subject + category)</h2>";
$sql3 = "ALTER TABLE `quizes` ADD INDEX `idx_subject_category` (`subject_name`, `category`)";
if (mysqli_query($con, $sql3)) {
    $success[] = "✅ Composite index added successfully";
    echo "<div class='success'>✅ Composite index added successfully</div>";
} else {
    $error = mysqli_error($con);
    if (strpos($error, 'Duplicate key name') !== false) {
        $success[] = "ℹ️ Composite index already exists (skipped)";
        echo "<div class='info'>ℹ️ Composite index already exists (skipped)</div>";
    } else {
        $errors[] = "❌ Error adding composite index: " . $error;
        echo "<div class='error'>❌ Error: " . $error . "</div>";
    }
}

// Step 4: Update existing records
echo "<h2>Step 4: Updating existing records</h2>";
$sql4 = "UPDATE `quizes` SET `category` = NULL WHERE `category` IS NULL OR `category` = ''";
if (mysqli_query($con, $sql4)) {
    $affected = mysqli_affected_rows($con);
    $success[] = "✅ Updated $affected existing records";
    echo "<div class='success'>✅ Updated $affected existing records</div>";
} else {
    $errors[] = "❌ Error updating records: " . mysqli_error($con);
    echo "<div class='error'>❌ Error: " . mysqli_error($con) . "</div>";
}

// Step 5: Verify the changes
echo "<h2>Step 5: Verifying changes</h2>";
$sql5 = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
         FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_SCHEMA = 'play2review_db' 
         AND TABLE_NAME = 'quizes' 
         AND COLUMN_NAME = 'category'";

$result = mysqli_query($con, $sql5);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo "<div class='success'>✅ Category column verified!</div>";
    echo "<pre>";
    echo "Column Name: " . $row['COLUMN_NAME'] . "\n";
    echo "Data Type: " . $row['DATA_TYPE'] . "\n";
    echo "Nullable: " . $row['IS_NULLABLE'] . "\n";
    echo "Default: " . ($row['COLUMN_DEFAULT'] ?? 'NULL') . "\n";
    echo "</pre>";
    $success[] = "✅ Category column verified in database";
} else {
    $errors[] = "❌ Could not verify category column";
    echo "<div class='error'>❌ Could not verify category column</div>";
}

// Summary
echo "<hr>";
echo "<h2>📊 Migration Summary</h2>";

if (empty($errors)) {
    echo "<div class='success'>";
    echo "<h3>✅ Migration Completed Successfully!</h3>";
    echo "<ul>";
    foreach ($success as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Go to <a href='manage-quizes.php'>Manage Quizzes</a></li>";
    echo "<li>Click 'Add New Question'</li>";
    echo "<li>Select a subject and verify category dropdown appears</li>";
    echo "</ol>";
    echo "<p><strong>⚠️ Important:</strong> You can delete this file (run_migration.php) after successful migration.</p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>❌ Migration Completed with Errors</h3>";
    echo "<p><strong>Errors:</strong></p>";
    echo "<ul>";
    foreach ($errors as $err) {
        echo "<li>$err</li>";
    }
    echo "</ul>";
    echo "<p><strong>Successes:</strong></p>";
    echo "<ul>";
    foreach ($success as $msg) {
        echo "<li>$msg</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "</body></html>";

mysqli_close($con);
?>
