# Excel Import Feature - Category System Analysis

## 🔍 Critical Issue Found

The Excel import feature is **NOT ALIGNED** with the new category KEY system. It's using **LABELS** instead of **KEYS**.

---

## ❌ Current Problems

### Problem 1: Excel Template Uses LABELS in Dropdown

**File:** `admin/includes/generate_quiz_xlsx.php`

**Current Code (Line 66):**
```php
$categories = getCategoriesBySubject($subject_name);
// Returns: ['grammar' => 'Grammar & Language Structure', 'vocabulary' => 'Vocabulary', ...]

// Line 145-148: Build category dropdown
$cat_escaped = implode(',', array_map(function($c) {
    return htmlspecialchars($c, ENT_XML1, 'UTF-8');
}, $categories)); // ❌ Uses VALUES (labels), not KEYS
```

**What Happens:**
```php
// $categories = ['grammar' => 'Grammar & Language Structure', 'vocabulary' => 'Vocabulary']
// array_map without keys → gets VALUES
// Result: "Grammar & Language Structure,Vocabulary,Reading Comprehension,..."
```

**Excel Dropdown Shows:**
```
✅ Grammar & Language Structure  ← LABEL (wrong!)
✅ Vocabulary                     ← LABEL (wrong!)
✅ Reading Comprehension          ← LABEL (wrong!)
```

**Should Show:**
```
✅ grammar      ← KEY (correct!)
✅ vocabulary   ← KEY (correct!)
✅ reading      ← KEY (correct!)
```

---

### Problem 2: Sample Data Uses LABELS

**File:** `admin/includes/generate_quiz_xlsx.php`

**Current Code (Line 27-30):**
```php
$samples = [
    [$subject_name,'1',$categories[0] ?? 'Category',...], // ❌ Uses first LABEL
    [$subject_name,'2',$categories[1] ?? ($categories[0] ?? 'Category'),...], // ❌ Uses second LABEL
];
```

**What Happens:**
```php
// $categories[0] gets the FIRST VALUE (label)
// For english: "Grammar & Language Structure"
// Sample row shows LABEL instead of KEY
```

---

### Problem 3: CSV Import Accepts LABELS

**File:** `admin/educ-quizes.php` (Line 133-145)

**Current Code:**
```php
while (($row = fgetcsv($handle)) !== false) {
    // ...
    $row_category = mysqli_real_escape_string($con, trim($row_category));
    // ❌ No validation - accepts whatever is in CSV (labels or keys)
    
    // Inserts directly into database
    $q = "INSERT INTO quizes (..., category, ...) 
          VALUES (..., '$row_category', ...)"; // ❌ Stores LABEL if that's what's in CSV
}
```

**Impact:**
- If user fills Excel with labels (which the dropdown provides), those labels get stored in database
- Database should only store KEYS

---

## 📊 Data Flow Analysis

### Current (Broken) Flow

```
1. Teacher clicks "Download Template"
   ↓
2. PHP generates Excel with LABEL dropdown
   getCategoriesBySubject('english')
   → ['grammar' => 'Grammar & Language Structure', ...]
   → array_map gets VALUES
   → Dropdown: "Grammar & Language Structure,Vocabulary,..." ❌ LABELS
   ↓
3. Teacher fills Excel, selects from dropdown
   → Selects: "Grammar & Language Structure" ❌ LABEL
   ↓
4. Teacher saves as CSV
   → CSV contains: "english,1,Grammar & Language Structure,..." ❌ LABEL
   ↓
5. Teacher uploads CSV
   ↓
6. PHP imports without validation
   → Inserts: category = "Grammar & Language Structure" ❌ LABEL in DB
   ↓
7. Database now has LABELS instead of KEYS ❌
```

### Correct Flow (Should Be)

```
1. Teacher clicks "Download Template"
   ↓
2. PHP generates Excel with KEY dropdown
   getCategoryKeys('english')
   → ['grammar', 'vocabulary', 'reading', ...]
   → Dropdown: "grammar,vocabulary,reading,..." ✅ KEYS
   ↓
3. Teacher fills Excel, selects from dropdown
   → Selects: "grammar" ✅ KEY
   ↓
4. Teacher saves as CSV
   → CSV contains: "english,1,grammar,..." ✅ KEY
   ↓
5. Teacher uploads CSV
   ↓
6. PHP validates KEY before import
   → isValidCategory('english', 'grammar') → true ✅
   → Inserts: category = "grammar" ✅ KEY in DB
   ↓
7. Database has KEYS ✅
```

---

## 🔧 Required Fixes

### Fix 1: Update Excel Template to Use KEYS

**File:** `admin/includes/generate_quiz_xlsx.php`

**Change Line 145-148:**
```php
// ❌ OLD CODE (uses labels)
$cat_escaped = implode(',', array_map(function($c) {
    return htmlspecialchars($c, ENT_XML1, 'UTF-8');
}, $categories));

// ✅ NEW CODE (uses keys)
$cat_keys = array_keys($categories); // Get KEYS only
$cat_escaped = implode(',', array_map(function($c) {
    return htmlspecialchars($c, ENT_XML1, 'UTF-8');
}, $cat_keys));
```

**Or better, use the helper function:**
```php
// ✅ BEST: Use getCategoryKeys() from category-config.php
require_once('category-config.php');
$cat_keys = getCategoryKeys($subject_name);
$cat_escaped = implode(',', array_map(function($c) {
    return htmlspecialchars($c, ENT_XML1, 'UTF-8');
}, $cat_keys));
```

---

### Fix 2: Update Sample Data to Use KEYS

**File:** `admin/includes/generate_quiz_xlsx.php`

**Change Line 27-30:**
```php
// ❌ OLD CODE (uses labels)
$samples = [
    [$subject_name,'1',$categories[0] ?? 'Category',...],
    [$subject_name,'2',$categories[1] ?? ($categories[0] ?? 'Category'),...],
];

// ✅ NEW CODE (uses keys)
$cat_keys = array_keys($categories);
$samples = [
    [$subject_name,'1',$cat_keys[0] ?? 'grammar',...],
    [$subject_name,'2',$cat_keys[1] ?? ($cat_keys[0] ?? 'vocabulary'),...],
];
```

---

### Fix 3: Add Validation to CSV Import

**File:** `admin/educ-quizes.php`

**Add after Line 133:**
```php
while (($row = fgetcsv($handle)) !== false) {
    // ... existing code ...
    
    $row_category = trim($row_category);
    
    // ✅ NEW: Validate category KEY
    if (!isValidCategory($row_subject, $row_category)) {
        // Try to convert label to key (backward compatibility)
        $converted_key = getCategoryKeyFromLabel($row_subject, $row_category);
        if ($converted_key !== null) {
            $row_category = $converted_key; // Use KEY
        } else {
            $skipped++;
            continue; // Skip invalid category
        }
    }
    
    $row_category = mysqli_real_escape_string($con, $row_category);
    
    // ... rest of code ...
}
```

---

### Fix 4: Update Hints to Mention KEYS

**File:** `admin/includes/generate_quiz_xlsx.php`

**Change Line 18:**
```php
// ❌ OLD
$hints = [
    'e.g. '.$subject_name,
    '1 to 10',
    'Pick from dropdown', // ❌ Doesn't explain what to pick
    // ...
];

// ✅ NEW
$hints = [
    'e.g. '.$subject_name,
    '1 to 10',
    'Pick category KEY from dropdown', // ✅ Clarifies to use KEY
    // ...
];
```

---

## 📝 Complete Fixed Code

### File 1: `admin/includes/generate_quiz_xlsx.php`

**Lines to Change:**

```php
// Line 18: Update hint
$hints = [
    'e.g. '.$subject_name,
    '1 to 10',
    'Pick category KEY from dropdown', // ✅ FIXED
    'Write the full question here',
    'First choice (Option A)',
    'Second choice (Option B)',
    'Third choice (Option C)',
    'Fourth choice (Option D)',
    '1=A  2=B  3=C  4=D',
];

// Line 27-30: Use KEYS in samples
$cat_keys = array_keys($categories); // ✅ FIXED
$samples = [
    [$subject_name,'1',$cat_keys[0] ?? 'grammar','What is the capital of the Philippines?','Cebu','Manila','Davao','Quezon City','2'],
    [$subject_name,'2',$cat_keys[1] ?? ($cat_keys[0] ?? 'vocabulary'),'Which planet is closest to the sun?','Earth','Venus','Mercury','Mars','3'],
];

// Line 145-148: Use KEYS in dropdown
$cat_keys = array_keys($categories); // ✅ FIXED
$cat_escaped = implode(',', array_map(function($c) {
    return htmlspecialchars($c, ENT_XML1, 'UTF-8');
}, $cat_keys)); // ✅ FIXED: Use keys, not labels
$cat_formula = '"'.$cat_escaped.'"';
```

---

### File 2: `admin/educ-quizes.php`

**Add validation after Line 133:**

```php
while (($row = fgetcsv($handle)) !== false) {
    if (count($row) < 9) { $skipped++; continue; }

    $first_cell = strtolower(trim($row[0]));
    if (empty($first_cell) || strpos($first_cell, 'e.g') !== false || strpos($first_cell, 'pick') !== false) {
        continue;
    }

    [$row_subject, $row_level, $row_category, $row_question, $row_a, $row_b, $row_c, $row_d, $row_correct] = $row;

    $row_subject  = mysqli_real_escape_string($con, trim($row_subject));
    $row_level    = (int) trim($row_level);
    $row_category = trim($row_category); // Don't escape yet
    
    // ✅ NEW: Validate and convert category
    if (!isValidCategory($row_subject, $row_category)) {
        // Try to convert label to key (backward compatibility for old templates)
        $converted_key = getCategoryKeyFromLabel($row_subject, $row_category);
        if ($converted_key !== null) {
            $row_category = $converted_key;
        } else {
            // Invalid category - skip this row
            $skipped++;
            continue;
        }
    }
    
    $row_category = mysqli_real_escape_string($con, $row_category); // Now escape
    $row_question = mysqli_real_escape_string($con, trim($row_question));
    // ... rest of code unchanged ...
}
```

---

## 🧪 Testing Checklist

After applying fixes:

### Test 1: Excel Template Generation
- [ ] Download template for English
- [ ] Check Category dropdown in Excel
- [ ] Should show: `grammar`, `vocabulary`, `reading`, `literature`, `writing` (KEYS)
- [ ] Should NOT show: `Grammar & Language Structure`, etc. (LABELS)

### Test 2: Sample Data
- [ ] Check Row 6 (first sample)
- [ ] Category column should show KEY (e.g., `grammar`)
- [ ] Should NOT show LABEL

### Test 3: CSV Import with KEYS
- [ ] Fill template with KEYS
- [ ] Save as CSV
- [ ] Upload
- [ ] Check database: `SELECT category FROM quizes ORDER BY id DESC LIMIT 5`
- [ ] Should see KEYS (e.g., `grammar`, `algebra`)

### Test 4: CSV Import with LABELS (Backward Compatibility)
- [ ] Manually edit CSV to use LABELS
- [ ] Upload
- [ ] Should convert LABELS to KEYS automatically
- [ ] Check database: should have KEYS

### Test 5: CSV Import with Invalid Categories
- [ ] Edit CSV with invalid category (e.g., `invalid_cat`)
- [ ] Upload
- [ ] Should skip those rows
- [ ] Should show message: "X rows skipped"

---

## 📊 Impact Assessment

### Files Affected: 3
1. ✅ `admin/includes/generate_quiz_xlsx.php` - Excel template generation
2. ✅ `admin/educ-quizes.php` - CSV import (educator)
3. ✅ `admin/manage-quizes.php` - CSV import (admin) - Same fix needed

### Database Impact
- **Existing data**: May have LABELS if imported before fix
- **Solution**: Run migration script (already created: `migrate-labels-to-keys.php`)

### User Impact
- **Teachers**: Will see KEYS in dropdown instead of LABELS
- **Benefit**: Consistent with database, no more confusion
- **Training**: May need to explain KEY vs LABEL concept

---

## 🎯 Recommendations

### Immediate Actions
1. ✅ Apply fixes to `generate_quiz_xlsx.php`
2. ✅ Apply fixes to `educ-quizes.php`
3. ✅ Apply fixes to `manage-quizes.php`
4. ✅ Run migration script on existing data
5. ✅ Test with sample imports

### Optional Enhancements
1. **Add KEY → LABEL reference in Excel**
   - Add a second sheet with KEY → LABEL mapping
   - Teachers can reference it while filling

2. **Improve dropdown UX**
   - Show both KEY and LABEL: `grammar (Grammar & Language Structure)`
   - Requires more complex Excel formula

3. **Add validation feedback**
   - Show which rows were skipped and why
   - Log invalid categories for review

---

## 🔄 Backward Compatibility

The fix includes backward compatibility:

```php
// If CSV has LABEL, convert to KEY
if (!isValidCategory($subject, $category)) {
    $key = getCategoryKeyFromLabel($subject, $category);
    if ($key !== null) {
        $category = $key; // Use KEY
    }
}
```

This means:
- ✅ Old templates with LABELS will still work
- ✅ New templates with KEYS will work
- ✅ Gradual migration possible

---

## ✅ Summary

### Current Status: ❌ NOT ALIGNED
- Excel template uses LABELS
- CSV import accepts LABELS
- Database gets LABELS (wrong!)

### After Fix: ✅ FULLY ALIGNED
- Excel template uses KEYS
- CSV import validates KEYS
- Database stores KEYS (correct!)
- Backward compatible with old templates

### Priority: 🔴 HIGH
This is a critical bug that affects data integrity. Should be fixed before next import.

---

**Status**: ❌ Issues Found - Fixes Required
**Priority**: 🔴 HIGH
**Estimated Fix Time**: 30 minutes
**Testing Time**: 15 minutes
