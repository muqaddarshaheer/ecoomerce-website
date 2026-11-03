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
            $next = '';
            if (!empty($_SESSION['after_login'])) {
                $next = safe_local_path((string) $_SESSION['after_login'], '');
                unset($_SESSION['after_login']);
            }
            if ($next === '') {
                $next = cart_count() > 0 ? 'checkout.php' : 'customer/orders.php';
            }
            set_flash('success', 'Your account is ready and you are signed in.');
            redirect($next);
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
        <h1>Create your account</h1>
        <p>This is your own customer panel. Fill this once, and you are signed in. If something is already in the cart, the next page is checkout.</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="hp"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
            <label class="form-label">Full name</label>
            <input class="form-control mb-3" name="full_name" value="<?= e($form['name']) ?>" required>
            <label class="form-label">Email</label>
            <input class="form-control mb-3" type="email" name="email" value="<?= e($form['email']) ?>" required>
            <label class="form-label">Phone</label>
            <input class="form-control mb-3" name="phone" value="<?= e($form['phone']) ?>" inputmode="tel" autocomplete="tel" placeholder="03001234567" required>
            <label class="form-label">Password</label>
            <div class="pass-field mb-3">
                <input class="form-control" type="password" name="password" autocomplete="new-password" required>
                <button class="eye-btn" type="button" data-toggle-pass aria-label="Show password">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.5" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                </button>
            </div>
            <label class="form-label">Confirm password</label>
            <input class="form-control mb-3" type="password" name="confirm_password" autocomplete="new-password" required>
            <p class="small text-secondary">At least 8 characters, with one letter and one number. Use the phone you want on the parcel.</p>
            <button class="btn btn-clay w-100" type="submit">Create my account</button>
        </form>
        <p class="mt-3 mb-0">Already registered? <a href="<?= e(base_url('auth/login.php')) ?>">Log in</a></p>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
