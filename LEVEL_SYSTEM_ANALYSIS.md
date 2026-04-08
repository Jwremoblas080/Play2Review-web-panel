# Level System Analysis: Category Key Integration

## ✅ Current Status

### What's Working Correctly

Your level update/load system **IS ALREADY USING CATEGORY KEYS** properly! Here's the flow:

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. USER SELECTS CATEGORY                                        │
├─────────────────────────────────────────────────────────────────┤
│ CategorySelectionManager.cs                                     │
│ • User clicks "Grammar & Language Structure" (LABEL)           │
│ • System stores "grammar" (KEY) in PlayerPrefs                 │
│                                                                 │
│ PlayerPrefs.SetString("SelectedCategory", "grammar"); ✅ KEY   │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. LOAD CATEGORY LEVEL FROM DATABASE                           │
├─────────────────────────────────────────────────────────────────┤
│ DatabaseManager.cs → LoadEnglishCategoryLevel()                │
│                                                                 │
│ string category = PlayerPrefs.GetString("SelectedCategory");   │
│ // category = "grammar" ✅ KEY                                 │
│                                                                 │
│ POST to: load_english_level.php                                │
│ • Loads ALL category levels from database                      │
│ • Returns: english_grammar_level, english_vocabulary_level...  │
│                                                                 │
│ GetCategoryLevel(data, "grammar"):                             │
│   switch (category) {                                          │
│     case "grammar": return data.english_grammar_level; ✅      │
│   }                                                            │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. USER COMPLETES LEVEL                                         │
├─────────────────────────────────────────────────────────────────┤
│ LessonProgressTracker.cs                                        │
│ • User completes level 5                                       │
│ • Calls: DatabaseManager.UpdateEnglishCategoryLevel(5)         │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 4. SAVE CATEGORY LEVEL TO DATABASE                             │
├─────────────────────────────────────────────────────────────────┤
│ DatabaseManager.cs → UpdateEnglishCategoryLevel(5)             │
│                                                                 │
│ string category = PlayerPrefs.GetString("SelectedCategory");   │
│ // category = "grammar" ✅ KEY                                 │
│                                                                 │
│ WWWForm form = new WWWForm();                                  │
│ form.AddField("username", username);                           │
│ form.AddField("english_grammar_level", 5); ✅ Uses KEY         │
│                                                                 │
│ POST to: update_english_level.php                              │
└─────────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────────┐
│ 5. PHP UPDATES DATABASE                                         │
├─────────────────────────────────────────────────────────────────┤
│ update_english_level.php                                        │
│                                                                 │
│ $english_grammar_level = $_POST['english_grammar_level'];      │
│                                                                 │
│ UPDATE users SET                                               │
│   english_grammar_level = GREATEST(english_grammar_level, 5)   │
│ WHERE username = 'user123';                                    │
│                                                                 │
│ ✅ Database column uses KEY-based naming                       │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🎯 Database Schema

Your `users` table has columns named with KEYS:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    username VARCHAR(50),
    
    -- English category levels (using KEYS)
    english_grammar_level INT DEFAULT 0,      ✅ KEY: grammar
    english_vocabulary_level INT DEFAULT 0,   ✅ KEY: vocabulary
    english_reading_level INT DEFAULT 0,      ✅ KEY: reading
    english_literature_level INT DEFAULT 0,   ✅ KEY: literature
    english_writing_level INT DEFAULT 0,      ✅ KEY: writing
    
    -- Math category levels (using KEYS)
    math_algebra_level INT DEFAULT 0,         ✅ KEY: algebra
    math_geometry_level INT DEFAULT 0,        ✅ KEY: geometry
    math_statistics_level INT DEFAULT 0,      ✅ KEY: statistics
    math_probability_level INT DEFAULT 0,     ✅ KEY: probability
    math_functions_level INT DEFAULT 0,       ✅ KEY: functions
    math_word_problems_level INT DEFAULT 0,   ✅ KEY: word_problems
    
    -- Filipino category levels (using KEYS)
    filipino_gramatika_level INT DEFAULT 0,   ✅ KEY: gramatika
    filipino_panitikan_level INT DEFAULT 0,   ✅ KEY: panitikan
    filipino_pag_unawa_level INT DEFAULT 0,   ✅ KEY: pag_unawa
    filipino_talasalitaan_level INT DEFAULT 0,✅ KEY: talasalitaan
    filipino_wika_level INT DEFAULT 0,        ✅ KEY: wika
    
    -- AP category levels (using KEYS)
    ap_ekonomiks_level INT DEFAULT 0,         ✅ KEY: ekonomiks
    ap_kasaysayan_level INT DEFAULT 0,        ✅ KEY: kasaysayan
    ap_kontemporaryo_level INT DEFAULT 0,     ✅ KEY: kontemporaryo
    ap_heograpiya_level INT DEFAULT 0,        ✅ KEY: heograpiya
    ap_pamahalaan_level INT DEFAULT 0,        ✅ KEY: pamahalaan
    
    -- Science category levels (using KEYS)
    science_biology_level INT DEFAULT 0,      ✅ KEY: biology
    science_chemistry_level INT DEFAULT 0,    ✅ KEY: chemistry
    science_physics_level INT DEFAULT 0,      ✅ KEY: physics
    science_earth_science_level INT DEFAULT 0,✅ KEY: earth_science
    science_investigation_level INT DEFAULT 0 ✅ KEY: investigation
);
```

---

## ✅ What's Already Correct

### 1. Unity C# (DatabaseManager.cs)
```csharp
// ✅ Gets KEY from PlayerPrefs
string category = PlayerPrefs.GetString("SelectedCategory", "");
// category = "grammar" (KEY, not label)

// ✅ Builds field name using KEY
form.AddField($"english_{category}_level", level);
// Sends: "english_grammar_level" = 5

// ✅ Reads level using KEY
switch (category) {
    case "grammar": return data.english_grammar_level; ✅
    case "vocabulary": return data.english_vocabulary_level; ✅
}
```

### 2. PHP Backend (update_english_level.php)
```php
// ✅ Receives KEY-based field names
$english_grammar_level = $_POST['english_grammar_level'] ?? 0;
$english_vocabulary_level = $_POST['english_vocabulary_level'] ?? 0;

// ✅ Updates database using KEY-based column names
UPDATE users SET
    english_grammar_level = GREATEST(english_grammar_level, :grammar),
    english_vocabulary_level = GREATEST(english_vocabulary_level, :vocabulary)
WHERE username = :username
```

### 3. Database Columns
```
✅ english_grammar_level (not english_grammar_&_language_structure_level)
✅ math_algebra_level (not math_algebra_level)
✅ filipino_gramatika_level (not filipino_gramatika_level)
```

---

## 🔍 Code Flow Verification

### Scenario: User Completes Grammar Level 5

```
1. User selects "Grammar & Language Structure" from dropdown
   → CategorySelectionManager stores KEY: "grammar"
   → PlayerPrefs.SetString("SelectedCategory", "grammar")

2. Scene loads, DatabaseManager.LoadEnglishCategoryLevel() runs
   → Reads category KEY: "grammar"
   → POST to load_english_level.php
   → PHP returns: { english_grammar_level: 3, ... }
   → Unity extracts: GetCategoryLevel(data, "grammar") → 3
   → LessonProgressTracker loads level 3

3. User plays and completes level 5
   → LessonProgressTracker.OnLevelComplete(5)
   → Calls: DatabaseManager.UpdateEnglishCategoryLevel(5)

4. DatabaseManager.UpdateEnglishCategoryLevel(5)
   → Reads category KEY: "grammar"
   → form.AddField("english_grammar_level", 5)
   → POST to update_english_level.php

5. PHP receives and updates
   → $english_grammar_level = 5
   → UPDATE users SET english_grammar_level = GREATEST(3, 5)
   → Database now has: english_grammar_level = 5 ✅
```

---

## 🎯 Integration with Category System

Your level system **perfectly integrates** with the category KEY system:

```
┌──────────────────────────────────────────────────────────────┐
│ CATEGORY SELECTION (CategorySelectionManager.cs)            │
├──────────────────────────────────────────────────────────────┤
│ Dictionary<string, Dictionary<string, string>>              │
│ ["english"]["grammar"] = "Grammar & Language Structure"     │
│                                                             │
│ User clicks button → Stores KEY                            │
│ PlayerPrefs.SetString("SelectedCategory", "grammar") ✅     │
└──────────────────────────────────────────────────────────────┘
                            ↓
┌──────────────────────────────────────────────────────────────┐
│ LEVEL MANAGEMENT (DatabaseManager.cs)                       │
├──────────────────────────────────────────────────────────────┤
│ Reads KEY from PlayerPrefs                                  │
│ string category = PlayerPrefs.GetString("SelectedCategory") │
│ // category = "grammar" ✅                                  │
│                                                             │
│ Builds database field name                                 │
│ $"english_{category}_level" → "english_grammar_level" ✅   │
└──────────────────────────────────────────────────────────────┘
                            ↓
┌──────────────────────────────────────────────────────────────┐
│ DATABASE (users table)                                       │
├──────────────────────────────────────────────────────────────┤
│ Column: english_grammar_level ✅                            │
│ Value: 5                                                    │
└──────────────────────────────────────────────────────────────┘
```

---

## ⚠️ Potential Issues (Minor)

### Issue 1: Hardcoded Column Names in PHP

**Current Code (update_english_level.php):**
```php
$english_grammar_level = $_POST['english_grammar_level'] ?? 0;
$english_vocabulary_level = $_POST['english_vocabulary_level'] ?? 0;
// ... hardcoded for each category
```

**Problem:** If you add a new category, you must manually update PHP code.

**Solution:** Make it dynamic (optional improvement):

```php
// Get all POST fields that match pattern: subject_category_level
$updates = [];
$validCategories = ['grammar', 'vocabulary', 'reading', 'literature', 'writing'];

foreach ($validCategories as $category) {
    $fieldName = "english_{$category}_level";
    if (isset($_POST[$fieldName])) {
        $updates[$fieldName] = (int)$_POST[$fieldName];
    }
}

// Build dynamic UPDATE query
if (!empty($updates)) {
    $setParts = [];
    foreach ($updates as $field => $value) {
        $setParts[] = "$field = GREATEST($field, :$field)";
    }
    
    $sql = "UPDATE users SET " . implode(', ', $setParts) . " WHERE username = :username";
    // ... bind params and execute
}
```

### Issue 2: No Validation of Category Keys

**Current Code (DatabaseManager.cs):**
```csharp
string category = PlayerPrefs.GetString("SelectedCategory", "");
form.AddField($"english_{category}_level", level);
```

**Problem:** If category is invalid (e.g., "invalid_key"), it sends "english_invalid_key_level" which doesn't exist in database.

**Solution:** Add validation:

```csharp
private bool IsValidCategory(string category)
{
    string[] validCategories = { "grammar", "vocabulary", "reading", "literature", "writing" };
    return System.Array.Exists(validCategories, c => c == category);
}

private IEnumerator UpdateCoroutine(int level)
{
    string category = Normalize(PlayerPrefs.GetString("SelectedCategory", ""));
    
    if (!IsValidCategory(category))
    {
        Debug.LogError($"Invalid category: {category}");
        yield break;
    }
    
    // ... rest of code
}
```

---

## 🎉 Conclusion

### ✅ Your Level System is CORRECT!

1. **Uses KEYS everywhere** - No labels in database or API calls
2. **Proper integration** - Works seamlessly with CategorySelectionManager
3. **Database schema** - Column names use KEYS
4. **PHP endpoints** - Accept and update KEY-based fields
5. **Unity scripts** - Read/write using KEYS

### 📊 Data Flow Summary

```
UI (LABEL) → PlayerPrefs (KEY) → Unity API Call (KEY) → PHP (KEY) → Database (KEY)

"Grammar & Language Structure" → "grammar" → "english_grammar_level" → Column: english_grammar_level
```

### 🚀 No Changes Needed!

Your level update/load system **already follows the KEY-based architecture**. The fixes we made to CategorySelectionManager ensure that:

1. User sees LABELS in UI ✅
2. System stores KEYS in PlayerPrefs ✅
3. DatabaseManager reads KEYS ✅
4. PHP receives KEY-based field names ✅
5. Database stores levels in KEY-based columns ✅

Everything is working correctly! 🎊

---

## 📝 Optional Improvements

If you want to make the system even more robust:

1. **Add category validation** in DatabaseManager
2. **Make PHP endpoints dynamic** to handle new categories automatically
3. **Add error handling** for invalid category keys
4. **Log category operations** for debugging

But these are enhancements, not fixes. Your current system is solid!
