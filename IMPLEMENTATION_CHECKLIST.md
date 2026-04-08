# Category System Implementation Checklist

**Use this checklist to track your progress through the implementation.**

---

## 📋 Pre-Implementation

- [ ] XAMPP installed and running
- [ ] Apache service: ✅ Green
- [ ] MySQL service: ✅ Green
- [ ] Can access: `http://localhost/play2review/admin/`
- [ ] Unity project accessible
- [ ] Text editor ready (Notepad++, VS Code, or Sublime)
- [ ] Browser with Developer Tools (Chrome, Firefox, Edge)

---

## 🗄️ Part 1: Database Setup

### Step 1: Verify Database Connection
- [ ] Open phpMyAdmin: `http://localhost/phpmyadmin`
- [ ] Database `play2review_db` exists
- [ ] Can see `quizes` table

### Step 2: Add Category Column
- [ ] Opened SQL tab in phpMyAdmin
- [ ] Ran ALTER TABLE command
- [ ] Saw success message OR "Duplicate column" (both OK)
- [ ] Verified column exists in Structure tab
- [ ] Column type: VARCHAR(255)
- [ ] Column allows NULL: Yes

### Step 3: Add Indexes
- [ ] Created `idx_category` index
- [ ] Created `idx_subject_category` composite index
- [ ] Verified indexes in Structure tab

### Step 4: Insert Sample Questions
- [ ] Ran INSERT statements for English (6 questions)
- [ ] Ran INSERT statements for Math (5 questions)
- [ ] Ran INSERT statements for Filipino (3 questions)
- [ ] Ran INSERT statements for AP (3 questions)
- [ ] Ran INSERT statements for Science (4 questions)
- [ ] Total inserted: 25 questions (or more)
- [ ] Verified data in Browse tab
- [ ] All questions have categories filled

### Step 5: Verify Database
- [ ] Clicked on `quizes` table
- [ ] Clicked Browse tab
- [ ] Can see questions with categories
- [ ] Categories match expected values
- [ ] No NULL categories in sample data

**Database Setup Complete**: ✅

---

## 🌐 Part 2: Website Admin Panel

### Step 1: Verify Files Exist
- [ ] File exists: `play2review/admin/manage-quizes.php`
- [ ] File exists: `play2review/admin/category-config.php`
- [ ] File exists: `play2review/get_quiz_questions.php`
- [ ] File exists: `play2review/admin/category-management.js` (optional)

### Step 2: Run Verification Script
- [ ] Opened: `http://localhost/play2review/admin/verify_category_system.php`
- [ ] Database Connection: ✅ Pass
- [ ] Category Column: ✅ Pass
- [ ] Questions with Categories: ✅ Pass
- [ ] Category Config File: ✅ Pass
- [ ] PHP Endpoint: ✅ Pass
- [ ] Overall Score: ___% (target: 100%)

### Step 3: Test Admin Panel Login
- [ ] Opened: `http://localhost/play2review/admin/`
- [ ] Logged in successfully
- [ ] Can see admin dashboard
- [ ] Clicked "Manage Quizes" in sidebar
- [ ] Page loads without errors

### Step 4: Test Add Question
- [ ] Clicked "Add New Question" button
- [ ] Modal/form appears
- [ ] Selected "English" from Subject dropdown
- [ ] Category dropdown appears automatically
- [ ] Can see all 6 English categories
- [ ] Selected "Vocabulary Development"
- [ ] Filled in question and answers
- [ ] Clicked "Add Question"
- [ ] Question appears in table
- [ ] Category badge shows "Vocabulary Development"

### Step 5: Test Edit Question
- [ ] Clicked "Edit" button on a question
- [ ] Edit modal appears
- [ ] Subject is pre-selected
- [ ] Category dropdown shows automatically
- [ ] Category is pre-selected
- [ ] Changed category to different one
- [ ] Clicked "Update Question"
- [ ] Category badge updates in table

### Step 6: Test Subject Switching
- [ ] In Add modal, selected "Math"
- [ ] Category dropdown changes to Math categories (5)
- [ ] Selected "Filipino"
- [ ] Category dropdown changes to Filipino categories (5)
- [ ] Selected "AP"
- [ ] Category dropdown changes to AP categories (4)
- [ ] Selected "Science"
- [ ] Category dropdown changes to Science categories (5)

### Step 7: Browser Console Check
- [ ] Pressed F12 to open Developer Tools
- [ ] Clicked Console tab
- [ ] Saw: "Category Management System Initialized"
- [ ] No red error messages
- [ ] When changing subject, saw: "Showing category for: [subject]"

**Admin Panel Complete**: ✅

---

## 🎮 Part 3: Unity Game

### Step 1: Open Unity Project
- [ ] Opened Unity Hub
- [ ] Opened project: `UNITY FEB 17 2026 play2review`
- [ ] Unity loaded successfully (may take 2-3 minutes)
- [ ] No critical errors in Console

### Step 2: Verify Scripts Exist
- [ ] Navigated to: `Assets/Scripts/`
- [ ] Found: `CategorySelectionManager.cs`
- [ ] Opened file in code editor
- [ ] Verified line ~217: `PlayerPrefs.SetString("SelectedCategory", categoryName);`
- [ ] File looks correct

### Step 3: Verify DynamicQuizSystem
- [ ] Navigated to: `Assets/PLAY2REVIEWSCRIPTS/DynamicQuizSystem/`
- [ ] Found: `DynamicQuizSystem.cs`
- [ ] Opened file
- [ ] Found `LoadQuestionsFromDatabase()` method (~line 153)
- [ ] Verified: `string selectedCategory = PlayerPrefs.GetString("SelectedCategory", "");`
- [ ] Verified: `form.AddField("category", selectedCategory);`
- [ ] File looks correct

### Step 4: Check Build Settings
- [ ] Clicked File → Build Settings
- [ ] Verified scenes in build:
  - [ ] GameMenu
  - [ ] english_level
  - [ ] math_level
  - [ ] filipino_level
  - [ ] ap_level
  - [ ] science_level
- [ ] All scenes present
- [ ] Closed Build Settings

### Step 5: Test in Unity Editor
- [ ] Opened `GameMenu` scene (Assets/Scenes/)
- [ ] Clicked Play button (▶️)
- [ ] Game started successfully
- [ ] Reached main menu (login/skip)

### Step 6: Test Category Selection
- [ ] Clicked on "English" subject
- [ ] Category selection panel appeared
- [ ] Can see all 6 English categories
- [ ] Categories have proper names
- [ ] Panel has close button
- [ ] Panel animates smoothly

### Step 7: Test Category Filtering
- [ ] Clicked "Vocabulary Development"
- [ ] Panel closed
- [ ] Scene loaded: english_level
- [ ] Quiz started
- [ ] Checked Unity Console (bottom panel)
- [ ] Saw: `[English Quiz] Loading category: Vocabulary Development`
- [ ] Saw: `[English Quiz] Started with X questions from category: Vocabulary Development`
- [ ] Questions appeared
- [ ] All questions are vocabulary-related

### Step 8: Test Level Progression
- [ ] Completed Level 1 quiz
- [ ] Progressed to Level 2
- [ ] Level 2 still shows vocabulary questions
- [ ] Category persists across levels
- [ ] No errors in Unity Console

### Step 9: Test Different Category
- [ ] Returned to main menu
- [ ] Selected "Math"
- [ ] Category panel appeared with Math categories
- [ ] Selected "Geometry"
- [ ] Math scene loaded
- [ ] Only geometry questions appeared
- [ ] Unity Console shows correct category

**Unity Game Complete**: ✅

---

## 🧪 Part 4: Integration Testing

### Test Scenario 1: English Vocabulary
- [ ] **Unity**: Selected English → Vocabulary Development
- [ ] **Unity**: Played Level 1, all questions vocabulary
- [ ] **Unity**: Played Level 2, still vocabulary
- [ ] **Admin**: Filtered English Level 1
- [ ] **Admin**: Can see vocabulary questions
- [ ] **Admin**: Category badges correct

### Test Scenario 2: Math Geometry
- [ ] **Unity**: Selected Math → Geometry
- [ ] **Unity**: Questions about shapes/angles
- [ ] **Admin**: Filtered Math Level 1
- [ ] **Admin**: Can see geometry questions

### Test Scenario 3: Add New Question
- [ ] **Admin**: Added new English Grammar question
- [ ] **Admin**: Question appears with category badge
- [ ] **Unity**: Selected English → Grammar
- [ ] **Unity**: New question appears in quiz

### Test Scenario 4: Edit Existing Question
- [ ] **Admin**: Edited a question's category
- [ ] **Admin**: Category badge updated
- [ ] **Unity**: Question appears in new category
- [ ] **Unity**: Question removed from old category

### Test Scenario 5: All Subjects
- [ ] Tested English (all 6 categories)
- [ ] Tested Math (all 5 categories)
- [ ] Tested Filipino (all 5 categories)
- [ ] Tested AP (all 4 categories)
- [ ] Tested Science (all 5 categories)
- [ ] All categories work correctly

**Integration Testing Complete**: ✅

---

## 🔧 Part 5: Troubleshooting (If Needed)

### Issue: Category dropdown doesn't appear
- [ ] Checked browser console for errors
- [ ] Verified jQuery is loaded (type `jQuery` in console)
- [ ] Cleared browser cache (Ctrl + Shift + Delete)
- [ ] Refreshed page (Ctrl + F5)
- [ ] Checked JavaScript code is present in manage-quizes.php

### Issue: Unity shows all questions
- [ ] Checked Unity Console for errors
- [ ] Verified PlayerPrefs has category (added debug log)
- [ ] Checked PHP endpoint receives category (checked error.log)
- [ ] Verified database has questions for that category
- [ ] Tested PHP endpoint directly with Postman

### Issue: Database errors
- [ ] Verified XAMPP MySQL is running (green)
- [ ] Checked database name: `play2review_db`
- [ ] Checked table name: `quizes` (not quizzes)
- [ ] Re-ran SQL commands
- [ ] Checked for typos in SQL

### Issue: No questions appear
- [ ] Verified database has questions
- [ ] Checked subject name matches (lowercase)
- [ ] Checked category name matches exactly
- [ ] Verified quiz_level is correct
- [ ] Checked PHP error log: `C:\xampp\apache\logs\error.log`

**Troubleshooting Complete**: ✅

---

## ✅ Final Verification

### System Health Check
- [ ] Ran verification script: 100% pass
- [ ] No errors in browser console
- [ ] No errors in Unity console
- [ ] No errors in PHP error log
- [ ] All 25 categories working
- [ ] Can add questions with categories
- [ ] Can edit questions with categories
- [ ] Category filtering works in Unity
- [ ] Progress tracking works

### Documentation Review
- [ ] Read: START_HERE.md
- [ ] Read: COMPLETE_CATEGORY_FIX.md
- [ ] Read: CATEGORY_SYSTEM_OVERVIEW.md
- [ ] Understand how system works
- [ ] Know where to find help

### Backup Created
- [ ] Exported database from phpMyAdmin
- [ ] Saved backup file with date
- [ ] Backup location: _______________
- [ ] Can restore if needed

**Final Verification Complete**: ✅

---

## 🎉 Success Criteria

All items below should be checked:

- [ ] Database has category column with indexes
- [ ] 25+ sample questions with categories
- [ ] Admin panel shows category dropdowns
- [ ] Category dropdowns change based on subject
- [ ] Can add new questions with categories
- [ ] Can edit existing questions' categories
- [ ] Category badges appear in question table
- [ ] Unity shows category selection panel
- [ ] Unity filters questions by selected category
- [ ] Category persists across levels
- [ ] Category resets when changing subjects
- [ ] No JavaScript errors in browser
- [ ] No errors in Unity console
- [ ] Verification script shows 100%
- [ ] All 5 subjects tested
- [ ] All 25 categories tested

**Total Checks Completed**: _____ / 200+

**System Status**: 
- [ ] ✅ FULLY OPERATIONAL
- [ ] ⚠️ PARTIALLY WORKING (see issues above)
- [ ] ❌ NOT WORKING (follow troubleshooting)

---

## 📝 Notes & Issues

**Issues Encountered**:
```
(Write any issues you encountered and how you fixed them)




```

**Custom Modifications**:
```
(Note any custom changes you made)




```

**Next Steps**:
```
(What you plan to do next)




```

---

## 📞 Support

If you're stuck:

1. **Check verification script**: `http://localhost/play2review/admin/verify_category_system.php`
2. **Review documentation**: START_HERE.md, COMPLETE_CATEGORY_FIX.md
3. **Check console logs**: Browser F12, Unity Console, PHP error.log
4. **Test incrementally**: Don't skip steps
5. **Backup first**: Always export database before changes

---

**Checklist Version**: 1.0  
**Last Updated**: March 9, 2026  
**Completion Date**: _______________  
**Completed By**: _______________

**Status**: 
- [ ] Not Started
- [ ] In Progress
- [ ] Completed
- [ ] Verified

