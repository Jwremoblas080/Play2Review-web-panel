# 🚀 Category System Deployment Checklist

## Pre-Deployment

### 1. Backup Everything
- [ ] Backup MySQL database
  ```bash
  mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
  ```
- [ ] Backup PHP files
  ```bash
  cp -r play2review play2review_backup_$(date +%Y%m%d)
  ```
- [ ] Backup Unity project
  ```bash
  # Create a zip of the entire Unity project folder
  ```

### 2. Verify Current State
- [ ] Check database for existing category values
  ```sql
  SELECT DISTINCT subject_name, category FROM quizes ORDER BY subject_name, category;
  ```
- [ ] Note if categories are currently labels or keys
- [ ] Count total records that need migration
  ```sql
  SELECT COUNT(*) FROM quizes;
  SELECT COUNT(*) FROM quiz_progress; -- if exists
  ```

### 3. Test Environment Setup
- [ ] Create test database copy
- [ ] Deploy to test server first
- [ ] Test with sample data

---

## Deployment Steps

### Phase 1: Backend Deployment (PHP)

#### Step 1.1: Upload Updated PHP Files
- [ ] Upload `admin/category-config.php`
- [ ] Upload `get_quiz_questions_by_category.php`
- [ ] Upload `admin/migrate-labels-to-keys.php`
- [ ] Upload `admin/category-api-example.php` (optional reference)

#### Step 1.2: Verify PHP Syntax
```bash
php -l admin/category-config.php
php -l get_quiz_questions_by_category.php
php -l admin/migrate-labels-to-keys.php
```
- [ ] All files show "No syntax errors detected"

#### Step 1.3: Test Category Config
- [ ] Access: `http://yourserver.com/admin/category-config.php`
- [ ] Should load without errors
- [ ] Test validation function:
  ```php
  <?php
  require_once('admin/category-config.php');
  var_dump(isValidCategory('english', 'grammar')); // Should return true
  var_dump(isValidCategory('english', 'invalid')); // Should return false
  ?>
  ```

### Phase 2: Database Migration (If Needed)

#### Step 2.1: Check if Migration is Needed
- [ ] Run query:
  ```sql
  SELECT category FROM quizes LIMIT 10;
  ```
- [ ] If you see labels (e.g., "Grammar & Language Structure"), migration is needed
- [ ] If you see keys (e.g., "grammar"), skip to Phase 3

#### Step 2.2: Run Migration Script
- [ ] Access: `http://yourserver.com/admin/migrate-labels-to-keys.php`
- [ ] Review the warning page
- [ ] Click confirmation link: `?confirm=yes`
- [ ] Wait for migration to complete
- [ ] Review migration results:
  - [ ] Note number of records updated
  - [ ] Note any errors
  - [ ] Verify all categories are now valid keys

#### Step 2.3: Verify Migration
- [ ] Run verification query:
  ```sql
  SELECT DISTINCT subject_name, category FROM quizes ORDER BY subject_name, category;
  ```
- [ ] Should see only keys (lowercase, underscored)
- [ ] Should NOT see any labels
- [ ] Test a sample query:
  ```sql
  SELECT * FROM quizes WHERE subject_name='english' AND category='grammar' LIMIT 5;
  ```
- [ ] Should return results

### Phase 3: Frontend Deployment (Unity)

#### Step 3.1: Update Unity Scripts
- [ ] Replace `CategorySelectionManager.cs` with updated version
- [ ] Add `CategorySystemExample.cs` (optional reference)
- [ ] Save all changes

#### Step 3.2: Rebuild Unity Project
- [ ] Open Unity project
- [ ] Check for compilation errors (should be none)
- [ ] Build project for target platform
- [ ] Test build locally before deployment

#### Step 3.3: Test Unity Integration
- [ ] Run Unity game in editor
- [ ] Select a subject (e.g., English)
- [ ] Verify dropdown shows labels (not keys)
- [ ] Select a category
- [ ] Check Debug.Log output:
  - [ ] Should show KEY being sent (e.g., "grammar")
  - [ ] Should NOT show label being sent
- [ ] Check PlayerPrefs:
  ```csharp
  Debug.Log(PlayerPrefs.GetString("SelectedCategory")); // Should be KEY
  ```

### Phase 4: Integration Testing

#### Step 4.1: End-to-End Test
- [ ] Start Unity game
- [ ] Select subject: English
- [ ] Select category: Grammar & Language Structure
- [ ] Verify quiz loads correctly
- [ ] Complete quiz
- [ ] Verify progress saves
- [ ] Check database:
  ```sql
  SELECT * FROM quiz_progress ORDER BY id DESC LIMIT 1;
  ```
- [ ] Verify `category` column contains KEY (e.g., "grammar")

#### Step 4.2: Test All Subjects
- [ ] Test English categories
- [ ] Test Math categories
- [ ] Test Filipino categories
- [ ] Test AP categories
- [ ] Test Science categories

#### Step 4.3: Test Edge Cases
- [ ] Test with invalid category (should fail gracefully)
- [ ] Test with empty category (should show error)
- [ ] Test with special characters (should be handled)
- [ ] Test rapid category switching

### Phase 5: API Testing

#### Step 5.1: Test Get Questions API
```bash
curl -X POST http://yourserver.com/get_quiz_questions_by_category.php \
  -d "subject_name=english" \
  -d "category=grammar" \
  -d "quiz_level=1"
```
- [ ] Response includes `category_key`
- [ ] Response includes `category_label`
- [ ] Response includes questions array
- [ ] `success` is true

#### Step 5.2: Test Invalid Requests
```bash
# Invalid category key
curl -X POST http://yourserver.com/get_quiz_questions_by_category.php \
  -d "subject_name=english" \
  -d "category=invalid_key"
```
- [ ] Response shows error
- [ ] `success` is false
- [ ] Error message is clear

#### Step 5.3: Test All Category Keys
For each subject, test at least one category:
- [ ] English: `grammar`
- [ ] Math: `algebra`
- [ ] Filipino: `gramatika`
- [ ] AP: `ekonomiks`
- [ ] Science: `biology`

---

## Post-Deployment

### Monitoring (First 24 Hours)

#### Hour 1: Immediate Checks
- [ ] Check server error logs
- [ ] Monitor database for any label insertions
- [ ] Test from multiple devices
- [ ] Verify no console errors in Unity

#### Hour 4: User Testing
- [ ] Have test users try the system
- [ ] Collect feedback
- [ ] Monitor for any issues
- [ ] Check analytics

#### Hour 24: Full Review
- [ ] Review all error logs
- [ ] Check database integrity
- [ ] Verify all categories working
- [ ] Review user feedback

### Performance Checks
- [ ] Check API response times
- [ ] Monitor database query performance
- [ ] Verify no memory leaks in Unity
- [ ] Check server load

### Data Integrity Checks
```sql
-- Verify all categories are valid keys
SELECT DISTINCT subject_name, category 
FROM quizes 
WHERE category NOT IN (
  SELECT DISTINCT category 
  FROM (
    SELECT 'grammar' as category UNION
    SELECT 'vocabulary' UNION
    SELECT 'reading' UNION
    -- Add all valid keys here
  ) valid_keys
);
-- Should return 0 rows

-- Check for any NULL categories
SELECT COUNT(*) FROM quizes WHERE category IS NULL;
-- Should return 0

-- Verify progress tracking
SELECT subject, category, COUNT(*) as total
FROM quiz_progress
GROUP BY subject, category
ORDER BY subject, category;
-- Should show reasonable distribution
```

---

## Rollback Plan (If Needed)

### If Critical Issues Found

#### Step 1: Stop New Deployments
- [ ] Revert Unity build to previous version
- [ ] Notify users of maintenance

#### Step 2: Restore Database
```bash
# Restore from backup
mysql -u username -p database_name < backup_YYYYMMDD_HHMMSS.sql
```
- [ ] Verify restoration successful
- [ ] Check record counts match

#### Step 3: Restore PHP Files
```bash
# Restore from backup
cp -r play2review_backup_YYYYMMDD/* play2review/
```
- [ ] Verify files restored
- [ ] Test API endpoints

#### Step 4: Verify Rollback
- [ ] Test Unity game
- [ ] Test API calls
- [ ] Verify database queries work
- [ ] Check user progress intact

---

## Success Criteria

### Technical Success
- [ ] ✅ All PHP files deployed without errors
- [ ] ✅ Database migration completed (if needed)
- [ ] ✅ Unity builds successfully
- [ ] ✅ All API endpoints responding correctly
- [ ] ✅ Database contains only keys (no labels)
- [ ] ✅ No errors in server logs
- [ ] ✅ No errors in Unity console

### Functional Success
- [ ] ✅ Users can select categories
- [ ] ✅ Quizzes load correctly
- [ ] ✅ Progress saves correctly
- [ ] ✅ Analytics work properly
- [ ] ✅ All subjects functional
- [ ] ✅ All categories functional

### User Experience Success
- [ ] ✅ Dropdowns show readable labels
- [ ] ✅ No visible errors to users
- [ ] ✅ Performance is acceptable
- [ ] ✅ User feedback is positive

---

## Documentation Updates

### After Successful Deployment
- [ ] Update README with new category system info
- [ ] Document any issues encountered
- [ ] Update API documentation
- [ ] Create training materials for team
- [ ] Update user documentation (if needed)

---

## Team Communication

### Before Deployment
- [ ] Notify team of deployment schedule
- [ ] Share this checklist
- [ ] Assign responsibilities
- [ ] Set up communication channel

### During Deployment
- [ ] Regular status updates
- [ ] Report any issues immediately
- [ ] Document all changes made

### After Deployment
- [ ] Send completion notification
- [ ] Share results and metrics
- [ ] Document lessons learned
- [ ] Schedule follow-up review

---

## Contact Information

### Key Personnel
- **Backend Developer**: _____________
- **Unity Developer**: _____________
- **Database Admin**: _____________
- **QA Tester**: _____________

### Emergency Contacts
- **Server Admin**: _____________
- **Database Support**: _____________

---

## Notes Section

### Deployment Date: _______________
### Deployed By: _______________
### Issues Encountered:
```
[Document any issues here]
```

### Resolution Steps:
```
[Document how issues were resolved]
```

### Performance Metrics:
```
API Response Time: _____ ms
Database Query Time: _____ ms
Unity Load Time: _____ seconds
```

---

## Final Sign-Off

- [ ] Backend deployment verified by: _____________
- [ ] Frontend deployment verified by: _____________
- [ ] Database migration verified by: _____________
- [ ] Integration testing completed by: _____________
- [ ] Production deployment approved by: _____________

**Deployment Status**: ⬜ Not Started | ⬜ In Progress | ⬜ Complete | ⬜ Rolled Back

**Date Completed**: _______________

---

**Print this checklist and check off items as you complete them!** ✅
