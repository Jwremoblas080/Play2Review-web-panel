# 📊 Category-Level Student Progress Tracking

## Overview

This system tracks individual student progress at the **category level** for each subject, providing detailed insights into student performance across all 25 DepEd-aligned categories.

---

## 🎯 What's New

### Before (Phase 1):
- ✅ Question distribution by category (admin view)
- ✅ Subject-level progress tracking
- ❌ No student-specific category tracking

### After (Phase 2):
- ✅ Question distribution by category
- ✅ Subject-level progress tracking
- ✅ **Student-specific category progress**
- ✅ **Category mastery levels**
- ✅ **Accuracy tracking per category**
- ✅ **API endpoint for Unity integration**

---

## 🗄️ Database Setup

### Step 1: Run Migration

**Option A: Browser-Based (Recommended for Windows)**
1. Open browser
2. Go to: `http://localhost/play2review/admin/run_student_answers_migration.php`
3. Click through the migration
4. Verify success message

**Option B: phpMyAdmin**
1. Open phpMyAdmin
2. Select `play2review_db` database
3. Go to SQL tab
4. Copy contents of `admin/create_student_answers_table.sql`
5. Execute

**Option C: Command Line**
```cmd
cd c:\xampp\mysql\bin
mysql -u root play2review_db < "c:\xampp\htdocs\play2review\admin\create_student_answers_table.sql"
```

### Step 2: Verify Table Created

In phpMyAdmin, check that `student_answers` table exists with these columns:
- `id` (Primary Key)
- `user_id` (Foreign Key → users)
- `quiz_id` (Foreign Key → quizes)
- `subject_name`
- `category`
- `level`
- `is_correct`
- `answered_at`

---

## 📊 New Features

### 1. Student Detail Modal - Category Breakdown

When you click "Details" on a student in **Manage Activities**, you now see:

```
┌─────────────────────────────────────────────────────────┐
│ 📊 Category Progress by Subject                         │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ 🟢 English                                              │
│ ├─ Grammar & Language Structure                         │
│ │   ████████████░░░░░░░░ 12/15 questions • 83% accuracy│
│ │   🏆 Gold                                             │
│ ├─ Vocabulary Development                               │
│ │   ████████░░░░░░░░░░░░ 7/10 questions • 71% accuracy │
│ │   🥈 Silver                                           │
│ └─ Reading Comprehension                                │
│     ████░░░░░░░░░░░░░░░░ 3/12 questions • 67% accuracy │
│     🥉 Bronze                                           │
└─────────────────────────────────────────────────────────┘
```

**Shows for each category:**
- Progress bar (% of questions answered)
- Questions answered vs total available
- Accuracy percentage
- Mastery badge (Gold/Silver/Bronze/Beginner/Not Started)

### 2. Mastery Level System

| Level | Requirements | Badge |
|-------|-------------|-------|
| **Gold** 🏆 | 80%+ completion + 70%+ accuracy | Yellow badge |
| **Silver** 🥈 | 50%+ completion + 60%+ accuracy | Blue badge |
| **Bronze** 🥉 | 25%+ completion | Primary badge |
| **Beginner** 📚 | Started but <25% | Light badge |
| **Not Started** ⚪ | No questions answered | Gray badge |

### 3. API Endpoint for Unity

**Endpoint:** `POST /play2review/log_student_answer.php`

**Parameters:**
```json
{
  "user_id": 123,
  "quiz_id": 456,
  "is_correct": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Answer logged successfully",
  "data": {
    "subject": "english",
    "category": "Grammar & Language Structure",
    "level": 5,
    "is_correct": true,
    "category_stats": {
      "total_answered": 12,
      "total_correct": 10,
      "accuracy": 83.3
    }
  }
}
```

---

## 🎮 Unity Integration

### C# Example Code

```csharp
using UnityEngine;
using UnityEngine.Networking;
using System.Collections;

public class AnswerLogger : MonoBehaviour
{
    private const string API_URL = "http://localhost/play2review/log_student_answer.php";
    
    public IEnumerator LogAnswer(int userId, int quizId, bool isCorrect)
    {
        WWWForm form = new WWWForm();
        form.AddField("user_id", userId);
        form.AddField("quiz_id", quizId);
        form.AddField("is_correct", isCorrect ? 1 : 0);
        
        using (UnityWebRequest www = UnityWebRequest.Post(API_URL, form))
        {
            yield return www.SendWebRequest();
            
            if (www.result == UnityWebRequest.Result.Success)
            {
                string response = www.downloadHandler.text;
                Debug.Log("Answer logged: " + response);
                
                // Parse JSON response
                AnswerResponse data = JsonUtility.FromJson<AnswerResponse>(response);
                
                if (data.success)
                {
                    Debug.Log($"Category: {data.data.category}");
                    Debug.Log($"Accuracy: {data.data.category_stats.accuracy}%");
                }
            }
            else
            {
                Debug.LogError("Failed to log answer: " + www.error);
            }
        }
    }
}

[System.Serializable]
public class AnswerResponse
{
    public bool success;
    public string message;
    public AnswerData data;
}

[System.Serializable]
public class AnswerData
{
    public string subject;
    public string category;
    public int level;
    public bool is_correct;
    public CategoryStats category_stats;
}

[System.Serializable]
public class CategoryStats
{
    public int total_answered;
    public int total_correct;
    public float accuracy;
}
```

### When to Call the API

Call `LogAnswer()` whenever a student:
1. Answers a quiz question (correct or incorrect)
2. Completes a level
3. Retries a question

**Example Usage:**
```csharp
// After student answers a question
int userId = PlayerPrefs.GetInt("user_id");
int quizId = currentQuestion.id;
bool isCorrect = studentAnswer == correctAnswer;

StartCoroutine(answerLogger.LogAnswer(userId, quizId, isCorrect));
```

---

## 📈 How to View Progress

### For Administrators:

1. **Login to Admin Panel**
   - URL: `http://localhost/play2review/admin/`

2. **Go to Manage Activities**
   - Click "Manage Activities" in sidebar
   - Or: `http://localhost/play2review/admin/manage-activities.php`

3. **View Overall Statistics**
   - Scroll to bottom: "Question Distribution by Category"
   - See how many questions exist per category

4. **View Individual Student Progress**
   - Click "Details" button on any student
   - Modal opens showing:
     - Subject-level progress
     - **Category-level breakdown** (NEW!)
     - Mastery badges
     - Accuracy percentages

---

## 🎨 Visual Design

### Category Progress Card

Each subject shows a collapsible card with:
- **Header**: Subject name with icon and color
- **Body**: List of categories with:
  - Category name
  - Progress bar (visual indicator)
  - "X/Y questions" counter
  - Accuracy percentage
  - Mastery badge

### Color Coding

- **English**: Green (#0A5F38)
- **Math**: Blue (#36b9cc)
- **Filipino**: Yellow (#f6c23e)
- **AP**: Teal (#1cc88a)
- **Science**: Red (#e74a3b)

---

## 📊 Sample Data

### Example Student Progress

**Student: Juan Dela Cruz**

**English:**
- Grammar & Language Structure: 15/15 (100%) • 87% accuracy • 🏆 Gold
- Vocabulary Development: 8/10 (80%) • 75% accuracy • 🏆 Gold
- Reading Comprehension: 6/12 (50%) • 67% accuracy • 🥈 Silver
- Listening Comprehension: 2/8 (25%) • 50% accuracy • 🥉 Bronze
- Writing & Composition: 0/7 (0%) • N/A • ⚪ Not Started

**Math:**
- Numbers & Operations: 18/20 (90%) • 83% accuracy • 🏆 Gold
- Algebra: 4/8 (50%) • 75% accuracy • 🥈 Silver
- Geometry: 1/5 (20%) • 100% accuracy • 📚 Beginner
- Measurement: 0/4 (0%) • N/A • ⚪ Not Started

---

## 🔍 Use Cases

### For Administrators:

1. **Identify Struggling Students**
   - See which categories students struggle with
   - Low accuracy = need intervention
   - Not started = need encouragement

2. **Content Gap Analysis**
   - Categories with low completion across all students
   - May indicate content is too difficult
   - Or not enough questions available

3. **Performance Tracking**
   - Monitor improvement over time
   - Track mastery progression
   - Identify high performers

### For Educators:

1. **Personalized Instruction**
   - Focus on weak categories
   - Provide targeted exercises
   - Celebrate mastery achievements

2. **Curriculum Planning**
   - See which topics need more coverage
   - Balance lesson plans
   - Align with DepEd standards

3. **Assessment Design**
   - Create quizzes targeting weak areas
   - Balance difficulty across categories
   - Track learning outcomes

---

## 🐛 Troubleshooting

### Issue: "Student answers tracking not enabled"
**Cause:** Migration not run
**Solution:** 
1. Run `run_student_answers_migration.php`
2. Verify table exists in phpMyAdmin

### Issue: No category progress showing
**Cause:** No answers logged yet
**Solution:**
1. Ensure Unity game is calling the API
2. Check API endpoint is accessible
3. Verify questions have categories assigned

### Issue: All categories show "Not Started"
**Cause:** Student hasn't answered questions yet
**Solution:** This is normal for new students

### Issue: API returns "Quiz not found"
**Cause:** Invalid quiz_id
**Solution:** Verify quiz_id exists in `quizes` table

### Issue: Accuracy shows 0%
**Cause:** All answers incorrect OR no answers yet
**Solution:** Check `is_correct` values in database

---

## 📊 Database Queries

### Check Student Progress
```sql
SELECT 
    u.player_name,
    sa.subject_name,
    sa.category,
    COUNT(*) as answered,
    SUM(sa.is_correct) as correct,
    ROUND((SUM(sa.is_correct) / COUNT(*)) * 100, 1) as accuracy
FROM student_answers sa
JOIN users u ON sa.user_id = u.id
WHERE u.id = 123
GROUP BY sa.subject_name, sa.category
ORDER BY sa.subject_name, sa.category;
```

### Top Performers by Category
```sql
SELECT 
    u.player_name,
    sa.category,
    COUNT(*) as answered,
    ROUND((SUM(sa.is_correct) / COUNT(*)) * 100, 1) as accuracy
FROM student_answers sa
JOIN users u ON sa.user_id = u.id
WHERE sa.subject_name = 'english'
AND sa.category = 'Grammar & Language Structure'
GROUP BY u.id
HAVING accuracy >= 80
ORDER BY accuracy DESC, answered DESC
LIMIT 10;
```

### Category Completion Rates
```sql
SELECT 
    sa.subject_name,
    sa.category,
    COUNT(DISTINCT sa.user_id) as students_attempted,
    COUNT(*) as total_answers,
    ROUND(AVG(sa.is_correct) * 100, 1) as avg_accuracy
FROM student_answers sa
GROUP BY sa.subject_name, sa.category
ORDER BY sa.subject_name, avg_accuracy DESC;
```

---

## 🚀 Future Enhancements

### Phase 3 (Planned):
- [ ] Category-based XP system
- [ ] Unlock achievements per category
- [ ] Leaderboards by category
- [ ] Recommended practice questions
- [ ] Category difficulty ratings
- [ ] Time-based analytics
- [ ] Export progress reports (PDF)
- [ ] Parent/guardian dashboard

### Phase 4 (Future):
- [ ] AI-powered recommendations
- [ ] Adaptive difficulty
- [ ] Peer comparison
- [ ] Gamification rewards
- [ ] Mobile app integration

---

## 📞 Support

### For Questions:
1. Verify migration completed successfully
2. Check Unity is calling the API correctly
3. Ensure questions have categories assigned
4. Test with sample data first

### For Issues:
1. Check browser console for errors
2. Verify database connection
3. Test API endpoint with Postman
4. Check PHP error logs

---

## 📋 Checklist

### Setup Checklist:
- [ ] Run database migration
- [ ] Verify `student_answers` table exists
- [ ] Test API endpoint with Postman
- [ ] Update Unity game to call API
- [ ] Assign categories to all questions
- [ ] Test with sample student data
- [ ] Verify progress displays correctly
- [ ] Train educators on new features

### Testing Checklist:
- [ ] Create test student account
- [ ] Answer questions in different categories
- [ ] Check progress in admin panel
- [ ] Verify accuracy calculations
- [ ] Test mastery level badges
- [ ] Check all 5 subjects
- [ ] Test with multiple students
- [ ] Verify API error handling

---

**Last Updated:** March 2, 2026  
**Version:** 2.0.0  
**Status:** ✅ Production Ready  
**Feature:** Category-Level Student Progress Tracking

