<?php
/**
 * Database Migration Script: Convert Category Labels to Keys
 * 
 * This script converts existing category LABELS in the database to KEYS
 * Run this ONCE to fix existing data
 * 
 * BACKUP YOUR DATABASE BEFORE RUNNING THIS SCRIPT!
 */

require_once('category-config.php');
require_once('../configurations/configurations.php');

// Set execution time limit for large databases
set_time_limit(300);

echo "<h1>Category Label to Key Migration</h1>";
echo "<p>This will convert all category labels to keys in the database.</p>";

// Safety check - require confirmation
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
    echo "<p style='color: red;'><strong>WARNING: This will modify your database!</strong></p>";
    echo "<p>Please backup your database before proceeding.</p>";
    echo "<p><a href='?confirm=yes'>Click here to confirm and run migration</a></p>";
    exit;
}

echo "<h2>Starting Migration...</h2>";

// ================= MIGRATION MAPPING =================

// Build reverse mapping: LABEL → KEY for each subject
$labelToKeyMap = [];

foreach ($CATEGORY_CONFIG as $subject => $categories) {
    $labelToKeyMap[$subject] = [];
    foreach ($categories as $key => $label) {
        // Store lowercase label for case-insensitive matching
        $labelToKeyMap[$subject][strtolower($label)] = $key;
    }
}

// ================= MIGRATE QUIZES TABLE =================

echo "<h3>Migrating 'quizes' table...</h3>";

try {
    // Get all records with category labels
    $sql = "SELECT id, subject_name, category FROM quizes";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updated = 0;
    $skipped = 0;
    $errors = 0;
    
    foreach ($records as $record) {
        $id = $record['id'];
        $subject = strtolower($record['subject_name']);
        $currentCategory = $record['category'];
        
        // Check if it's already a key (lowercase, no spaces)
        if (isValidCategory($subject, $currentCategory)) {
            $skipped++;
            continue; // Already a key, skip
        }
        
        // Try to find the key for this label
        $categoryLower = strtolower(trim($currentCategory));
        
        if (isset($labelToKeyMap[$subject][$categoryLower])) {
            $newKey = $labelToKeyMap[$subject][$categoryLower];
            
            // Update the record
            $updateSql = "UPDATE quizes SET category = :new_key WHERE id = :id";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindParam(':new_key', $newKey);
            $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $updateStmt->execute();
            
            echo "✅ Updated quiz #{$id}: '{$currentCategory}' → '{$newKey}'<br>";
            $updated++;
        } else {
            echo "❌ Could not find key for quiz #{$id}: '{$currentCategory}' (subject: {$subject})<br>";
            $errors++;
        }
    }
    
    echo "<p><strong>Quizes table migration complete:</strong></p>";
    echo "<ul>";
    echo "<li>Updated: {$updated}</li>";
    echo "<li>Skipped (already keys): {$skipped}</li>";
    echo "<li>Errors: {$errors}</li>";
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error migrating quizes table: " . $e->getMessage() . "</p>";
}

// ================= MIGRATE QUIZ_PROGRESS TABLE (if exists) =================

echo "<h3>Migrating 'quiz_progress' table (if exists)...</h3>";

try {
    // Check if table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'quiz_progress'");
    
    if ($checkTable->rowCount() > 0) {
        $sql = "SELECT id, subject, category FROM quiz_progress";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($records as $record) {
            $id = $record['id'];
            $subject = strtolower($record['subject']);
            $currentCategory = $record['category'];
            
            // Check if it's already a key
            if (isValidCategory($subject, $currentCategory)) {
                $skipped++;
                continue;
            }
            
            // Try to find the key for this label
            $categoryLower = strtolower(trim($currentCategory));
            
            if (isset($labelToKeyMap[$subject][$categoryLower])) {
                $newKey = $labelToKeyMap[$subject][$categoryLower];
                
                // Update the record
                $updateSql = "UPDATE quiz_progress SET category = :new_key WHERE id = :id";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bindParam(':new_key', $newKey);
                $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
                $updateStmt->execute();
                
                echo "✅ Updated progress #{$id}: '{$currentCategory}' → '{$newKey}'<br>";
                $updated++;
            } else {
                echo "❌ Could not find key for progress #{$id}: '{$currentCategory}' (subject: {$subject})<br>";
                $errors++;
            }
        }
        
        echo "<p><strong>Quiz progress table migration complete:</strong></p>";
        echo "<ul>";
        echo "<li>Updated: {$updated}</li>";
        echo "<li>Skipped (already keys): {$skipped}</li>";
        echo "<li>Errors: {$errors}</li>";
        echo "</ul>";
        
    } else {
        echo "<p>Table 'quiz_progress' does not exist. Skipping.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error migrating quiz_progress table: " . $e->getMessage() . "</p>";
}

// ================= MIGRATE USER_STATS TABLE (if exists) =================

echo "<h3>Migrating 'user_stats' table (if exists)...</h3>";

try {
    $checkTable = $conn->query("SHOW TABLES LIKE 'user_stats'");
    
    if ($checkTable->rowCount() > 0) {
        // Check if category column exists
        $checkColumn = $conn->query("SHOW COLUMNS FROM user_stats LIKE 'category'");
        
        if ($checkColumn->rowCount() > 0) {
            $sql = "SELECT id, subject, category FROM user_stats WHERE category IS NOT NULL";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $updated = 0;
            $skipped = 0;
            $errors = 0;
            
            foreach ($records as $record) {
                $id = $record['id'];
                $subject = strtolower($record['subject']);
                $currentCategory = $record['category'];
                
                if (isValidCategory($subject, $currentCategory)) {
                    $skipped++;
                    continue;
                }
                
                $categoryLower = strtolower(trim($currentCategory));
                
                if (isset($labelToKeyMap[$subject][$categoryLower])) {
                    $newKey = $labelToKeyMap[$subject][$categoryLower];
                    
                    $updateSql = "UPDATE user_stats SET category = :new_key WHERE id = :id";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bindParam(':new_key', $newKey);
                    $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $updateStmt->execute();
                    
                    echo "✅ Updated stats #{$id}: '{$currentCategory}' → '{$newKey}'<br>";
                    $updated++;
                } else {
                    echo "❌ Could not find key for stats #{$id}: '{$currentCategory}' (subject: {$subject})<br>";
                    $errors++;
                }
            }
            
            echo "<p><strong>User stats table migration complete:</strong></p>";
            echo "<ul>";
            echo "<li>Updated: {$updated}</li>";
            echo "<li>Skipped (already keys): {$skipped}</li>";
            echo "<li>Errors: {$errors}</li>";
            echo "</ul>";
        } else {
            echo "<p>Column 'category' does not exist in user_stats. Skipping.</p>";
        }
    } else {
        echo "<p>Table 'user_stats' does not exist. Skipping.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error migrating user_stats table: " . $e->getMessage() . "</p>";
}

// ================= VERIFICATION =================

echo "<h2>Verification</h2>";
echo "<p>Checking if all categories are now valid keys...</p>";

try {
    $sql = "SELECT DISTINCT subject_name, category FROM quizes ORDER BY subject_name, category";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $allValid = true;
    
    foreach ($categories as $cat) {
        $subject = strtolower($cat['subject_name']);
        $category = $cat['category'];
        
        if (!isValidCategory($subject, $category)) {
            echo "❌ Invalid category found: {$subject} - {$category}<br>";
            $allValid = false;
        }
    }
    
    if ($allValid) {
        echo "<p style='color: green;'><strong>✅ All categories are now valid keys!</strong></p>";
    } else {
        echo "<p style='color: orange;'><strong>⚠️ Some invalid categories remain. Please review above.</strong></p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error during verification: " . $e->getMessage() . "</p>";
}

echo "<h2>Migration Complete!</h2>";
echo "<p>You can now safely use the new category system.</p>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ul>";
echo "<li>Test your Unity app to ensure categories load correctly</li>";
echo "<li>Verify quiz questions are fetched properly</li>";
echo "<li>Check that progress tracking works</li>";
echo "</ul>";
?>
