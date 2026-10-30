<?php
require_once __DIR__ . '/../includes/init.php';
$user = require_customer();
$stmt = db()->prepare('SELECT r.*, o.order_number FROM return_requests r JOIN orders o ON o.id = r.order_id WHERE r.customer_id = ? ORDER BY r.id DESC');
$stmt->execute([(int) $user['customer_id']]);
$rows = $stmt->fetchAll();
$pageTitle = 'Returns';
$accountActive = 'returns';
$accountUser = $user;
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/customer_open.php';
?>
    <h1>Returns and replacements</h1>
    <p>A request can be opened from an order for 7 days after it is delivered.</p>
    <?php if (!$rows): ?>
        <div class="empty-state"><p>You have no requests.</p></div>
    <?php else: ?>
        <div class="table-responsive soft-card">
            <table class="table mb-0">
                <thead><tr><th>Order</th><th>Type</th><th>Status</th><th>Requested</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['order_number']) ?></td>
                        <td><?= e(ucfirst($row['request_type'])) ?></td>
                        <td><?= status_badge($row['status']) ?></td>
                        <td><?= e(format_date($row['requested_at'])) ?></td>
                        <td><a href="order.php?id=<?= (int) $row['order_id'] ?>">View order</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php
require __DIR__ . '/../includes/customer_close.php';
require __DIR__ . '/../includes/footer.php';
?>
