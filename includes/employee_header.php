<?php
$employeeUser = require_employee();
$pageTitle = $pageTitle ?? 'Employee';
$active = $active ?? '';
$groups = [
    'Orders' => [
        'orders' => ['orders.php', 'Update orders'],
    ],
    'Records' => [
        'reports' => ['reports.php', 'Delivery records'],
    ],
    'Account' => [
        'password' => ['password.php', 'Change password'],
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
<?php page_head($pageTitle); ?>
</head>
<body class="shop">
<div class="panel panel-desk">
    <aside class="panel-side">
        <button class="theme-switch side-theme" type="button" data-theme-switch aria-pressed="false" aria-label="Switch to light mode">
            <span data-theme-label>Light</span>
        </button>
        <a class="brand mb-2" href="index.php">
            <img src="<?= e(base_url('assets/images/logo.svg')) ?>" alt="">
            <span><span class="brand-name">Employee</span><small><?= e($employeeUser['employee_code']) ?></small></span>
        </a>
        <p class="lane-chip">Parcel desk</p>
        <p class="account-help">Confirmed parcels only. Prices and new items stay with the administrator.</p>
        <a class="side-btn<?= $active === 'dash' ? ' active' : '' ?>" href="index.php">Dashboard</a>
        <?php foreach ($groups as $label => $links): ?>
            <p class="side-label"><?= e($label) ?></p>
            <?php foreach ($links as $key => $link): ?>
                <a class="side-btn<?= $active === $key ? ' active' : '' ?>" href="<?= e($link[0]) ?>"><?= e($link[1]) ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <p class="side-label">Leave</p>
        <a class="side-btn" href="<?= e(base_url('index.php')) ?>">View shop</a>
        <a class="side-btn" href="logout.php">Log out</a>
    </aside>
    <div class="panel-main">
        <?php $flash = take_flash(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
