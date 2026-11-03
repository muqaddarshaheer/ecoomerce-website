<?php

if (PHP_VERSION_ID < 80000) {
    exit('PHP 8.0 or newer is required.');
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/gift_custom.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/orders.php';

date_default_timezone_set(APP_TIMEZONE);

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
    ini_set('log_errors', '1');
}

set_exception_handler(function (Throwable $e): void {
    app_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (APP_DEBUG) {
        echo '<pre>' . e($e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString()) . '</pre>';
        return;
    }
    $message = $e instanceof AppException
        ? $e->getMessage()
        : 'Something went wrong. Please try again later.';
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Error</title></head><body><p>'
        . e($message) . '</p><p><a href="' . e(base_url('index.php')) . '">Back to the shop</a></p></body></html>';
});

if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $path = BASE_URL === '' ? '/' : BASE_URL;
    $keep = isset($_COOKIE['paperlane_keep']) && $_COOKIE['paperlane_keep'] === '1';
    if ($keep) {
        ini_set('session.gc_maxlifetime', '1209600');
    }
    session_name('paperlane_session');
    session_set_cookie_params([
        'lifetime' => $keep ? 1209600 : 0,
        'path' => $path,
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $secure,
    ]);
    session_start();
}

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
}
