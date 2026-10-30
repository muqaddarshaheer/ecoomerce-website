<?php
require_once __DIR__ . '/../includes/init.php';

if ($user = current_user()) {
    if ($user['role'] === 'admin') {
        redirect('admin/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('auth/login.php?role=admin');
}

verify_csrf();
$email = strtolower(post('email', 150));
try {
    attempt_login($email, (string) ($_POST['password'] ?? ''), 'admin');
    apply_remember_choice(!empty($_POST['remember']));
    redirect('admin/index.php');
} catch (AppException $e) {
    $_SESSION['login_error'] = $e->getMessage();
    $_SESSION['login_email'] = $email;
    $_SESSION['login_role'] = 'admin';
    redirect('auth/login.php?role=admin');
}
