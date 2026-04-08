# Quick Reference - Dynamic Category Selection

## 🎯 Key Files

| File | Purpose |
|------|---------|
| `category-config.php` | Centralized category configuration |
| `add_category_column.sql` | Database migration script |
| `manage-quizes.php` | Admin quiz management (updated) |
| `educ-quizes.php` | Educator quiz management (needs update) |

## 🔧 Quick Setup

```bash
# 1. Run SQL migration
mysql -u root -p play2review_db < admin/add_category_column.sql

# 2. Verify column exists
mysql -u root -p play2review_db -e "DESCRIBE quizes;"

# 3. Test in browser
# - Open admin/manage-quizes.php
# - Click "Add New Question"
# - Select a subject
# - Verify category dropdown appears
```

## 📝 Adding New Categories

Edit `admin/category-config.php`:

```php
$CATEGORY_CONFIG = [
    'english' => [
        'Grammar & Language Structure',
        'NEW CATEGORY HERE',  // Add here
        // ...
    ],
    // ...
];
```

## 🎨 Modal Structure

### Add Modal
```html
<div id="addQuizModal">
    <select id="add-subject-select">...</select>
    <div id="add-english-category">...</div>
    <div id="add-math-category">...</div>
    <!-- etc -->
</div>
```

### Edit Modal
```html
<div id="editQuizModal">
    <select id="edit-subject-select">...</select>
    <div id="edit-english-category">...</div>
    <div id="edit-math-category">...</div>
    <!-- etc -->
</div>
```

## 🔍 JavaScript API

```javascript
// Setup category toggle for a modal
setupCategoryToggle(modalId, prefix);

// Example
setupCategoryToggle('addQuizModal', 'add');
setupCategoryToggle('editQuizModal', 'edit');
```

## 🐛 Common Issues

| Issue | Solution |
|-------|----------|
| Categories not showing | Check console, verify `setupCategoryToggle()` called |
| Multiple values submitted | Ensure hidden selects have `disabled` attribute |
| Edit doesn't preselect | Check `quiz.category` in JSON data |
| Database error | Run SQL migration |

## ✅ Testing Commands

```sql
-- Check if category column exists
SHOW COLUMNS FROM quizes LIKE 'category';

-- View quizzes with categories
SELECT id, subject_name, category, question 
FROM quizes 
LIMIT 10;

-- Count quizzes by category
SELECT subject_name, category, COUNT(*) as count 
FROM quizes 
GROUP BY subject_name, category;
```

## 📊 Category Counts

```sql
-- English categories
SELECT category, COUNT(*) FROM quizes 
WHERE subject_name = 'english' 
GROUP BY category;

-- All subjects
SELECT subject_name, category, COUNT(*) as total
FROM quizes 
GROUP BY subject_name, category
ORDER BY subject_name, total DESC;
```

## 🎮 Future Features

- [ ] Category-based XP tracking
- [ ] Category mastery badges
- [ ] Analytics per category
- [ ] Category filtering in quiz list
- [ ] Bulk category assignment

## 📞 Quick Help

**No categories showing?**
1. Check browser console
2. Verify subject select has correct ID
3. Ensure JavaScript loaded

**Form submitting wrong data?**
1. Check only ONE select is enabled
2. Verify hidden selects are disabled
3. Inspect POST data in Network tab

**Database errors?**
1. Run migration script
2. Check column exists
3. Verify data types match

---

**Last Updated:** March 2, 2026
