# 🚀 Quick Start - Category Progress Tracking

## Get Started in 5 Minutes

This guide will get category-level student progress tracking up and running quickly.

---

## Step 1: Run Database Migration (2 minutes)

### Option A: Browser (Easiest)
1. Open your browser
2. Go to: `http://localhost/play2review/admin/run_student_answers_migration.php`
3. Wait for success message
4. Done! ✅

### Option B: phpMyAdmin
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select `play2review_db` database
3. Click "SQL" tab
4. Open file: `admin/create_student_answers_table.sql`
5. Copy all content
6. Paste into SQL box
7. Click "Go"
8. Done! ✅

---

## Step 2: Verify Setup (1 minute)

1. Open phpMyAdmin
2. Select `play2review_db`
3. Look for `student_answers` table in left sidebar
4. Click on it
5. You should see these columns:
   - id
   - user_id
   - quiz_id
   - subject_name
   - category
   - level
   - is_correct
   - answered_at

✅ If you see all columns, setup is complete!

---

## Step 3: View in Admin Panel (1 minute)

1. Login to admin panel: `http://localhost/play2review/admin/`
2. Click "Manage Activities" in sidebar
3. Click "Details" button on any student
4. Scroll down to see "Category Progress by Subject"

**What you'll see:**
- Subject cards (English, Math, Filipino, AP, Science)
- Category progress bars
- Mastery badges
- Accuracy percentages

**Note:** If no data shows, that's normal! Students need to answer questions first.

---

## Step 4: Test the API (1 minute)

### Using Postman:
1. Open Postman
2. Create new POST request
3. URL: `http://localhost/play2review/log_student_answer.php`
4. Body type: `x-www-form-urlencoded`
5. Add fields:
   - `user_id`: 1
   - `quiz_id`: 1
   - `is_correct`: 1
6. Click "Send"
7. You should see:
   ```json
   {
     "success": true,
     "message": "Answer logged successfully"
   }
   ```

### Using Browser (Alternative):
1. Create test file: `test_api.html`
2. Add this code:
   ```html
   <!DOCTYPE html>
   <html>
   <body>
   <h1>Test API</h1>
   <button onclick="testAPI()">Test Log Answer</button>
   <div id="result"></div>
   
   <script>
   function testAPI() {
       fetch('http://localhost/play2review/log_student_answer.php', {
           method: 'POST',
           headers: {'Content-Type': 'application/x-www-form-urlencoded'},
           body: 'user_id=1&quiz_id=1&is_correct=1'
       })
       .then(r => r.json())
       .then(data => {
           document.getElementById('result').innerHTML = 
               '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
       });
   }
   </script>
   </body>
   </html>
   ```
3. Open in browser
4. Click "Test Log Answer"
5. See result

---

## Step 5: Update Unity (For Developers)

### Quick Integration:

1. **Add this script to Unity:**
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
                   Debug.Log("Answer logged: " + www.downloadHandler.text);
               }
               else
               {
                   Debug.LogError("Failed: " + www.error);
               }
           }
       }
   }
   ```

2. **Call it when student answers:**
   ```csharp
   int userId = PlayerPrefs.GetInt("user_id");
   StartCoroutine(answerLogger.LogAnswer(userId, quizId, isCorrect));
   ```

3. **Done!** Answers will now be tracked.

---

## 🎉 You're Done!

The system is now ready to track student progress by category.

### What Happens Next:

1. **Students play the game** → Answers are logged
2. **Data accumulates** → Progress builds up
3. **Admins view progress** → See detailed analytics
4. **Educators intervene** → Help struggling students

---

## 📊 Where to See Results

### Admin Panel:
- **URL:** `http://localhost/play2review/admin/`
- **Page:** Manage Activities
- **Action:** Click "Details" on any student

### What You'll See:
- ✅ Subject-level progress (already existed)
- ✅ Category-level breakdown (NEW!)
- ✅ Mastery badges (NEW!)
- ✅ Accuracy percentages (NEW!)

---

## 🐛 Troubleshooting

### Issue: Migration fails
**Solution:** 
- Check XAMPP is running
- Verify MySQL is started
- Try phpMyAdmin method instead

### Issue: API returns error
**Solution:**
- Check `user_id` exists in `users` table
- Check `quiz_id` exists in `quizes` table
- Verify table was created

### Issue: No progress showing
**Solution:**
- This is normal if no answers logged yet
- Test API with Postman first
- Add sample data manually

### Issue: Unity can't connect
**Solution:**
- Check XAMPP is running
- Verify URL is correct
- Test API in browser first

---

## 📚 Full Documentation

For detailed information, see:

- **Admin Guide:** `admin/CATEGORY_STUDENT_TRACKING_GUIDE.md`
- **Unity Guide:** `UNITY_INTEGRATION_GUIDE.md`
- **Summary:** `admin/IMPLEMENTATION_SUMMARY.md`

---

## ✅ Checklist

- [ ] Database migration completed
- [ ] Table verified in phpMyAdmin
- [ ] Admin panel shows category section
- [ ] API tested with Postman
- [ ] Unity script added (if applicable)
- [ ] Test answer logged successfully
- [ ] Progress displays correctly

---

## 🎯 Next Steps

1. **Add sample data** (optional for testing)
2. **Update Unity game** to call API
3. **Train educators** on new features
4. **Monitor usage** and gather feedback
5. **Celebrate!** 🎉

---

**Setup Time:** ~5 minutes  
**Difficulty:** Easy  
**Status:** Ready to use!

