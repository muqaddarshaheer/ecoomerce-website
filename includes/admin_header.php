<?php
$adminUser = require_admin();
$pageTitle = $pageTitle ?? 'Admin';
$active = $active ?? '';
$waitingOrders = (int) db()->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('pending','confirmed')")->fetchColumn();
$groups = [
    'Catalogue' => [
        'add' => ['product-form.php', 'Add new item'],
        'products' => ['products.php', 'Manage products'],
        'categories' => ['categories.php', 'Categories'],
    ],
    'Sales' => [
        'orders' => ['orders.php', 'Orders' . ($waitingOrders > 0 ? ' (' . $waitingOrders . ')' : '')],
        'returns' => ['returns.php', 'Returns'],
        'customers' => ['customers.php', 'Customers'],
    ],
    'Shop' => [
        'employees' => ['employees.php', 'Employees'],
        'feedback' => ['feedback.php', 'Feedback'],
        'faqs' => ['faqs.php', 'Agent answers'],
        'reports' => ['reports.php', 'Reports'],
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
<?php page_head($pageTitle); ?>
</head>
<body class="shop">
<div class="panel panel-admin">
    <aside class="panel-side">
        <button class="theme-switch side-theme" type="button" data-theme-switch aria-pressed="false" aria-label="Switch to light mode">
            <span data-theme-label>Light</span>
        </button>
        <a class="brand mb-2" href="index.php">
            <img src="<?= e(base_url('assets/images/logo.svg')) ?>" alt="">
            <span><span class="brand-name">Admin</span><small><?= e($adminUser['full_name']) ?></small></span>
        </a>
        <p class="lane-chip">Shop desk</p>
        <a class="<?= $active === 'dash' ? 'active' : '' ?>" href="index.php">Dashboard</a>
        <?php foreach ($groups as $label => $links): ?>
            <p class="side-label"><?= e($label) ?></p>
            <?php foreach ($links as $key => $link): ?>
                <a class="<?= $active === $key ? 'active' : '' ?><?= $key === 'add' ? ' side-add' : '' ?>" href="<?= e($link[0]) ?>"><?= e($link[1]) ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
        <p class="side-label">Leave</p>
        <a href="<?= e(base_url('index.php')) ?>">View shop</a>
        <a href="logout.php">Log out</a>
    </aside>
    <div class="panel-main">
        <?php if ($waitingOrders > 0): ?>
            <div class="alert alert-warning">You have <strong><?= (int) $waitingOrders ?></strong> order<?= $waitingOrders === 1 ? '' : 's' ?> waiting. <a class="btn btn-line btn-sm" href="orders.php">Open the order list</a></div>
        <?php endif; ?>
        <?php $flash = take_flash(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>
