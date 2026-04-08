# Category System Guide: KEY vs LABEL Architecture

## 🎯 Overview

This guide explains the proper implementation of the category system for the Play2Review quiz app. The system uses a **KEY → LABEL** architecture to separate database storage from UI display.

---

## 📋 Core Concepts

### KEY (Database/API)
- **Purpose**: Stored in database, used in API calls, used for validation
- **Format**: Lowercase, underscore-separated (e.g., `grammar`, `word_problems`)
- **Examples**: `algebra`, `reading`, `kasaysayan`, `earth_science`
- **Never changes**: Keys remain constant even if labels are updated

### LABEL (UI Display)
- **Purpose**: Shown to users in dropdowns, buttons, and UI elements
- **Format**: Human-readable, properly formatted (e.g., `Grammar & Language Structure`)
- **Examples**: `Algebra`, `Reading Comprehension`, `Kasaysayan ng Pilipinas`
- **Can change**: Labels can be updated for clarity or translation

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                         UNITY (C#)                          │
├─────────────────────────────────────────────────────────────┤
│  Dictionary<string, Dictionary<string, string>>             │
│  ┌──────────┬─────────────────────────────────────────┐    │
│  │ Subject  │ Categories (KEY → LABEL)                │    │
│  ├──────────┼─────────────────────────────────────────┤    │
│  │ english  │ "grammar" → "Grammar & Language..."     │    │
│  │          │ "vocabulary" → "Vocabulary"             │    │
│  │ math     │ "algebra" → "Algebra"                   │    │
│  │          │ "geometry" → "Geometry"                 │    │
│  └──────────┴─────────────────────────────────────────┘    │
│                                                             │
│  UI Dropdown: Shows LABELS                                 │
│  API Calls: Sends KEYS                                     │
│  PlayerPrefs: Stores KEYS                                  │
└─────────────────────────────────────────────────────────────┘
                            ↓ API Call
                    (category: "grammar")
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                         PHP BACKEND                         │
├─────────────────────────────────────────────────────────────┤
│  $CATEGORY_CONFIG = [                                       │
│    'english' => [                                           │
│      'grammar' => 'Grammar & Language Structure',           │
│      'vocabulary' => 'Vocabulary'                           │
│    ]                                                        │
│  ]                                                          │
│                                                             │
│  Validation: isValidCategory($subject, $key)               │
│  Conversion: getCategoryLabel($subject, $key)              │
└─────────────────────────────────────────────────────────────┘
                            ↓
                    Stores KEY in DB
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                      MYSQL DATABASE                         │
├─────────────────────────────────────────────────────────────┤
│  quizes table:                                              │
│  ┌────┬─────────┬──────────┬──────────────┐               │
│  │ id │ subject │ category │ question     │               │
│  ├────┼─────────┼──────────┼──────────────┤               │
│  │ 1  │ english │ grammar  │ What is...   │  ✅ KEY       │
│  │ 2  │ math    │ algebra  │ Solve for... │  ✅ KEY       │
│  └────┴─────────┴──────────┴──────────────┘               │
│                                                             │
│  ❌ NEVER store: "Grammar & Language Structure"            │
│  ✅ ALWAYS store: "grammar"                                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔧 Implementation

### 1. Unity C# - CategorySelectionManager.cs

```csharp
// ✅ Correct: Dictionary<string, Dictionary<string, string>>
private Dictionary<string, Dictionary<string, string>> subjectCategories;

// Initialize with KEY → LABEL mapping
subjectCategories = new Dictionary<string, Dictionary<string, string>>
{
    ["english"] = new Dictionary<string, string>
    {
        { "grammar", "Grammar & Language Structure" },
        { "vocabulary", "Vocabulary" }
    }
};

// Display LABEL in UI, but track KEY internally
foreach (var kvp in subjectCategories[subject])
{
    string key = kvp.Key;      // "grammar"
    string label = kvp.Value;  // "Grammar & Language Structure"
    
    // Button shows LABEL
    button.text = label;
    
    // Button click sends KEY
    button.onClick.AddListener(() => OnCategorySelected(key));
}

// Store KEY in PlayerPrefs
PlayerPrefs.SetString("SelectedCategory", categoryKey); // ✅ "grammar"
```

### 2. PHP Backend - category-config.php

```php
// ✅ Correct: KEY => LABEL mapping
$CATEGORY_CONFIG = [
    'english' => [
        'grammar' => 'Grammar & Language Structure',
        'vocabulary' => 'Vocabulary'
    ]
];

// Validate using KEY
function isValidCategory(string $subject, string $categoryKey): bool {
    global $CATEGORY_CONFIG;
    return isset($CATEGORY_CONFIG[$subject][$categoryKey]);
}

// Get LABEL from KEY
function getCategoryLabel(string $subject, string $categoryKey): ?string {
    global $CATEGORY_CONFIG;
    return $CATEGORY_CONFIG[$subject][$categoryKey] ?? null;
}
```

### 3. API Endpoint - get_quiz_questions_by_category.php

```php
// Receive KEY from Unity
$categoryKey = $_POST['category'] ?? ''; // ✅ "grammar"

// Validate KEY
if (!isValidCategory($subject_name, $categoryKey)) {
    throw new Exception("Invalid category key");
}

// Query database using KEY
$sql = "SELECT * FROM quizes 
        WHERE subject_name = :subject 
        AND category = :category"; // ✅ Uses KEY

// Return both KEY and LABEL in response
$response->category_key = $categoryKey;
$response->category_label = getCategoryLabel($subject_name, $categoryKey);
```

---

## 📝 Usage Examples

### Example 1: Populate Dropdown

```csharp
// Unity C#
public void PopulateDropdown(string subject)
{
    dropdown.ClearOptions();
    
    // Get labels for display
    List<string> labels = categoryManager.GetCategoryLabels(subject);
    dropdown.AddOptions(labels);
    
    // When user selects, convert label to key
    dropdown.onValueChanged.AddListener(index => {
        string selectedLabel = dropdown.options[index].text;
        string selectedKey = categoryManager.GetCategoryKey(subject, selectedLabel);
        
        // Use KEY for everything else
        SendToBackend(selectedKey);
    });
}
```

### Example 2: Submit to Backend

```csharp
// Unity C#
IEnumerator SubmitQuiz(string subject, string categoryKey)
{
    WWWForm form = new WWWForm();
    form.AddField("subject_name", subject);
    form.AddField("category", categoryKey); // ✅ Send KEY
    
    using (UnityWebRequest www = UnityWebRequest.Post(url, form))
    {
        yield return www.SendWebRequest();
        // Handle response
    }
}
```

### Example 3: Save to Database

```php
// PHP
$categoryKey = $_POST['category']; // ✅ Receive KEY

// Validate
if (!isValidCategory($subject, $categoryKey)) {
    die("Invalid category");
}

// Save to database
$sql = "INSERT INTO quiz_progress (user_id, subject, category, score) 
        VALUES (:user_id, :subject, :category, :score)";

$stmt->bindParam(':category', $categoryKey); // ✅ Store KEY
```

---

## 🚀 Benefits

### 1. **Easy Label Updates**
```php
// Change label without database migration
'grammar' => 'Grammar & Language Structure' // Old
'grammar' => 'English Grammar Basics'       // New ✅
// Database still uses 'grammar' key - no migration needed!
```

### 2. **Multi-Language Support**
```php
// English version
'grammar' => 'Grammar & Language Structure'

// Filipino version (future)
'grammar' => 'Gramatika at Istruktura ng Wika'

// Same KEY, different LABEL!
```

### 3. **Consistent Analytics**
```sql
-- All queries use consistent keys
SELECT category, COUNT(*) 
FROM quiz_progress 
WHERE category = 'grammar' -- ✅ Always works
GROUP BY category;
```

### 4. **Smaller Database**
```
❌ Storing labels: "Grammar & Language Structure" (32 bytes)
✅ Storing keys: "grammar" (7 bytes)
= 78% storage reduction!
```

---

## ⚠️ Common Mistakes

### ❌ DON'T: Store labels in database
```php
$category = "Grammar & Language Structure"; // ❌ WRONG
$sql = "INSERT INTO quizes (category) VALUES (:category)";
```

### ✅ DO: Store keys in database
```php
$categoryKey = "grammar"; // ✅ CORRECT
$sql = "INSERT INTO quizes (category) VALUES (:category)";
```

### ❌ DON'T: Send labels from Unity
```csharp
form.AddField("category", "Grammar & Language Structure"); // ❌ WRONG
```

### ✅ DO: Send keys from Unity
```csharp
form.AddField("category", "grammar"); // ✅ CORRECT
```

### ❌ DON'T: Validate using labels
```php
if ($category == "Grammar & Language Structure") // ❌ WRONG
```

### ✅ DO: Validate using keys
```php
if (isValidCategory($subject, $categoryKey)) // ✅ CORRECT
```

---

## 🔄 Migration Guide

If you have existing data with labels, run the migration script:

```bash
# 1. Backup your database
mysqldump -u username -p database_name > backup.sql

# 2. Run migration script
http://yourserver.com/admin/migrate-labels-to-keys.php?confirm=yes

# 3. Verify all categories are now keys
```

---

## 📊 Database Schema

### Recommended Schema

```sql
CREATE TABLE quizes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT NOT NULL,
    subject_name VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL,  -- ✅ Stores KEY
    level INT NOT NULL,
    question TEXT NOT NULL,
    answer_a VARCHAR(255),
    answer_b VARCHAR(255),
    answer_c VARCHAR(255),
    answer_d VARCHAR(255),
    correct_answer_number INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_subject_category (subject_name, category),
    INDEX idx_level (level)
);

CREATE TABLE quiz_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    subject VARCHAR(50) NOT NULL,
    category VARCHAR(50) NOT NULL,  -- ✅ Stores KEY
    score INT NOT NULL,
    xp_earned INT DEFAULT 0,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_subject (user_id, subject),
    INDEX idx_category (category)
);
```

---

## 🎮 Gamification Support

The KEY-based system makes gamification features easy:

### XP Tracking
```sql
SELECT category, SUM(xp_earned) as total_xp
FROM quiz_progress
WHERE user_id = ? AND subject = ?
GROUP BY category;
```

### Category Mastery Badges
```php
$categoryXP = getUserCategoryXP($userId, $subject, $categoryKey);
if ($categoryXP >= 1000) {
    awardBadge($userId, "master_" . $categoryKey);
}
```

### Analytics
```sql
-- Most popular categories
SELECT category, COUNT(*) as attempts
FROM quiz_progress
GROUP BY category
ORDER BY attempts DESC;

-- Category difficulty (by average score)
SELECT category, AVG(score) as avg_score
FROM quiz_progress
GROUP BY category
ORDER BY avg_score ASC;
```

---

## 🧪 Testing Checklist

- [ ] Dropdown shows labels correctly
- [ ] Clicking category sends key to backend
- [ ] Database stores keys (not labels)
- [ ] API validates keys properly
- [ ] Quiz questions load correctly
- [ ] Progress tracking works
- [ ] Analytics queries return correct data
- [ ] Labels can be changed without breaking system

---

## 📚 File Reference

| File | Purpose |
|------|---------|
| `CategorySelectionManager.cs` | Unity category selection with KEY→LABEL mapping |
| `category-config.php` | PHP configuration with validation functions |
| `get_quiz_questions_by_category.php` | API endpoint with KEY validation |
| `CategorySystemExample.cs` | Complete Unity integration example |
| `category-api-example.php` | Complete PHP API examples |
| `migrate-labels-to-keys.php` | Database migration script |

---

## 🆘 Troubleshooting

### Problem: "Invalid category key" error
**Solution**: Check that Unity is sending the KEY, not the LABEL
```csharp
// ❌ Wrong
form.AddField("category", "Grammar & Language Structure");

// ✅ Correct
form.AddField("category", "grammar");
```

### Problem: No quiz questions returned
**Solution**: Verify database has keys, not labels
```sql
-- Check current values
SELECT DISTINCT category FROM quizes WHERE subject_name = 'english';

-- Should return: grammar, vocabulary, reading
-- NOT: Grammar & Language Structure, Vocabulary, Reading Comprehension
```

### Problem: Dropdown shows keys instead of labels
**Solution**: Use GetCategoryLabels() not GetCategoryKeys()
```csharp
// ❌ Wrong
List<string> options = categoryManager.GetCategoryKeys(subject);

// ✅ Correct
List<string> options = categoryManager.GetCategoryLabels(subject);
```

---

## 📞 Support

For questions or issues with the category system:
1. Check this guide first
2. Review the example files
3. Verify your database has keys (not labels)
4. Test with the provided example scripts

---

**Last Updated**: 2026-04-06
**Version**: 1.0.0
