<?php
require_once __DIR__ . '/../includes/init.php';
$user = require_customer();
$stmt = db()->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC');
$stmt->execute([(int) $user['customer_id']]);
$orders = $stmt->fetchAll();
$preview = [];
if ($orders) {
    $ids = array_map(static fn(array $order): int => (int) $order['id'], $orders);
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $shot = db()->prepare("SELECT oi.order_id, oi.product_name, oi.quantity, (SELECT pi.file_name FROM product_images pi JOIN products p ON p.id = pi.product_id WHERE p.product_code = oi.product_code ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS image FROM order_items oi WHERE oi.order_id IN ($marks) ORDER BY oi.id");
    $shot->execute($ids);
    foreach ($shot->fetchAll() as $line) {
        $key = (int) $line['order_id'];
        if (!isset($preview[$key])) {
            $preview[$key] = ['image' => $line['image'], 'name' => $line['product_name'], 'count' => 0, 'qty' => 0];
        }
        $preview[$key]['count']++;
        $preview[$key]['qty'] += (int) $line['quantity'];
    }
}
$pageTitle = 'Orders';
$accountActive = 'orders';
$accountUser = $user;
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/customer_open.php';
?>
    <h1>Your orders</h1>
    <p class="lane-note">The chips below use the same colours on every order.</p>
    <?= state_key() ?>
    <?php if (!$orders): ?>
        <div class="empty-state"><p>You have not placed an order yet. <a href="<?= e(base_url('shop.php')) ?>">Visit the shop</a></p></div>
    <?php else: ?>
        <div class="order-list">
            <?php foreach ($orders as $order): ?>
                <?php $shot = $preview[(int) $order['id']] ?? null; ?>
                <a class="order-card" href="order.php?id=<?= (int) $order['id'] ?>">
                    <?= picture_or_fallback($shot ? media_url('products', $shot['image']) : null, $shot ? $shot['name'] : 'Order') ?>
                    <span>
                        <strong><?= e($order['order_number']) ?></strong>
                        <em><?= e(format_date($order['created_at'])) ?> · <?= $shot ? (int) $shot['count'] . ' product' . ($shot['count'] === 1 ? '' : 's') : 'Order' ?></em>
                        <span class="order-states"><?= status_badge($order['order_status']) ?> <?= status_badge($order['payment_status']) ?></span>
                    </span>
                    <b><?= e(money($order['total_amount'])) ?></b>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php
require __DIR__ . '/../includes/customer_close.php';
require __DIR__ . '/../includes/footer.php';
?>
