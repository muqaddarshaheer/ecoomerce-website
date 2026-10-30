<?php
require_once __DIR__ . '/../includes/init.php';
$admin = require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $action = post('action', 20);
        if ($action === 'status') {
            $status = post('status') === 'inactive' ? 'inactive' : 'active';
            $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'employee'")->execute([$status, (int) post('user_id', 10)]);
            set_flash('success', 'Employee status updated.');
        } else {
            $userId = (int) post('user_id', 10);
            $name = post('full_name', 100);
            $email = strtolower(post('email', 150));
            $phone = post('phone', 20);
            $designation = post('designation', 80);
            $password = (string) ($_POST['password'] ?? '');
            if ($name === '' || !valid_email($email) || !valid_phone($phone) || $designation === '') {
                throw new AppException('Enter the name, a valid email, phone and designation.');
            }
            $dup = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
            $dup->execute([$email, $userId]);
            if ($dup->fetch()) {
                throw new AppException('That email is already used.');
            }
            if ($userId) {
                $pdo->prepare("UPDATE users SET full_name=?, email=?, phone=? WHERE id=? AND role='employee'")->execute([$name, $email, $phone, $userId]);
                $pdo->prepare('UPDATE employees SET designation=? WHERE user_id=?')->execute([$designation, $userId]);
                if ($password !== '') {
                    $problem = password_problem($password);
                    if ($problem) {
                        throw new AppException($problem);
                    }
                    $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
                }
                set_flash('success', 'Employee updated.');
            } else {
                $problem = password_problem($password);
                if ($problem) {
                    throw new AppException($problem);
                }
                $pdo->beginTransaction();
                $last = $pdo->query("SELECT employee_code FROM employees WHERE employee_code LIKE 'EMP%' ORDER BY employee_code DESC LIMIT 1")->fetchColumn();
                $next = 1;
                if ($last && preg_match('/(\d+)$/', (string) $last, $match)) {
                    $next = (int) $match[1] + 1;
                }
                $code = 'EMP' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
                $pdo->prepare("INSERT INTO users (role, full_name, email, password_hash, phone, status) VALUES ('employee',?,?,?,?,'pending')")
                    ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $phone]);
                $newId = (int) $pdo->lastInsertId();
                $pdo->prepare('INSERT INTO employees (user_id, employee_code, designation) VALUES (?,?,?)')->execute([$newId, $code, $designation]);
                $pdo->commit();
                set_flash('success', 'Employee ' . $code . ' was created and is waiting for your approval.');
            }
        }
    } catch (AppException $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        set_flash('danger', $e->getMessage());
    } catch (Throwable $e) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        app_log($e->getMessage());
        set_flash('danger', 'The employee could not be saved.');
    }
    redirect('admin/employees.php' . ((int) post('user_id', 10) && post('action') !== 'status' ? '?edit=' . (int) post('user_id', 10) : ''));
}

$editId = (int) ($_GET['edit'] ?? 0);
$edit = null;
if ($editId) {
    $stmt = $pdo->prepare("SELECT u.*, e.employee_code, e.designation, e.id AS employee_id FROM users u JOIN employees e ON e.user_id = u.id WHERE u.id = ? AND u.role = 'employee'");
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}
$rows = $pdo->query("SELECT u.*, e.employee_code, e.designation FROM users u JOIN employees e ON e.user_id = u.id WHERE u.role = 'employee' ORDER BY e.employee_code")->fetchAll();
$pageTitle = 'Employees';
$active = 'employees';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Employees</h1>
<p>A new employee cannot log in until you approve them. Approved staff can dispatch and deliver orders. They cannot change prices, create staff, or open this admin panel.</p>
<div class="row g-4">
    <div class="col-lg-5">
        <form method="post" class="soft-card">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= (int) ($edit['id'] ?? 0) ?>">
            <h2 class="h5"><?= $edit ? 'Edit ' . e($edit['employee_code']) : 'New employee' ?></h2>
            <label class="form-label">Name</label>
            <input class="form-control mb-2" name="full_name" value="<?= e($edit['full_name'] ?? '') ?>" required>
            <label class="form-label">Email</label>
            <input class="form-control mb-2" type="email" name="email" value="<?= e($edit['email'] ?? '') ?>" required>
            <label class="form-label">Phone</label>
            <input class="form-control mb-2" name="phone" value="<?= e($edit['phone'] ?? '') ?>" required>
            <label class="form-label">Designation</label>
            <input class="form-control mb-2" name="designation" value="<?= e($edit['designation'] ?? 'Dispatch executive') ?>" required>
            <label class="form-label"><?= $edit ? 'New password (optional)' : 'Temporary password' ?></label>
            <input class="form-control mb-3" type="password" name="password" <?= $edit ? '' : 'required' ?>>
            <button class="btn btn-forest" type="submit"><?= $edit ? 'Save' : 'Create employee' ?></button>
        </form>
    </div>
    <div class="col-lg-7">
        <div class="table-responsive soft-card">
            <table class="table align-middle mb-0">
                <thead><tr><th>Code</th><th>Name</th><th>Designation</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['employee_code']) ?></td>
                        <td><?= e($row['full_name']) ?><div class="small text-secondary"><?= e($row['email']) ?></div></td>
                        <td><?= e($row['designation']) ?></td>
                        <td><?= status_badge($row['status']) ?></td>
                        <td class="text-nowrap">
                            <a href="employees.php?edit=<?= (int) $row['id'] ?>">Edit</a>
                            <form class="d-inline" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="status">
                                <input type="hidden" name="user_id" value="<?= (int) $row['id'] ?>">
                                <?php if ($row['status'] === 'pending'): ?>
                                    <input type="hidden" name="status" value="active">
                                    <button class="btn btn-link p-0" type="submit" data-confirm="Approve this employee?">Approve</button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="<?= $row['status'] === 'active' ? 'inactive' : 'active' ?>">
                                    <button class="btn btn-link p-0" type="submit" data-confirm="Change this employee's access?"><?= $row['status'] === 'active' ? 'Deactivate' : 'Approve again' ?></button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?><tr><td colspan="5">No employees yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
