# Renaming Progress

## COMPLETED ✅

### DatabaseManager Files
- ✅ DatabaseManager_filipino.cs
- ✅ DatabaseManager_math.cs
- ✅ DatabaseManager_science.cs

### LessonProgressTracker Files
- ✅ LessonProgressTracker_filipino.cs

## IN PROGRESS 🔄

### LessonProgressTracker Files
- ⏳ LessonProgressTracker_math.cs
- ⏳ LessonProgressTracker_science.cs

### LoadGameData Files
- ⏳ LoadGameData_filipino.cs
- ⏳ LoadGameData_math.cs
- ⏳ LoadGameData_science.cs

## CHANGES NEEDED FOR EACH FILE

### LessonProgressTracker_math.cs
- `LessonProgressTracker` → `LessonProgressTracker_math`
- `DatabaseManager` → `DatabaseManager_math`
- `UpdateEnglishCategoryLevel` → `UpdateMathCategoryLevel`
- `LoadEnglishCategoryLevel` → `LoadMathCategoryLevel`
- `LessonProgressTracker_References` → `LessonProgressTracker_math_References`
- `[LessonProgressTracker]` → `[LessonProgressTracker_math]`

### LessonProgressTracker_science.cs
- `LessonProgressTracker` → `LessonProgressTracker_science`
- `DatabaseManager` → `DatabaseManager_science`
- `UpdateEnglishCategoryLevel` → `UpdateScienceCategoryLevel`
- `LoadEnglishCategoryLevel` → `LoadScienceCategoryLevel`
- `LessonProgressTracker_References` → `LessonProgressTracker_science_References`
- `[LessonProgressTracker]` → `[LessonProgressTracker_science]`

### LoadGameData Files
- Class names
- DatabaseManager references
- LessonProgressTracker references
- Response class names
- PlayerPrefs keys
