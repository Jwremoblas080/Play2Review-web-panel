# Dynamic Category System - Feasibility Analysis & Implementation Guide

**Author**: Senior Web & Game Developer  
**Date**: May 20, 2026  
**Project**: Play2Review Educational Game System  
**Status**: ✅ **FEASIBLE** - Requires Moderate Refactoring

---

## Executive Summary

**YES, it is absolutely possible to make the category system dynamic!** 

Currently, your system uses **hardcoded categories** in both PHP and C# code. Converting to a **dynamic, database-driven category system** will allow administrators to add, edit, and remove categories without modifying code.

**Complexity Level**: ⭐⭐⭐ Moderate (3/5)  
**Estimated Development Time**: 40-60 hours  
**Risk Level**: Low (with proper testing)  
**Benefits**: High (future-proof, scalable, maintainable)

---

## Current System Architecture

### 🔴 **HARDCODED APPROACH** (Current State)

#### PHP Side (`category-config.php`)
```php
$CATEGORY_CONFIG = [
    'english' => [
        'grammar' => 'Grammar & Language Structure',
        'vocabulary' => 'Vocabulary',
        // ... hardcoded categories
    ],
    'math' => [
        'algebra' => 'Algebra',
        // ... hardcoded categories
    ]
];
```

#### Unity C# Side (`CategorySelectionManager.cs`)
```csharp
private void InitializeCategories()
{
    subjectCategories = new Dictionary<string, Dictionary<string, string>>
    {
        ["english"] = new Dictionary<string, string>
        {
            { "grammar", "Grammar & Language Structure" },
            { "vocabulary", "Vocabulary" },
            // ... hardcoded categories
        }
    };
}
```

#### Database Side
```sql
-- User progress table has FIXED columns for each category
users table:
  - english_grammar_level INT
  - english_vocabulary_level INT
  - english_reading_level INT
  - math_algebra_level INT
  - math_geometry_level INT
  -- ... 30+ hardcoded columns
```

---

## 🎯 Proposed Dynamic System Architecture

### ✅ **DATABASE-DRIVEN APPROACH** (Target State)

### 1. Database Schema Changes

#### **NEW TABLE: `categories`**
```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(50) NOT NULL,
    category_key VARCHAR(50) NOT NULL,
    category_label VARCHAR(100) NOT NULL,
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    icon_name VARCHAR(50) NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_subject_category (subject_name, category_key),
    INDEX idx_subject (subject_name),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### **Sample Data**
```sql
INSERT INTO categories (subject_name, category_key, category_label, display_order) VALUES
('english', 'grammar', 'Grammar & Language Structure', 1),
('english', 'vocabulary', 'Vocabulary', 2),
('english', 'reading', 'Reading Comprehension', 3),
('english', 'literature', 'Literature', 4),
('english', 'writing', 'Writing Skills', 5),
('math', 'algebra', 'Algebra', 1),
('math', 'geometry', 'Geometry', 2),
('math', 'statistics', 'Statistics', 3),
('math', 'probability', 'Probability', 4),
('math', 'functions', 'Functions & Equations', 5),
('math', 'word_problems', 'Word Problems', 6);
-- ... continue for filipino, ap, science
```

#### **NEW TABLE: `user_category_progress`**
```sql
CREATE TABLE user_category_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_name VARCHAR(50) NOT NULL,
    category_key VARCHAR(50) NOT NULL,
    current_level INT DEFAULT 0,
    max_level INT DEFAULT 10,
    completed_lessons INT DEFAULT 0,
    completed_quizzes INT DEFAULT 0,
    last_played_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_progress (user_id, subject_name, category_key),
    INDEX idx_user_subject (user_id, subject_name),
    INDEX idx_category (subject_name, category_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### **MIGRATION: Existing `users` Table**
```sql
-- Option 1: Keep old columns for backward compatibility (RECOMMENDED)
-- Add migration script to copy data to new table

-- Option 2: Drop old columns (RISKY - requires full data migration)
ALTER TABLE users 
DROP COLUMN english_grammar_level,
DROP COLUMN english_vocabulary_level,
-- ... drop all category columns
```

---

### 2. PHP Backend Changes

#### **NEW FILE: `admin/manage-categories.php`**
```php
<?php
// CRUD interface for managing categories
// Features:
// - Add new category
// - Edit category (key, label, order)
// - Delete category (with safety checks)
// - Reorder categories (drag & drop)
// - Toggle active/inactive
// - Bulk operations
?>
```

#### **UPDATED: `admin/category-config.php`**
```php
<?php
/**
 * Dynamic Category Configuration
 * Loads categories from database instead of hardcoded array
 */

// Cache categories in session to reduce database queries
function getCategoriesBySubject(string $subject): array {
    global $con;
    
    // Check cache first
    $cacheKey = "categories_{$subject}";
    if (isset($_SESSION[$cacheKey])) {
        return $_SESSION[$cacheKey];
    }
    
    // Load from database
    $query = "SELECT category_key, category_label 
              FROM categories 
              WHERE subject_name = ? AND is_active = 1 
              ORDER BY display_order ASC";
    
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $subject);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[$row['category_key']] = $row['category_label'];
    }
    
    // Cache for this session
    $_SESSION[$cacheKey] = $categories;
    
    return $categories;
}

// Clear cache when categories are modified
function clearCategoryCache() {
    $subjects = ['english', 'math', 'filipino', 'ap', 'science'];
    foreach ($subjects as $subject) {
        unset($_SESSION["categories_{$subject}"]);
    }
}
?>
```

#### **NEW FILE: `api/get_categories.php`**
```php
<?php
/**
 * API endpoint for Unity to fetch categories dynamically
 * Returns JSON with all active categories
 */
require_once('../configurations/configurations.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$subject = $_GET['subject'] ?? '';

if (empty($subject)) {
    echo json_encode(['success' => false, 'message' => 'Subject required']);
    exit;
}

$query = "SELECT category_key, category_label, icon_name, description 
          FROM categories 
          WHERE subject_name = ? AND is_active = 1 
          ORDER BY display_order ASC";

$stmt = $conn->prepare($query);
$stmt->bindParam(1, $subject);
$stmt->execute();

$categories = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $categories[] = [
        'key' => $row['category_key'],
        'label' => $row['category_label'],
        'icon' => $row['icon_name'],
        'description' => $row['description']
    ];
}

echo json_encode([
    'success' => true,
    'subject' => $subject,
    'categories' => $categories
]);
?>
```

#### **UPDATED: `load_english_level.php`** (and all subject loaders)
```php
<?php
// OLD: Fixed columns
$stmt = $conn->prepare("SELECT 
    english_grammar_level, 
    english_vocabulary_level, 
    english_reading_level
    FROM users WHERE username = :username");

// NEW: Dynamic query from user_category_progress table
$stmt = $conn->prepare("SELECT 
    category_key, 
    current_level 
    FROM user_category_progress 
    WHERE user_id = :user_id AND subject_name = 'english'");

// Return as dynamic JSON
$response = [
    'success' => true,
    'categories' => []
];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $response['categories'][$row['category_key']] = (int)$row['current_level'];
}

echo json_encode($response);
?>
```

#### **UPDATED: `update_english_level.php`** (and all subject updaters)
```php
<?php
// OLD: Fixed column update
$stmt = $conn->prepare("UPDATE users 
    SET english_grammar_level = :level 
    WHERE username = :username");

// NEW: Upsert into user_category_progress
$stmt = $conn->prepare("INSERT INTO user_category_progress 
    (user_id, subject_name, category_key, current_level, last_played_at) 
    VALUES (:user_id, :subject, :category, :level, NOW())
    ON DUPLICATE KEY UPDATE 
    current_level = :level, 
    last_played_at = NOW()");

$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':subject', $subject);
$stmt->bindParam(':category', $category);
$stmt->bindParam(':level', $level);
$stmt->execute();
?>
```

---

### 3. Unity C# Changes

#### **UPDATED: `CategorySelectionManager.cs`**
```csharp
using UnityEngine;
using UnityEngine.Networking;
using System.Collections;
using System.Collections.Generic;

public class CategorySelectionManager : MonoBehaviour
{
    [Header("API Configuration")]
    public string getCategoriesUrl = "https://your-domain.com/api/get_categories.php";
    
    // Remove hardcoded dictionary
    // private Dictionary<string, Dictionary<string, string>> subjectCategories;
    
    // Replace with dynamic data
    private Dictionary<string, List<CategoryData>> dynamicCategories;
    
    private void Awake()
    {
        dynamicCategories = new Dictionary<string, List<CategoryData>>();
    }
    
    // NEW: Fetch categories from server
    public void ShowCategorySelection(string subjectName)
    {
        currentSubject = Normalize(subjectName);
        StartCoroutine(FetchCategoriesFromServer(currentSubject));
    }
    
    private IEnumerator FetchCategoriesFromServer(string subject)
    {
        string url = $"{getCategoriesUrl}?subject={subject}";
        
        using (UnityWebRequest www = UnityWebRequest.Get(url))
        {
            yield return www.SendWebRequest();
            
            if (www.result == UnityWebRequest.Result.Success)
            {
                string json = www.downloadHandler.text;
                CategoryResponse response = JsonUtility.FromJson<CategoryResponse>(json);
                
                if (response.success)
                {
                    dynamicCategories[subject] = response.categories;
                    DisplayCategories(response.categories);
                }
                else
                {
                    Debug.LogError("Failed to load categories: " + response.message);
                }
            }
            else
            {
                Debug.LogError("Network error: " + www.error);
            }
        }
    }
    
    private void DisplayCategories(List<CategoryData> categories)
    {
        ClearButtons(categoryButtonContainer);
        
        foreach (CategoryData category in categories)
        {
            SpawnButton(category.key, category.label, categoryButtonContainer);
        }
        
        AnimateIn(categorySelectionPanel);
    }
}

[System.Serializable]
public class CategoryData
{
    public string key;
    public string label;
    public string icon;
    public string description;
}

[System.Serializable]
public class CategoryResponse
{
    public bool success;
    public string subject;
    public List<CategoryData> categories;
}
```

#### **UPDATED: `DatabaseManager.cs`**
```csharp
public class DatabaseManager : MonoBehaviour
{
    // OLD: Fixed response class
    /*
    [System.Serializable]
    public class CategoryLevelResponse
    {
        public int english_grammar_level;
        public int english_vocabulary_level;
        // ... hardcoded fields
    }
    */
    
    // NEW: Dynamic response class
    [System.Serializable]
    public class DynamicLevelResponse
    {
        public bool success;
        public string message;
        public Dictionary<string, int> categories; // key => level
    }
    
    private IEnumerator LoadCoroutine()
    {
        string username = PlayerPrefs.GetString("Username", "");
        string subject = PlayerPrefs.GetString("SelectedSubject", "");
        
        WWWForm form = new WWWForm();
        form.AddField("username", username);
        form.AddField("subject", subject);
        
        using (UnityWebRequest www = UnityWebRequest.Post(loadLevelUrl, form))
        {
            yield return www.SendWebRequest();
            
            if (www.result == UnityWebRequest.Result.Success)
            {
                string json = www.downloadHandler.text;
                DynamicLevelResponse data = JsonUtility.FromJson<DynamicLevelResponse>(json);
                
                if (data != null && data.success)
                {
                    // Get level for selected category
                    string category = PlayerPrefs.GetString("SelectedCategory", "");
                    int level = data.categories.ContainsKey(category) ? data.categories[category] : 0;
                    
                    Debug.Log($"[LOAD] Category: {category} | Level: {level}");
                    
                    LessonProgressTracker tracker = FindProgressTracker();
                    if (tracker != null)
                    {
                        tracker.LoadProgressFromDatabase(level);
                    }
                }
            }
        }
    }
}
```

#### **UPDATED: `LessonProgressTracker.cs`**
```csharp
// No major changes needed - already uses dynamic level loading
// Just ensure it works with the new DatabaseManager response format
```

---

## 4. Admin Panel UI Changes

### **NEW PAGE: Category Management**

```
Admin Panel
├── Dashboard
├── Manage Students
├── Manage Educators
├── Manage Quizzes
└── ✨ Manage Categories (NEW)
    ├── View All Categories
    ├── Add New Category
    ├── Edit Category
    ├── Delete Category
    ├── Reorder Categories
    └── Import/Export Categories
```

### **Features**:
1. **Add Category**: Form with subject, key, label, icon, description
2. **Edit Category**: Inline editing with validation
3. **Delete Category**: Safety check (prevent deletion if quizzes exist)
4. **Reorder**: Drag & drop interface
5. **Bulk Operations**: Enable/disable multiple categories
6. **Import/Export**: CSV/JSON format for backup

---

## 5. Migration Strategy

### **Phase 1: Database Setup** (2-4 hours)
1. Create `categories` table
2. Create `user_category_progress` table
3. Populate `categories` with existing hardcoded data
4. Write migration script to copy user progress from `users` table to `user_category_progress`
5. Test data integrity

### **Phase 2: PHP Backend** (8-12 hours)
1. Create `admin/manage-categories.php` (CRUD interface)
2. Create `api/get_categories.php` (Unity API)
3. Update `category-config.php` to load from database
4. Update all `load_*_level.php` files
5. Update all `update_*_level.php` files
6. Add caching layer (session/Redis)
7. Test all endpoints

### **Phase 3: Unity C# Updates** (12-16 hours)
1. Update `CategorySelectionManager.cs` to fetch from API
2. Update `DatabaseManager.cs` response classes
3. Add loading indicators
4. Add error handling & retry logic
5. Test offline mode (cached categories)
6. Test all 5 subjects

### **Phase 4: Admin UI** (8-12 hours)
1. Create category management page
2. Add CRUD forms
3. Add drag & drop reordering
4. Add validation
5. Add bulk operations
6. Test all operations

### **Phase 5: Testing & Deployment** (8-12 hours)
1. Unit testing (PHP & C#)
2. Integration testing
3. User acceptance testing
4. Performance testing
5. Backup existing data
6. Deploy to production
7. Monitor for issues

---

## 6. Files That Need Changes

### **PHP Files** (12 files)
```
✏️ MODIFY:
- admin/category-config.php (load from DB)
- load_english_level.php (dynamic query)
- load_math_level.php (dynamic query)
- load_filipino_level.php (dynamic query)
- load_ap_level.php (dynamic query)
- load_science_level.php (dynamic query)
- update_english_level.php (upsert to new table)
- update_math_level.php (upsert to new table)
- update_filipino_level.php (upsert to new table)
- update_ap_level.php (upsert to new table)
- update_science_level.php (upsert to new table)

➕ CREATE:
- admin/manage-categories.php (CRUD UI)
- api/get_categories.php (Unity API)
- migrations/migrate_categories.php (data migration)
```

### **Unity C# Files** (3 files)
```
✏️ MODIFY:
- CategorySelectionManager.cs (fetch from API)
- DatabaseManager.cs (dynamic response)
- LessonProgressTracker.cs (minor adjustments)

➕ CREATE:
- Scripts/CategoryCache.cs (optional caching)
```

### **Database Files** (2 files)
```
➕ CREATE:
- database/create_categories_table.sql
- database/create_user_category_progress_table.sql
- database/migrate_existing_data.sql
```

---

## 7. Benefits of Dynamic System

### ✅ **Advantages**
1. **No Code Changes**: Add categories without touching code
2. **Scalability**: Easily add new subjects/categories
3. **Flexibility**: Enable/disable categories per school/region
4. **Maintainability**: Centralized category management
5. **Analytics**: Track category popularity and performance
6. **Localization**: Easy to add multi-language support
7. **A/B Testing**: Test different category structures
8. **Future-Proof**: Ready for curriculum changes

### ⚠️ **Challenges**
1. **Migration Complexity**: Moving existing user data
2. **Performance**: Additional database queries (mitigated by caching)
3. **Testing**: More edge cases to test
4. **Backward Compatibility**: Need to support old app versions
5. **Cache Invalidation**: Must clear cache when categories change

---

## 8. Risk Mitigation

### **Data Loss Prevention**
- ✅ Keep old `users` table columns during migration
- ✅ Run migration script in test environment first
- ✅ Create full database backup before deployment
- ✅ Implement rollback procedure

### **Performance Optimization**
- ✅ Cache categories in PHP session
- ✅ Cache categories in Unity PlayerPrefs
- ✅ Add database indexes
- ✅ Use CDN for category icons

### **Error Handling**
- ✅ Fallback to hardcoded categories if API fails
- ✅ Retry logic with exponential backoff
- ✅ Offline mode support
- ✅ Graceful degradation

---

## 9. Testing Checklist

### **Database Testing**
- [ ] Categories table created successfully
- [ ] User progress table created successfully
- [ ] Migration script copies all data correctly
- [ ] No data loss during migration
- [ ] Indexes improve query performance

### **PHP Testing**
- [ ] Category CRUD operations work
- [ ] API returns correct JSON format
- [ ] Load level endpoints return dynamic data
- [ ] Update level endpoints save to new table
- [ ] Caching works correctly
- [ ] Cache invalidation works

### **Unity Testing**
- [ ] Categories load from server
- [ ] Offline mode works with cached data
- [ ] Progress saves correctly
- [ ] Progress loads correctly
- [ ] All 5 subjects work
- [ ] Error handling works

### **Integration Testing**
- [ ] End-to-end flow works (select category → play → save progress)
- [ ] Admin adds category → appears in Unity
- [ ] Admin deletes category → removed from Unity
- [ ] Admin reorders categories → order updates in Unity

---

## 10. Estimated Timeline

| Phase | Duration | Dependencies |
|-------|----------|--------------|
| Database Setup | 2-4 hours | None |
| PHP Backend | 8-12 hours | Database Setup |
| Unity C# Updates | 12-16 hours | PHP Backend |
| Admin UI | 8-12 hours | PHP Backend |
| Testing & Deployment | 8-12 hours | All phases |
| **TOTAL** | **40-60 hours** | - |

---

## 11. Recommended Approach

### **Option A: Full Migration** (Recommended)
- Implement complete dynamic system
- Migrate all existing data
- Remove hardcoded categories
- **Timeline**: 40-60 hours
- **Risk**: Medium
- **Benefit**: Future-proof, scalable

### **Option B: Hybrid Approach**
- Keep hardcoded categories as fallback
- Add dynamic system on top
- Gradual migration
- **Timeline**: 30-40 hours
- **Risk**: Low
- **Benefit**: Safer, but more complex codebase

### **Option C: Minimal Changes**
- Only add admin UI for categories
- Keep hardcoded categories in code
- Generate code from database
- **Timeline**: 20-30 hours
- **Risk**: Very Low
- **Benefit**: Quick win, but not truly dynamic

---

## 12. Conclusion

**YES, making the category system dynamic is absolutely feasible and highly recommended!**

### **Key Takeaways**:
1. ✅ **Feasible**: Requires moderate refactoring but no architectural changes
2. ✅ **Scalable**: Future-proof for curriculum changes
3. ✅ **Maintainable**: Centralized category management
4. ⚠️ **Moderate Effort**: 40-60 hours of development
5. ⚠️ **Testing Critical**: Thorough testing required to prevent data loss

### **Next Steps**:
1. Review this document with your team
2. Decide on migration approach (Option A recommended)
3. Create detailed project plan
4. Set up development environment
5. Begin Phase 1 (Database Setup)

---

## 13. Questions & Answers

### **Q: Will this break existing user progress?**
**A**: No, if migration is done correctly. We keep old columns during transition.

### **Q: What if the API fails in Unity?**
**A**: Implement fallback to cached categories or hardcoded defaults.

### **Q: Can we add categories without updating the app?**
**A**: Yes! That's the main benefit of this system.

### **Q: How do we handle deleted categories?**
**A**: Soft delete (mark as inactive) to preserve user progress data.

### **Q: What about performance?**
**A**: Caching mitigates performance impact. Minimal overhead.

---

**Document Version**: 1.0  
**Last Updated**: May 20, 2026  
**Status**: Ready for Implementation
