# Category System Fix - Complete Implementation Guide

**Issue**: Category dropdowns not working in Quiz Management  
**Root Cause**: Missing JavaScript to show/hide category dropdowns based on subject selection  
**Solution**: Add JavaScript event handlers and ensure proper form submission

---

## 🔧 Step 1: Add JavaScript to manage-quizes.php

**Location**: `play2review/admin/manage-quizes.php`  
**Action**: Add this JavaScript code BEFORE the closing `</body>` tag

Find this line near the end of the file:
```html
</body>
</html>
```

**ADD THIS CODE BEFORE `</body>`**:

```html
<script>
// Category Management JavaScript
$(document).ready(function() {
    
    // ========== ADD MODAL: Subject Change Handler ==========
    $('#add-subject-select').on('change', function() {
        const selectedSubject = $(this).val();
        console.log('Add Modal - Subject changed to:', selectedSubject);
        
        // Hide all category containers
        $('#addQuizModal .category-container').hide();
        $('#addQuizModal .category-select').prop('disabled', true).prop('required', false);
        
        // Show and enable the selected subject's category dropdown
        if (selectedSubject) {
            const categoryContainer = $('#add-' + selectedSubject + '-category');
            if (categoryContainer.length) {
                categoryContainer.show();
                categoryContainer.find('.category-select').prop('disabled', false).prop('required', true);
                console.log('Showing category container for:', selectedSubject);
            } else {
                console.error('Category container not found for:', selectedSubject);
            }
        }
    });
    
    // ========== EDIT MODAL: Subject Change Handler ==========
    $('#edit-subject-select').on('change', function() {
        const selectedSubject = $(this).val();
        console.log('Edit Modal - Subject changed to:', selectedSubject);
        
        // Hide all category containers
        $('#editQuizModal .category-container').hide();
        $('#editQuizModal .category-select').prop('disabled', true).prop('required', false);
        
        // Show and enable the selected subject's category dropdown
        if (selectedSubject) {
            const categoryContainer = $('#edit-' + selectedSubject + '-category');
            if (categoryContainer.length) {
                categoryContainer.show();
                categoryContainer.find('.category-select').prop('disabled', false).prop('required', true);
                console.log('Showing category container for:', selectedSubject);
            } else {
                console.error('Category container not found for:', selectedSubject);
            }
        }
    });
    
    // ========== EDIT MODAL: Populate Data ==========
    $('.btn-edit').on('click', function() {
        const quizData = $(this).data('quiz');
        console.log('Edit button clicked, quiz data:', quizData);
        
        // Populate form fields
        $('#edit_quiz_id').val(quizData.id);
        $('#edit-subject-select').val(quizData.subject_name);
        $('#edit_quiz_level').val(quizData.quiz_level);
        $('#edit_question').val(quizData.question);
        $('#edit_answer_a').val(quizData.answer_a);
        $('#edit_answer_b').val(quizData.answer_b);
        $('#edit_answer_c').val(quizData.answer_c);
        $('#edit_answer_d').val(quizData.answer_d);
        $('#edit_correct_answer').val(quizData.correct_answer_number);
        
        // Trigger subject change to show correct category dropdown
        $('#edit-subject-select').trigger('change');
        
        // Set category value after dropdown is shown
        setTimeout(function() {
            const categorySelect = $('#edit-' + quizData.subject_name + '-category').find('.category-select');
            if (categorySelect.length) {
                categorySelect.val(quizData.category || '');
                console.log('Set category to:', quizData.category);
            }
        }, 100);
    });
    
    // ========== DELETE MODAL: Populate Data ==========
    $('.btn-delete').on('click', function() {
        const quizId = $(this).data('quiz-id');
        const subject = $(this).data('subject');
        const question = $(this).data('question');
        
        $('#delete_quiz_id').val(quizId);
        $('#delete_subject_name').val(subject);
        $('#delete_question_preview').text(question);
    });
    
    // ========== INITIALIZE: Show category for current subject ==========
    const currentSubject = $('#add-subject-select').val();
    if (currentSubject) {
        $('#add-subject-select').trigger('change');
    }
    
    console.log('Category management JavaScript initialized');
});
</script>
```

---

## 🗄️ Step 2: Verify Database Structure

**Action**: Run this SQL to ensure the category column exists

**Open**: phpMyAdmin → play2review_db → SQL tab

**Execute this SQL**:

```sql
-- Check if category column exists
SHOW COLUMNS FROM quizes LIKE 'category';

-- If it doesn't exist, add it
ALTER TABLE `quizes` 
ADD COLUMN IF NOT EXISTS `category` VARCHAR(255) NULL AFTER `subject_name`;

-- Add indexes for performance
ALTER TABLE `quizes` 
ADD INDEX IF NOT EXISTS `idx_category` (`category`);

ALTER TABLE `quizes` 
ADD INDEX IF NOT EXISTS `idx_subject_category` (`subject_name`, `category`);

-- Verify the structure
DESCRIBE quizes;
```

**Expected Output**: You should see a `category` column of type VARCHAR(255)

---

## 📝 Step 3: Insert Sample Questions with Categories

**Action**: Add test questions to verify the system works

**Open**: phpMyAdmin → play2review_db → SQL tab

**Execute this SQL**:

```sql
-- Sample English Questions with Categories
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number, teacher_id) VALUES
('english', 1, 'Grammar & Language Structure', 'What is a noun?', 'A person, place, or thing', 'An action word', 'A describing word', 'A connecting word', 1, NULL),
('english', 1, 'Vocabulary Development', 'What does "happy" mean?', 'Sad', 'Joyful', 'Angry', 'Tired', 2, NULL),
('english', 1, 'Reading Comprehension', 'What is the main idea of a story?', 'The title', 'The central message', 'The first sentence', 'The last word', 2, NULL),
('english', 2, 'Grammar & Language Structure', 'Which is a verb?', 'Dog', 'Run', 'Blue', 'Happy', 2, NULL),
('english', 2, 'Writing & Composition', 'What is a paragraph?', 'A single word', 'A group of sentences', 'A title', 'A picture', 2, NULL);

-- Sample Math Questions with Categories
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number, teacher_id) VALUES
('math', 1, 'Numbers & Operations', 'What is 2 + 2?', '3', '4', '5', '6', 2, NULL),
('math', 1, 'Numbers & Operations', 'What is 5 - 3?', '1', '2', '3', '4', 2, NULL),
('math', 1, 'Geometry', 'How many sides does a triangle have?', '2', '3', '4', '5', 2, NULL),
('math', 2, 'Algebra', 'If x + 3 = 7, what is x?', '3', '4', '5', '6', 2, NULL),
('math', 2, 'Measurement', 'How many centimeters in a meter?', '10', '50', '100', '1000', 3, NULL);

-- Sample Filipino Questions with Categories
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number, teacher_id) VALUES
('filipino', 1, 'Wika at Gramatika', 'Ano ang pangngalan?', 'Tao, lugar, o bagay', 'Kilos', 'Paglalarawan', 'Pangatnig', 1, NULL),
('filipino', 1, 'Talasalitaan', 'Ano ang kahulugan ng "masaya"?', 'Malungkot', 'Maligaya', 'Galit', 'Pagod', 2, NULL),
('filipino', 2, 'Pag-unawa sa Binasa', 'Ano ang pangunahing ideya ng kuwento?', 'Ang pamagat', 'Ang sentral na mensahe', 'Ang unang pangungusap', 'Ang huling salita', 2, NULL);

-- Sample AP Questions with Categories
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number, teacher_id) VALUES
('ap', 1, 'Kasaysayan', 'Sino ang unang Presidente ng Pilipinas?', 'Jose Rizal', 'Emilio Aguinaldo', 'Andres Bonifacio', 'Manuel Quezon', 2, NULL),
('ap', 1, 'Heograpiya', 'Ano ang kabisera ng Pilipinas?', 'Cebu', 'Davao', 'Manila', 'Baguio', 3, NULL),
('ap', 2, 'Ekonomiks', 'Ano ang tawag sa pangangalakal?', 'Agrikultura', 'Industriya', 'Komersyo', 'Turismo', 3, NULL);

-- Sample Science Questions with Categories
INSERT INTO quizes (subject_name, quiz_level, category, question, answer_a, answer_b, answer_c, answer_d, correct_answer_number, teacher_id) VALUES
('science', 1, 'Living Things', 'What do plants need to grow?', 'Only water', 'Only sunlight', 'Water, sunlight, and air', 'Only soil', 3, NULL),
('science', 1, 'Matter', 'What are the three states of matter?', 'Solid, liquid, gas', 'Hot, cold, warm', 'Big, small, medium', 'Fast, slow, still', 1, NULL),
('science', 2, 'Energy', 'What is the main source of energy on Earth?', 'The Moon', 'The Sun', 'The Stars', 'The Wind', 2, NULL),
('science', 2, 'Earth & Space', 'How many planets are in our solar system?', '7', '8', '9', '10', 2, NULL);

-- Verify the data was inserted
SELECT subject_name, quiz_level, category, COUNT(*) as question_count 
FROM quizes 
GROUP BY subject_name, quiz_level, category 
ORDER BY subject_name, quiz_level, category;
```

**Expected Output**: You should see questions grouped by subject, level, and category

---

## ✅ Step 4: Test the System

### Test 1: Add New Question

1. Go to: `http://localhost/play2review/admin/manage-quizes.php`
2. Click "Add New Question" button
3. Select a subject (e.g., "English")
4. **VERIFY**: Category dropdown should appear automatically
5. Select a category (e.g., "Grammar & Language Structure")
6. Select a level (e.g., "Level 1")
7. Fill in question and answers
8. Click "Add Question"
9. **VERIFY**: Question appears in the table with category badge

### Test 2: Edit Existing Question

1. Find a question in the table
2. Click the yellow "Edit" button
3. **VERIFY**: Modal opens with all fields populated including category
4. Change the category
5. Click "Update Question"
6. **VERIFY**: Category is updated in the table

### Test 3: Filter by Category

1. Select a subject from the subject tabs
2. **VERIFY**: Questions show category badges
3. Check that questions with different categories are displayed

### Test 4: Unity Integration

1. Open Unity project
2. Play the game
3. Select a subject (e.g., English)
4. Select a category (e.g., "Grammar & Language Structure")
5. **VERIFY**: Only questions from that category load

---

## 🐛 Troubleshooting

### Issue: Category dropdown doesn't appear

**Solution**:
1. Open browser console (F12)
2. Check for JavaScript errors
3. Verify jQuery is loaded: Type `$` in console, should not be undefined
4. Check if the JavaScript code was added correctly

### Issue: Category not saving to database

**Solution**:
1. Check if category column exists:
```sql
SHOW COLUMNS FROM quizes LIKE 'category';
```

2. Check form submission in browser Network tab
3. Verify the category value is being sent in POST data

### Issue: "Category container not found" error in console

**Solution**:
1. Check that category-config.php is included at the top of manage-quizes.php
2. Verify the HTML has all 5 category containers (english, math, filipino, ap, science)
3. Check that container IDs match: `add-{subject}-category` and `edit-{subject}-category`

### Issue: Questions load but no category filter works

**Solution**:
1. Check get_quiz_questions.php has category filtering code
2. Verify Unity is sending the category parameter
3. Check Unity console for debug logs showing category selection

---

## 📊 Verification Queries

Run these SQL queries to verify everything is working:

```sql
-- 1. Check total questions per subject and category
SELECT 
    subject_name,
    category,
    COUNT(*) as total_questions
FROM quizes
GROUP BY subject_name, category
ORDER BY subject_name, category;

-- 2. Find questions without categories
SELECT 
    id,
    subject_name,
    quiz_level,
    LEFT(question, 50) as question_preview,
    category
FROM quizes
WHERE category IS NULL OR category = '';

-- 3. Check category distribution
SELECT 
    subject_name,
    COUNT(DISTINCT category) as unique_categories,
    COUNT(*) as total_questions
FROM quizes
GROUP BY subject_name;

-- 4. Verify indexes exist
SHOW INDEX FROM quizes WHERE Key_name LIKE '%category%';
```

---

## 🎯 Success Criteria

✅ **System is working correctly when**:

1. **Admin Panel**:
   - [ ] Subject dropdown shows all 5 subjects
   - [ ] Changing subject shows correct category dropdown
   - [ ] Category dropdown shows correct categories for each subject
   - [ ] Adding question saves category to database
   - [ ] Editing question shows and updates category
   - [ ] Table displays category badges for each question

2. **Database**:
   - [ ] `category` column exists in `quizes` table
   - [ ] Questions have category values (not NULL)
   - [ ] Indexes exist for performance

3. **Unity Integration**:
   - [ ] Category selection panel appears when subject is clicked
   - [ ] Selecting category loads only questions from that category
   - [ ] PlayerPrefs stores selected category
   - [ ] Quiz system reads category from PlayerPrefs

4. **API**:
   - [ ] get_quiz_questions.php accepts category parameter
   - [ ] API filters questions by category when provided
   - [ ] API returns category in response JSON

---

## 📝 Quick Reference: Category Lists

### English (6 categories)
1. Grammar & Language Structure
2. Vocabulary Development
3. Reading Comprehension
4. Listening Comprehension
5. Writing & Composition
6. Phonics & Word Recognition

### Math (5 categories)
1. Numbers & Operations
2. Algebra
3. Geometry
4. Measurement
5. Data & Probability

### Filipino (5 categories)
1. Wika at Gramatika
2. Talasalitaan
3. Pag-unawa sa Binasa
4. Pakikinig
5. Pagsulat

### Araling Panlipunan (4 categories)
1. Kasaysayan
2. Heograpiya
3. Ekonomiks
4. Sibika at Kultura

### Science (5 categories)
1. Living Things
2. Matter
3. Energy
4. Earth & Space
5. Scientific Skills

---

## 🔄 Maintenance

### Adding New Categories

1. Edit `play2review/admin/category-config.php`
2. Add category to appropriate subject array
3. Update Unity's `CategorySelectionManager.cs` with same categories
4. No database changes needed!

### Removing Categories

1. Check if any questions use the category:
```sql
SELECT COUNT(*) FROM quizes WHERE category = 'Category Name';
```

2. If questions exist, either delete them or reassign to another category
3. Remove from category-config.php
4. Update Unity's CategorySelectionManager.cs

---

## 📞 Support

If you encounter issues:

1. Check browser console for JavaScript errors
2. Check PHP error logs: `xampp/apache/logs/error.log`
3. Verify database structure with SQL queries above
4. Test with sample data provided
5. Check Unity console for category-related debug logs

---

**Document Version**: 1.0  
**Last Updated**: March 9, 2026  
**Status**: Ready for Implementation  
**Estimated Time**: 30-45 minutes
