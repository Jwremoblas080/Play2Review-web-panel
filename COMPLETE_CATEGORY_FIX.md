# Complete Category System Fix - Both Website & Unity Game

**Status**: Ready to implement  
**Time Required**: 20-30 minutes  
**Difficulty**: Easy (Follow steps exactly)

---

## 🎯 What This Fix Does

After this fix:
1. **Website Admin Panel**: Category dropdowns will work when adding/editing questions
2. **Unity Game**: When you select English → Vocabulary, ONLY Vocabulary questions will appear in all levels
3. **Database**: Questions will be properly categorized
4. **Progress Tracking**: Student progress will be tracked by category

---

## ✅ Prerequisites Checklist

Before starting:
- [ ] XAMPP is running (Apache + MySQL both green)
- [ ] You can access: `http://localhost/play2review/admin/`
- [ ] Unity project is closed (we'll open it after database fixes)
- [ ] You have a text editor ready (Notepad++, VS Code, or Sublime)

---

## 📝 PART 1: Database Setup (5 minutes)

### Step 1: Open phpMyAdmin

1. Open browser
2. Go to: `http://localhost/phpmyadmin`
3. Click on `play2review_db` database (left sidebar)

### Step 2: Add Category Column (if not exists)

1. Click "SQL" tab at the top
2. **COPY and PASTE this SQL**:

```sql
-- Add category column if it doesn't exist
ALTER TABLE `quizes` 
ADD COLUMN `category` VARCHAR(255) NULL AFTER `subject_name`;

-- Add indexes for performance
CREATE INDEX `idx_category` ON `quizes`(`category`);
CREATE INDEX `idx_subject_category` ON `quizes`(`subject_name`, `category`);
```

3. Click "Go" button
4. You should see: "Query executed successfully" OR "Duplicate column name 'category'" (both are OK!)

### Step 3: Add Sample Questions with Categories

1. Still in phpMyAdmin SQL tab
2. **COPY and PASTE this SQL**:

```sql
-- Clear existing test data (optional - skip if you have real data)
-- DELETE FROM quizes WHERE quiz_level IN (1, 2);

-- English Questions with Categories
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) VALUES
('english', 1, 'Grammar & Language Structure', 'What is a noun?', 'A person, place, or thing', 'An action word', 'A describing word', 'A connecting word', 1),
('english', 1, 'Vocabulary Development', 'What does "happy" mean?', 'Sad', 'Joyful', 'Angry', 'Tired', 2),
('english', 1, 'Reading Comprehension', 'What is the main idea of a story?', 'The title', 'The central message', 'The first sentence', 'The last word', 2),
('english', 2, 'Grammar & Language Structure', 'Which is a verb?', 'Dog', 'Run', 'Blue', 'Happy', 2),
('english', 2, 'Writing & Composition', 'What is a paragraph?', 'A single word', 'A group of sentences', 'A title', 'A picture', 2),
('english', 2, 'Vocabulary Development', 'What is a synonym for "big"?', 'Small', 'Large', 'Tiny', 'Short', 2);

-- Math Questions with Categories
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) VALUES
('math', 1, 'Numbers & Operations', 'What is 2 + 2?', '3', '4', '5', '6', 2),
('math', 1, 'Numbers & Operations', 'What is 5 - 3?', '1', '2', '3', '4', 2),
('math', 1, 'Geometry', 'How many sides does a triangle have?', '2', '3', '4', '5', 2),
('math', 2, 'Algebra', 'If x + 3 = 7, what is x?', '3', '4', '5', '6', 2),
('math', 2, 'Measurement', 'How many centimeters in a meter?', '10', '50', '100', '1000', 3);

-- Filipino Questions with Categories
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) VALUES
('filipino', 1, 'Wika at Gramatika', 'Ano ang pangngalan?', 'Tao, lugar, o bagay', 'Kilos', 'Paglalarawan', 'Pangatnig', 1),
('filipino', 1, 'Talasalitaan', 'Ano ang kahulugan ng "masaya"?', 'Malungkot', 'Maligaya', 'Galit', 'Pagod', 2),
('filipino', 2, 'Pag-unawa sa Binasa', 'Ano ang pangunahing ideya ng kuwento?', 'Ang pamagat', 'Ang sentral na mensahe', 'Ang unang pangungusap', 'Ang huling salita', 2);

-- AP Questions with Categories
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) VALUES
('ap', 1, 'Kasaysayan', 'Sino ang unang Presidente ng Pilipinas?', 'Jose Rizal', 'Emilio Aguinaldo', 'Andres Bonifacio', 'Manuel Quezon', 2),
('ap', 1, 'Heograpiya', 'Ano ang kabisera ng Pilipinas?', 'Cebu', 'Davao', 'Manila', 'Baguio', 3),
('ap', 2, 'Ekonomiks', 'Ano ang tawag sa pangangalakal?', 'Agrikultura', 'Industriya', 'Komersyo', 'Turismo', 3);

-- Science Questions with Categories
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) VALUES
('science', 1, 'Living Things', 'What do plants need to grow?', 'Only water', 'Only sunlight', 'Water, sunlight, and air', 'Only soil', 3),
('science', 1, 'Matter', 'What are the three states of matter?', 'Solid, liquid, gas', 'Hot, cold, warm', 'Big, small, medium', 'Fast, slow, still', 1),
('science', 2, 'Energy', 'What is the main source of energy on Earth?', 'The Moon', 'The Sun', 'The Stars', 'The Wind', 2),
('science', 2, 'Earth & Space', 'How many planets are in our solar system?', '7', '8', '9', '10', 2);
```

3. Click "Go" button
4. You should see: "25 rows inserted" (or similar)

### Step 4: Verify Database

1. Click on `quizes` table (left sidebar)
2. Click "Browse" tab
3. You should see questions with categories filled in
4. ✅ Database setup complete!

---

## 🌐 PART 2: Website Admin Panel Fix (10 minutes)

The admin panel already has the JavaScript code, but we need to verify the HTML structure has category dropdowns.

### Step 1: Test Current Admin Panel

1. Go to: `http://localhost/play2review/admin/`
2. Login with admin credentials
3. Click "Manage Quizes" in sidebar
4. Click "Add New Question" button
5. Select "English" from Subject dropdown

**EXPECTED RESULT**: Category dropdown should appear with English categories

**IF IT WORKS**: ✅ Skip to Part 3 (Unity Game)

**IF IT DOESN'T WORK**: Continue to Step 2

### Step 2: Check Browser Console for Errors

1. Press `F12` to open Developer Tools
2. Click "Console" tab
3. Look for error messages

**Common Issues**:
- "Category container not found" = HTML structure missing
- "jQuery is not defined" = jQuery not loaded
- No messages = JavaScript not running

### Step 3: Verify JavaScript is Loaded

1. In browser console, type: `jQuery`
2. Press Enter
3. If you see "undefined" = jQuery not loaded (contact support)
4. If you see function code = jQuery is working

### Step 4: Manual Test

1. In "Add New Question" modal:
   - Select "English" → Category dropdown should appear
   - Select "Math" → Different categories should appear
   - Select "Filipino" → Filipino categories should appear

2. Test "Edit Question":
   - Click edit button on any question
   - Category should be pre-selected
   - Changing subject should show different categories

✅ **If everything works, admin panel is fixed!**

---

## 🎮 PART 3: Unity Game Fix (10 minutes)

The Unity scripts already support category filtering. We just need to verify the flow works.

### Step 1: Open Unity Project

1. Open Unity Hub
2. Open project: `UNITY FEB 17 2026 play2review`
3. Wait for Unity to load (may take 2-3 minutes)

### Step 2: Verify Scripts Exist

1. In Project window, navigate to: `Assets/Scripts/`
2. Find: `CategorySelectionManager.cs`
3. Double-click to open in code editor
4. Verify line 217 has: `PlayerPrefs.SetString("SelectedCategory", categoryName);`

✅ **If the line exists, script is correct!**

### Step 3: Verify DynamicQuizSystem

1. Navigate to: `Assets/PLAY2REVIEWSCRIPTS/DynamicQuizSystem/`
2. Open: `DynamicQuizSystem.cs`
3. Find the `LoadQuestionsFromDatabase()` method (around line 153)
4. Verify it has:
```csharp
string selectedCategory = PlayerPrefs.GetString("SelectedCategory", "");
```
and
```csharp
if (!string.IsNullOrEmpty(selectedCategory))
{
    form.AddField("category", selectedCategory);
}
```

✅ **If these lines exist, Unity is ready!**

### Step 4: Test in Unity Editor

1. Click "File" → "Build Settings"
2. Make sure these scenes are in build:
   - GameMenu
   - english_level
   - math_level
   - filipino_level
   - ap_level
   - science_level

3. Close Build Settings

4. In Project window, navigate to: `Assets/Scenes/`
5. Double-click `GameMenu` scene to open it

6. Click the Play button (▶️) at top of Unity

7. **Test the flow**:
   - Login/skip to main menu
   - Click on a subject (e.g., English)
   - **EXPECTED**: Category selection panel should appear
   - Click on a category (e.g., "Vocabulary Development")
   - **EXPECTED**: Game loads english_level scene
   - Start quiz
   - **EXPECTED**: Only Vocabulary questions appear

8. Check Unity Console (bottom of screen) for messages:
   - Should see: `[English Quiz] Loading category: Vocabulary Development`
   - Should see: `[English Quiz] Started with X questions from category: Vocabulary Development`

✅ **If you see these messages and only vocabulary questions appear, Unity is working!**

---

## 🧪 PART 4: Complete System Test (5 minutes)

### Test Scenario 1: English Vocabulary

1. **In Unity Game**:
   - Select English → Vocabulary Development
   - Play through Level 1
   - **VERIFY**: All questions are about vocabulary
   - Play through Level 2
   - **VERIFY**: Still only vocabulary questions

2. **In Admin Panel**:
   - Go to Manage Quizes
   - Filter: Subject = English, Level = 1
   - **VERIFY**: You see the vocabulary questions you just answered
   - **VERIFY**: Category badge shows "Vocabulary Development"

### Test Scenario 2: Math Geometry

1. **In Unity Game**:
   - Return to main menu
   - Select Math → Geometry
   - Play through Level 1
   - **VERIFY**: Questions are about shapes, angles, etc.

2. **In Admin Panel**:
   - Filter: Subject = Math, Level = 1
   - **VERIFY**: Geometry questions are there

### Test Scenario 3: Add New Question

1. **In Admin Panel**:
   - Click "Add New Question"
   - Subject: English
   - **VERIFY**: Category dropdown appears
   - Category: Grammar & Language Structure
   - Level: 1
   - Fill in question and answers
   - Click "Add Question"
   - **VERIFY**: Question appears in table with category badge

2. **In Unity Game**:
   - Select English → Grammar & Language Structure
   - **VERIFY**: Your new question appears in the quiz

✅ **If all tests pass, the system is fully working!**

---

## 🎉 Success Checklist

After completing all steps, verify:

- [ ] Database has `category` column in `quizes` table
- [ ] Sample questions inserted (25+ questions with categories)
- [ ] Admin panel shows category dropdown when adding questions
- [ ] Admin panel shows category dropdown when editing questions
- [ ] Category badges appear in question table
- [ ] Unity game shows category selection panel
- [ ] Unity game loads only selected category questions
- [ ] Unity console shows category filter messages
- [ ] New questions can be added with categories
- [ ] Progress tracking works by category

---

## 🔧 Troubleshooting

### Issue: Category dropdown doesn't appear in admin panel

**Solution**:
1. Press F12 → Console tab
2. Look for JavaScript errors
3. Check if jQuery is loaded: type `jQuery` in console
4. Clear browser cache: Ctrl + Shift + Delete
5. Refresh page: Ctrl + F5

### Issue: Unity shows all questions, not filtered by category

**Solution**:
1. Check Unity Console for errors
2. Verify PlayerPrefs has category: 
   - Add debug line: `Debug.Log("Selected Category: " + PlayerPrefs.GetString("SelectedCategory"));`
3. Check PHP endpoint is receiving category:
   - Open `play2review/get_quiz_questions.php`
   - Add at top: `error_log("Category: " . ($_POST['category'] ?? 'none'));`
   - Check: `C:\xampp\apache\logs\error.log`

### Issue: No questions appear in Unity

**Solution**:
1. Check database has questions for that category
2. Check Unity Console for error messages
3. Verify URL in DynamicQuizSystem.cs:
   - Should be: `http://localhost/play2review/get_quiz_questions.php`
4. Test PHP endpoint directly:
   - Use Postman or browser
   - POST to: `http://localhost/play2review/get_quiz_questions.php`
   - Body: `subject_name=english&quiz_level=1&category=Vocabulary Development`

### Issue: Database errors

**Solution**:
1. Check XAMPP MySQL is running (green light)
2. Verify database name is `play2review_db`
3. Check table name is `quizes` (not `quizzes`)
4. Run SQL again from Step 2

---

## 📊 How It Works

### Data Flow:

```
1. User clicks subject in Unity
   ↓
2. CategorySelectionManager shows categories
   ↓
3. User clicks category (e.g., "Vocabulary Development")
   ↓
4. Category saved to PlayerPrefs
   ↓
5. Scene loads (english_level)
   ↓
6. DynamicQuizSystem reads category from PlayerPrefs
   ↓
7. HTTP POST to get_quiz_questions.php with:
   - subject_name: "english"
   - quiz_level: 1
   - category: "Vocabulary Development"
   ↓
8. PHP queries database:
   SELECT * FROM quizes 
   WHERE subject_name = 'english' 
   AND quiz_level = 1 
   AND category = 'Vocabulary Development'
   ↓
9. PHP returns JSON with filtered questions
   ↓
10. Unity displays ONLY vocabulary questions
```

### Category Persistence:

- **Between levels**: Category stays the same
  - Level 1 Vocabulary → Level 2 Vocabulary → Level 3 Vocabulary
  
- **Between subjects**: Category resets
  - English Vocabulary → (back to menu) → Math Geometry

- **Stored in**: `PlayerPrefs.GetString("SelectedCategory")`

---

## 📝 Quick Reference

### All 25 Categories:

**English (6)**:
1. Grammar & Language Structure
2. Vocabulary Development
3. Reading Comprehension
4. Listening Comprehension
5. Writing & Composition
6. Phonics & Word Recognition

**Math (5)**:
1. Numbers & Operations
2. Algebra
3. Geometry
4. Measurement
5. Data & Probability

**Filipino (5)**:
1. Wika at Gramatika
2. Talasalitaan
3. Pag-unawa sa Binasa
4. Pakikinig
5. Pagsulat

**AP (4)**:
1. Kasaysayan
2. Heograpiya
3. Ekonomiks
4. Sibika at Kultura

**Science (5)**:
1. Living Things
2. Matter
3. Energy
4. Earth & Space
5. Scientific Skills

---

## 🎯 Next Steps

After successful implementation:

1. **Add more questions**: Use admin panel to add questions for each category
2. **Test all categories**: Make sure each category has at least 5 questions per level
3. **Train teachers**: Show them how to add categorized questions
4. **Monitor progress**: Check student progress by category in admin dashboard

---

**Document Version**: 1.0  
**Last Updated**: March 9, 2026  
**Estimated Time**: 20-30 minutes  
**Status**: Ready to implement

