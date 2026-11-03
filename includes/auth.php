<?php

/*
 * Three doors share one users table: customer, admin and employee.
 * Login checks the password hash, then the role of that door. A correct
 * password on the wrong door does not start a session. Five failures lock
 * that email for two minutes. Employees must already be approved. A new
 * customer registration creates the user and the customer row together.
 */

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cached = null;
    static $cachedId = 0;
    $id = (int) $_SESSION['user_id'];
    if ($cached && $cachedId === $id) {
        return $cached;
    }
    $stmt = db()->prepare('SELECT id, role, full_name, email, phone, status FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user || $user['status'] !== 'active') {
        unset($_SESSION['user_id'], $_SESSION['role']);
        $cached = null;
        return null;
    }
    $cached = $user;
    $cachedId = $id;
    return $user;
}

function customer_by_user(int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM customers WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function employee_by_user(int $userId): ?array
{
    $stmt = db()->prepare('SELECT * FROM employees WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function login_session(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
    unset($_SESSION['login_fails'], $_SESSION['login_lock_until']);
    if ($user['role'] === 'customer') {
        merge_session_cart((int) $user['id']);
    }
}

function attempt_login(string $email, string $password, string $expectedRole): array
{
    $lockUntil = (int) ($_SESSION['login_lock_until'] ?? 0);
    if ($lockUntil > time()) {
        throw new AppException('Too many attempts. Please wait two minutes and try again.');
    }

    $email = strtolower(trim($email));
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        $_SESSION['login_fails'] = (int) ($_SESSION['login_fails'] ?? 0) + 1;
        if ((int) $_SESSION['login_fails'] >= 5) {
            $_SESSION['login_lock_until'] = time() + 120;
            $_SESSION['login_fails'] = 0;
        }
        throw new AppException('Email or password is incorrect.');
    }
    if ($user['status'] === 'pending') {
        throw new AppException('An administrator still has to approve this account.');
    }
    if ($user['status'] !== 'active') {
        throw new AppException('This account is inactive. Contact the administrator.');
    }
    if ($user['role'] !== $expectedRole) {
        $pages = [
            'admin' => 'Use the admin login page for this account.',
            'employee' => 'Use the employee login page for this account.',
            'customer' => 'Use the customer login page for this account.',
        ];
        throw new AppException($pages[$user['role']] ?? 'Use the correct login page.');
    }

    login_session($user);
    return $user;
}

function apply_remember_choice(bool $remember): void
{
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $path = BASE_URL === '' ? '/' : BASE_URL;
    $options = [
        'path' => $path,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $secure,
    ];
    if ($remember) {
        setcookie('paperlane_keep', '1', $options + ['expires' => time() + 1209600]);
        setcookie(session_name(), session_id(), $options + ['expires' => time() + 1209600]);
        return;
    }
    setcookie('paperlane_keep', '', $options + ['expires' => time() - 3600]);
    if (session_id() !== '') {
        setcookie(session_name(), session_id(), $options + ['expires' => 0]);
    }
}

function register_customer(string $name, string $email, string $phone, string $password): array
{
    $name = trim($name);
    $email = strtolower(trim($email));
    $phone = trim($phone);
    if ($name === '' || strlen($name) > 100) {
        throw new AppException('Enter your full name.');
    }
    if (!valid_email($email)) {
        throw new AppException('Enter a valid email address.');
    }
    if (!valid_phone($phone)) {
        throw new AppException('Enter a valid phone number.');
    }
    $problem = password_problem($password);
    if ($problem) {
        throw new AppException($problem);
    }

    $pdo = db();
    $exists = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $exists->execute([$email]);
    if ($exists->fetch()) {
        throw new AppException('An account with this email already exists.');
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO users (role, full_name, email, password_hash, phone, status) VALUES (\'customer\', ?, ?, ?, ?, \'active\')')
            ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $phone]);
        $userId = (int) $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO customers (user_id) VALUES (?)')->execute([$userId]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('The account could not be created.');
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    login_session($user);
    return $user;
}

function require_customer(): array
{
    $user = current_user();
    if (!$user || $user['role'] !== 'customer') {
        $_SESSION['after_login'] = safe_local_path(current_page(), 'checkout.php');
        set_flash('warning', 'Sign in, or create an account, to place this order. Your cart stays with you.');
        redirect('auth/login.php');
    }
    $customer = customer_by_user((int) $user['id']);
    if (!$customer) {
        throw new AppException('Customer profile is missing.');
    }
    $user['customer_id'] = (int) $customer['id'];
    return $user;
}

function require_admin(): array
{
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        set_flash('warning', 'Please log in as an administrator.');
        redirect('admin/login.php');
    }
    return $user;
}

function require_employee(): array
{
    $user = current_user();
    if (!$user || $user['role'] !== 'employee') {
        set_flash('warning', 'Please log in as an employee.');
        redirect('employee/login.php');
    }
    $employee = employee_by_user((int) $user['id']);
    if (!$employee) {
        throw new AppException('Employee profile is missing.');
    }
    $user['employee_id'] = (int) $employee['id'];
    $user['employee_code'] = $employee['employee_code'];
    $user['designation'] = $employee['designation'];
    return $user;
}

function logout_user(): void
{
    apply_remember_choice(false);
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
