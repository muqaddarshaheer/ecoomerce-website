<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) post('id', 10);
    if (post('action') === 'delete') {
        $pdo->prepare('DELETE FROM feedback WHERE id = ?')->execute([$id]);
        set_flash('success', 'Feedback deleted.');
    } else {
        $pdo->prepare("UPDATE feedback SET status = 'reviewed' WHERE id = ?")->execute([$id]);
        set_flash('success', 'Marked as reviewed.');
    }
    redirect('admin/feedback.php');
}
$rows = $pdo->query('SELECT * FROM feedback ORDER BY id DESC')->fetchAll();
$pageTitle = 'Feedback';
$active = 'feedback';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Feedback</h1>
<?php if (!$rows): ?><div class="empty-state">No feedback yet.</div><?php endif; ?>
<?php foreach ($rows as $row): ?>
    <article class="soft-card mb-3">
        <div class="d-flex justify-content-between gap-2">
            <h2 class="h5 mb-1"><?= e($row['subject']) ?></h2>
            <?= status_badge($row['status']) ?>
        </div>
        <p class="mb-1"><?= e($row['name']) ?> · <?= e($row['email']) ?> · rating <?= (int) $row['rating'] ?>/5 · <?= e(format_date($row['created_at'])) ?></p>
        <p><?= nl2br(e($row['message'])) ?></p>
        <form method="post" class="d-flex gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
            <?php if ($row['status'] === 'new'): ?><button class="btn btn-sm btn-forest" type="submit">Mark reviewed</button><?php endif; ?>
            <button class="btn btn-sm btn-outline-danger" name="action" value="delete" type="submit" data-confirm="Delete this feedback?">Delete</button>
        </form>
    </article>
<?php endforeach; ?>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
