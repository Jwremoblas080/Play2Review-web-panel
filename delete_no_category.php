<?php
// ============================================================
//  PLAY2REVIEW — Delete Questions With No Category
//  Visit: http://localhost/play2review/delete_no_category.php?token=clean2024
// ============================================================
include('configurations/configurations.php');

$token = $_GET['token'] ?? '';
if ($token !== 'clean2024') {
    die('<h2 style="color:red;font-family:sans-serif;">Access denied. Add ?token=clean2024 to the URL.</h2>');
}

// Preview mode: show what will be deleted without deleting
$preview = isset($_GET['preview']);

// Count questions with no category (NULL or empty string)
$count_result = mysqli_query($con,
    "SELECT COUNT(*) as total FROM quizes WHERE category IS NULL OR category = ''"
);
$count_row = mysqli_fetch_assoc($count_result);
$total = (int)$count_row['total'];

// Fetch sample rows for preview
$sample = mysqli_query($con,
    "SELECT id, subject_name, quiz_level, category, question
     FROM quizes
     WHERE category IS NULL OR category = ''
     ORDER BY subject_name, quiz_level
     LIMIT 50"
);

$deleted = 0;

if (!$preview && $total > 0) {
    $del = mysqli_query($con,
        "DELETE FROM quizes WHERE category IS NULL OR category = ''"
    );
    if ($del) {
        $deleted = mysqli_affected_rows($con);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Delete No-Category Questions</title>
<style>
  body { font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
  h2 { color: #e53935; }
  .success { color: #4CAF50; font-weight: bold; }
  .warn { color: #FF9800; font-weight: bold; }
  table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
  th { background: #333; color: #fff; padding: 8px; text-align: left; }
  td { padding: 6px 8px; border-bottom: 1px solid #ddd; }
  tr:nth-child(even) { background: #f9f9f9; }
  .btn { display: inline-block; margin-top: 16px; padding: 10px 20px; background: #e53935;
         color: white; text-decoration: none; border-radius: 4px; font-weight: bold; }
  .btn-preview { background: #1976D2; }
  .note { color: #888; font-size: 12px; margin-top: 8px; }
</style>
</head>
<body>

<h2>🗑️ Delete Questions With No Category</h2>

<?php if ($preview): ?>
  <p class="warn">PREVIEW MODE — nothing deleted yet.</p>
  <p>Found <strong><?= $total ?></strong> question(s) with no category (showing up to 50):</p>

  <?php if ($total > 0): ?>
    <table>
      <tr><th>ID</th><th>Subject</th><th>Level</th><th>Category</th><th>Question (truncated)</th></tr>
      <?php while ($row = mysqli_fetch_assoc($sample)): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['subject_name']) ?></td>
          <td><?= $row['quiz_level'] ?></td>
          <td><?= $row['category'] === '' || $row['category'] === null ? '<em style="color:#aaa;">(empty)</em>' : htmlspecialchars($row['category']) ?></td>
          <td><?= htmlspecialchars(mb_substr($row['question'], 0, 80)) ?>...</td>
        </tr>
      <?php endwhile; ?>
    </table>
    <a class="btn" href="?token=clean2024">⚠️ Confirm Delete All <?= $total ?> Questions</a>
  <?php else: ?>
    <p class="success">✅ No questions without a category found. Nothing to delete.</p>
  <?php endif; ?>

<?php else: ?>

  <?php if ($total === 0 && $deleted === 0): ?>
    <p class="success">✅ No questions without a category found. Database is clean.</p>
  <?php else: ?>
    <p class="success">✅ Deleted <strong><?= $deleted ?></strong> question(s) with no category.</p>
  <?php endif; ?>

<?php endif; ?>

<p class="note">
  Run with <code>?token=clean2024&preview=1</code> to preview before deleting.<br>
  Run with <code>?token=clean2024</code> to delete immediately.
</p>

<a class="btn btn-preview" href="?token=clean2024&preview=1">👁 Preview First</a>

</body>
</html>
