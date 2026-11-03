<?php
require_once __DIR__ . '/../includes/init.php';
$user = require_customer();
$pdo = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post('action', 30);
    try {
        if ($action === 'profile') {
            $name = post('full_name', 100);
            $phone = post('phone', 20);
            $email = strtolower(post('email', 150));
            if ($name === '' || !valid_phone($phone) || !valid_email($email)) {
                throw new AppException('Check your name, phone and email.');
            }
            $exists = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
            $exists->execute([$email, (int) $user['id']]);
            if ($exists->fetch()) {
                throw new AppException('That email is already used.');
            }
            $pdo->prepare('UPDATE users SET full_name = ?, phone = ?, email = ? WHERE id = ? AND role = \'customer\'')
                ->execute([$name, $phone, $email, (int) $user['id']]);
            set_flash('success', 'Profile updated.');
        } elseif ($action === 'password') {
            $current = (string) ($_POST['current_password'] ?? '');
            $new = (string) ($_POST['new_password'] ?? '');
            $row = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
            $row->execute([(int) $user['id']]);
            if (!password_verify($current, (string) $row->fetchColumn())) {
                throw new AppException('The current password is incorrect.');
            }
            if ($new !== ($_POST['confirm_password'] ?? '')) {
                throw new AppException('The new passwords do not match.');
            }
            $problem = password_problem($new);
            if ($problem) {
                throw new AppException($problem);
            }
            $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), (int) $user['id']]);
            set_flash('success', 'Password changed.');
        } elseif ($action === 'address') {
            $address = clean_address($_POST);
            $count = $pdo->prepare('SELECT COUNT(*) FROM customer_addresses WHERE customer_id = ?');
            $count->execute([(int) $user['customer_id']]);
            $default = (int) $count->fetchColumn() === 0 ? 1 : 0;
            $pdo->prepare('INSERT INTO customer_addresses (customer_id, full_name, phone, address_line, city, state, postal_code, is_default) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([(int) $user['customer_id'], $address['full_name'], $address['phone'], $address['address_line'], $address['city'], $address['state'], $address['postal_code'], $default]);
            set_flash('success', 'Address saved.');
        } elseif ($action === 'default') {
            $id = (int) post('address_id', 10);
            $pdo->prepare('UPDATE customer_addresses SET is_default = 0 WHERE customer_id = ?')->execute([(int) $user['customer_id']]);
            $pdo->prepare('UPDATE customer_addresses SET is_default = 1 WHERE id = ? AND customer_id = ?')->execute([$id, (int) $user['customer_id']]);
            set_flash('success', 'Default address updated.');
        } elseif ($action === 'delete_address') {
            $pdo->prepare('DELETE FROM customer_addresses WHERE id = ? AND customer_id = ?')->execute([(int) post('address_id', 10), (int) $user['customer_id']]);
            set_flash('success', 'Address removed.');
        }
        redirect('customer/profile.php');
    } catch (AppException $e) {
        $error = $e->getMessage();
    }
}

$fresh = current_user();
$addresses = $pdo->prepare('SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, id DESC');
$addresses->execute([(int) $user['customer_id']]);
$rows = $addresses->fetchAll();
$pageTitle = 'Profile';
$accountActive = 'profile';
$accountUser = $user;
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/customer_open.php';
?>
    <h1>Your profile</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-6">
            <form method="post" class="soft-card mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="profile">
                <h2 class="h5">Details</h2>
                <label class="form-label">Name</label>
                <input class="form-control mb-2" name="full_name" value="<?= e($fresh['full_name']) ?>">
                <label class="form-label">Email</label>
                <input class="form-control mb-2" type="email" name="email" value="<?= e($fresh['email']) ?>">
                <label class="form-label">Phone</label>
                <input class="form-control mb-3" name="phone" value="<?= e((string) $fresh['phone']) ?>">
                <button class="btn btn-forest" type="submit">Save details</button>
            </form>
            <form method="post" class="soft-card">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password">
                <h2 class="h5">Password</h2>
                <input class="form-control mb-2" type="password" name="current_password" placeholder="Current password" required>
                <input class="form-control mb-2" type="password" name="new_password" placeholder="New password" required>
                <input class="form-control mb-3" type="password" name="confirm_password" placeholder="Confirm new password" required>
                <button class="btn btn-outline-dark" type="submit">Change password</button>
            </form>
        </div>
        <div class="col-lg-6">
            <div class="soft-card mb-3">
                <h2 class="h5">Addresses</h2>
                <?php if (!$rows): ?><p>No saved address yet.</p><?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <div class="border rounded p-2 mb-2">
                        <strong><?= e($row['full_name']) ?></strong>
                        <?= (int) $row['is_default'] === 1 ? '<span class="badge text-bg-success">Default</span>' : '' ?>
                        <div><?= e($row['address_line']) ?>, <?= e($row['city']) ?>, <?= e($row['state']) ?> <?= e($row['postal_code']) ?></div>
                        <div class="d-flex gap-2 mt-2">
                            <?php if ((int) $row['is_default'] !== 1): ?>
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="default"><input type="hidden" name="address_id" value="<?= (int) $row['id'] ?>"><button class="btn btn-sm btn-outline-dark" type="submit">Make default</button></form>
                            <?php endif; ?>
                            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete_address"><input type="hidden" name="address_id" value="<?= (int) $row['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this address?">Delete</button></form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form method="post" class="soft-card">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="address">
                <h2 class="h5">Add an address</h2>
                <input class="form-control mb-2" name="full_name" placeholder="Recipient" value="<?= e($fresh['full_name']) ?>">
                <input class="form-control mb-2" name="phone" placeholder="Phone" value="<?= e((string) $fresh['phone']) ?>">
                <input class="form-control mb-2" name="address_line" placeholder="Street address">
                <div class="row g-2">
                    <div class="col-md-4"><input class="form-control mb-2" name="city" placeholder="City"></div>
                    <div class="col-md-4"><input class="form-control mb-2" name="state" placeholder="State"></div>
                    <div class="col-md-4"><input class="form-control mb-2" name="postal_code" placeholder="Postal code"></div>
                </div>
                <button class="btn btn-forest" type="submit">Save address</button>
            </form>
        </div>
    </div>
<?php
require __DIR__ . '/../includes/customer_close.php';
require __DIR__ . '/../includes/footer.php';
?>
