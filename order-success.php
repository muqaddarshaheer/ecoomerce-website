<?php
require_once __DIR__ . '/includes/init.php';
$user = require_customer();
$number = query_text('n', 16);
$stmt = db()->prepare('SELECT * FROM orders WHERE order_number = ? AND customer_id = ?');
$stmt->execute([$number, (int) $user['customer_id']]);
$order = $stmt->fetch();
if (!$order) {
    set_flash('warning', 'That order could not be opened.');
    redirect('customer/orders.php');
}
$parts = order_number_parts($order['order_number']);
$pageTitle = 'Order placed';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-5">
    <div class="soft-card">
        <p class="eyebrow"><?= $order['order_status'] === 'confirmed' ? 'Confirmed' : 'Received' ?></p>
        <h1><?= $order['order_status'] === 'confirmed' ? 'Your order is confirmed' : 'We have your order' ?></h1>
        <p>Your order number is <strong><?= e($order['order_number']) ?></strong>.</p>
        <?php if ($order['order_status'] === 'confirmed'): ?>
            <p>The shop has confirmed this order. It is now on the administrator’s order list.</p>
        <?php else: ?>
            <p>This order stays pending until an administrator clears the cheque. It is already visible in the admin panel.</p>
        <?php endif; ?>
        <p>Read as delivery <strong><?= e($parts['delivery']) ?></strong> (<?= e(delivery_label($parts['delivery'])) ?>), primary product <strong><?= e($parts['product_code']) ?></strong>, sequence <strong><?= e($parts['sequence']) ?></strong>.</p>
        <p>Payment status: <?= status_badge($order['payment_status']) ?> · Order status: <?= status_badge($order['order_status']) ?></p>
        <p>Total charged in our records: <strong><?= e(money($order['total_amount'])) ?></strong></p>
        <?php if ($order['delivery_type'] === '1'): ?>
            <p>The simulated card payment was accepted. The parcel still waits for an employee to dispatch it.</p>
        <?php elseif ($order['delivery_type'] === '2'): ?>
            <p>This cheque order will not be dispatched until an administrator verifies it and marks it cleared.</p>
        <?php else: ?>
            <p>This cash-on-delivery order can be dispatched now. Please keep the cash ready for the rider.</p>
        <?php endif; ?>
        <a class="btn btn-forest" href="<?= e(base_url('customer/order.php?id=' . (int) $order['id'])) ?>">Track this order</a>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
