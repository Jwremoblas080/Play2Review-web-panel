# Educator Archive Feature - Documentation

## Overview
The archive feature implements a **soft-delete** system for educators, allowing administrators to archive (hide) educators instead of permanently deleting them. Archived educators can be restored at any time or permanently deleted if needed.

---

## Features

### 1. **Soft Delete (Archive)**
- When an admin "deletes" an educator, they are moved to the archive
- The educator's data remains in the database
- Can be restored at any time
- Provides a safety net against accidental deletions

### 2. **Archive View**
- Separate view to see all archived educators
- Shows when each educator was archived
- Quick access to restore or permanently delete

### 3. **Restore Functionality**
- One-click restore from archive
- Educator returns to active list with all data intact
- No data loss during archive/restore cycle

### 4. **Permanent Delete**
- Available only from the archive view
- Requires typing "DELETE" to confirm
- Completely removes educator from database
- Cannot be undone

---

## Database Changes

### New Columns Added to `educators` Table:

| Column | Type | Default | Description |
|--------|------|---------|-------------|
| `is_archived` | TINYINT(1) | 0 | Soft delete flag (0=active, 1=archived) |
| `archived_at` | DATETIME | NULL | Timestamp when archived |
| `archived_by` | INT | NULL | Admin ID who archived the record |

### Installation

Run the SQL migration script:
```sql
-- Located at: admin/sql/add_archive_feature.sql
ALTER TABLE educators 
ADD COLUMN is_archived TINYINT(1) DEFAULT 0,
ADD COLUMN archived_at DATETIME NULL,
ADD COLUMN archived_by INT NULL;

CREATE INDEX idx_is_archived ON educators(is_archived);
CREATE INDEX idx_archived_at ON educators(archived_at);
```

---

## User Interface

### Statistics Dashboard
- **Active Teachers**: Count of non-archived educators
- **Currently Active**: Educators with status='active'
- **Pending Approval**: Educators with status='pending'
- **Archived**: Count of archived educators (clickable to view archive)

### View Toggle
Two view modes accessible via buttons:
1. **Active Teachers** - Shows all non-archived educators
2. **Archived** - Shows all archived educators

### Action Buttons

#### Active View:
- 👁️ **View** - View educator details
- ✏️ **Edit** - Edit educator information
- 📦 **Archive** - Move to archive (soft delete)

#### Archive View:
- ↩️ **Restore** - Restore to active list
- 🗑️ **Permanently Delete** - Remove from database forever

---

## Workflow

### Archiving an Educator

1. Admin clicks the **Archive** button (trash icon) on an active educator
2. Confirmation modal appears:
   - Shows educator name
   - Explains that educator will be moved to archive
   - Notes that restoration is possible
3. Admin confirms
4. Educator is marked as archived:
   - `is_archived` = 1
   - `archived_at` = current timestamp
   - `archived_by` = admin's ID
5. Success message: "Teacher archived successfully! You can restore it from the archive."
6. Educator disappears from active list

### Restoring an Educator

1. Admin navigates to **Archived** view
2. Clicks **Restore** button (undo icon) on archived educator
3. Confirmation modal appears
4. Admin confirms
5. Educator is restored:
   - `is_archived` = 0
   - `archived_at` = NULL
   - `archived_by` = NULL
6. Success message: "Teacher restored successfully!"
7. Educator appears in active list

### Permanently Deleting an Educator

1. Admin navigates to **Archived** view
2. Clicks **Permanently Delete** button (trash icon) on archived educator
3. Warning modal appears:
   - Shows educator name
   - Displays danger warning
   - Requires typing "DELETE" to confirm
4. Admin types "DELETE" and confirms
5. Educator is permanently removed from database
6. Success message: "Teacher permanently deleted!"
7. **This action cannot be undone**

---

## Code Structure

### PHP Actions (manage-educators.php)

```php
// Archive (Soft Delete)
case 'delete_teacher':
    $query = "UPDATE educators SET 
             is_archived = 1, 
             archived_at = NOW(),
             archived_by = '$admin_id'
             WHERE id = '$id'";

// Restore
case 'restore_teacher':
    $query = "UPDATE educators SET 
             is_archived = 0, 
             archived_at = NULL,
             archived_by = NULL
             WHERE id = '$id'";

// Permanent Delete
case 'permanent_delete':
    $query = "DELETE FROM educators WHERE id = '$id'";
```

### Query Logic

```php
// Get view mode
$view_mode = isset($_GET['view']) ? $_GET['view'] : 'active';

// Fetch based on view
if($view_mode == 'archived') {
    $query = "SELECT * FROM educators WHERE is_archived = 1 ORDER BY archived_at DESC";
} else {
    $query = "SELECT * FROM educators WHERE is_archived = 0 ORDER BY created_at DESC";
}
```

### JavaScript Handlers

```javascript
// Archive
$('.delete-teacher').click(function() {
    // Show archive confirmation modal
});

// Restore
$('.restore-teacher').click(function() {
    // Show restore confirmation modal
});

// Permanent Delete
$('.permanent-delete-teacher').click(function() {
    // Show permanent delete modal with DELETE confirmation
});

// Confirm DELETE text
$('#confirm_delete_text').on('input', function() {
    if($(this).val() === 'DELETE') {
        $('#permanent_delete_btn').prop('disabled', false);
    }
});
```

---

## Security Considerations

### 1. **Admin Authentication**
- Only admins can access manage-educators.php
- Session check: `$_SESSION['priviledges'] == 'admin'`

### 2. **SQL Injection Prevention**
- All inputs sanitized with `mysqli_real_escape_string()`
- Prepared statements recommended for production

### 3. **Confirmation Requirements**
- Archive: Simple confirmation
- Restore: Simple confirmation
- Permanent Delete: Requires typing "DELETE"

### 4. **Audit Trail**
- `archived_by` tracks which admin archived the educator
- `archived_at` tracks when archiving occurred
- Useful for accountability and auditing

---

## Benefits

### 1. **Data Safety**
- Prevents accidental permanent deletion
- Provides recovery option
- Maintains data integrity

### 2. **Compliance**
- Meets data retention requirements
- Allows for data recovery if needed
- Audit trail for regulatory compliance

### 3. **User Experience**
- Clear separation between active and archived
- Easy restoration process
- Confirmation steps prevent mistakes

### 4. **Database Performance**
- Archived records excluded from main queries
- Indexes on `is_archived` improve query speed
- Clean active data set

---

## Best Practices

### 1. **Regular Archive Review**
- Periodically review archived educators
- Permanently delete old archived records if no longer needed
- Keep archive clean and manageable

### 2. **Communication**
- Inform educators before archiving
- Document reason for archiving
- Maintain professional records

### 3. **Backup Before Permanent Delete**
- Always backup database before permanent deletions
- Consider export of archived data
- Maintain offline records if required

### 4. **Access Control**
- Only senior admins should permanently delete
- Consider role-based permissions
- Log all permanent deletions

---

## Testing Checklist

- [ ] Archive an educator from active list
- [ ] Verify educator appears in archived view
- [ ] Verify educator removed from active view
- [ ] Verify statistics update correctly
- [ ] Restore an educator from archive
- [ ] Verify educator returns to active list
- [ ] Verify archived count decreases
- [ ] Test permanent delete with wrong confirmation text
- [ ] Test permanent delete with correct confirmation text
- [ ] Verify permanent delete removes from database
- [ ] Test search functionality in both views
- [ ] Test pagination in both views
- [ ] Verify all modals display correctly
- [ ] Test on different screen sizes

---

## Troubleshooting

### Issue: Archived educators still showing in active view
**Solution:** Check database query - ensure `WHERE is_archived = 0` is present

### Issue: Cannot restore educator
**Solution:** Check database permissions and verify `archived_at` column exists

### Issue: Permanent delete button always disabled
**Solution:** Verify JavaScript is loaded and "DELETE" is typed exactly (case-sensitive)

### Issue: Statistics not updating
**Solution:** Clear browser cache and refresh page

### Issue: Archive columns don't exist
**Solution:** Run the SQL migration script: `admin/sql/add_archive_feature.sql`

---

## Future Enhancements

### Potential Improvements:
1. **Bulk Operations**
   - Archive multiple educators at once
   - Restore multiple educators at once

2. **Archive Reasons**
   - Add dropdown to select reason for archiving
   - Store reason in database

3. **Auto-Archive**
   - Automatically archive inactive educators after X days
   - Configurable threshold

4. **Archive Notifications**
   - Email notification when educator is archived
   - Email notification when educator is restored

5. **Advanced Filters**
   - Filter archived by date range
   - Filter by who archived
   - Filter by subject

6. **Export Functionality**
   - Export archived educators to CSV/Excel
   - Backup archived data

---

## Support

For issues or questions:
1. Check this documentation
2. Review the code comments
3. Check database structure
4. Verify SQL migration was run
5. Check browser console for JavaScript errors

---

## Version History

**Version 1.0** (Current)
- Initial implementation
- Soft delete (archive) functionality
- Restore functionality
- Permanent delete with confirmation
- Separate archive view
- Statistics dashboard
- Search functionality

---

## Summary

The archive feature provides a robust, user-friendly way to manage educator records with safety and flexibility. It prevents accidental data loss while maintaining a clean active educator list and providing easy restoration when needed.

**Key Points:**
- ✅ Soft delete prevents permanent data loss
- ✅ Easy restoration process
- ✅ Separate archive view
- ✅ Permanent delete requires confirmation
- ✅ Audit trail with timestamps
- ✅ Clean, intuitive interface
