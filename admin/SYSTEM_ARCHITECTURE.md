# 🏗️ System Architecture - Category Progress Tracking

## Overview

This document explains how the category-level student progress tracking system works.

---

## 📊 System Components

```
┌─────────────────────────────────────────────────────────────┐
│                    PLAY2REVIEW SYSTEM                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌──────────────┐      ┌──────────────┐      ┌───────────┐ │
│  │              │      │              │      │           │ │
│  │  Unity Game  │─────▶│  PHP API     │─────▶│  MySQL   │ │
│  │  (Frontend)  │      │  (Backend)   │      │  (Data)  │ │
│  │              │      │              │      │           │ │
│  └──────────────┘      └──────────────┘      └───────────┘ │
│         │                     │                     │       │
│         │                     │                     │       │
│         ▼                     ▼                     ▼       │
│  ┌──────────────┐      ┌──────────────┐      ┌───────────┐ │
│  │              │      │              │      │           │ │
│  │  Student     │      │  Admin       │      │  Tables:  │ │
│  │  Gameplay    │      │  Dashboard   │      │  - users  │ │
│  │              │      │              │      │  - quizes │ │
│  └──────────────┘      └──────────────┘      │  - student│ │
│                                               │    _answers│ │
│                                               └───────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔄 Data Flow

### 1. Student Answers Question

```
Unity Game
    │
    │ Student selects answer
    │
    ▼
Check if correct
    │
    ├─ Correct ──▶ is_correct = 1
    │
    └─ Wrong ────▶ is_correct = 0
    │
    ▼
Prepare API call
    │
    ├─ user_id (from PlayerPrefs)
    ├─ quiz_id (current question)
    └─ is_correct (0 or 1)
```

### 2. API Processes Request

```
log_student_answer.php
    │
    │ Receive POST data
    │
    ▼
Validate inputs
    │
    ├─ user_id > 0?
    ├─ quiz_id > 0?
    └─ Table exists?
    │
    ▼
Get quiz details
    │
    ├─ subject_name
    ├─ category
    └─ level
    │
    ▼
Insert/Update answer
    │
    └─ ON DUPLICATE KEY UPDATE
    │
    ▼
Calculate stats
    │
    ├─ Total answered
    ├─ Total correct
    └─ Accuracy %
    │
    ▼
Return JSON response
```

### 3. Admin Views Progress

```
manage-activities.php
    │
    │ Admin clicks "Details"
    │
    ▼
get_student_progress_details.php
    │
    │ Load student data
    │
    ▼
For each subject:
    │
    ├─ Get categories
    │
    ├─ Count total questions
    │
    ├─ Count answered questions
    │
    ├─ Calculate accuracy
    │
    └─ Determine mastery level
    │
    ▼
Display in modal
    │
    ├─ Progress bars
    ├─ Mastery badges
    └─ Statistics
```

---

## 🗄️ Database Schema

### Tables Relationship

```
┌─────────────────┐
│     users       │
│─────────────────│
│ id (PK)         │◀─────┐
│ player_name     │      │
│ student_id      │      │
│ english_level   │      │
│ math_level      │      │
│ ...             │      │
└─────────────────┘      │
                         │
                         │ Foreign Key
                         │
┌─────────────────┐      │
│     quizes      │      │
│─────────────────│      │
│ id (PK)         │◀─┐   │
│ subject_name    │  │   │
│ category        │  │   │
│ level           │  │   │
│ question        │  │   │
│ answer          │  │   │
└─────────────────┘  │   │
                     │   │
                     │   │ Foreign Keys
                     │   │
              ┌──────┴───┴──────┐
              │ student_answers  │
              │──────────────────│
              │ id (PK)          │
              │ user_id (FK)     │
              │ quiz_id (FK)     │
              │ subject_name     │
              │ category         │
              │ level            │
              │ is_correct       │
              │ answered_at      │
              └──────────────────┘
```

### Key Relationships

1. **users.id → student_answers.user_id**
   - One user can have many answers
   - CASCADE DELETE: If user deleted, answers deleted

2. **quizes.id → student_answers.quiz_id**
   - One quiz can be answered by many users
   - CASCADE DELETE: If quiz deleted, answers deleted

3. **Unique Constraint: (user_id, quiz_id)**
   - Prevents duplicate answers
   - ON DUPLICATE KEY UPDATE: Updates existing answer

---

## 🔍 Query Patterns

### 1. Get Student's Category Progress

```sql
SELECT 
    sa.category,
    COUNT(*) as answered,
    SUM(sa.is_correct) as correct,
    ROUND((SUM(sa.is_correct) / COUNT(*)) * 100, 1) as accuracy
FROM student_answers sa
WHERE sa.user_id = ? 
AND sa.subject_name = ?
GROUP BY sa.category
```

### 2. Get Total Questions per Category

```sql
SELECT 
    category,
    COUNT(*) as total
FROM quizes
WHERE subject_name = ?
AND category IS NOT NULL
GROUP BY category
```

### 3. Calculate Mastery Level

```php
$completion = ($answered / $total) * 100;
$accuracy = ($correct / $answered) * 100;

if ($completion >= 80 && $accuracy >= 70) {
    $mastery = 'Gold';
} elseif ($completion >= 50 && $accuracy >= 60) {
    $mastery = 'Silver';
} elseif ($completion >= 25) {
    $mastery = 'Bronze';
} elseif ($answered > 0) {
    $mastery = 'Beginner';
} else {
    $mastery = 'Not Started';
}
```

---

## 🎯 API Endpoints

### 1. Log Student Answer

**Endpoint:** `POST /play2review/log_student_answer.php`

**Purpose:** Record when a student answers a question

**Input:**
```json
{
  "user_id": 123,
  "quiz_id": 456,
  "is_correct": 1
}
```

**Output:**
```json
{
  "success": true,
  "data": {
    "subject": "english",
    "category": "Grammar",
    "category_stats": {
      "total_answered": 12,
      "total_correct": 10,
      "accuracy": 83.3
    }
  }
}
```

**Process:**
1. Validate inputs
2. Get quiz details (subject, category, level)
3. Insert/update answer record
4. Calculate category statistics
5. Return JSON response

---

## 🎨 Frontend Components

### 1. Admin Dashboard

**File:** `admin/manage-activities.php`

**Features:**
- Student list with progress
- Overall statistics
- Category distribution charts
- Recent activities

**Data Sources:**
- `users` table
- `quizes` table
- `student_answers` table (NEW)

### 2. Student Detail Modal

**File:** `admin/get_student_progress_details.php`

**Features:**
- Subject-level progress
- Category-level breakdown (NEW)
- Mastery badges (NEW)
- Accuracy tracking (NEW)
- Game statistics

**Data Flow:**
```
AJAX Request
    │
    ▼
Load student data
    │
    ▼
For each subject:
    │
    ├─ Get categories
    ├─ Calculate progress
    └─ Determine mastery
    │
    ▼
Render HTML
    │
    ├─ Progress bars
    ├─ Badges
    └─ Statistics
```

---

## 🔐 Security Considerations

### 1. Input Validation

```php
// Sanitize user inputs
$user_id = intval($_POST['user_id']);
$quiz_id = intval($_POST['quiz_id']);
$is_correct = intval($_POST['is_correct']);

// Validate ranges
if ($user_id <= 0 || $quiz_id <= 0) {
    return error();
}
```

### 2. SQL Injection Prevention

```php
// Use mysqli_real_escape_string
$category = mysqli_real_escape_string($con, $quiz['category']);

// Or use prepared statements (recommended)
$stmt = $con->prepare("INSERT INTO student_answers VALUES (?, ?, ?)");
$stmt->bind_param("iii", $user_id, $quiz_id, $is_correct);
```

### 3. Authentication

```php
// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    header("Location: logout.php");
    exit();
}
```

---

## 📊 Performance Optimization

### 1. Database Indexes

```sql
-- Speed up user-specific queries
INDEX idx_user_subject (user_id, subject_name)

-- Speed up category queries
INDEX idx_user_category (user_id, subject_name, category)

-- Speed up time-based queries
INDEX idx_answered_at (answered_at)
```

### 2. Query Optimization

```php
// Use single query instead of multiple
SELECT 
    COUNT(*) as total,
    SUM(is_correct) as correct
FROM student_answers
WHERE user_id = ? AND category = ?
```

### 3. Caching (Future)

```php
// Cache category statistics
$cache_key = "category_stats_{$subject}";
if (!$stats = cache_get($cache_key)) {
    $stats = getCategoryStatistics($subject);
    cache_set($cache_key, $stats, 3600); // 1 hour
}
```

---

## 🔄 Data Lifecycle

### 1. Answer Creation

```
Student answers question
    │
    ▼
API receives request
    │
    ▼
Validate & process
    │
    ▼
Insert into student_answers
    │
    ▼
Return confirmation
```

### 2. Answer Update

```
Student retries question
    │
    ▼
API receives request
    │
    ▼
Check if answer exists
    │
    ├─ Exists ──▶ UPDATE
    │
    └─ New ─────▶ INSERT
    │
    ▼
Return updated stats
```

### 3. Data Deletion

```
Admin deletes user
    │
    ▼
CASCADE DELETE
    │
    └─ All student_answers deleted
    
Admin deletes quiz
    │
    ▼
CASCADE DELETE
    │
    └─ All related answers deleted
```

---

## 🎯 Integration Points

### 1. Unity → PHP

```
Unity Game
    │
    │ UnityWebRequest.Post()
    │
    ▼
PHP API
    │
    │ Process request
    │
    ▼
MySQL Database
```

### 2. PHP → Database

```
PHP Script
    │
    │ mysqli_query()
    │
    ▼
MySQL Server
    │
    │ Execute query
    │
    ▼
Return results
```

### 3. Admin Panel → Data

```
Admin Dashboard
    │
    │ AJAX request
    │
    ▼
PHP Script
    │
    │ Query database
    │
    ▼
Return JSON/HTML
```

---

## 📈 Scalability

### Current Capacity

- **Students:** Unlimited
- **Questions:** Unlimited
- **Answers:** Millions (with proper indexing)
- **Concurrent Users:** Depends on server

### Bottlenecks

1. **Database queries** - Mitigated by indexes
2. **API calls** - Can add caching
3. **Admin dashboard** - Can add pagination

### Future Improvements

1. **Redis caching** for frequently accessed data
2. **Database replication** for read scaling
3. **API rate limiting** to prevent abuse
4. **CDN** for static assets

---

## 🔧 Maintenance

### Regular Tasks

1. **Monitor database size**
   ```sql
   SELECT 
       table_name,
       ROUND(((data_length + index_length) / 1024 / 1024), 2) AS "Size (MB)"
   FROM information_schema.TABLES
   WHERE table_schema = 'play2review_db';
   ```

2. **Check for orphaned records**
   ```sql
   SELECT * FROM student_answers sa
   LEFT JOIN users u ON sa.user_id = u.id
   WHERE u.id IS NULL;
   ```

3. **Optimize tables**
   ```sql
   OPTIMIZE TABLE student_answers;
   ```

### Backup Strategy

1. **Daily backups** of database
2. **Weekly full backups** of entire system
3. **Test restore** monthly

---

## 📞 Troubleshooting

### Common Issues

1. **API not responding**
   - Check XAMPP is running
   - Verify PHP error logs
   - Test endpoint in browser

2. **No data showing**
   - Check table exists
   - Verify answers are being logged
   - Check foreign key constraints

3. **Slow queries**
   - Check indexes are created
   - Analyze query execution plan
   - Consider adding more indexes

---

**Last Updated:** March 2, 2026  
**Version:** 1.0.0  
**Architecture:** LAMP Stack (Linux/Windows, Apache, MySQL, PHP)

