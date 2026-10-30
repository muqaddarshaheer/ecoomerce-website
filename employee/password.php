<?php
require_once __DIR__ . '/../includes/init.php';
$user = require_employee();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $row = db()->prepare('SELECT password_hash FROM users WHERE id = ?');
        $row->execute([(int) $user['id']]);
        if (!password_verify((string) ($_POST['current_password'] ?? ''), (string) $row->fetchColumn())) {
            throw new AppException('The current password is incorrect.');
        }
        $new = (string) ($_POST['new_password'] ?? '');
        if ($new !== ($_POST['confirm_password'] ?? '')) {
            throw new AppException('The new passwords do not match.');
        }
        $problem = password_problem($new);
        if ($problem) {
            throw new AppException($problem);
        }
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), (int) $user['id']]);
        set_flash('success', 'Password changed.');
    } catch (AppException $e) {
        set_flash('danger', $e->getMessage());
    }
    redirect('employee/password.php');
}
$pageTitle = 'Password';
$active = 'password';
require __DIR__ . '/../includes/employee_header.php';
?>
<h1>Change password</h1>
<form method="post" class="soft-card" style="max-width:460px">
    <?= csrf_field() ?>
    <label class="form-label">Current password</label>
    <input class="form-control mb-2" type="password" name="current_password" required>
    <label class="form-label">New password</label>
    <input class="form-control mb-2" type="password" name="new_password" required>
    <label class="form-label">Confirm new password</label>
    <input class="form-control mb-3" type="password" name="confirm_password" required>
    <button class="btn btn-forest" type="submit">Update password</button>
</form>
<?php require __DIR__ . '/../includes/employee_footer.php'; ?>
