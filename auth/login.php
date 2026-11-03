<?php
require_once __DIR__ . '/../includes/init.php';

if ($user = current_user()) {
    if ($user['role'] === 'customer') {
        redirect('customer/orders.php');
    }
    if ($user['role'] === 'admin') {
        redirect('admin/index.php');
    }
    if ($user['role'] === 'employee') {
        redirect('employee/index.php');
    }
}

$allowedRoles = ['customer', 'employee', 'admin'];
$role = $_SESSION['login_role'] ?? ($_GET['role'] ?? 'customer');
if (!in_array($role, $allowedRoles, true)) {
    $role = 'customer';
}
$error = (string) ($_SESSION['login_error'] ?? '');
$email = (string) ($_SESSION['login_email'] ?? '');
unset($_SESSION['login_error'], $_SESSION['login_email'], $_SESSION['login_role']);
$flash = take_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $role = 'customer';
    $email = strtolower(post('email', 150));
    try {
        attempt_login($email, (string) ($_POST['password'] ?? ''), 'customer');
        apply_remember_choice(!empty($_POST['remember']));
        $next = safe_local_path($_SESSION['after_login'] ?? 'customer/orders.php', 'customer/orders.php');
        unset($_SESSION['after_login']);
        redirect($next);
    } catch (AppException $e) {
        $error = $e->getMessage();
    }
}

$doors = [
    'customer' => [
        'label' => 'Customer',
        'hint' => 'Shop & Buy',
        'title' => 'Customer Login',
        'text' => 'Sign in to place orders and open your own account.',
        'action' => '',
        'demo_email' => 'customer@paperlane.local',
        'demo_pass' => 'Customer@123',
        'button' => 'Login as Customer',
    ],
    'employee' => [
        'label' => 'Employee',
        'hint' => 'Manage Work',
        'title' => 'Employee Login',
        'text' => 'Sign in to update dispatch and delivery records.',
        'action' => base_url('employee/login.php'),
        'demo_email' => 'employee@paperlane.local',
        'demo_pass' => 'Employee@123',
        'button' => 'Login as Employee',
    ],
    'admin' => [
        'label' => 'Admin',
        'hint' => 'Full Control',
        'title' => 'Admin Login',
        'text' => 'Access the admin panel to manage the system.',
        'action' => base_url('admin/login.php'),
        'demo_email' => 'admin@paperlane.local',
        'demo_pass' => 'Admin@123',
        'button' => 'Login as Admin',
    ],
];

$doorShots = [];
try {
    $doorShots = db()->query(product_sql() . " WHERE p.status = 'active' AND p.product_code IN ('0100004','0300004','0400006')")->fetchAll();
} catch (Throwable $e) {
    $doorShots = [];
}
?>
<!doctype html>
<html lang="en">
<head>
<?php page_head('Welcome'); ?>
<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&display=swap" rel="stylesheet">
</head>
<body class="login-body shop">
<div class="gate">
    <section class="gate-story gate-photo">
        <a class="gate-brand" href="<?= e(base_url('index.php')) ?>">
            <img src="<?= e(base_url('assets/images/logo.svg')) ?>" alt="">
                <span>
                    <strong>Paperlane</strong>
                </span>
        </a>

        <div class="gate-caption">
            <p class="gate-script">Karachi, on the counter</p>
            <p class="gate-lead">Shawls, jewellery, cards and the school shelf. Sign in when you are ready to order.</p>
            <?php if ($doorShots): ?>
                <div class="gate-thumbs">
                    <?php foreach ($doorShots as $shot): ?>
                        <?= picture_or_fallback(media_url('products', $shot['image'] ?? null), $shot['name']) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="gate-panel">
        <div class="gate-card">
            <h1>Welcome to Paperlane</h1>
            <p class="gate-sub">Sign in to continue</p>
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endif; ?>

            <div class="role-switch" role="tablist" aria-label="Choose a login">
                <?php foreach ($doors as $key => $door): ?>
                    <button class="role-btn<?= $role === $key ? ' is-on' : '' ?>" type="button" role="tab" data-door="<?= e($key) ?>" aria-selected="<?= $role === $key ? 'true' : 'false' ?>">
                        <?php if ($key === 'admin'): ?>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l7 3v6c0 4.2-2.8 7.2-7 9-4.2-1.8-7-4.8-7-9V6z" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                        <?php elseif ($key === 'employee'): ?>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="8" cy="9" r="2.2" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="16" cy="9" r="2.2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M4.5 18c.6-2.2 2.2-3.4 3.5-3.4S11 15.8 11.6 18M12.4 18c.6-2.2 2.2-3.4 3.5-3.4s2.9 1.2 3.6 3.4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M6 19c.8-2.8 2.8-4 6-4s5.2 1.2 6 4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        <?php endif; ?>
                        <strong><?= e($door['label']) ?></strong>
                        <span><?= e($door['hint']) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="gate-sheet" data-role="<?= e($role) ?>">
                <?php foreach ($doors as $key => $door): ?>
                    <form class="door-form" id="door-<?= e($key) ?>" method="post" action="<?= e($door['action']) ?>"<?= $role === $key ? '' : ' hidden' ?>>
                        <?= csrf_field() ?>
                        <div class="sheet-title">
                            <span class="sheet-badge" aria-hidden="true">
                                <?php if ($key === 'admin'): ?>
                                    <svg viewBox="0 0 24 24"><path d="M12 3l7 3v6c0 4.2-2.8 7.2-7 9-4.2-1.8-7-4.8-7-9V6z" fill="none" stroke="currentColor" stroke-width="1.7"/></svg>
                                <?php elseif ($key === 'employee'): ?>
                                    <svg viewBox="0 0 24 24"><circle cx="8" cy="9" r="2.2" fill="none" stroke="currentColor" stroke-width="1.7"/><circle cx="16" cy="9" r="2.2" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M4.5 18c.6-2.2 2.2-3.4 3.5-3.4S11 15.8 11.6 18M12.4 18c.6-2.2 2.2-3.4 3.5-3.4s2.9 1.2 3.6 3.4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                <?php else: ?>
                                    <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M6 19c.8-2.8 2.8-4 6-4s5.2 1.2 6 4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                <?php endif; ?>
                            </span>
                            <span>
                                <strong><?= e($door['title']) ?></strong>
                                <small><?= e($door['text']) ?></small>
                            </span>
                        </div>
                        <?php if ($role === $key && $error !== ''): ?>
                            <div class="alert alert-danger"><?= e($error) ?></div>
                        <?php endif; ?>
                        <label class="form-label" for="<?= e($key) ?>-email">Email address</label>
                        <div class="field-shell">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M4 7l8 6 8-6" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                            <input class="form-control" id="<?= e($key) ?>-email" type="email" name="email" autocomplete="username" required value="<?= e($role === $key ? $email : '') ?>" placeholder="Email address">
                        </div>
                        <label class="form-label" for="<?= e($key) ?>-password">Password</label>
                        <div class="field-shell pass-field">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M8 10V7a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                            <input class="form-control" id="<?= e($key) ?>-password" type="password" name="password" autocomplete="current-password" required placeholder="Password">
                            <button class="eye-btn" type="button" data-toggle-pass aria-label="Show password">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="2.5" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                            </button>
                        </div>
                        <div class="sheet-row">
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" value="1">
                                <span class="form-check-label">Remember me</span>
                            </label>
                            <button class="text-link" type="button" data-forgot>Forgot password?</button>
                        </div>
                        <p class="forgot-note d-none">Paperlane does not email passwords. Ask an administrator to reset this account. The demo button below fills the class login.</p>
                        <button class="btn btn-forest w-100 gate-submit" type="submit">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 7V5H5v14h5v-2M10 12h9M16 9l3 3-3 3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <?= e($door['button']) ?>
                        </button>
                        <button class="text-link demo-link" type="button" data-demo-email="<?= e($door['demo_email']) ?>" data-demo-pass="<?= e($door['demo_pass']) ?>">Use demo account</button>
                        <?php if ($key === 'customer'): ?>
                            <p class="sheet-extra">New here? <a href="<?= e(base_url('auth/register.php')) ?>">Create a customer account</a></p>
                        <?php endif; ?>
                    </form>
                <?php endforeach; ?>
            </div>
            <div class="demo-list">
                <p>Demo logins</p>
                <ul>
                    <?php foreach ($doors as $door): ?>
                        <li><strong><?= e($door['label']) ?></strong> <?= e($door['demo_email']) ?> · <?= e($door['demo_pass']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <p class="back-shop"><a href="<?= e(base_url('index.php')) ?>">Back to the shop</a></p>
        </div>
    </section>
</div>
<?php page_scripts(); ?>
</body>
</html>
