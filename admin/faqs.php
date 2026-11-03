<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        if (post('action') === 'delete') {
            $pdo->prepare('DELETE FROM faqs WHERE id = ?')->execute([(int) post('id', 10)]);
            set_flash('success', 'Question deleted.');
        } else {
            $id = (int) post('id', 10);
            $question = post('question', 255);
            $answer = post('answer', 5000);
            $sort = (int) post('sort_order', 6);
            $status = post('status') === 'inactive' ? 'inactive' : 'active';
            if (strlen($question) < 5 || strlen($answer) < 5) {
                throw new AppException('Enter a question and an answer.');
            }
            if ($id) {
                $pdo->prepare('UPDATE faqs SET question=?, answer=?, sort_order=?, status=? WHERE id=?')->execute([$question, $answer, $sort, $status, $id]);
                set_flash('success', 'Question updated.');
            } else {
                $pdo->prepare('INSERT INTO faqs (question, answer, sort_order, status) VALUES (?,?,?,?)')->execute([$question, $answer, $sort, $status]);
                set_flash('success', 'Question added.');
            }
        }
    } catch (AppException $e) {
        set_flash('danger', $e->getMessage());
    }
    redirect('admin/faqs.php');
}

$edit = null;
if ((int) ($_GET['edit'] ?? 0)) {
    $stmt = $pdo->prepare('SELECT * FROM faqs WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $edit = $stmt->fetch();
}
$rows = $pdo->query('SELECT * FROM faqs ORDER BY sort_order, id')->fetchAll();
$pageTitle = 'FAQs';
$active = 'faqs';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>FAQs</h1>
<div class="row g-4">
    <div class="col-lg-5">
        <form method="post" class="soft-card">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
            <label class="form-label">Question</label>
            <input class="form-control mb-2" name="question" value="<?= e($edit['question'] ?? '') ?>" required>
            <label class="form-label">Answer</label>
            <textarea class="form-control mb-2" name="answer" rows="5" required><?= e($edit['answer'] ?? '') ?></textarea>
            <label class="form-label">Sort order</label>
            <input class="form-control mb-2" name="sort_order" value="<?= e((string) ($edit['sort_order'] ?? 0)) ?>">
            <label class="form-label">Status</label>
            <select class="form-select mb-3" name="status">
                <option value="active">Active</option>
                <option value="inactive" <?= (($edit['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
            <button class="btn btn-forest" type="submit">Save</button>
        </form>
    </div>
    <div class="col-lg-7">
        <?php foreach ($rows as $row): ?>
            <article class="soft-card mb-2">
                <strong><?= e($row['question']) ?></strong> <?= status_badge($row['status']) ?>
                <p class="mb-2"><?= e($row['answer']) ?></p>
                <div class="row-acts">
                    <a class="btn btn-line btn-sm" href="faqs.php?edit=<?= (int) $row['id'] ?>">Edit</a>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <button class="btn btn-outline-danger btn-sm" type="submit" data-confirm="Delete this question?">Delete</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
