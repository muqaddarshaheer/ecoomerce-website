<?php
require_once __DIR__ . '/../includes/init.php';
$user = require_customer();
$orderId = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND customer_id = ?');
$stmt->execute([$orderId, (int) $user['customer_id']]);
$order = $stmt->fetch();
if (!$order) {
    http_response_code(404);
    $pageTitle = 'Order not found';
    $accountActive = 'orders';
    $accountUser = $user;
    require __DIR__ . '/../includes/header.php';
    require __DIR__ . '/../includes/customer_open.php';
    echo '<div class="empty-state"><h2>Order not found</h2></div>';
    require __DIR__ . '/../includes/customer_close.php';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $action = post('action', 20);
        if ($action === 'cancel') {
            cancel_order($orderId, (int) $user['customer_id'], post('reason', 255));
            set_flash('success', 'The order was cancelled and the stock was returned.');
        } elseif ($action === 'return') {
            create_return_request((int) $user['customer_id'], $orderId, post('request_type', 20), post('reason', 2000));
            set_flash('success', 'Your request was sent to the shop.');
        }
    } catch (AppException $e) {
        set_flash('danger', $e->getMessage());
    }
    redirect('customer/order.php?id=' . $orderId);
}

$items = order_items($orderId);
$payment = order_payment($orderId);
$records = order_timeline_records($orderId);
$returns = order_return_rows($orderId);
$refunds = order_refund_rows($orderId);
$parts = order_number_parts($order['order_number']);
$openReturn = false;
foreach ($returns as $request) {
    if ($request['status'] !== 'rejected') {
        $openReturn = true;
    }
}
$pageTitle = 'Order ' . $order['order_number'];
$accountActive = 'orders';
$accountUser = $user;
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/customer_open.php';
?>
    <p class="no-print"><a href="orders.php">All orders</a></p>
    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
        <div>
            <h1>Order <?= e($order['order_number']) ?></h1>
            <p>Delivery digit <?= e($parts['delivery']) ?> · primary product <?= e($parts['product_code']) ?> · sequence <?= e($parts['sequence']) ?></p>
        </div>
        <button class="btn btn-outline-dark no-print" type="button" onclick="window.print()">Print</button>
    </div>
    <p><?= status_badge($order['order_status']) ?> <?= status_badge($order['payment_status']) ?> <?= e(delivery_label($order['delivery_type'])) ?></p>
    <?php if ($order['order_status'] === 'cancelled'): ?>
        <div class="alert alert-secondary">Cancelled. <?= e((string) $order['cancel_reason']) ?></div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="soft-card mb-3">
                <h2 class="h5">Items</h2>
                <table class="table mb-0">
                    <thead><tr><th>Product ID</th><th>Item</th><th>Qty</th><th>Price</th><th>Line</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= e($item['product_code']) ?></td>
                            <td class="item-with-photo"><?= picture_or_fallback(media_url('products', $item['image'] ?? null), $item['product_name']) ?> <?= e($item['product_name']) ?></td>
                            <td><?= (int) $item['quantity'] ?></td>
                            <td><?= e(money($item['unit_price'])) ?></td>
                            <td><?= e(money($item['line_total'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="mb-0 mt-2">Goods <?= e(money($order['subtotal'])) ?> · Delivery <?= e(money($order['shipping_fee'])) ?> · <strong>Total <?= e(money($order['total_amount'])) ?></strong></p>
            </div>
            <div class="soft-card">
                <h2 class="h5">Deliver to</h2>
                <p class="mb-0"><?= e($order['ship_name']) ?>, <?= e($order['ship_phone']) ?><br><?= e($order['ship_address']) ?>, <?= e($order['ship_city']) ?>, <?= e($order['ship_state']) ?> <?= e($order['ship_postal']) ?></p>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="soft-card mb-3">
                <h2 class="h5">Tracking</h2>
                <div class="timeline">
                    <div class="step"><strong>Placed</strong><?= e(format_date($order['created_at'])) ?></div>
                    <div class="step"><strong>Payment</strong><?= e(payment_method_label($payment['method'] ?? '')) ?> · <?= e(ucwords(str_replace('_', ' ', $order['payment_status']))) ?></div>
                    <div class="step"><strong>Dispatched</strong><?= $order['dispatch_date'] ? e(format_date($order['dispatch_date'])) : 'Waiting' ?></div>
                    <div class="step"><strong>Delivered</strong><?= $order['delivery_date'] ? e(format_date($order['delivery_date'])) : 'Waiting' ?></div>
                </div>
                <?php if ($order['delivery_date']): ?>
                    <p class="small mb-0">Return window closes <?= e((string) return_deadline($order)) ?>.</p>
                <?php endif; ?>
            </div>
            <?php if ($payment): ?>
                <div class="soft-card mb-3">
                    <h2 class="h5">Payment</h2>
                    <p class="mb-1"><?= e(payment_method_label($payment['method'])) ?> · <?= status_badge($payment['status']) ?></p>
                    <?php if ($payment['method'] === 'credit_card'): ?>
                        <p class="small mb-0"><?= e((string) $payment['card_brand']) ?> ending <?= e((string) $payment['card_last4']) ?>. Reference <?= e((string) $payment['txn_reference']) ?>. This was a simulated payment.</p>
                    <?php elseif ($payment['method'] === 'cheque'): ?>
                        <p class="small mb-0">Cheque <?= e((string) $payment['cheque_number']) ?>, <?= e((string) $payment['cheque_bank']) ?>, <?= e(format_day($payment['cheque_date'])) ?>.</p>
                    <?php else: ?>
                        <p class="small mb-0">Cash is due when the order is delivered.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($records): ?>
                <div class="soft-card mb-3">
                    <h2 class="h5">Staff notes</h2>
                    <?php foreach ($records as $record): ?>
                        <p class="mb-2"><strong><?= e(ucwords(str_replace('_', ' ', $record['action']))) ?></strong> · <?= e(format_date($record['action_date'])) ?><br><?= e((string) $record['notes']) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php foreach ($refunds as $refund): ?>
                <div class="alert alert-info">Refund of <?= e(money($refund['amount'])) ?> is <?= e($refund['status']) ?>. <?= e($refund['reason']) ?></div>
            <?php endforeach; ?>
            <?php if (can_cancel($order)): ?>
                <form method="post" class="soft-card mb-3 no-print">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="cancel">
                    <h2 class="h5">Cancel before dispatch</h2>
                    <textarea class="form-control mb-2" name="reason" rows="2" placeholder="Optional reason"></textarea>
                    <button class="btn btn-outline-danger" type="submit" data-confirm="Cancel this order?">Cancel order</button>
                </form>
            <?php endif; ?>
            <?php if (can_request_return($order) && !$openReturn): ?>
                <form method="post" class="soft-card no-print">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="return">
                    <h2 class="h5">Return or replacement</h2>
                    <select class="form-select mb-2" name="request_type">
                        <option value="return">Return for a refund</option>
                        <option value="replacement">Replacement</option>
                    </select>
                    <textarea class="form-control mb-2" name="reason" rows="3" placeholder="What should the shop know?" required></textarea>
                    <button class="btn btn-forest" type="submit">Send request</button>
                </form>
            <?php endif; ?>
            <?php foreach ($returns as $request): ?>
                <div class="soft-card mt-3">
                    <strong><?= e(ucfirst($request['request_type'])) ?></strong> <?= status_badge($request['status']) ?>
                    <p class="mb-0"><?= e($request['reason']) ?></p>
                    <?php if ($request['admin_notes']): ?><p class="small mb-0 mt-1">Shop note: <?= e($request['admin_notes']) ?></p><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
require __DIR__ . '/../includes/customer_close.php';
require __DIR__ . '/../includes/footer.php';
?>
