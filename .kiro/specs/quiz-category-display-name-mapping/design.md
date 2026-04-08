# Quiz Category Display Name Mapping Bugfix Design

## Overview

This bugfix addresses the issue where quiz listing tables in both the admin panel (manage-quizes.php) and educator panel (educ-quizes.php) display raw database category keys (e.g., "grammar", "algebra") instead of user-friendly display labels (e.g., "Grammar & Language Structure", "Algebra"). The fix involves converting category keys to labels using the existing `getCategoryLabel()` function from category-config.php at the point of display in the HTML table output.

## Glossary

- **Bug_Condition (C)**: The condition that triggers the bug - when a quiz with a non-empty category value is displayed in the listing table
- **Property (P)**: The desired behavior - category keys should be converted to display labels using `getCategoryLabel()`
- **Preservation**: Existing database storage, filtering, and modal data passing that must remain unchanged by the fix
- **getCategoryLabel()**: The function in `category-config.php` that converts a category key to its display label: `getCategoryLabel($subject, $categoryKey)`
- **Category Key**: The database value stored in the `category` column (e.g., "grammar", "algebra", "ekonomiks")
- **Category Label**: The user-friendly display name (e.g., "Grammar & Language Structure", "Algebra", "Ekonomiks")

## Bug Details

### Bug Condition

The bug manifests when a quiz with a non-empty category value is displayed in the quiz listing table. The display logic directly outputs the raw database key value without converting it to the corresponding label using the `getCategoryLabel()` function.

**Formal Specification:**
```
FUNCTION isBugCondition(quiz)
  INPUT: quiz of type array (database row)
  OUTPUT: boolean
  
  RETURN NOT empty(quiz['category'])
         AND quiz is being displayed in listing table
         AND getCategoryLabel(quiz['subject_name'], quiz['category']) is not called
END FUNCTION
```

### Examples

- **manage-quizes.php line 994**: When displaying a quiz with `category = "grammar"` and `subject_name = "english"`, the system displays "grammar" instead of "Grammar & Language Structure"
- **educ-quizes.php line 905**: When displaying a quiz with `category = "algebra"` and `subject_name = "math"`, the system displays "algebra" instead of "Algebra"
- **Any subject/category combination**: When displaying a quiz with `category = "pag_unawa"` and `subject_name = "filipino"`, the system displays "pag_unawa" instead of "Pag-unawa sa Binasa"
- **Edge case - empty category**: When displaying a quiz with `category = ""` or `category = NULL`, the system correctly displays "Not Set" badge (this behavior should be preserved)

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Database storage must continue to store category KEYS, not LABELS
- Database queries and WHERE clauses must continue to use category KEYS
- The "Not Set" badge for empty/null categories must continue to display unchanged
- Modal data attributes must continue to pass raw category KEY values in JSON
- Category dropdowns in add/edit forms must continue to function as they currently do
- All filtering, sorting, and counting logic must remain unchanged

**Scope:**
All inputs that do NOT involve the specific table cell display of the category column should be completely unaffected by this fix. This includes:
- Database INSERT/UPDATE operations
- Database SELECT queries with WHERE clauses
- JavaScript modal population logic
- Form submission handling
- CSV import/export functionality

## Hypothesized Root Cause

Based on the bug description and code analysis, the root cause is:

1. **Direct Output of Database Value**: The table display logic at lines 994 (manage-quizes.php) and 905 (educ-quizes.php) directly outputs `$quiz['category']` without transformation
   - Current code: `<?php echo htmlspecialchars($quiz['category']); ?>`
   - Missing step: No call to `getCategoryLabel()` before display

2. **Incomplete Implementation of KEY → LABEL Architecture**: While the category-config.php system was designed with KEY → LABEL separation, the display layer was not updated to use the conversion function

3. **Copy-Paste Pattern**: Both files have identical display logic, suggesting the bug exists in both locations due to code duplication

## Correctness Properties

Property 1: Bug Condition - Category Display Label Conversion

_For any_ quiz row where the category field is non-empty (isBugCondition returns true), the fixed display logic SHALL convert the category key to its display label by calling `getCategoryLabel($quiz['subject_name'], $quiz['category'])` and display the resulting label instead of the raw key.

**Validates: Requirements 2.1, 2.2, 2.3**

Property 2: Preservation - Database and Logic Unchanged

_For any_ operation that is NOT the specific table cell display of the category column (isBugCondition returns false), the fixed code SHALL produce exactly the same behavior as the original code, preserving all database operations, queries, modal data passing, and form handling.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

**File 1**: `play2review/admin/manage-quizes.php`

**Location**: Line 994 (inside the quiz listing table, category column)

**Specific Changes**:
1. **Replace Direct Output with Label Conversion**:
   - Current code:
     ```php
     <i class="fas fa-tag"></i> <?php echo htmlspecialchars($quiz['category']); ?>
     ```
   - Fixed code:
     ```php
     <i class="fas fa-tag"></i> <?php echo htmlspecialchars(getCategoryLabel($quiz['subject_name'], $quiz['category']) ?? $quiz['category']); ?>
     ```

2. **Fallback Handling**: Use the null coalescing operator (`??`) to fall back to the raw key if `getCategoryLabel()` returns null (handles invalid/unknown category keys gracefully)

**File 2**: `play2review/admin/educ-quizes.php`

**Location**: Line 905 (inside the quiz listing table, category column)

**Specific Changes**:
1. **Replace Direct Output with Label Conversion**:
   - Current code:
     ```php
     <i class="fas fa-tag"></i> <?php echo htmlspecialchars($quiz['category']); ?>
     ```
   - Fixed code:
     ```php
     <i class="fas fa-tag"></i> <?php echo htmlspecialchars(getCategoryLabel($quiz['subject_name'], $quiz['category']) ?? $quiz['category']); ?>
     ```

2. **Fallback Handling**: Use the null coalescing operator (`??`) to fall back to the raw key if `getCategoryLabel()` returns null

**No Changes Required**:
- category-config.php (already provides the necessary function)
- Database schema or queries
- Modal JavaScript logic
- Form handling code
- CSV import/export logic

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bug on unfixed code, then verify the fix works correctly and preserves existing behavior.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bug BEFORE implementing the fix. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Manually inspect the quiz listing tables in both admin and educator panels with quizzes that have various category values. Observe that raw keys are displayed instead of labels. Run these observations on the UNFIXED code to confirm the bug.

**Test Cases**:
1. **English Grammar Test**: Create/view a quiz with `subject_name = "english"` and `category = "grammar"` (will show "grammar" instead of "Grammar & Language Structure" on unfixed code)
2. **Math Algebra Test**: Create/view a quiz with `subject_name = "math"` and `category = "algebra"` (will show "algebra" instead of "Algebra" on unfixed code)
3. **Filipino Pag-unawa Test**: Create/view a quiz with `subject_name = "filipino"` and `category = "pag_unawa"` (will show "pag_unawa" instead of "Pag-unawa sa Binasa" on unfixed code)
4. **Empty Category Test**: View a quiz with empty/null category (should show "Not Set" badge correctly on both unfixed and fixed code)

**Expected Counterexamples**:
- Category column displays raw database keys like "grammar", "algebra", "pag_unawa"
- Possible causes: Missing call to `getCategoryLabel()` in display logic

### Fix Checking

**Goal**: Verify that for all inputs where the bug condition holds, the fixed function produces the expected behavior.

**Pseudocode:**
```
FOR ALL quiz WHERE isBugCondition(quiz) DO
  displayedValue := renderCategoryCell_fixed(quiz)
  expectedLabel := getCategoryLabel(quiz['subject_name'], quiz['category'])
  ASSERT displayedValue CONTAINS expectedLabel
END FOR
```

**Test Plan**: After implementing the fix, manually verify that the quiz listing tables display category labels instead of keys.

**Test Cases**:
1. **English Grammar Display**: Verify quiz with `category = "grammar"` displays "Grammar & Language Structure"
2. **Math Algebra Display**: Verify quiz with `category = "algebra"` displays "Algebra"
3. **Filipino Pag-unawa Display**: Verify quiz with `category = "pag_unawa"` displays "Pag-unawa sa Binasa"
4. **All Subjects/Categories**: Verify all category keys across all subjects display their corresponding labels
5. **Invalid Category Fallback**: Verify quiz with invalid category key displays the raw key (fallback behavior)

### Preservation Checking

**Goal**: Verify that for all inputs where the bug condition does NOT hold, the fixed function produces the same result as the original function.

**Pseudocode:**
```
FOR ALL operation WHERE NOT isBugCondition(operation) DO
  ASSERT originalBehavior(operation) = fixedBehavior(operation)
END FOR
```

**Testing Approach**: Manual testing is recommended for preservation checking because the changes are isolated to display logic only. We need to verify that all other functionality remains unchanged.

**Test Plan**: Observe behavior on UNFIXED code first for non-display operations, then verify the same behavior continues after the fix.

**Test Cases**:
1. **Database Storage Preservation**: Add a new quiz with a category - verify the database stores the KEY, not the LABEL
2. **Modal Data Preservation**: Click edit button on a quiz - verify the modal receives the raw category KEY in the JSON data attribute
3. **Empty Category Badge Preservation**: View a quiz with no category - verify "Not Set" badge displays correctly
4. **Filtering Preservation**: Filter quizzes by subject/level - verify filtering continues to work correctly
5. **CSV Import Preservation**: Import quizzes via CSV - verify category keys are stored correctly
6. **Form Submission Preservation**: Submit add/edit forms - verify category keys are saved to database correctly

### Unit Tests

- Test `getCategoryLabel()` function with valid category keys returns correct labels
- Test `getCategoryLabel()` function with invalid category keys returns null
- Test display logic with empty/null category shows "Not Set" badge
- Test display logic with valid category shows label
- Test display logic with invalid category shows raw key (fallback)

### Property-Based Tests

Not applicable for this bugfix - the changes are isolated to display logic and do not involve complex algorithmic behavior that would benefit from property-based testing. Manual testing and unit tests are sufficient.

### Integration Tests

- Test full quiz management flow: add quiz → view in listing → verify label displayed
- Test both admin and educator panels show correct labels
- Test all subjects (english, math, filipino, ap, science) display correct labels
- Test that editing a quiz preserves the category key in the database
- Test that CSV import/export continues to work with category keys
