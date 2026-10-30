<?php
require_once __DIR__ . '/../includes/init.php';
$user = require_employee();
$stmt = db()->prepare('SELECT d.*, o.order_number, o.payment_status, o.ship_name FROM dispatch_records d JOIN orders o ON o.id = d.order_id WHERE d.actor_user_id = ? ORDER BY d.id DESC');
$stmt->execute([(int) $user['id']]);
$rows = $stmt->fetchAll();
$pageTitle = 'My reports';
$active = 'reports';
require __DIR__ . '/../includes/employee_header.php';
?>
<h1>My delivery reports</h1>
<div class="table-responsive soft-card">
    <table class="table mb-0">
        <thead><tr><th>When</th><th>Order</th><th>Action</th><th>Payment</th><th>Note</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e(format_date($row['action_date'])) ?></td>
                <td><a href="order.php?id=<?= (int) $row['order_id'] ?>"><?= e($row['order_number']) ?></a><div class="small"><?= e($row['ship_name']) ?></div></td>
                <td><?= e(ucwords(str_replace('_', ' ', $row['action']))) ?></td>
                <td><?= status_badge($row['payment_status']) ?></td>
                <td><?= e((string) $row['notes']) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5">You have not recorded a dispatch or delivery yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/employee_footer.php'; ?>
