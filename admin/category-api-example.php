<?php
/**
 * Complete Category System API Example
 * 
 * This demonstrates the proper KEY vs LABEL separation
 * for a production-ready quiz system
 */

require_once('category-config.php');

// ================= EXAMPLE 1: Get Categories for Dropdown =================

/**
 * API: Get categories for a subject (returns KEY => LABEL pairs)
 * Frontend will display LABELS in dropdown but track KEYS internally
 */
function apiGetCategories() {
    header('Content-Type: application/json');
    
    $subject = $_GET['subject'] ?? '';
    
    if (empty($subject)) {
        echo json_encode([
            'success' => false,
            'message' => 'Subject is required'
        ]);
        return;
    }
    
    // Get KEY => LABEL mapping
    $categories = getCategoriesBySubject($subject);
    
    if (empty($categories)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid subject or no categories found'
        ]);
        return;
    }
    
    // Return both keys and labels for frontend flexibility
    $categoryList = [];
    foreach ($categories as $key => $label) {
        $categoryList[] = [
            'key' => $key,      // ✅ For database/API
            'label' => $label   // ✅ For UI display
        ];
    }
    
    echo json_encode([
        'success' => true,
        'subject' => $subject,
        'categories' => $categoryList
    ]);
}

// ================= EXAMPLE 2: Save Quiz Progress =================

/**
 * API: Save quiz progress with category KEY
 * ✅ CRITICAL: Only accept and store KEYS, never labels
 */
function apiSaveQuizProgress($conn) {
    header('Content-Type: application/json');
    
    $userId = $_POST['user_id'] ?? 0;
    $subject = $_POST['subject'] ?? '';
    $categoryKey = $_POST['category'] ?? ''; // ✅ Must be KEY
    $score = $_POST['score'] ?? 0;
    $xpEarned = $_POST['xp_earned'] ?? 0;
    
    // Validate inputs
    if (empty($userId) || empty($subject) || empty($categoryKey)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields'
        ]);
        return;
    }
    
    // ✅ CRITICAL: Validate category KEY
    if (!isValidCategory($subject, $categoryKey)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid category key: ' . $categoryKey
        ]);
        return;
    }
    
    try {
        // ✅ Store KEY in database, not label
        $sql = "INSERT INTO quiz_progress 
                (user_id, subject, category, score, xp_earned, completed_at) 
                VALUES 
                (:user_id, :subject, :category, :score, :xp_earned, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':category', $categoryKey); // ✅ Store KEY
        $stmt->bindParam(':score', $score, PDO::PARAM_INT);
        $stmt->bindParam(':xp_earned', $xpEarned, PDO::PARAM_INT);
        $stmt->execute();
        
        // Get label for response (UI display)
        $categoryLabel = getCategoryLabel($subject, $categoryKey);
        
        echo json_encode([
            'success' => true,
            'message' => 'Progress saved successfully',
            'category_key' => $categoryKey,
            'category_label' => $categoryLabel,
            'xp_earned' => $xpEarned
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// ================= EXAMPLE 3: Get User Statistics =================

/**
 * API: Get user statistics by category
 * Returns data with both KEY and LABEL for flexibility
 */
function apiGetUserStats($conn) {
    header('Content-Type: application/json');
    
    $userId = $_GET['user_id'] ?? 0;
    $subject = $_GET['subject'] ?? '';
    
    if (empty($userId) || empty($subject)) {
        echo json_encode([
            'success' => false,
            'message' => 'User ID and subject are required'
        ]);
        return;
    }
    
    try {
        // Query database using KEYS
        $sql = "SELECT category, 
                       COUNT(*) as attempts,
                       AVG(score) as avg_score,
                       SUM(xp_earned) as total_xp
                FROM quiz_progress
                WHERE user_id = :user_id AND subject = :subject
                GROUP BY category";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':subject', $subject);
        $stmt->execute();
        
        $stats = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categoryKey = $row['category']; // ✅ KEY from database
            
            $stats[] = [
                'category_key' => $categoryKey,
                'category_label' => getCategoryLabel($subject, $categoryKey), // ✅ Add label
                'attempts' => (int)$row['attempts'],
                'avg_score' => round((float)$row['avg_score'], 2),
                'total_xp' => (int)$row['total_xp']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'subject' => $subject,
            'stats' => $stats
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// ================= EXAMPLE 4: Validate Category Submission =================

/**
 * Middleware: Validate category before processing
 * Use this in all endpoints that accept category parameter
 */
function validateCategoryMiddleware($subject, $categoryKey) {
    if (empty($subject) || empty($categoryKey)) {
        return [
            'valid' => false,
            'error' => 'Subject and category are required'
        ];
    }
    
    if (!isValidCategory($subject, $categoryKey)) {
        return [
            'valid' => false,
            'error' => 'Invalid category key: ' . $categoryKey . ' for subject: ' . $subject
        ];
    }
    
    return [
        'valid' => true,
        'category_key' => $categoryKey,
        'category_label' => getCategoryLabel($subject, $categoryKey)
    ];
}

// ================= EXAMPLE 5: Admin - Add New Question =================

/**
 * Admin API: Add quiz question with category KEY
 */
function apiAddQuestion($conn) {
    header('Content-Type: application/json');
    
    $subject = $_POST['subject'] ?? '';
    $categoryKey = $_POST['category'] ?? ''; // ✅ Must be KEY
    $question = $_POST['question'] ?? '';
    $level = $_POST['level'] ?? 1;
    // ... other fields
    
    // Validate category
    $validation = validateCategoryMiddleware($subject, $categoryKey);
    if (!$validation['valid']) {
        echo json_encode([
            'success' => false,
            'message' => $validation['error']
        ]);
        return;
    }
    
    try {
        // ✅ Insert with KEY
        $sql = "INSERT INTO quizes 
                (subject_name, category, question, level, created_at) 
                VALUES 
                (:subject, :category, :question, :level, NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':category', $categoryKey); // ✅ Store KEY
        $stmt->bindParam(':question', $question);
        $stmt->bindParam(':level', $level, PDO::PARAM_INT);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Question added successfully',
            'category_key' => $categoryKey,
            'category_label' => $validation['category_label']
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// ================= EXAMPLE 6: Get All Categories (JSON for Unity) =================

/**
 * API: Get complete category configuration
 * Unity can cache this on startup
 */
function apiGetAllCategories() {
    header('Content-Type: application/json');
    
    echo getCategoryConfigJSON();
}

// ================= ROUTING EXAMPLE =================

// Simple router for demonstration
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_categories':
            apiGetCategories();
            break;
        case 'get_all_categories':
            apiGetAllCategories();
            break;
        case 'get_stats':
            // apiGetUserStats($conn); // Uncomment when $conn is available
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

// ================= KEY TAKEAWAYS =================

/*
 * ✅ DO:
 * - Store KEYS in database (grammar, algebra, etc.)
 * - Validate using KEYS
 * - Return both KEY and LABEL in API responses
 * - Use getCategoryLabel() to convert KEY → LABEL for UI
 * 
 * ❌ DON'T:
 * - Store labels in database
 * - Validate using labels
 * - Accept labels from frontend without conversion
 * - Hardcode category names in queries
 * 
 * 🎯 BENEFITS:
 * - Easy to update labels without database migration
 * - Consistent data across all tables
 * - Scalable for analytics and gamification
 * - Multi-language support (just change labels)
 * - Smaller database storage
 */
?>
