# 🔄 Discussion Panel Flow Diagram

## Complete User Journey

```
┌─────────────────────────────────────────────────────────────────┐
│                        MAIN MENU SCENE                          │
│                                                                 │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐           │
│  │English│  │ Math │  │Filipino│  │  AP  │  │Science│           │
│  └───┬──┘  └───┬──┘  └───┬──┘  └───┬──┘  └───┬──┘           │
│      │         │         │         │         │                 │
│      └─────────┴─────────┴─────────┴─────────┘                 │
│                        │                                        │
│                        ▼                                        │
│              User clicks Subject                                │
└─────────────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│              CATEGORY SELECTION PANEL                           │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │  Select [Subject Name] Category                         │  │
│  ├─────────────────────────────────────────────────────────┤  │
│  │                                                         │  │
│  │  ┌──────────────────┐  ┌──────────────────┐           │  │
│  │  │   Category 1     │  │   Category 2     │           │  │
│  │  └──────────────────┘  └──────────────────┘           │  │
│  │                                                         │  │
│  │  ┌──────────────────┐  ┌──────────────────┐           │  │
│  │  │   Category 3     │  │   Category 4     │           │  │
│  │  └──────────────────┘  └──────────────────┘           │  │
│  │                                                         │  │
│  │  ┌──────────────────┐                                  │  │
│  │  │   Category 5     │                                  │  │
│  │  └──────────────────┘                                  │  │
│  │                                                         │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                 │
│  User selects a category (e.g., "Algebra")                    │
└─────────────────────────────────────────────────────────────────┘
                         │
                         ▼
              Category Selection Panel
              animates out (fade + scale)
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│              ✨ DISCUSSION PANEL (NEW!)                         │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │  👨‍🏫                                                      │  │
│  │                                                         │  │
│  │  ╔═══════════════════════════════════════════════════╗ │  │
│  │  ║         Algebra Fundamentals                      ║ │  │
│  │  ╚═══════════════════════════════════════════════════╝ │  │
│  │                                                         │  │
│  │  Welcome to the world of Algebra! Think of algebra    │  │
│  │  as a powerful tool that helps us solve puzzles       │  │
│  │  using letters and numbers. It's like learning a      │  │
│  │  secret code that unlocks countless problems.         │  │
│  │                                                         │  │
│  │  Key Learning Points:                                  │  │
│  │  • Working with variables and expressions             │  │
│  │  • Solving linear and quadratic equations             │  │
│  │  • Understanding inequalities and systems             │  │
│  │  • Applying algebraic thinking to real problems       │  │
│  │                                                         │  │
│  │  Algebra might seem challenging at first, but with    │  │
│  │  practice, you'll see patterns everywhere. You've     │  │
│  │  got this!                                             │  │
│  │                                                         │  │
│  │              ┌──────────────────┐                      │  │
│  │              │ Start Learning!  │                      │  │
│  │              └──────────────────┘                      │  │
│  │                                                    [×]  │  │
│  └─────────────────────────────────────────────────────────┘  │
│                                                                 │
│  User reads educational content (30-60 seconds)                │
│  User clicks "Start Learning!" button                          │
└─────────────────────────────────────────────────────────────────┘
                         │
                         ▼
              Discussion Panel
              animates out (fade + scale)
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                    GAME SCENE LOADS                             │
│                                                                 │
│  Subject: Math                                                  │
│  Category: Algebra                                              │
│  Level: Based on player progress                               │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │                                                         │  │
│  │              GAME CONTENT LOADS                         │  │
│  │                                                         │  │
│  │  • Questions specific to Algebra                       │  │
│  │  • Player's current level in this category            │  │
│  │  • Progress tracking for this category                │  │
│  │                                                         │  │
│  └─────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Event Flow Diagram

```
┌──────────────────┐
│  StageLoader     │
│  (Subject Button)│
└────────┬─────────┘
         │
         │ OnSubjectButtonClicked()
         ▼
┌──────────────────────────┐
│ CategorySelectionManager │
│ ShowCategorySelection()  │
└────────┬─────────────────┘
         │
         │ User selects category
         ▼
┌──────────────────────────┐
│ CategorySelectionManager │
│ OnCategorySelected()     │
│ - Saves to PlayerPrefs   │
│ - Fires OnCategoryChosen │
│ - Closes category panel  │
└────────┬─────────────────┘
         │
         │ if useDiscussionPanel
         ▼
┌──────────────────────────┐
│ CategoryDiscussionManager│
│ ShowDiscussion()         │
│ - Loads content          │
│ - Animates in            │
└────────┬─────────────────┘
         │
         │ User clicks "Start"
         ▼
┌──────────────────────────┐
│ CategoryDiscussionManager│
│ OnStartButtonClicked()   │
│ - Animates out           │
│ - Fires event            │
└────────┬─────────────────┘
         │
         │ OnDiscussionComplete event
         ▼
┌──────────────────────────┐
│ CategorySelectionManager │
│ OnDiscussionCompleted()  │
│ - Calls ProceedToGame()  │
└────────┬─────────────────┘
         │
         ▼
┌──────────────────────────┐
│      StageLoader         │
│ LoadSceneWithCategory()  │
│ - Loads game scene       │
└──────────────────────────┘
```

---

## Data Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    DATA STORAGE                             │
└─────────────────────────────────────────────────────────────┘

User Selection:
  Subject: "math"
  Category: "algebra"
           │
           ▼
┌─────────────────────────────────────────────────────────────┐
│                   PlayerPrefs                               │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  "SelectedSubject"  = "math"                          │ │
│  │  "SelectedCategory" = "algebra"                       │ │
│  │  "CurrentLevel"     = (deleted, will be loaded fresh)│ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────────────────────────┐
│              Discussion Content Lookup                      │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  discussionDatabase["math"]["algebra"]               │ │
│  │  {                                                    │ │
│  │    title: "Algebra Fundamentals"                     │ │
│  │    introduction: "Welcome to..."                     │ │
│  │    keyPoints: "• Variables..."                       │ │
│  │    encouragement: "You've got this!"                 │ │
│  │  }                                                    │ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────────────────────────┐
│                  Display in UI                              │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  CategoryTitleText.text = title                       │ │
│  │  DiscussionContentText.text = formatted content      │ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
           │
           ▼
┌─────────────────────────────────────────────────────────────┐
│              Scene Load with Context                        │
│  ┌───────────────────────────────────────────────────────┐ │
│  │  SceneManager.LoadScene("math_level")                 │ │
│  │  + PlayerPrefs carries subject & category             │ │
│  └───────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## Animation Timeline

```
Category Selection → Discussion Panel → Game Scene

Time: 0s
┌────────────────┐
│ Category Panel │  ← Visible, user clicks category
│   (visible)    │
└────────────────┘

Time: 0.2s
┌────────────────┐
│ Category Panel │  ← Animating out (fade + scale)
│  (fading out)  │
└────────────────┘

Time: 0.4s
┌────────────────┐
│ Category Panel │  ← Hidden
│    (hidden)    │
└────────────────┘
┌────────────────┐
│ Discussion     │  ← Starts animating in
│  (scale 0.8)   │
└────────────────┘

Time: 0.7s
┌────────────────┐
│ Discussion     │  ← Fully visible, interactive
│  (scale 1.0)   │
│  [Start Button]│
└────────────────┘

Time: 30-60s (user reads)
┌────────────────┐
│ Discussion     │  ← User clicks "Start"
│  [Start Button]│  ← Button clicked
└────────────────┘

Time: 30.2s
┌────────────────┐
│ Discussion     │  ← Animating out
│  (fading out)  │
└────────────────┘

Time: 30.4s
┌────────────────┐
│ Discussion     │  ← Hidden
│    (hidden)    │
└────────────────┘
│
▼ Scene loads
┌────────────────┐
│  Game Scene    │  ← New scene loads
│   (loading)    │
└────────────────┘
```

---

## Component Relationships

```
┌─────────────────────────────────────────────────────────────┐
│                    GameObject Hierarchy                     │
└─────────────────────────────────────────────────────────────┘

Canvas
├── CategorySelectionPanel
│   ├── CategoryButtonContainer
│   │   └── CategoryButton (prefab instances)
│   └── CloseButton
│
├── DiscussionPanel (NEW!)
│   └── ContentPanel
│       ├── CategoryTitleText
│       ├── DiscussionContentText
│       ├── StartButton
│       ├── CloseButton
│       └── TeacherIcon (optional)
│
└── Other UI Elements

Managers (Empty GameObjects)
├── CategorySelectionManager
│   ├── Script: CategorySelectionManager.cs
│   └── References:
│       ├── categorySelectionPanel
│       ├── discussionManager ← NEW!
│       └── useDiscussionPanel ← NEW!
│
├── CategoryDiscussionManager (NEW!)
│   ├── Script: CategoryDiscussionManager.cs
│   └── References:
│       ├── discussionPanel
│       ├── categoryTitleText
│       ├── discussionContentText
│       ├── startButton
│       └── closeButton
│
└── StageLoader
    ├── Script: StageLoader.cs
    └── References:
        └── categorySelectionManager
```

---

## State Machine

```
┌─────────────────────────────────────────────────────────────┐
│                    Panel State Machine                      │
└─────────────────────────────────────────────────────────────┘

[IDLE]
  │
  │ User clicks subject button
  ▼
[CATEGORY_SELECTION_OPEN]
  │
  │ User selects category
  ▼
[CATEGORY_SELECTION_CLOSING]
  │
  │ Animation complete
  ▼
[DISCUSSION_OPENING] ← NEW STATE
  │
  │ Animation complete
  ▼
[DISCUSSION_OPEN] ← NEW STATE
  │
  │ User clicks "Start" or "Close"
  ▼
[DISCUSSION_CLOSING] ← NEW STATE
  │
  │ Animation complete
  ▼
[LOADING_SCENE]
  │
  │ Scene loaded
  ▼
[GAME_ACTIVE]
```

---

## Optional: Skip Discussion Flow

```
If user has seen discussion before:

┌────────────────┐
│ Category Panel │
└───────┬────────┘
        │
        │ User selects category
        ▼
┌────────────────────────┐
│  Discussion Panel      │
│  ┌──────────────────┐  │
│  │ You've seen this │  │
│  │ before!          │  │
│  │                  │  │
│  │ [Start] [Skip]   │  │
│  └──────────────────┘  │
└────────────────────────┘
        │
        │ User clicks "Skip"
        ▼
┌────────────────┐
│  Game Scene    │
└────────────────┘

Implementation:
- Check PlayerPrefs for viewed flag
- Show "Skip" button if already viewed
- Track view count per category
```

---

**Visual Flow Created**: April 13, 2026
**Version**: 1.0
