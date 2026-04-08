# Bugfix Requirements Document

## Introduction

In the quiz module web panel (admin interface), when displaying quizzes in the listing tables, the category column shows the raw database value (e.g., "grammar") instead of the user-friendly display name (e.g., "Grammar & Language Structure"). This affects both the admin panel (manage-quizes.php) and the educator panel (educ-quizes.php).

The category configuration system already has display names defined in category-config.php with a KEY → LABEL architecture where:
- **KEYS** (e.g., "grammar", "algebra") are stored in the database
- **LABELS** (e.g., "Grammar & Language Structure", "Algebra") should be displayed in the UI

The bug occurs because the quiz listing tables directly output the database KEY value without converting it to its corresponding LABEL using the `getCategoryLabel()` function.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN a quiz with category "grammar" is displayed in the admin quiz listing table (manage-quizes.php) THEN the system displays the raw database value "grammar" instead of the formatted label "Grammar & Language Structure"

1.2 WHEN a quiz with category "algebra" is displayed in the educator quiz listing table (educ-quizes.php) THEN the system displays the raw database value "algebra" instead of the formatted label "Algebra"

1.3 WHEN any quiz with a category key is displayed in either admin or educator quiz listing tables THEN the system displays the database KEY instead of the user-friendly LABEL

### Expected Behavior (Correct)

2.1 WHEN a quiz with category "grammar" is displayed in the admin quiz listing table (manage-quizes.php) THEN the system SHALL display the formatted label "Grammar & Language Structure" by calling `getCategoryLabel($quiz['subject_name'], $quiz['category'])`

2.2 WHEN a quiz with category "algebra" is displayed in the educator quiz listing table (educ-quizes.php) THEN the system SHALL display the formatted label "Algebra" by calling `getCategoryLabel($quiz['subject_name'], $quiz['category'])`

2.3 WHEN any quiz with a category key is displayed in either admin or educator quiz listing tables THEN the system SHALL convert the database KEY to its corresponding LABEL using the `getCategoryLabel()` function from category-config.php

2.4 WHEN a quiz has an empty or null category value THEN the system SHALL continue to display "Not Set" as it currently does

2.5 WHEN a quiz has a category key that is not found in the category configuration THEN the system SHALL fall back to displaying the raw key value to prevent display errors

### Unchanged Behavior (Regression Prevention)

3.1 WHEN quizzes are stored in the database THEN the system SHALL CONTINUE TO store category KEYS (e.g., "grammar", "algebra") not LABELS

3.2 WHEN quizzes are queried from the database THEN the system SHALL CONTINUE TO use category KEYS in WHERE clauses and filters

3.3 WHEN the "Not Set" badge is displayed for quizzes without categories THEN the system SHALL CONTINUE TO display this badge unchanged

3.4 WHEN quiz data is passed to edit/delete modals THEN the system SHALL CONTINUE TO pass the raw category KEY value in the JSON data attribute

3.5 WHEN category dropdowns are populated in add/edit forms THEN the system SHALL CONTINUE TO function as they currently do (already using the correct KEY → LABEL mapping)

3.6 WHEN the category-config.php file is loaded THEN the system SHALL CONTINUE TO provide the `getCategoryLabel()` function without modification

3.7 WHEN quizzes are filtered, sorted, or counted THEN the system SHALL CONTINUE TO use the existing logic without changes
