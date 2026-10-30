<?php
require_once __DIR__ . '/../includes/init.php';
$admin = require_admin();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $action = post('action', 30);
        if ($action === 'cheque') {
            settle_cheque($id, post('cheque_action', 20), (int) $admin['id'], post('notes', 255));
            set_flash('success', 'Cheque payment updated.');
        } elseif ($action === 'cash') {
            mark_cash_collected($id, (int) $admin['id']);
            set_flash('success', 'Cash marked as collected.');
        } elseif ($action === 'dispatch') {
            record_dispatch($id, (int) $admin['id'], null, post('notes', 500));
            set_flash('success', 'Order marked dispatched.');
        } elseif ($action === 'deliver') {
            record_delivery($id, (int) $admin['id'], null, post('notes', 500), post('result') !== 'failed', isset($_POST['cash_collected']));
            set_flash('success', 'Delivery report saved.');
        } elseif ($action === 'refund') {
            issue_refund((int) post('refund_id', 10), (int) $admin['id'], post('notes', 255));
            set_flash('success', 'Refund marked as issued. No bank transfer is sent by this project.');
        }
    } catch (AppException $e) {
        set_flash('danger', $e->getMessage());
    }
    redirect('admin/order.php?id=' . $id);
}

$order = db()->prepare('SELECT o.*, u.full_name, u.email, u.phone FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = c.user_id WHERE o.id = ?');
$order->execute([$id]);
$order = $order->fetch();
if (!$order) {
    set_flash('danger', 'Order not found.');
    redirect('admin/orders.php');
}
$items = order_items($id);
$payment = order_payment($id);
$records = order_timeline_records($id);
$refunds = order_refund_rows($id);
$parts = order_number_parts($order['order_number']);
$pageTitle = 'Order ' . $order['order_number'];
$active = 'orders';
require __DIR__ . '/../includes/admin_header.php';
?>
<p><a href="orders.php">All orders</a></p>
<h1><?= e($order['order_number']) ?></h1>
<p>Delivery <?= e($parts['delivery']) ?> · product <?= e($parts['product_code']) ?> · sequence <?= e($parts['sequence']) ?></p>
<p><?= status_badge($order['order_status']) ?> <?= status_badge($order['payment_status']) ?> <?= e(delivery_label($order['delivery_type'])) ?></p>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="soft-card mb-3">
            <h2 class="h5"><?= e($order['full_name']) ?></h2>
            <p><?= e($order['email']) ?> · <?= e((string) $order['phone']) ?></p>
            <p class="mb-0"><?= e($order['ship_name']) ?>, <?= e($order['ship_phone']) ?><br><?= e($order['ship_address']) ?>, <?= e($order['ship_city']) ?>, <?= e($order['ship_state']) ?> <?= e($order['ship_postal']) ?></p>
        </div>
        <div class="soft-card">
            <table class="table mb-0">
                <thead><tr><th>ID</th><th>Item</th><th>Qty</th><th>Price</th><th>Line</th></tr></thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= e($item['product_code']) ?></td>
                        <td><?= e($item['product_name']) ?></td>
                        <td><?= (int) $item['quantity'] ?></td>
                        <td><?= e(money($item['unit_price'])) ?></td>
                        <td><?= e(money($item['line_total'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p class="mt-2 mb-0">Goods <?= e(money($order['subtotal'])) ?> · Delivery <?= e(money($order['shipping_fee'])) ?> · <strong><?= e(money($order['total_amount'])) ?></strong></p>
        </div>
    </div>
    <div class="col-lg-5">
        <?php if ($payment): ?>
            <div class="soft-card mb-3">
                <h2 class="h5">Payment</h2>
                <p><?= e(payment_method_label($payment['method'])) ?> <?= status_badge($payment['status']) ?></p>
                <?php if ($payment['method'] === 'credit_card'): ?>
                    <p class="small"><?= e((string) $payment['card_brand']) ?> ending <?= e((string) $payment['card_last4']) ?><br>Reference <?= e((string) $payment['txn_reference']) ?><br><?= e((string) $payment['notes']) ?></p>
                <?php elseif ($payment['method'] === 'cheque'): ?>
                    <p class="small">Cheque <?= e((string) $payment['cheque_number']) ?><br><?= e((string) $payment['cheque_bank']) ?> · <?= e(format_day($payment['cheque_date'])) ?><br><?= e((string) $payment['notes']) ?></p>
                    <?php if (in_array($payment['status'], ['pending', 'verified'], true) && !in_array($order['order_status'], ['cancelled', 'dispatched', 'delivered'], true)): ?>
                        <form method="post" class="mb-2">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $id ?>">
                            <input type="hidden" name="action" value="cheque">
                            <textarea class="form-control mb-2" name="notes" rows="2" placeholder="Staff note"></textarea>
                            <?php if ($payment['status'] === 'pending'): ?>
                                <button class="btn btn-outline-dark" name="cheque_action" value="verified" type="submit">Mark verified</button>
                            <?php else: ?>
                                <button class="btn btn-forest" name="cheque_action" value="cleared" type="submit">Mark cleared</button>
                            <?php endif; ?>
                            <button class="btn btn-outline-danger" name="cheque_action" value="failed" type="submit" data-confirm="Reject this cheque and cancel the order?">Reject cheque</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="small mb-2">Cash on delivery. <?= e((string) $payment['notes']) ?></p>
                    <?php if ($payment['status'] !== 'cleared' && in_array($order['order_status'], ['dispatched', 'delivered'], true)): ?>
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $id ?>"><input type="hidden" name="action" value="cash"><button class="btn btn-forest btn-sm" type="submit">Mark cash collected</button></form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if (can_dispatch($order)): ?>
            <form method="post" class="soft-card mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <input type="hidden" name="action" value="dispatch">
                <h2 class="h5">Dispatch</h2>
                <textarea class="form-control mb-2" name="notes" rows="2" placeholder="Optional note"></textarea>
                <button class="btn btn-forest" type="submit" data-confirm="Mark this order dispatched?">Mark dispatched</button>
            </form>
        <?php endif; ?>
        <?php if (can_deliver($order)): ?>
            <form method="post" class="soft-card mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <input type="hidden" name="action" value="deliver">
                <h2 class="h5">Delivery report</h2>
                <select class="form-select mb-2" name="result"><option value="delivered">Delivered</option><option value="failed">Could not deliver</option></select>
                <textarea class="form-control mb-2" name="notes" rows="2" required placeholder="What happened?"></textarea>
                <?php if ($order['delivery_type'] === '3' && $order['payment_status'] !== 'cleared'): ?>
                    <label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="cash_collected" checked> Cash collected</label>
                <?php endif; ?>
                <button class="btn btn-forest" type="submit">Save report</button>
            </form>
        <?php endif; ?>
        <?php foreach ($refunds as $refund): ?>
            <div class="soft-card mb-3">
                <h2 class="h5">Refund <?= status_badge($refund['status']) ?></h2>
                <p><?= e(money($refund['amount'])) ?> · <?= e($refund['reason']) ?></p>
                <?php if ($refund['status'] === 'pending'): ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $id ?>">
                        <input type="hidden" name="action" value="refund">
                        <input type="hidden" name="refund_id" value="<?= (int) $refund['id'] ?>">
                        <textarea class="form-control mb-2" name="notes" rows="2" placeholder="Note"></textarea>
                        <button class="btn btn-forest" type="submit" data-confirm="Mark this refund as issued?">Issue refund</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($records): ?>
            <div class="soft-card">
                <h2 class="h5">Dispatch log</h2>
                <?php foreach ($records as $record): ?>
                    <p class="mb-2"><strong><?= e(ucwords(str_replace('_', ' ', $record['action']))) ?></strong> by <?= e($record['full_name']) ?><br><?= e(format_date($record['action_date'])) ?><br><?= e((string) $record['notes']) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
