# 🎮 Unity Integration Guide - Category Progress Tracking

## Quick Start for Unity Developers

This guide shows you how to integrate category-level progress tracking into your Unity game.

---

## 📡 API Endpoint

**URL:** `http://localhost/play2review/log_student_answer.php`  
**Method:** POST  
**Content-Type:** `application/x-www-form-urlencoded`

---

## 📤 Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `user_id` | int | ✅ Yes | Student's user ID from database |
| `quiz_id` | int | ✅ Yes | Question ID from `quizes` table |
| `is_correct` | int | ✅ Yes | 1 for correct, 0 for incorrect |

---

## 📥 Response Format

### Success Response
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

### Error Response
```json
{
  "success": false,
  "message": "Invalid user_id or quiz_id"
}
```

---

## 🔧 Unity Implementation

### Step 1: Create AnswerLogger Script

Create a new C# script called `AnswerLogger.cs`:

```csharp
using UnityEngine;
using UnityEngine.Networking;
using System.Collections;

public class AnswerLogger : MonoBehaviour
{
    // Change this to your server URL in production
    private const string API_URL = "http://localhost/play2review/log_student_answer.php";
    
    /// <summary>
    /// Log a student's answer to the server
    /// </summary>
    /// <param name="userId">Student's user ID</param>
    /// <param name="quizId">Question ID from database</param>
    /// <param name="isCorrect">Whether the answer was correct</param>
    public IEnumerator LogAnswer(int userId, int quizId, bool isCorrect)
    {
        // Create form data
        WWWForm form = new WWWForm();
        form.AddField("user_id", userId);
        form.AddField("quiz_id", quizId);
        form.AddField("is_correct", isCorrect ? 1 : 0);
        
        // Send POST request
        using (UnityWebRequest www = UnityWebRequest.Post(API_URL, form))
        {
            yield return www.SendWebRequest();
            
            if (www.result == UnityWebRequest.Result.Success)
            {
                string response = www.downloadHandler.text;
                Debug.Log("Answer logged successfully: " + response);
                
                // Parse JSON response
                try
                {
                    AnswerResponse data = JsonUtility.FromJson<AnswerResponse>(response);
                    
                    if (data.success)
                    {
                        Debug.Log($"Category: {data.data.category}");
                        Debug.Log($"Accuracy: {data.data.category_stats.accuracy}%");
                        
                        // Optional: Show feedback to player
                        ShowCategoryProgress(data.data);
                    }
                    else
                    {
                        Debug.LogWarning("API returned error: " + data.message);
                    }
                }
                catch (System.Exception e)
                {
                    Debug.LogError("Failed to parse response: " + e.Message);
                }
            }
            else
            {
                Debug.LogError("Failed to log answer: " + www.error);
            }
        }
    }
    
    /// <summary>
    /// Optional: Show category progress to player
    /// </summary>
    private void ShowCategoryProgress(AnswerData data)
    {
        // Implement your UI feedback here
        // Example: Show toast notification with accuracy
        string message = $"{data.category}: {data.category_stats.accuracy}% accuracy";
        Debug.Log(message);
        
        // You can trigger UI updates, animations, etc.
    }
}

// JSON Response Classes
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

---

### Step 2: Integrate into Quiz System

In your existing quiz/question script, add this code:

```csharp
public class QuizManager : MonoBehaviour
{
    private AnswerLogger answerLogger;
    
    void Start()
    {
        // Get or add AnswerLogger component
        answerLogger = GetComponent<AnswerLogger>();
        if (answerLogger == null)
        {
            answerLogger = gameObject.AddComponent<AnswerLogger>();
        }
    }
    
    /// <summary>
    /// Call this when student submits an answer
    /// </summary>
    public void OnAnswerSubmitted(int quizId, string studentAnswer, string correctAnswer)
    {
        // Get user ID (stored when student logs in)
        int userId = PlayerPrefs.GetInt("user_id", 0);
        
        if (userId == 0)
        {
            Debug.LogError("User ID not found! Student must be logged in.");
            return;
        }
        
        // Check if answer is correct
        bool isCorrect = studentAnswer.Trim().ToLower() == correctAnswer.Trim().ToLower();
        
        // Log to server
        StartCoroutine(answerLogger.LogAnswer(userId, quizId, isCorrect));
        
        // Continue with your existing logic (show feedback, next question, etc.)
        if (isCorrect)
        {
            ShowCorrectFeedback();
        }
        else
        {
            ShowIncorrectFeedback();
        }
    }
}
```

---

### Step 3: Store User ID on Login

When student logs in, store their user ID:

```csharp
public class LoginManager : MonoBehaviour
{
    public void OnLoginSuccess(int userId, string playerName)
    {
        // Store user ID for later use
        PlayerPrefs.SetInt("user_id", userId);
        PlayerPrefs.SetString("player_name", playerName);
        PlayerPrefs.Save();
        
        Debug.Log($"Logged in as {playerName} (ID: {userId})");
    }
}
```

---

## 🎯 When to Call the API

Call `LogAnswer()` in these scenarios:

### ✅ DO Call:
- ✅ When student submits an answer (correct or incorrect)
- ✅ After completing a quiz question
- ✅ When retrying a failed question
- ✅ During practice mode

### ❌ DON'T Call:
- ❌ When loading questions (before answering)
- ❌ When navigating between questions
- ❌ During tutorial/demo mode
- ❌ Multiple times for the same answer

---

## 🔍 Testing

### Test with Postman

1. **Open Postman**
2. **Create POST request:**
   - URL: `http://localhost/play2review/log_student_answer.php`
   - Method: POST
   - Body: x-www-form-urlencoded
   - Add fields:
     - `user_id`: 1
     - `quiz_id`: 1
     - `is_correct`: 1

3. **Send request**
4. **Verify response:**
   ```json
   {
     "success": true,
     "message": "Answer logged successfully"
   }
   ```

### Test in Unity

1. **Create test scene**
2. **Add AnswerLogger to GameObject**
3. **Create test button:**
   ```csharp
   public void TestLogAnswer()
   {
       StartCoroutine(answerLogger.LogAnswer(1, 1, true));
   }
   ```
4. **Check Unity Console for response**

---

## 🐛 Common Issues

### Issue: "User ID not found"
**Solution:** Ensure `PlayerPrefs.SetInt("user_id", userId)` is called on login

### Issue: "Quiz not found"
**Solution:** Verify `quiz_id` exists in database `quizes` table

### Issue: "Connection refused"
**Solution:** 
- Check XAMPP is running
- Verify URL is correct
- Test in browser first

### Issue: "CORS error"
**Solution:** Add to PHP file (if needed):
```php
header('Access-Control-Allow-Origin: *');
```

---

## 📊 View Results

After logging answers:

1. **Open Admin Panel**
   - URL: `http://localhost/play2review/admin/`

2. **Go to Manage Activities**
   - Click "Manage Activities" in sidebar

3. **Click "Details" on student**
   - See category-level progress
   - View mastery badges
   - Check accuracy percentages

---

## 🎨 Optional: Show Progress in Game

### Example: Category Progress UI

```csharp
public class CategoryProgressUI : MonoBehaviour
{
    public Text categoryNameText;
    public Slider progressSlider;
    public Text accuracyText;
    public Image masteryBadge;
    
    public void UpdateProgress(AnswerData data)
    {
        categoryNameText.text = data.category;
        
        // Calculate progress (you'll need total questions from another API)
        float progress = data.category_stats.total_answered / 15f; // Example: 15 total
        progressSlider.value = progress;
        
        accuracyText.text = $"{data.category_stats.accuracy}% Accuracy";
        
        // Show mastery badge
        if (progress >= 0.8f && data.category_stats.accuracy >= 70f)
        {
            masteryBadge.sprite = goldBadgeSprite;
        }
        else if (progress >= 0.5f && data.category_stats.accuracy >= 60f)
        {
            masteryBadge.sprite = silverBadgeSprite;
        }
        else if (progress >= 0.25f)
        {
            masteryBadge.sprite = bronzeBadgeSprite;
        }
    }
}
```

---

## 📋 Integration Checklist

- [ ] Add `AnswerLogger.cs` script to project
- [ ] Store user ID on login
- [ ] Call `LogAnswer()` when student answers
- [ ] Test with Postman
- [ ] Test in Unity
- [ ] Verify data in admin panel
- [ ] Handle error cases
- [ ] Add loading indicators (optional)
- [ ] Show progress feedback (optional)

---

## 🚀 Production Deployment

When deploying to production:

1. **Change API URL:**
   ```csharp
   private const string API_URL = "https://yourdomain.com/play2review/log_student_answer.php";
   ```

2. **Add error handling:**
   ```csharp
   if (www.result != UnityWebRequest.Result.Success)
   {
       // Retry logic
       // Or queue for later
   }
   ```

3. **Add offline support:**
   ```csharp
   // Queue answers when offline
   // Send when connection restored
   ```

---

## 📞 Support

### For Unity Issues:
- Check Unity Console for errors
- Verify UnityWebRequest is imported
- Test API with Postman first

### For API Issues:
- Check PHP error logs
- Verify database connection
- Test endpoint in browser

---

**Last Updated:** March 2, 2026  
**Version:** 1.0.0  
**For:** Unity Developers  
**Feature:** Category Progress Tracking API Integration

