# Archive Feature - Quick Setup Guide

## 🚀 Quick Start (5 Minutes)

### Step 1: Run SQL Migration
Execute the SQL script to add archive columns to your database:

```bash
# Option 1: Using MySQL command line
mysql -u root -p play2review < admin/sql/add_archive_feature.sql

# Option 2: Using phpMyAdmin
# 1. Open phpMyAdmin
# 2. Select 'play2review' database
# 3. Click 'SQL' tab
# 4. Copy and paste contents of admin/sql/add_archive_feature.sql
# 5. Click 'Go'
```

### Step 2: Verify Installation
Check that the new columns were added:

```sql
DESCRIBE educators;
```

You should see:
- `is_archived` (tinyint)
- `archived_at` (datetime)
- `archived_by` (int)

### Step 3: Test the Feature
1. Open `admin/manage-educators.php` in your browser
2. You should see:
   - Updated statistics with "Archived" count
   - View toggle buttons (Active Teachers / Archived)
   - Archive icon on delete buttons

### Step 4: Test Archive Workflow
1. Click archive button on any educator
2. Confirm archiving
3. Click "Archived" view button
4. Verify educator appears in archive
5. Click restore button
6. Verify educator returns to active list

---

## ✅ Features Checklist

After setup, you should have:

- [x] Soft delete (archive) functionality
- [x] Separate archive view
- [x] Restore from archive
- [x] Permanent delete with confirmation
- [x] Updated statistics dashboard
- [x] Audit trail (who archived, when)
- [x] Search works in both views

---

## 🎨 Visual Changes

### Before:
- Delete button permanently removed educators
- No way to recover deleted educators
- Single view of all educators

### After:
- Archive button moves educators to archive
- Archived view shows all archived educators
- Restore button brings educators back
- Permanent delete requires typing "DELETE"
- Statistics show archived count

---

## 🔧 Configuration

No configuration needed! The feature works out of the box after running the SQL migration.

### Optional Customizations:

**Change confirmation text:**
Edit `manage-educators.php` line ~730:
```javascript
if($(this).val() === 'DELETE') {
    // Change 'DELETE' to your preferred text
}
```

**Change archive button color:**
Edit `manage-educators.php` CSS section:
```css
.btn-danger {
    background-color: #your-color;
}
```

---

## 📊 Database Schema

```sql
educators table:
├── id (primary key)
├── teacher_name
├── age
├── contact
├── address
├── email
├── password
├── handled_subject
├── status
├── created_at
├── updated_at
├── is_archived (NEW) ← 0=active, 1=archived
├── archived_at (NEW) ← timestamp when archived
└── archived_by (NEW) ← admin ID who archived
```

---

## 🧪 Testing Script

Run these tests to verify everything works:

```
1. Archive Test:
   ✓ Click archive on educator
   ✓ Confirm modal appears
   ✓ Educator moves to archive
   ✓ Success message shows
   ✓ Statistics update

2. Restore Test:
   ✓ Switch to archived view
   ✓ Click restore on educator
   ✓ Confirm modal appears
   ✓ Educator returns to active
   ✓ Success message shows
   ✓ Statistics update

3. Permanent Delete Test:
   ✓ Switch to archived view
   ✓ Click permanent delete
   ✓ Warning modal appears
   ✓ Type "DELETE" to enable button
   ✓ Educator removed from database
   ✓ Success message shows

4. Search Test:
   ✓ Search works in active view
   ✓ Search works in archived view

5. Statistics Test:
   ✓ Active count correct
   ✓ Archived count correct
   ✓ Counts update after actions
```

---

## 🐛 Troubleshooting

### Problem: SQL migration fails
**Solution:**
```sql
-- Run manually:
ALTER TABLE educators ADD COLUMN is_archived TINYINT(1) DEFAULT 0;
ALTER TABLE educators ADD COLUMN archived_at DATETIME NULL;
ALTER TABLE educators ADD COLUMN archived_by INT NULL;
```

### Problem: Archived view shows nothing
**Solution:** Check that `is_archived` column exists and has correct values (0 or 1)

### Problem: Permanent delete button stays disabled
**Solution:** Type exactly "DELETE" (all caps, no spaces)

### Problem: Statistics not updating
**Solution:** Hard refresh browser (Ctrl+F5 or Cmd+Shift+R)

---

## 📝 Quick Reference

### URLs:
- Active view: `manage-educators.php?view=active`
- Archive view: `manage-educators.php?view=archived`

### Actions:
- Archive: `POST action=delete_teacher`
- Restore: `POST action=restore_teacher`
- Permanent Delete: `POST action=permanent_delete`

### Database Queries:
```sql
-- Get active educators
SELECT * FROM educators WHERE is_archived = 0;

-- Get archived educators
SELECT * FROM educators WHERE is_archived = 1;

-- Archive an educator
UPDATE educators SET is_archived=1, archived_at=NOW() WHERE id=?;

-- Restore an educator
UPDATE educators SET is_archived=0, archived_at=NULL WHERE id=?;

-- Permanently delete
DELETE FROM educators WHERE id=?;
```

---

## 🎯 Success Criteria

Your archive feature is working correctly if:

1. ✅ Clicking archive moves educator to archive (not deleted)
2. ✅ Archived view shows archived educators
3. ✅ Restore button brings educators back
4. ✅ Permanent delete requires "DELETE" confirmation
5. ✅ Statistics show correct counts
6. ✅ Search works in both views
7. ✅ No console errors
8. ✅ All modals display correctly

---

## 📚 Additional Resources

- Full Documentation: `ARCHIVE_FEATURE_DOCUMENTATION.md`
- SQL Migration: `sql/add_archive_feature.sql`
- Main File: `manage-educators.php`

---

## 🎉 You're Done!

The archive feature is now fully functional. Educators can be safely archived and restored as needed, with the option for permanent deletion when absolutely necessary.

**Next Steps:**
1. Train your admin team on the new feature
2. Document your archiving policy
3. Set up regular archive reviews
4. Consider implementing additional features (see documentation)

---

**Need Help?** Check the full documentation or review the code comments in `manage-educators.php`.
