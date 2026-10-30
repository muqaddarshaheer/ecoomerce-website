<?php
require_once __DIR__ . '/../includes/init.php';
$user = require_employee();
$ready = (int) db()->query("SELECT COUNT(*) FROM orders WHERE order_status = 'confirmed' AND (delivery_type = '3' OR payment_status = 'cleared')")->fetchColumn();
$out = (int) db()->query("SELECT COUNT(*) FROM orders WHERE order_status = 'dispatched' AND (delivery_type = '3' OR payment_status = 'cleared')")->fetchColumn();
$done = (int) db()->query("SELECT COUNT(*) FROM orders WHERE order_status = 'delivered' AND delivery_date >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetchColumn();
$list = db()->query("SELECT o.*, u.full_name, (SELECT pi.file_name FROM order_items oi JOIN products p ON p.product_code = oi.product_code JOIN product_images pi ON pi.product_id = p.id WHERE oi.order_id = o.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS image, (SELECT oi.product_name FROM order_items oi WHERE oi.order_id = o.id ORDER BY oi.id ASC LIMIT 1) AS product_name FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = c.user_id WHERE o.order_status = 'confirmed' AND (o.delivery_type = '3' OR o.payment_status = 'cleared') ORDER BY o.id ASC LIMIT 6")->fetchAll();
$pageTitle = 'Employee desk';
$active = 'dash';
require __DIR__ . '/../includes/employee_header.php';
?>
<h1>Hello, <?= e($user['full_name']) ?></h1>
<p><?= e($user['designation']) ?> · <?= e($user['employee_code']) ?></p>
<p class="lane-note">This desk is blue. You only see parcels that are ready to move.</p>
<?= state_key() ?>
<div class="stat-grid mb-3">
    <article class="stat-card"><span>Ready to dispatch</span><strong><?= $ready ?></strong></article>
    <article class="stat-card"><span>Out for delivery</span><strong><?= $out ?></strong></article>
    <article class="stat-card"><span>Delivered this month</span><strong><?= $done ?></strong></article>
</div>
<div class="soft-card">
    <h2 class="h5">Next parcels</h2>
    <?php if (!$list): ?><p class="mb-0">Nothing is waiting. Card and cheque orders appear here only after payment is cleared.</p><?php endif; ?>
    <?php foreach ($list as $order): ?>
        <p class="mb-2 parcel-line"><?= picture_or_fallback(media_url('products', $order['image'] ?? null), $order['product_name'] ?? 'Parcel') ?> <span><a href="order.php?id=<?= (int) $order['id'] ?>"><?= e($order['order_number']) ?></a> · <?= e($order['full_name']) ?> · <?= e(delivery_label($order['delivery_type'])) ?> <?= status_badge($order['payment_status']) ?></span></p>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/employee_footer.php'; ?>
