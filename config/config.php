<?php

define('APP_NAME', 'Paperlane');
define('APP_TAGLINE', 'Stationery, gifts and little luxuries');
define('APP_TIMEZONE', 'Asia/Karachi');
define('CURRENCY', 'Rs');
define('FREE_SHIPPING_OVER', 1500);
define('SHIPPING_FEE', 99);
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);
define('APP_DEBUG', false);
define('ORDER_RETURN_DAYS', 7);

define('ROOT_PATH', realpath(__DIR__ . '/..'));

function detect_base_url(): string
{
    $fallback = '/e_projject_ecomerce_website';
    if (PHP_SAPI === 'cli' || empty($_SERVER['DOCUMENT_ROOT'])) {
        return $fallback;
    }

    $doc = realpath($_SERVER['DOCUMENT_ROOT']);
    $app = realpath(__DIR__ . '/..');
    if ($doc === false || $app === false) {
        return $fallback;
    }

    $docNorm = str_replace('\\', '/', $doc);
    $appNorm = str_replace('\\', '/', $app);
    if (!str_starts_with(strtolower($appNorm), strtolower($docNorm))) {
        return $fallback;
    }

    $rel = substr($appNorm, strlen($docNorm));
    $rel = rtrim($rel, '/');
    return $rel === '' ? '' : $rel;
}

define('BASE_URL', detect_base_url());
