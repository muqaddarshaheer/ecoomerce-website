<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$pdo = db();
$stats = [
    'Products' => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'Orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'Customers' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn(),
    'Cleared sales' => money($pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status = 'cleared'")->fetchColumn()),
];
$cheques = (int) $pdo->query("SELECT COUNT(*) FROM payments WHERE method = 'cheque' AND status IN ('pending','verified')")->fetchColumn();
$returns = (int) $pdo->query("SELECT COUNT(*) FROM return_requests WHERE status = 'pending'")->fetchColumn();
$refunds = (int) $pdo->query("SELECT COUNT(*) FROM refunds WHERE status = 'pending'")->fetchColumn();
$low = $pdo->query("SELECT p.product_code, p.name, p.stock, (SELECT pi.file_name FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS image FROM products p WHERE p.status = 'active' AND p.stock <= 5 ORDER BY p.stock ASC, p.name LIMIT 6")->fetchAll();
$recent = $pdo->query("SELECT o.*, u.full_name, (SELECT pi.file_name FROM order_items oi JOIN products p ON p.product_code = oi.product_code JOIN product_images pi ON pi.product_id = p.id WHERE oi.order_id = o.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS image FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = c.user_id ORDER BY o.id DESC LIMIT 8")->fetchAll();
$pageTitle = 'Dashboard';
$active = 'dash';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
    <h1 class="mb-0">Dashboard</h1>
    <a class="btn btn-line" href="products.php">Manage products</a>
</div>
<p class="lane-note">Order chips keep their own colours so a waiting order never looks like a delivered one.</p>
<?= state_key() ?>
<div class="stat-grid mb-3">
    <?php foreach ($stats as $label => $value): ?>
        <article class="stat-card"><span><?= e($label) ?></span><strong><?= e((string) $value) ?></strong></article>
    <?php endforeach; ?>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="soft-card">
            <h2 class="h5">Recent orders</h2>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Number</th><th>Customer</th><th>Total</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $order): ?>
                        <tr>
                            <td class="item-with-photo"><?= picture_or_fallback(media_url('products', $order['image'] ?? null), $order['order_number']) ?> <?= e($order['order_number']) ?></td>
                            <td><?= e($order['full_name']) ?></td>
                            <td><?= e(money($order['total_amount'])) ?></td>
                            <td><?= status_badge($order['order_status']) ?> <?= status_badge($order['payment_status']) ?></td>
                            <td><a class="btn btn-line btn-sm" href="order.php?id=<?= (int) $order['id'] ?>">Open</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$recent): ?><tr><td colspan="5">No orders yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="soft-card mb-3">
            <h2 class="h5">Needs attention</h2>
            <p><a href="orders.php?pay=cheque"><?= (int) $cheques ?> cheque payment<?= $cheques === 1 ? '' : 's' ?></a> waiting.</p>
            <p><a href="returns.php"><?= (int) $returns ?> return request<?= $returns === 1 ? '' : 's' ?></a> pending.</p>
            <p class="mb-0"><?= (int) $refunds ?> refund<?= $refunds === 1 ? '' : 's' ?> waiting to be issued.</p>
        </div>
        <div class="soft-card">
            <h2 class="h5">Low stock</h2>
            <?php if (!$low): ?><p class="mb-0">No product is at 5 or below.</p><?php endif; ?>
            <?php foreach ($low as $item): ?>
                <p class="mb-2 item-with-photo"><?= picture_or_fallback(media_url('products', $item['image'] ?? null), $item['name']) ?> <span><?= e($item['name']) ?> · <?= (int) $item['stock'] ?></span></p>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
