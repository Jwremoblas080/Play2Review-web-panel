# Implementation Plan

- [ ] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Category Display Shows Raw Keys Instead of Labels
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Scoped PBT Approach**: For deterministic bugs, scope the property to the concrete failing case(s) to ensure reproducibility
  - Manually inspect quiz listing tables in both admin (manage-quizes.php) and educator (educ-quizes.php) panels
  - Test with quiz having `subject_name = "english"` and `category = "grammar"` - verify it displays "grammar" instead of "Grammar & Language Structure"
  - Test with quiz having `subject_name = "math"` and `category = "algebra"` - verify it displays "algebra" instead of "Algebra"
  - Test with quiz having `subject_name = "filipino"` and `category = "pag_unawa"` - verify it displays "pag_unawa" instead of "Pag-unawa sa Binasa"
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists)
  - Document counterexamples found: raw keys displayed in category column instead of labels
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3_

- [ ] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Non-Display Operations Remain Unchanged
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-display operations
  - Test database storage: Add a new quiz with category "grammar" - verify database stores "grammar" (KEY), not "Grammar & Language Structure" (LABEL)
  - Test modal data passing: Click edit button on a quiz - verify modal receives raw category KEY in JSON data attribute
  - Test empty category badge: View quiz with no category - verify "Not Set" badge displays correctly
  - Test filtering: Filter quizzes by subject/level - verify filtering works correctly
  - Test form submission: Submit add/edit form with category - verify KEY is saved to database
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

- [ ] 3. Fix category display to show labels instead of keys

  - [ ] 3.1 Implement the fix in manage-quizes.php
    - Navigate to line 994 in play2review/admin/manage-quizes.php
    - Replace direct output of `$quiz['category']` with label conversion
    - Change from: `<i class="fas fa-tag"></i> <?php echo htmlspecialchars($quiz['category']); ?>`
    - Change to: `<i class="fas fa-tag"></i> <?php echo htmlspecialchars(getCategoryLabel($quiz['subject_name'], $quiz['category']) ?? $quiz['category']); ?>`
    - Use null coalescing operator (`??`) to fall back to raw key if getCategoryLabel() returns null
    - _Bug_Condition: isBugCondition(quiz) where NOT empty(quiz['category']) AND quiz is being displayed in listing table_
    - _Expected_Behavior: displayedValue CONTAINS getCategoryLabel(quiz['subject_name'], quiz['category'])_
    - _Preservation: Database storage, queries, modal data, forms, filtering remain unchanged_
    - _Requirements: 2.1, 2.3, 2.4, 2.5, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

  - [ ] 3.2 Implement the fix in educ-quizes.php
    - Navigate to line 905 in play2review/admin/educ-quizes.php
    - Replace direct output of `$quiz['category']` with label conversion
    - Change from: `<i class="fas fa-tag"></i> <?php echo htmlspecialchars($quiz['category']); ?>`
    - Change to: `<i class="fas fa-tag"></i> <?php echo htmlspecialchars(getCategoryLabel($quiz['subject_name'], $quiz['category']) ?? $quiz['category']); ?>`
    - Use null coalescing operator (`??`) to fall back to raw key if getCategoryLabel() returns null
    - _Bug_Condition: isBugCondition(quiz) where NOT empty(quiz['category']) AND quiz is being displayed in listing table_
    - _Expected_Behavior: displayedValue CONTAINS getCategoryLabel(quiz['subject_name'], quiz['category'])_
    - _Preservation: Database storage, queries, modal data, forms, filtering remain unchanged_
    - _Requirements: 2.2, 2.3, 2.4, 2.5, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7_

  - [ ] 3.3 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Category Display Shows Labels
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Manually verify quiz listing tables in both admin and educator panels
    - Verify quiz with `category = "grammar"` displays "Grammar & Language Structure"
    - Verify quiz with `category = "algebra"` displays "Algebra"
    - Verify quiz with `category = "pag_unawa"` displays "Pag-unawa sa Binasa"
    - Verify all category keys across all subjects display their corresponding labels
    - Verify quiz with invalid category key displays the raw key (fallback behavior)
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5_

  - [ ] 3.4 Verify preservation tests still pass
    - **Property 2: Preservation** - Non-Display Operations Unchanged
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - Verify database storage still stores category KEYS, not LABELS
    - Verify modal data still passes raw category KEY values
    - Verify "Not Set" badge still displays correctly for empty categories
    - Verify filtering, sorting, and counting logic still works correctly
    - Verify form submission still saves category KEYS to database
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all tests still pass after fix (no regressions)

- [ ] 4. Checkpoint - Ensure all tests pass
  - Verify all manual tests from tasks 1-3 pass
  - Verify both admin and educator panels display category labels correctly
  - Verify all preservation behaviors remain unchanged
  - Ask the user if questions arise
