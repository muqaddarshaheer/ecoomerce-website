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
<body>
<div class="panel panel-desk">
    <aside class="panel-side">
        <a class="brand mb-2" href="index.php">
            <img src="<?= e(base_url('assets/images/logo.svg')) ?>" alt="">
            <span><span class="brand-name">Employee</span><small><?= e($employeeUser['employee_code']) ?></small></span>
        </a>
        <p class="lane-chip">Parcel desk</p>
        <img class="side-face" src="<?= e(base_url('assets/images/look/lahore-blue.jpg')) ?>" alt="Woman in a blue suit and beige shawl">
        <a class="<?= $active === 'dash' ? 'active' : '' ?>" href="index.php">Dashboard</a>
        <?php foreach ($groups as $label => $links): ?>
            <p class="side-label"><?= e($label) ?></p>
            <?php foreach ($links as $key => $link): ?>
                <a class="<?= $active === $key ? 'active' : '' ?>" href="<?= e($link[0]) ?>"><?= e($link[1]) ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <p class="side-label">Leave</p>
        <a href="<?= e(base_url('index.php')) ?>">View shop</a>
        <a href="logout.php">Log out</a>
    </aside>
    <div class="panel-main">
        <?php $flash = take_flash(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
