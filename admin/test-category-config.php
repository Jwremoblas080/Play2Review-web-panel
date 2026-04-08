<?php
/**
 * Test Script for Category Config
 * Run this to verify all functions work correctly
 * 
 * Usage: php test-category-config.php
 * Or access via browser: http://localhost/play2review/admin/test-category-config.php
 */

require_once('category-config.php');

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║         CATEGORY CONFIG TEST SUITE                          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$testsPassed = 0;
$testsFailed = 0;

function test($name, $condition, $expected = null, $actual = null) {
    global $testsPassed, $testsFailed;
    
    if ($condition) {
        echo "✅ PASS: $name\n";
        $testsPassed++;
    } else {
        echo "❌ FAIL: $name\n";
        if ($expected !== null) {
            echo "   Expected: " . print_r($expected, true) . "\n";
            echo "   Actual: " . print_r($actual, true) . "\n";
        }
        $testsFailed++;
    }
}

// ==================== TEST 1: getCategoriesBySubject ====================
echo "TEST 1: getCategoriesBySubject()\n";
echo "─────────────────────────────────────────────────────────────\n";

$englishCategories = getCategoriesBySubject('english');
test("Returns array for valid subject", is_array($englishCategories));
test("English has 5 categories", count($englishCategories) === 5);
test("Contains 'grammar' key", isset($englishCategories['grammar']));
test("Grammar label is correct", $englishCategories['grammar'] === 'Grammar & Language Structure');

$invalidCategories = getCategoriesBySubject('invalid');
test("Returns empty array for invalid subject", empty($invalidCategories));

echo "\n";

// ==================== TEST 2: getCategoryKeys ====================
echo "TEST 2: getCategoryKeys()\n";
echo "─────────────────────────────────────────────────────────────\n";

$mathKeys = getCategoryKeys('math');
test("Returns array", is_array($mathKeys));
test("Math has 6 keys", count($mathKeys) === 6);
test("Contains 'algebra'", in_array('algebra', $mathKeys));
test("Contains 'geometry'", in_array('geometry', $mathKeys));
test("Does not contain labels", !in_array('Algebra', $mathKeys));

echo "\n";

// ==================== TEST 3: getCategoryLabels ====================
echo "TEST 3: getCategoryLabels()\n";
echo "─────────────────────────────────────────────────────────────\n";

$filipinoLabels = getCategoryLabels('filipino');
test("Returns array", is_array($filipinoLabels));
test("Filipino has 5 labels", count($filipinoLabels) === 5);
test("Contains 'Gramatika'", in_array('Gramatika', $filipinoLabels));
test("Does not contain keys", !in_array('gramatika', $filipinoLabels));

echo "\n";

// ==================== TEST 4: getAllSubjects ====================
echo "TEST 4: getAllSubjects()\n";
echo "─────────────────────────────────────────────────────────────\n";

$subjects = getAllSubjects();
test("Returns array", is_array($subjects));
test("Has 5 subjects", count($subjects) === 5);
test("Contains 'english'", in_array('english', $subjects));
test("Contains 'math'", in_array('math', $subjects));
test("Contains 'filipino'", in_array('filipino', $subjects));
test("Contains 'ap'", in_array('ap', $subjects));
test("Contains 'science'", in_array('science', $subjects));

echo "\n";

// ==================== TEST 5: isValidCategory ====================
echo "TEST 5: isValidCategory()\n";
echo "─────────────────────────────────────────────────────────────\n";

test("Valid: english + grammar", isValidCategory('english', 'grammar') === true);
test("Valid: math + algebra", isValidCategory('math', 'algebra') === true);
test("Valid: filipino + gramatika", isValidCategory('filipino', 'gramatika') === true);
test("Invalid: english + invalid", isValidCategory('english', 'invalid') === false);
test("Invalid: invalid + grammar", isValidCategory('invalid', 'grammar') === false);

echo "\n";

// ==================== TEST 6: getCategoryLabel ====================
echo "TEST 6: getCategoryLabel()\n";
echo "─────────────────────────────────────────────────────────────\n";

$label1 = getCategoryLabel('english', 'grammar');
test("English grammar label", $label1 === 'Grammar & Language Structure');

$label2 = getCategoryLabel('math', 'word_problems');
test("Math word_problems label", $label2 === 'Word Problems');

$label3 = getCategoryLabel('ap', 'ekonomiks');
test("AP ekonomiks label", $label3 === 'Ekonomiks');

$label4 = getCategoryLabel('english', 'invalid');
test("Invalid category returns null", $label4 === null);

echo "\n";

// ==================== TEST 7: getCategoryKeyFromLabel ====================
echo "TEST 7: getCategoryKeyFromLabel()\n";
echo "─────────────────────────────────────────────────────────────\n";

$key1 = getCategoryKeyFromLabel('english', 'Grammar & Language Structure');
test("Grammar label → key", $key1 === 'grammar');

$key2 = getCategoryKeyFromLabel('math', 'Algebra');
test("Algebra label → key", $key2 === 'algebra');

$key3 = getCategoryKeyFromLabel('science', 'Earth Science');
test("Earth Science label → key", $key3 === 'earth_science');

$key4 = getCategoryKeyFromLabel('english', 'Invalid Label');
test("Invalid label returns null", $key4 === null);

echo "\n";

// ==================== TEST 8: getCategoryConfigJSON ====================
echo "TEST 8: getCategoryConfigJSON()\n";
echo "─────────────────────────────────────────────────────────────\n";

$json = getCategoryConfigJSON();
test("Returns string", is_string($json));
test("Valid JSON", json_decode($json) !== null);

$decoded = json_decode($json, true);
test("Contains english", isset($decoded['english']));
test("Contains math", isset($decoded['math']));
test("Preserves Unicode (Filipino)", strpos($json, 'Pag-unawa sa Binasa') !== false);

echo "\n";

// ==================== TEST 9: isValidSubject ====================
echo "TEST 9: isValidSubject()\n";
echo "─────────────────────────────────────────────────────────────\n";

test("Valid: english", isValidSubject('english') === true);
test("Valid: math", isValidSubject('math') === true);
test("Invalid: invalid", isValidSubject('invalid') === false);

echo "\n";

// ==================== TEST 10: getCategoryLevelColumnName ====================
echo "TEST 10: getCategoryLevelColumnName()\n";
echo "─────────────────────────────────────────────────────────────\n";

$col1 = getCategoryLevelColumnName('english', 'grammar');
test("English grammar column", $col1 === 'english_grammar_level');

$col2 = getCategoryLevelColumnName('math', 'word_problems');
test("Math word_problems column", $col2 === 'math_word_problems_level');

$col3 = getCategoryLevelColumnName('filipino', 'pag_unawa');
test("Filipino pag_unawa column", $col3 === 'filipino_pag_unawa_level');

$col4 = getCategoryLevelColumnName('english', 'invalid');
test("Invalid category returns null", $col4 === null);

echo "\n";

// ==================== TEST 11: validateAndSanitizeCategoryKey ====================
echo "TEST 11: validateAndSanitizeCategoryKey()\n";
echo "─────────────────────────────────────────────────────────────\n";

$san1 = validateAndSanitizeCategoryKey('english', '  GRAMMAR  ');
test("Sanitizes whitespace and case", $san1 === 'grammar');

$san2 = validateAndSanitizeCategoryKey('math', 'Algebra');
test("Sanitizes case", $san2 === 'algebra');

$san3 = validateAndSanitizeCategoryKey('english', 'invalid');
test("Invalid returns null", $san3 === null);

echo "\n";

// ==================== TEST 12: getCategoryInfo ====================
echo "TEST 12: getCategoryInfo()\n";
echo "─────────────────────────────────────────────────────────────\n";

$info = getCategoryInfo('science', 'biology');
test("Returns array", is_array($info));
test("Has subject key", isset($info['subject']));
test("Has key key", isset($info['key']));
test("Has label key", isset($info['label']));
test("Has column_name key", isset($info['column_name']));
test("Subject is correct", $info['subject'] === 'science');
test("Key is correct", $info['key'] === 'biology');
test("Label is correct", $info['label'] === 'Biology');
test("Column name is correct", $info['column_name'] === 'science_biology_level');

$invalidInfo = getCategoryInfo('english', 'invalid');
test("Invalid returns null", $invalidInfo === null);

echo "\n";

// ==================== TEST 13: getAllCategoryKeys ====================
echo "TEST 13: getAllCategoryKeys()\n";
echo "─────────────────────────────────────────────────────────────\n";

$allKeys = getAllCategoryKeys();
test("Returns array", is_array($allKeys));
test("Has entries", count($allKeys) > 0);
test("Total categories is 27", count($allKeys) === 27); // 5+6+5+5+6

$firstEntry = $allKeys[0];
test("Entry has subject", isset($firstEntry['subject']));
test("Entry has key", isset($firstEntry['key']));
test("Entry has label", isset($firstEntry['label']));

echo "\n";

// ==================== SUMMARY ====================
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║                      TEST SUMMARY                            ║\n";
echo "╠══════════════════════════════════════════════════════════════╣\n";
echo "║ Total Tests: " . ($testsPassed + $testsFailed) . "                                              ║\n";
echo "║ ✅ Passed: " . $testsPassed . "                                                ║\n";
echo "║ ❌ Failed: " . $testsFailed . "                                                 ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";

if ($testsFailed === 0) {
    echo "\n🎉 ALL TESTS PASSED! Category config is working perfectly!\n";
    exit(0);
} else {
    echo "\n⚠️  SOME TESTS FAILED! Please review the errors above.\n";
    exit(1);
}
?>
