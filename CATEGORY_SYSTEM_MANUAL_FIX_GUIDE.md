# Category System - Complete Manual Fix Guide

**Problem**: Category dropdowns not working in Quiz Management  
**Solution**: Add JavaScript code to manage-quizes.php  
**Time Required**: 15-20 minutes  
**Difficulty**: Easy (Copy & Paste)

---

## 🎯 What You'll Fix

1. Category dropdown will appear when you select a subject
2. Each subject will show its own categories
3. Adding/editing questions will save the category
4. Questions will display with category badges

---

## 📋 Prerequisites

Before starting, make sure:
- [ ] XAMPP is running (Apache + MySQL)
- [ ] You can access: `http://localhost/play2review/admin/`
- [ ] You have a text editor (Notepad++, VS Code, or Sublime Text)

---

## 🔧 Fix #1: Add JavaScript to manage-quizes.php

### Step 1: Open the File

1. Navigate to: `C:\xampp\htdocs\play2review\admin\`
2. Find file: `manage-quizes.php`
3. Right-click → Open with your text editor

### Step 2: Find the Closing Body Tag

1. Press `Ctrl + F` to open Find
2. Search for: `</body>`
3. You should find it near the end of the file (around line 1380-1390)

### Step 3: Add JavaScript Code

**COPY THIS ENTIRE CODE BLOCK**:

```html
<script>
// ========== CATEGORY MANAGEMENT SYSTEM ==========
$(document).ready(function() {
    console.log('Category Management System Initialized');
    
    // ADD MODAL: Subject Change Handler
    $('#add-subject-select').on('change', function() {
        const selectedSubject = $(this).val();
        console.log('Add Modal - Subject changed to:', selectedSubject);
        
        // Hide all category containers
        $('#addQuizModal .category-container').hide();
        $('#addQuizModal .category-select').prop('disabled', true).prop('required', false);
        
        // Show selected subject's category dropdown
        if (selectedSubject) {
            const categoryContainer = $('#add-' + selectedSubject + '-category');
            if (categoryContainer.length) {
                categoryContainer.show();
                categoryContainer.find('.category-select').prop('disabled', false).prop('required', true);
                console.log('Showing category for:', selectedSubject);
            }
        }
    });
    
    // EDIT MODAL: Subject Change Handler
    $('#edit-subject-select').on('change', function() {
        const selectedSubject = $(this).val();
        console.log('Edit Modal - Subject changed to:', selectedSubject);
        
        // Hide all category containers
        $('#editQuizModal .category-container').hide();
        $('#editQuizModal .category-select').prop('disabled', true).prop('required', false);
        
        // Show selected subject's category dropdown
        if (selectedSubject) {
            const categoryContainer = $('#edit-' + selectedSubject + '-category');
            if (categoryContainer.length) {
                categoryContainer.show();
                categoryContainer.find('.category-select').prop('disabled', false).prop('required', true);
                console.log('Showing category for:', selectedSubject);
            }
        }
    });
    
    // EDIT MODAL: Populate Data
    $('.btn-edit').on('click', function() {
        const quizData = $(this).data('quiz');
        console.log('Edit button clicked, quiz data:', quizData);
        
        if (!quizData) return;
        
        // Fill form fields
        $('#edit_quiz_id').val(quizData.id);
        $('#edit-subject-select').val(quizData.subject_name);
        $('#edit_quiz_level').val(quizData.quiz_level);
        $('#edit_question').val(quizData.question);
        $('#edit_answer_a').val(quizData.answer_a);
        $('#edit_answer_b').val(quizData.answer_b);
        $('#edit_answer_c').val(quizData.answer_c);
        $('#edit_answer_d').val(quizData.answer_d);
        $('#edit_correct_answer').val(quizData.correct_answer_number);
        
        // Show category dropdown
        $('#edit-subject-select').trigger('change');
        
        // Set category value
        setTimeout(function() {
            const categorySelect = $('#edit-' + quizData.subject_name + '-category').find('.category-select');
            if (categorySelect.length) {
                categorySelect.val(quizData.category || '');
                console.log('Category set to:', quizData.category);
            }
        }, 100);
    });
    
    // DELETE MODAL: Populate Data
    $('.btn-delete').on('click', function() {
        const quizId = $(this).data('quiz-id');
        const subject = $(this).data('subject');
        const question = $(this).data('question');
        
        $('#delete_quiz_id').val(quizId);
        $('#delete_subject_name').val(subject);
        $('#delete_question_preview').text(question);
    });
    
    // Initialize on page load
    const currentSubject = $('#add-subject-select').val();
    if (currentSubject) {
        $('#add-subject-select').trigger('change');
    }
    
    // Form validation
    $('form').on('submit', function(e) {
        const form = $(this);
        const modal = form.closest('.modal');
        
        if (modal.attr('id') === 'addQuizModal' || modal.attr('id') === 'editQuizModal') {
            const categorySelect = modal.find('.category-select:visible:enabled');
            
            if (categorySelect.length > 0 && !categorySelect.val()) {
                e.preventDefault();
                alert('Please select a category before submitting.');
                categorySelect.focus();
                return false;
            }
        }
    });
    
    // Reset modals when closed
    $('#addQuizModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $(this).find('.category-container').hide();
        $(this).find('.category-select').prop('disabled', true).prop('required', false);
    });
    
    $('#editQuizModal').on('hidden.bs.modal', function() {
        $(this).find('.category-container').hide();
        $(this).find('.category-select').prop('disabled', true).prop('required', false);
    });
    
    console.log('Category Management JavaScript fully initialized');
});
</script>
```

**PASTE IT RIGHT BEFORE `</body>`**

Your file should look like this:

```html
    ... existing HTML code ...
    
    <script>
    // ========== CATEGORY MANAGEMENT SYSTEM ==========
    $(document).ready(function() {
        ... (the code you just copied) ...
    });
    </script>

</body>
</html>
```

### Step 4: Save the File

1. Press `Ctrl + S` to save
2. Close the text editor

---

## 🗄️ Fix #2: Verify Database Structure

### Step 1: Open phpMyAdmin

1. Open browser
2. Go to: `http://localhost/phpmyadmin`
3. Click on `play2review_db` database (left sidebar)

### Step 2: Check if Category Column Exists

1. Click on `quizes` table (left sidebar)
2. Click "Structure" tab at the top
3. Look for a column named `category`

**If you SEE the `category` column**: ✅ Skip to Fix #3

**If you DON'T SEE the `category` column**: Continue below

### Step 3: Add Category Column (if missing)

1. Click "SQL" tab at the top
2. **COPY and PASTE this SQL**:

```sql
-- Add category column
ALTER TABLE `quizes` 
ADD COLUMN `category` VARCHAR(255) NULL AFTER `subject_name`;

-- Add indexes for performance
ALTER TABLE `quizes` 
ADD INDEX `idx_category` (`category`);

ALTER TABLE `quizes` 
ADD INDEX `idx_subject_category` (`subject_name`, `category`);
```

3. Click "Go" button (bottom right)
4. You should see: "Query executed successfully"

---

## 📝 Fix #3: Add Sample Questions

### Step 1: Insert Test Questions

1. In phpMyAdmin, make sure you're in `play2review_db`
2. Click "SQL" tab
3. **COPY and PASTE this SQL**:

```sql
-- English Questions
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) VALUES
('english', 1, 'Grammar & Language Structure', 'What is a noun?', 'A person, place, or thing', 'An action word', 'A describing word', 'A connecting word', 1),
('english', 1, 'Vocabulary Development', 'What does "happy" mean?', 'Sad', 'Joyful', 'Angry', 'Tired', 2),
('english', 1, 'Reading Comprehension', 'What is the main idea of a story?', 'The title', 'The central message', 'The first sentence', 'The last word', 2),
('english', 2, 'Grammar & Language Structure', 'Which is a verb?', 'Dog', 'Run', 'Blue', 'Happy', 2),
('english', 2, 'Writing & Composition', 'What is a paragraph?', 'A single word', 'A group of sentences', 'A title', 'A picture', 2);

-- Math Questions
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) VALUES
('math', 1, 'Numbers & Operations', 'What is 2 + 2?', '3', '4', '5', '6', 2),
('math', 1, 'Numbers & Operations', 'What is 5 - 3?', '1', '2', '3', '4', 2),
('math', 1, 'Geometry', 'How many sides does a triangle have?', '2', '3', '4', '5', 2),
('math', 2, 'Algebra', 'If x + 3 = 7, what is x?', '3', '4', '5', '6', 2),
('math', 2, 'Measurement', 'How many centimeters in a meter?', '10', '50', '100', '1000', 3);

-- Filipino Questions
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) VALUES
('filipino', 1, 'Wika at Gramatika', 'Ano ang pangngalan?', 'Tao, lugar, o bagay', 'Kilos', 'Paglalarawan', 'Pangatnig', 1),
('filipino', 1, 'Talasalitaan', 'Ano ang kahulugan ng "masaya"?', 'Malungkot', 'Maligaya', 'Galit', 'Pagod', 2),
('filipino', 2, 'Pag-unawa sa Binasa', 'Ano ang pangunahing ideya ng kuwento?', 'Ang pamagat', 'Ang sentral na mensahe', 'Ang unang pangungusap', 'Ang huling salita', 2);

-- AP Questions
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) VALUES
('ap', 1, 'Kasaysayan', 'Sino ang unang Presidente ng Pilipinas?', 'Jose Rizal', 'Emilio Aguinaldo', 'Andres Bonifacio', 'Manuel Quezon', 2),
('ap', 1, 'Heograpiya', 'Ano ang kabisera ng Pilipinas?', 'Cebu', 'Davao', 'Manila', 'Baguio', 3),
('ap', 2, 'Ekonomiks', 'Ano ang tawag sa pangangalakal?', 'Agrikultura', 'Industriya', 'Komersyo', 'Turismo', 3);

-- Science Questions
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number) VALUES
('science', 1, 'Living Things', 'What do plants need to grow?', 'Only water', 'Only sunlight', 'Water, sunlight, and air', 'Only soil', 3),
('science', 1, 'Matter', 'What are the three states of matter?', 'Solid, liquid, gas', 'Hot, cold, warm', 'Big, small, medium', 'Fast, slow, still', 1),
('science', 2, 'Energy', 'What is the main source of energy on Earth?', 'The Moon', 'The Sun', 'The Stars', 'The Wind', 2),
('science', 2, 'Earth & Space', 'How many planets are in our solar system?', '7', '8', '9', '10', 2);
```

4. Click "Go" button
5. You should see: "25 rows inserted"

---

## ✅ Testing the Fix

### Test 1: Open Admin Panel

1. Go to: `http://localhost/play2review/admin/`
2. Login with admin credentials
3. Click "Manage Quizes" in the sidebar

### Test 2: Add New Question

1. Click "Add New Question" button (top left)
2. Select "English" from Subject dropdown
3. **VERIFY**: Category dropdown should appear automatically!
4. You should see categories like:
   - Grammar & Language Structure
   - Vocabulary Development
   - Reading Comprehension
   - etc.
5. Select a category
6. Fill in the rest of the form
7. Click "Add Question"
8. **VERIFY**: Question appears in table with a blue category badge

### Test 3: Edit Question

1. Find a question in the table
2. Click the yellow "Edit" button (pencil icon)
3. **VERIFY**: Modal opens with category already selected
4. Change the category to a different one
5. Click "Update Question"
6. **VERIFY**: Category badge updates in the table

### Test 4: Check Browser Console

1. Press `F12` to open Developer Tools
2. Click "Console" tab
3. You should see messages like:
   - "Category Management System Initialized"
   - "Showing category for: english"
4. **NO RED ERRORS should appear**

---

## 🐛 Troubleshooting

### Problem: Category dropdown doesn't appear

**Solution 1**: Check JavaScript was added correctly
1. Open manage-quizes.php in text editor
2. Search for: `Category Management System`
3. Make sure the entire `<script>` block is there
4. Make sure it's BEFORE `</body>`

**Solution 2**: Clear browser cache
1. Press `Ctrl + Shift + Delete`
2. Select "Cached images and files"
3. Click "Clear data"
4. Refresh the page (`Ctrl + F5`)

**Solution 3**: Check jQuery is loaded
1. Press `F12` → Console tab
2. Type: `$`
3. Press Enter
4. If you see "undefined", jQuery is not loaded (contact support)

### Problem: Category not saving

**Solution**: Check database column exists
1. Go to phpMyAdmin
2. Click `play2review_db` → `quizes` → Structure
3. Look for `category` column
4. If missing, run the ALTER TABLE SQL from Fix #2

### Problem: JavaScript errors in console

**Solution**: Check for typos
1. Open manage-quizes.php
2. Find the `<script>` block you added
3. Compare it character-by-character with the code in this guide
4. Look for missing brackets `}` or parentheses `)`

---

## 📊 Verification Checklist

After completing all fixes, verify:

- [ ] JavaScript code added to manage-quizes.php before `</body>`
- [ ] File saved successfully
- [ ] Database has `category` column in `quizes` table
- [ ] Sample questions inserted (25 rows)
- [ ] Admin panel loads without errors
- [ ] Selecting subject shows category dropdown
- [ ] Adding question saves category
- [ ] Editing question shows category
- [ ] Table displays category badges
- [ ] No errors in browser console

---

## 🎉 Success!

If all tests pass, your category system is now working!

**What you can do now**:
1. Add more questions with categories
2. Filter questions by subject and level
3. See category badges in the question table
4. Edit existing questions to add categories

**Next Steps**:
1. Test in Unity game
2. Verify category filtering works in-game
3. Add more questions for each category
4. Train teachers to use the system

---

## 📞 Need Help?

If you're stuck:

1. **Check browser console** (F12 → Console) for error messages
2. **Check PHP errors**: `C:\xampp\apache\logs\error.log`
3. **Verify database**: Use phpMyAdmin to check data
4. **Test with sample data**: Use the SQL queries provided

---

## 📝 Quick Reference

### File Locations
- Admin Panel: `C:\xampp\htdocs\play2review\admin\manage-quizes.php`
- Database: `play2review_db` → `quizes` table
- Error Log: `C:\xampp\apache\logs\error.log`

### URLs
- Admin Login: `http://localhost/play2review/admin/`
- phpMyAdmin: `http://localhost/phpmyadmin`
- Quiz Management: `http://localhost/play2review/admin/manage-quizes.php`

### Categories by Subject

**English** (6):
- Grammar & Language Structure
- Vocabulary Development
- Reading Comprehension
- Listening Comprehension
- Writing & Composition
- Phonics & Word Recognition

**Math** (5):
- Numbers & Operations
- Algebra
- Geometry
- Measurement
- Data & Probability

**Filipino** (5):
- Wika at Gramatika
- Talasalitaan
- Pag-unawa sa Binasa
- Pakikinig
- Pagsulat

**AP** (4):
- Kasaysayan
- Heograpiya
- Ekonomiks
- Sibika at Kultura

**Science** (5):
- Living Things
- Matter
- Energy
- Earth & Space
- Scientific Skills

---

**Document Version**: 1.0  
**Last Updated**: March 9, 2026  
**Estimated Time**: 15-20 minutes  
**Difficulty**: ⭐ Easy (Copy & Paste)
