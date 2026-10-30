<?php
require_once __DIR__ . '/../includes/init.php';

if ($user = current_user()) {
    if ($user['role'] === 'customer') {
        redirect('index.php');
    }
}

$error = '';
$form = ['name' => '', 'email' => '', 'phone' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form['name'] = post('full_name', 100);
    $form['email'] = strtolower(post('email', 150));
    $form['phone'] = post('phone', 20);
    if (post('website') !== '') {
        $error = 'The account could not be created.';
    } elseif (($_POST['password'] ?? '') !== ($_POST['confirm_password'] ?? '')) {
        $error = 'The two passwords do not match.';
    } else {
        try {
            register_customer($form['name'], $form['email'], $form['phone'], (string) ($_POST['password'] ?? ''));
            set_flash('success', 'Your account is ready.');
            redirect('index.php');
        } catch (AppException $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'Register';
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="auth-wrap soft-card">
        <h1>Create an account</h1>
        <p>You can browse without an account. Registration is for orders, addresses and tracking.</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="hp"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
            <label class="form-label">Full name</label>
            <input class="form-control mb-3" name="full_name" value="<?= e($form['name']) ?>" required>
            <label class="form-label">Email</label>
            <input class="form-control mb-3" type="email" name="email" value="<?= e($form['email']) ?>" required>
            <label class="form-label">Phone</label>
            <input class="form-control mb-3" name="phone" value="<?= e($form['phone']) ?>" required>
            <label class="form-label">Password</label>
            <input class="form-control mb-3" type="password" name="password" required>
            <label class="form-label">Confirm password</label>
            <input class="form-control mb-3" type="password" name="confirm_password" required>
            <p class="small text-secondary">At least 8 characters, with one letter and one number.</p>
            <button class="btn btn-clay w-100" type="submit">Register</button>
        </form>
        <p class="mt-3 mb-0">Already registered? <a href="<?= e(base_url('auth/login.php')) ?>">Log in</a></p>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
