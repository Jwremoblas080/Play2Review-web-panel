# Category System Quick Reference Card

## 🎯 Golden Rule
**Database stores KEYS. UI displays LABELS.**

---

## 📋 Quick Lookup

### All Category Keys

#### English
- `grammar` → Grammar & Language Structure
- `vocabulary` → Vocabulary
- `reading` → Reading Comprehension
- `literature` → Literature
- `writing` → Writing Skills

#### Math
- `algebra` → Algebra
- `geometry` → Geometry
- `statistics` → Statistics
- `probability` → Probability
- `functions` → Functions & Equations
- `word_problems` → Word Problems

#### Filipino
- `gramatika` → Gramatika
- `panitikan` → Panitikan
- `pag_unawa` → Pag-unawa sa Binasa
- `talasalitaan` → Talasalitaan
- `wika` → Wika at Kultura

#### Araling Panlipunan (AP)
- `ekonomiks` → Ekonomiks
- `kasaysayan` → Kasaysayan ng Pilipinas
- `kontemporaryo` → Kontemporaryong Isyu
- `heograpiya` → Heograpiya
- `pamahalaan` → Pamahalaan at Lipunan

#### Science
- `biology` → Biology
- `chemistry` → Chemistry
- `physics` → Physics
- `earth_science` → Earth Science
- `investigation` → Scientific Investigation

---

## 🔧 Unity C# Cheat Sheet

```csharp
// Get label for display
string label = categoryManager.GetCategoryLabel("english", "grammar");
// Returns: "Grammar & Language Structure"

// Get key from label (reverse lookup)
string key = categoryManager.GetCategoryKey("english", "Grammar & Language Structure");
// Returns: "grammar"

// Get all keys for a subject
List<string> keys = categoryManager.GetCategoryKeys("math");
// Returns: ["algebra", "geometry", "statistics", ...]

// Get all labels for a subject (for dropdown)
List<string> labels = categoryManager.GetCategoryLabels("math");
// Returns: ["Algebra", "Geometry", "Statistics", ...]

// Store key in PlayerPrefs
PlayerPrefs.SetString("SelectedCategory", "grammar"); // ✅ KEY

// Send key to backend
form.AddField("category", "grammar"); // ✅ KEY
```

---

## 🐘 PHP Cheat Sheet

```php
// Validate category key
if (isValidCategory("english", "grammar")) {
    // Valid!
}

// Get label from key
$label = getCategoryLabel("english", "grammar");
// Returns: "Grammar & Language Structure"

// Get key from label (reverse)
$key = getCategoryKeyFromLabel("english", "Grammar & Language Structure");
// Returns: "grammar"

// Get all categories for subject
$categories = getCategoriesBySubject("math");
// Returns: ["algebra" => "Algebra", "geometry" => "Geometry", ...]

// Get only keys
$keys = getCategoryKeys("math");
// Returns: ["algebra", "geometry", "statistics", ...]

// Get only labels
$labels = getCategoryLabels("math");
// Returns: ["Algebra", "Geometry", "Statistics", ...]
```

---

## 🗄️ SQL Cheat Sheet

```sql
-- ✅ CORRECT: Query using KEY
SELECT * FROM quizes 
WHERE subject_name = 'english' 
AND category = 'grammar';

-- ✅ CORRECT: Insert using KEY
INSERT INTO quizes (subject_name, category, question) 
VALUES ('math', 'algebra', 'Solve for x...');

-- ✅ CORRECT: Analytics by KEY
SELECT category, COUNT(*) as total
FROM quiz_progress
WHERE subject = 'science'
GROUP BY category;

-- ❌ WRONG: Never use labels in queries
SELECT * FROM quizes 
WHERE category = 'Grammar & Language Structure'; -- DON'T DO THIS!
```

---

## 🔄 Common Workflows

### Workflow 1: User Selects Category

```
1. User clicks dropdown → sees LABELS
2. User selects "Grammar & Language Structure"
3. Unity converts to KEY: "grammar"
4. Unity stores KEY in PlayerPrefs
5. Unity sends KEY to backend
6. Backend validates KEY
7. Backend queries database using KEY
8. Backend returns KEY + LABEL in response
```

### Workflow 2: Load User Progress

```
1. Backend queries database → gets KEY "grammar"
2. Backend converts KEY to LABEL for response
3. Unity receives both KEY and LABEL
4. Unity displays LABEL in UI
5. Unity stores KEY for next API call
```

### Workflow 3: Add New Question (Admin)

```
1. Admin selects category from dropdown → sees LABELS
2. Form converts LABEL to KEY before submit
3. Backend receives KEY "algebra"
4. Backend validates KEY
5. Backend stores KEY in database
6. Success response includes both KEY and LABEL
```

---

## ⚡ Quick Fixes

### Fix 1: Dropdown shows keys instead of labels
```csharp
// ❌ Wrong
dropdown.AddOptions(categoryManager.GetCategoryKeys(subject));

// ✅ Correct
dropdown.AddOptions(categoryManager.GetCategoryLabels(subject));
```

### Fix 2: Backend receives label instead of key
```csharp
// ❌ Wrong
string selectedLabel = dropdown.options[dropdown.value].text;
form.AddField("category", selectedLabel);

// ✅ Correct
string selectedLabel = dropdown.options[dropdown.value].text;
string selectedKey = categoryManager.GetCategoryKey(subject, selectedLabel);
form.AddField("category", selectedKey);
```

### Fix 3: Database has labels instead of keys
```php
// Run migration script
http://yourserver.com/admin/migrate-labels-to-keys.php?confirm=yes
```

---

## 🎨 Visual Guide

```
┌─────────────────────────────────────────────────────────┐
│                    USER INTERFACE                       │
│  Dropdown: [Grammar & Language Structure ▼]  ← LABEL   │
└─────────────────────────────────────────────────────────┘
                         ↓
                    User Selects
                         ↓
┌─────────────────────────────────────────────────────────┐
│                    UNITY C# CODE                        │
│  selectedKey = "grammar"  ← KEY                         │
│  PlayerPrefs.SetString("SelectedCategory", "grammar")   │
└─────────────────────────────────────────────────────────┘
                         ↓
                    API Call
                         ↓
┌─────────────────────────────────────────────────────────┐
│                    PHP BACKEND                          │
│  $_POST['category'] = "grammar"  ← KEY                  │
│  isValidCategory("english", "grammar") → true           │
└─────────────────────────────────────────────────────────┘
                         ↓
                   Database Query
                         ↓
┌─────────────────────────────────────────────────────────┐
│                    MYSQL DATABASE                       │
│  WHERE category = 'grammar'  ← KEY                      │
└─────────────────────────────────────────────────────────┘
```

---

## 🚨 Critical Rules

1. **NEVER** store labels in database
2. **ALWAYS** validate using keys
3. **ALWAYS** send keys in API calls
4. **ALWAYS** display labels in UI
5. **ALWAYS** use helper functions for conversion

---

## 📞 Need Help?

1. Check `CATEGORY_SYSTEM_GUIDE.md` for detailed explanation
2. Review `CategorySystemExample.cs` for Unity examples
3. Review `category-api-example.php` for PHP examples
4. Run diagnostics if something breaks

---

**Print this and keep it at your desk!** 📌
