# Category System - Visual Overview

## 🎯 System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         UNITY GAME                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────┐      ┌──────────────────────────────────┐   │
│  │  Game Menu   │──────▶│  CategorySelectionManager.cs     │   │
│  │   Scene      │      │  - Shows category buttons         │   │
│  └──────────────┘      │  - Saves to PlayerPrefs           │   │
│                         └──────────────────────────────────┘   │
│                                      │                          │
│                                      │ Stores category          │
│                                      ▼                          │
│                         PlayerPrefs.SetString(                  │
│                           "SelectedCategory",                   │
│                           "Vocabulary Development"              │
│                         )                                       │
│                                      │                          │
│                                      │ Scene loads              │
│                                      ▼                          │
│  ┌──────────────────────────────────────────────────────────┐ │
│  │  english_level Scene                                      │ │
│  │  ┌────────────────────────────────────────────────────┐  │ │
│  │  │  DynamicQuizSystem.cs                              │  │ │
│  │  │  1. Reads category from PlayerPrefs                │  │ │
│  │  │  2. Creates HTTP POST request                      │  │ │
│  │  │  3. Sends to PHP backend                           │  │ │
│  │  └────────────────────────────────────────────────────┘  │ │
│  └──────────────────────────────────────────────────────────┘ │
│                                      │                          │
└──────────────────────────────────────┼──────────────────────────┘
                                       │
                                       │ HTTP POST
                                       │ subject_name: "english"
                                       │ quiz_level: 1
                                       │ category: "Vocabulary Development"
                                       │
                                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                      PHP BACKEND                                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  get_quiz_questions.php                                   │  │
│  │  1. Receives POST data                                    │  │
│  │  2. Validates inputs                                      │  │
│  │  3. Builds SQL query with category filter                │  │
│  │  4. Queries database                                      │  │
│  │  5. Returns JSON response                                 │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                      │                          │
│                                      │ SQL Query                │
│                                      ▼                          │
│  SELECT * FROM quizes                                           │
│  WHERE subject_name = 'english'                                 │
│    AND quiz_level = 1                                           │
│    AND category = 'Vocabulary Development'  ← NEW FILTER        │
│  ORDER BY RAND()                                                │
│                                      │                          │
└──────────────────────────────────────┼──────────────────────────┘
                                       │
                                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                      MYSQL DATABASE                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Database: play2review_db                                       │
│  Table: quizes                                                  │
│                                                                  │
│  ┌────┬──────────┬───────┬─────────────────────┬──────────┐   │
│  │ id │ subject  │ level │ category            │ question │   │
│  ├────┼──────────┼───────┼─────────────────────┼──────────┤   │
│  │ 1  │ english  │   1   │ Vocabulary Dev...   │ What...  │   │
│  │ 2  │ english  │   1   │ Grammar & Lang...   │ Which... │   │
│  │ 3  │ english  │   1   │ Vocabulary Dev...   │ Define.. │   │
│  │ 4  │ english  │   2   │ Vocabulary Dev...   │ Synonym. │   │
│  └────┴──────────┴───────┴─────────────────────┴──────────┘   │
│                                                                  │
│  Returns: Only rows where category = "Vocabulary Development"   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
                                       │
                                       │ JSON Response
                                       ▼
                          {
                            "success": true,
                            "questions": [
                              {
                                "id": 1,
                                "question": "What does 'happy' mean?",
                                "answer_a": "Sad",
                                "answer_b": "Joyful",
                                "answer_c": "Angry",
                                "answer_d": "Tired",
                                "correct_answer_number": 2,
                                "category": "Vocabulary Development"
                              },
                              ...
                            ],
                            "category": "Vocabulary Development"
                          }
                                       │
                                       │ Returns to Unity
                                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                         UNITY GAME                               │
│                                                                  │
│  DynamicQuizSystem receives filtered questions                  │
│  Displays ONLY Vocabulary Development questions                 │
│  Student plays through levels with consistent category          │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔄 User Flow Example

### Scenario: Student wants to practice English Vocabulary

```
Step 1: Main Menu
┌─────────────────────────────────┐
│      PLAY2REVIEW GAME           │
│                                 │
│  ┌─────────┐  ┌─────────┐     │
│  │ English │  │  Math   │     │
│  └─────────┘  └─────────┘     │
│  ┌─────────┐  ┌─────────┐     │
│  │Filipino │  │   AP    │     │
│  └─────────┘  └─────────┘     │
│  ┌─────────┐                   │
│  │ Science │                   │
│  └─────────┘                   │
└─────────────────────────────────┘
         │
         │ Student clicks "English"
         ▼

Step 2: Category Selection
┌─────────────────────────────────┐
│   SELECT ENGLISH CATEGORY       │
│                                 │
│  ┌───────────────────────────┐ │
│  │ Grammar & Language Struct │ │
│  └───────────────────────────┘ │
│  ┌───────────────────────────┐ │
│  │ Vocabulary Development ✓  │ │ ← Student clicks this
│  └───────────────────────────┘ │
│  ┌───────────────────────────┐ │
│  │ Reading Comprehension     │ │
│  └───────────────────────────┘ │
│  ┌───────────────────────────┐ │
│  │ Listening Comprehension   │ │
│  └───────────────────────────┘ │
│  ┌───────────────────────────┐ │
│  │ Writing & Composition     │ │
│  └───────────────────────────┘ │
│  ┌───────────────────────────┐ │
│  │ Phonics & Word Recognition│ │
│  └───────────────────────────┘ │
└─────────────────────────────────┘
         │
         │ Category saved: "Vocabulary Development"
         │ Scene loads: english_level
         ▼

Step 3: Level 1 Quiz
┌─────────────────────────────────┐
│   ENGLISH - LEVEL 1             │
│   Category: Vocabulary Dev.     │
│                                 │
│  Question 1 of 5:               │
│  What does "happy" mean?        │
│                                 │
│  A) Sad                         │
│  B) Joyful      ✓               │
│  C) Angry                       │
│  D) Tired                       │
│                                 │
│  [Next Question]                │
└─────────────────────────────────┘
         │
         │ Student completes Level 1
         │ ALL questions were vocabulary
         ▼

Step 4: Level 2 Quiz
┌─────────────────────────────────┐
│   ENGLISH - LEVEL 2             │
│   Category: Vocabulary Dev.     │
│                                 │
│  Question 1 of 5:               │
│  What is a synonym for "big"?   │
│                                 │
│  A) Small                       │
│  B) Large       ✓               │
│  C) Tiny                        │
│  D) Short                       │
│                                 │
│  [Next Question]                │
└─────────────────────────────────┘
         │
         │ Category persists across levels
         │ Student continues with vocabulary
         ▼

Step 5: Progress Tracking
┌─────────────────────────────────┐
│   STUDENT PROGRESS              │
│                                 │
│  English - Vocabulary Dev.      │
│  ├─ Level 1: ✓ Completed        │
│  ├─ Level 2: ✓ Completed        │
│  ├─ Level 3: In Progress        │
│  └─ Accuracy: 85%               │
│                                 │
│  English - Grammar              │
│  └─ Not started                 │
│                                 │
└─────────────────────────────────┘
```

---

## 🗄️ Database Schema

```sql
CREATE TABLE quizes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_id INT,
    subject_name VARCHAR(50),        -- english, math, filipino, ap, science
    quiz_level INT,                  -- 1-10
    category VARCHAR(255),           -- ← NEW: Category field
    question TEXT,
    answer_a TEXT,
    answer_b TEXT,
    answer_c TEXT,
    answer_d TEXT,
    correct_answer_number INT,       -- 1-4
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_subject_level (subject_name, quiz_level),
    INDEX idx_category (category),
    INDEX idx_subject_category (subject_name, category)  -- ← NEW: Composite index
);
```

### Example Data

```
┌────┬──────────┬───────┬──────────────────────────┬─────────────────────────┐
│ id │ subject  │ level │ category                 │ question                │
├────┼──────────┼───────┼──────────────────────────┼─────────────────────────┤
│ 1  │ english  │   1   │ Vocabulary Development   │ What does "happy" mean? │
│ 2  │ english  │   1   │ Grammar & Language...    │ What is a noun?         │
│ 3  │ english  │   1   │ Reading Comprehension    │ What is main idea?      │
│ 4  │ english  │   2   │ Vocabulary Development   │ Synonym for "big"?      │
│ 5  │ math     │   1   │ Numbers & Operations     │ What is 2 + 2?          │
│ 6  │ math     │   1   │ Geometry                 │ Sides of triangle?      │
│ 7  │ filipino │   1   │ Wika at Gramatika        │ Ano ang pangngalan?     │
│ 8  │ ap       │   1   │ Kasaysayan               │ Unang Presidente?       │
│ 9  │ science  │   1   │ Living Things            │ What do plants need?    │
└────┴──────────┴───────┴──────────────────────────┴─────────────────────────┘
```

---

## 🎨 Admin Panel Interface

```
┌──────────────────────────────────────────────────────────────────┐
│  MANAGE QUIZES                                    [Add Question]  │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Filter: [English ▼] [Level 1 ▼] [Search...]                    │
│                                                                   │
│  ┌────────────────────────────────────────────────────────────┐ │
│  │ Question                          Category        Actions   │ │
│  ├────────────────────────────────────────────────────────────┤ │
│  │ What does "happy" mean?          [Vocabulary Dev] [Edit]   │ │
│  │ A) Sad  B) Joyful ✓              Level 1         [Delete]  │ │
│  │ C) Angry  D) Tired                                          │ │
│  ├────────────────────────────────────────────────────────────┤ │
│  │ What is a noun?                  [Grammar]       [Edit]    │ │
│  │ A) Person, place, thing ✓        Level 1         [Delete]  │ │
│  │ B) Action  C) Describing                                    │ │
│  └────────────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────────────┘
```

### Add Question Modal

```
┌──────────────────────────────────────────────────────────────────┐
│  ADD NEW QUESTION                                          [X]    │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Subject: [English ▼]                                            │
│           ↓                                                       │
│           Category dropdown appears automatically                │
│           ↓                                                       │
│  Category: [Vocabulary Development ▼]  ← Shows English categories│
│            - Grammar & Language Structure                         │
│            - Vocabulary Development                               │
│            - Reading Comprehension                                │
│            - Listening Comprehension                              │
│            - Writing & Composition                                │
│            - Phonics & Word Recognition                           │
│                                                                   │
│  Level: [1 ▼]                                                    │
│                                                                   │
│  Question: [What does "happy" mean?                    ]         │
│                                                                   │
│  Answer A: [Sad                                        ]         │
│  Answer B: [Joyful                                     ]         │
│  Answer C: [Angry                                      ]         │
│  Answer D: [Tired                                      ]         │
│                                                                   │
│  Correct Answer: [B ▼]                                           │
│                                                                   │
│                                    [Cancel]  [Add Question]      │
└──────────────────────────────────────────────────────────────────┘
```

---

## 📊 Category Distribution

```
Total Categories: 25

English (6)  ████████████████████████ 24%
Math (5)     ████████████████████ 20%
Filipino (5) ████████████████████ 20%
AP (4)       ████████████████ 16%
Science (5)  ████████████████████ 20%
```

### Detailed Breakdown

```
ENGLISH (6 categories)
├── Grammar & Language Structure
├── Vocabulary Development
├── Reading Comprehension
├── Listening Comprehension
├── Writing & Composition
└── Phonics & Word Recognition

MATH (5 categories)
├── Numbers & Operations
├── Algebra
├── Geometry
├── Measurement
└── Data & Probability

FILIPINO (5 categories)
├── Wika at Gramatika
├── Talasalitaan
├── Pag-unawa sa Binasa
├── Pakikinig
└── Pagsulat

ARALING PANLIPUNAN (4 categories)
├── Kasaysayan
├── Heograpiya
├── Ekonomiks
└── Sibika at Kultura

SCIENCE (5 categories)
├── Living Things
├── Matter
├── Energy
├── Earth & Space
└── Scientific Skills
```

---

## 🔐 Security Considerations

### Current Implementation (Development)
```
Unity → HTTP → PHP → MySQL
  ↓      ↓      ↓      ↓
 No     No    Basic  Direct
Auth   SSL   Valid  Access
```

### Recommended for Production
```
Unity → HTTPS → PHP (JWT) → MySQL
  ↓       ↓        ↓          ↓
Token   SSL    Prepared   Indexed
Auth  Enabled  Statements Queries
```

**Note**: See `WEB_SECURITY_FIXES_GUIDE.md` for security improvements.

---

## 📈 Performance Optimization

### Query Performance

**Without Category Index** (Slow):
```sql
SELECT * FROM quizes 
WHERE subject_name = 'english' 
  AND quiz_level = 1 
  AND category = 'Vocabulary Development';
-- Scans all rows: ~1000ms for 10,000 questions
```

**With Category Index** (Fast):
```sql
-- Same query with index
CREATE INDEX idx_subject_category ON quizes(subject_name, category);
-- Uses index: ~10ms for 10,000 questions
```

### Caching Strategy (Future Enhancement)

```
Request → Check Cache → Cache Hit? → Return Cached Data
              ↓              ↓
         Cache Miss    Update Cache
              ↓              ↓
         Query DB ←─────────┘
              ↓
         Return Data
```

---

## 🎯 Benefits of Category System

### For Students
✅ Focused practice on specific topics  
✅ Better learning progression  
✅ Clear understanding of strengths/weaknesses  
✅ Targeted improvement areas

### For Teachers
✅ Granular progress tracking  
✅ Identify struggling topics  
✅ Create targeted lesson plans  
✅ Better assessment data

### For Administrators
✅ Detailed analytics  
✅ Curriculum alignment (DepEd)  
✅ Performance metrics by category  
✅ Data-driven decisions

---

**Document Version**: 1.0  
**Last Updated**: March 9, 2026  
**Purpose**: Visual reference for category system architecture

