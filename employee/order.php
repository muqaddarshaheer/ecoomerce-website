<?php
require_once __DIR__ . '/../includes/init.php';
$user = require_employee();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $orderCheck = db()->prepare('SELECT * FROM orders WHERE id = ?');
        $orderCheck->execute([$id]);
        $existing = $orderCheck->fetch();
        if (!$existing || !employee_can_view($existing)) {
            throw new AppException('This order is not available on the employee desk.');
        }
        if (post('action') === 'dispatch') {
            record_dispatch($id, (int) $user['id'], (int) $user['employee_id'], post('notes', 500));
            set_flash('success', 'Dispatch recorded.');
        } else {
            record_delivery($id, (int) $user['id'], (int) $user['employee_id'], post('notes', 500), post('result') !== 'failed', isset($_POST['cash_collected']));
            set_flash('success', 'Delivery report saved.');
        }
    } catch (AppException $e) {
        set_flash('danger', $e->getMessage());
    }
    redirect('employee/order.php?id=' . $id);
}

$stmt = db()->prepare('SELECT o.*, u.full_name, u.phone AS customer_phone FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = c.user_id WHERE o.id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order || !employee_can_view($order)) {
    set_flash('warning', 'That order is not available to employees.');
    redirect('employee/orders.php');
}
$items = order_items($id);
$payment = order_payment($id);
$records = order_timeline_records($id);
$pageTitle = 'Order ' . $order['order_number'];
$active = 'orders';
require __DIR__ . '/../includes/employee_header.php';
?>
<p><a href="orders.php">Back to orders</a></p>
<h1><?= e($order['order_number']) ?></h1>
<p><?= status_badge($order['order_status']) ?> Payment <?= status_badge($order['payment_status']) ?> · <?= e(delivery_label($order['delivery_type'])) ?></p>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="soft-card mb-3">
            <h2 class="h5">Delivery</h2>
            <p class="mb-0"><strong><?= e($order['ship_name']) ?></strong> · <?= e($order['ship_phone']) ?><br><?= e($order['ship_address']) ?><br><?= e($order['ship_city']) ?>, <?= e($order['ship_state']) ?> <?= e($order['ship_postal']) ?></p>
        </div>
        <div class="soft-card">
            <h2 class="h5">Contents</h2>
            <ul class="mb-0">
                <?php foreach ($items as $item): ?>
                    <li><?= e($item['product_name']) ?> (<?= e($item['product_code']) ?>) × <?= (int) $item['quantity'] ?><?php $customText = gift_custom_summary($item); if ($customText !== '') { echo ' — ' . e($customText); } ?></li>
                <?php endforeach; ?>
            </ul>
            <p class="mt-2 mb-0"><?php if ($order['delivery_type'] === '3' && $order['payment_status'] !== 'cleared'): ?>Collect <?= e(money($order['total_amount'])) ?> in cash when you hand over the parcel.<?php else: ?>Payment is already settled. Do not collect cash for this parcel.<?php endif; ?></p>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="soft-card mb-3">
            <h2 class="h5">Payment clearance</h2>
            <p class="mb-0"><?= e(payment_method_label($payment['method'] ?? '')) ?> is <?= e($order['payment_status']) ?>.</p>
        </div>
        <?php if (can_dispatch($order)): ?>
            <form method="post" class="soft-card mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <input type="hidden" name="action" value="dispatch">
                <h2 class="h5">Dispatch</h2>
                <textarea class="form-control mb-2" name="notes" rows="2" placeholder="Courier or rider note"></textarea>
                <button class="btn btn-forest" type="submit" data-confirm="Mark this order dispatched?">Mark dispatched</button>
            </form>
        <?php endif; ?>
        <?php if (can_deliver($order)): ?>
            <form method="post" class="soft-card mb-3">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <input type="hidden" name="action" value="deliver">
                <h2 class="h5">Delivery report</h2>
                <select class="form-select mb-2" name="result">
                    <option value="delivered">Delivered</option>
                    <option value="failed">Could not deliver</option>
                </select>
                <textarea class="form-control mb-2" name="notes" rows="3" required placeholder="Who received it, or why it failed"></textarea>
                <?php if ($order['delivery_type'] === '3' && $order['payment_status'] !== 'cleared'): ?>
                    <label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="cash_collected" checked> Cash collected</label>
                <?php endif; ?>
                <button class="btn btn-forest" type="submit">Save report</button>
            </form>
        <?php endif; ?>
        <?php foreach ($records as $record): ?>
            <p class="small"><strong><?= e(ucwords(str_replace('_', ' ', $record['action']))) ?></strong> · <?= e(format_date($record['action_date'])) ?><br><?= e((string) $record['notes']) ?></p>
        <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__ . '/../includes/employee_footer.php'; ?>
