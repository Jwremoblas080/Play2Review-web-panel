<?php
/**
 * Centralized Category Configuration for Quiz Management System
 * DepEd-aligned subject categories for Play2Review
 * 
 * Production-Ready Version with Full KEY → LABEL Architecture
 * 
 * Features:
 * - KEY → LABEL mapping for database vs UI separation
 * - Validation using KEYS only
 * - Helper functions for dropdowns, JSON output, and API
 * - Scalable for gamification: XP tracking, badges, analytics
 * - Multi-language support ready
 * 
 * CRITICAL RULES:
 * - Database stores ONLY KEYS (e.g., "grammar", "algebra")
 * - UI displays ONLY LABELS (e.g., "Grammar & Language Structure")
 * - API calls send/receive KEYS
 * - Validation uses KEYS
 * 
 * @version 2.0
 * @date 2026-04-06
 */

// ------------------------------
// CATEGORY CONFIGURATION (KEY => LABEL)
// ------------------------------
// ✅ CRITICAL: Database stores KEYS, UI displays LABELS
// 
// Structure: $CATEGORY_CONFIG[subject][key] = label
// Example: $CATEGORY_CONFIG['english']['grammar'] = 'Grammar & Language Structure'
//
// KEYS: Lowercase, underscore-separated (e.g., "word_problems", "pag_unawa")
// LABELS: Human-readable, properly formatted (e.g., "Word Problems", "Pag-unawa sa Binasa")
//
$CATEGORY_CONFIG = [
    'english' => [
        'grammar' => 'Grammar & Language Structure',
        'vocabulary' => 'Vocabulary',
        'reading' => 'Reading Comprehension',
        'literature' => 'Literature',
        'writing' => 'Writing Skills'
    ],
    'math' => [
        'algebra' => 'Algebra',
        'geometry' => 'Geometry',
        'statistics' => 'Statistics',
        'probability' => 'Probability',
        'functions' => 'Functions & Equations',
        'word_problems' => 'Word Problems'
    ],
    'filipino' => [
        'gramatika' => 'Gramatika',
        'panitikan' => 'Panitikan',
        'pag_unawa' => 'Pag-unawa sa Binasa',
        'talasalitaan' => 'Talasalitaan',
        'wika' => 'Wika at Kultura'
    ],
    'ap' => [
        'ekonomiks' => 'Ekonomiks',
        'kasaysayan' => 'Kasaysayan ng Pilipinas',
        'kontemporaryo' => 'Kontemporaryong Isyu',
        'heograpiya' => 'Heograpiya',
        'pamahalaan' => 'Pamahalaan at Lipunan'
    ],
    'science' => [
        'biology' => 'Biology',
        'chemistry' => 'Chemistry',
        'physics' => 'Physics',
        'earth_science' => 'Earth Science',
        'investigation' => 'Scientific Investigation'
    ]
];

// ------------------------------
// FUNCTION: Get categories for a subject (KEY => LABEL)
// ------------------------------
function getCategoriesBySubject(string $subject): array {
    global $CATEGORY_CONFIG;
    return $CATEGORY_CONFIG[$subject] ?? [];
}

// ------------------------------
// FUNCTION: Get only category KEYS (for validation)
// ------------------------------
function getCategoryKeys(string $subject): array {
    $categories = getCategoriesBySubject($subject);
    return array_keys($categories);
}

// ------------------------------
// FUNCTION: Get only labels (for dropdowns/UI)
// ------------------------------
function getCategoryLabels(string $subject): array {
    $categories = getCategoriesBySubject($subject);
    return array_values($categories);
}

// ------------------------------
// FUNCTION: Get all subjects
// ------------------------------
function getAllSubjects(): array {
    global $CATEGORY_CONFIG;
    return array_keys($CATEGORY_CONFIG);
}

// ------------------------------
// FUNCTION: Validate category using KEY
// ------------------------------
function isValidCategory(string $subject, string $categoryKey): bool {
    global $CATEGORY_CONFIG;
    return isset($CATEGORY_CONFIG[$subject][$categoryKey]);
}

// ------------------------------
// FUNCTION: Get category label from KEY (for UI)
// ------------------------------
function getCategoryLabel(string $subject, string $categoryKey): ?string {
    global $CATEGORY_CONFIG;
    return $CATEGORY_CONFIG[$subject][$categoryKey] ?? null;
}

// ------------------------------
// FUNCTION: Get category key from label (reverse lookup)
// ✅ FIXED: Returns string|false, handles not found case
// ------------------------------
function getCategoryKeyFromLabel(string $subject, string $label) {
    $categories = getCategoriesBySubject($subject);
    $key = array_search($label, $categories, true);
    return $key !== false ? $key : null;
}

// ------------------------------
// FUNCTION: Get full config as JSON (for Unity / JS frontend)
// ------------------------------
function getCategoryConfigJSON(): string {
    global $CATEGORY_CONFIG;
    return json_encode($CATEGORY_CONFIG, JSON_UNESCAPED_UNICODE);
}

// ------------------------------
// FUNCTION: Validate subject exists
// ------------------------------
function isValidSubject(string $subject): bool {
    global $CATEGORY_CONFIG;
    return isset($CATEGORY_CONFIG[$subject]);
}

// ------------------------------
// FUNCTION: Get all category keys across all subjects
// ------------------------------
function getAllCategoryKeys(): array {
    global $CATEGORY_CONFIG;
    $allKeys = [];
    foreach ($CATEGORY_CONFIG as $subject => $categories) {
        foreach ($categories as $key => $label) {
            $allKeys[] = ['subject' => $subject, 'key' => $key, 'label' => $label];
        }
    }
    return $allKeys;
}

// ------------------------------
// FUNCTION: Get database column name for category level
// Used for building dynamic UPDATE/SELECT queries
// ------------------------------
function getCategoryLevelColumnName(string $subject, string $categoryKey): ?string {
    if (!isValidCategory($subject, $categoryKey)) {
        return null;
    }
    return "{$subject}_{$categoryKey}_level";
}

// ------------------------------
// FUNCTION: Validate and sanitize category key
// Returns sanitized key or null if invalid
// ------------------------------
function validateAndSanitizeCategoryKey(string $subject, string $categoryKey): ?string {
    // Normalize: trim and lowercase
    $categoryKey = strtolower(trim($categoryKey));
    
    // Validate
    if (!isValidCategory($subject, $categoryKey)) {
        return null;
    }
    
    return $categoryKey;
}

// ------------------------------
// FUNCTION: Get category info (key, label, subject)
// Returns array with full category information
// ------------------------------
function getCategoryInfo(string $subject, string $categoryKey): ?array {
    if (!isValidCategory($subject, $categoryKey)) {
        return null;
    }
    
    return [
        'subject' => $subject,
        'key' => $categoryKey,
        'label' => getCategoryLabel($subject, $categoryKey),
        'column_name' => getCategoryLevelColumnName($subject, $categoryKey)
    ];
}

// ------------------------------
// EXAMPLE USAGE (commented out)
// ------------------------------
/*
// Example 1: Validate category
if (isValidCategory('english', 'grammar')) {
    echo "Valid category!";
}

// Example 2: Get label for UI
$label = getCategoryLabel('math', 'algebra');
echo $label; // Output: "Algebra"

// Example 3: Get key from label (reverse lookup)
$key = getCategoryKeyFromLabel('filipino', 'Gramatika');
echo $key; // Output: "gramatika"

// Example 4: Get all categories for dropdown
$categories = getCategoriesBySubject('science');
foreach ($categories as $key => $label) {
    echo "<option value='$key'>$label</option>";
}

// Example 5: Get database column name
$columnName = getCategoryLevelColumnName('english', 'grammar');
echo $columnName; // Output: "english_grammar_level"

// Example 6: Get full category info
$info = getCategoryInfo('ap', 'ekonomiks');
print_r($info);
// Output: [
//   'subject' => 'ap',
//   'key' => 'ekonomiks',
//   'label' => 'Ekonomiks',
//   'column_name' => 'ap_ekonomiks_level'
// ]
*/
?>