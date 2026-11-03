<?php
require_once __DIR__ . '/../includes/init.php';
require_employee();
$tab = query_text('tab', 20);
if (!in_array($tab, ['ready', 'dispatched', 'delivered'], true)) {
    $tab = 'ready';
}
$status = ['ready' => 'confirmed', 'dispatched' => 'dispatched', 'delivered' => 'delivered'][$tab];
$q = query_text('q', 20);
$sql = "SELECT o.*, u.full_name, u.phone FROM orders o
    JOIN customers c ON c.id = o.customer_id
    JOIN users u ON u.id = c.user_id
    WHERE o.order_status = ?
    AND (o.delivery_type = '3' OR o.payment_status = 'cleared')";
$params = [$status];
if ($q !== '') {
    $sql .= " AND o.order_number LIKE ? ESCAPE '\\\\'";
    $params[] = like_term($q);
}
$sql .= ' ORDER BY o.id DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$pageTitle = 'Orders';
$active = 'orders';
require __DIR__ . '/../includes/employee_header.php';
?>
<h1>Orders you can fulfil</h1>
<p>Credit-card and cheque orders stay hidden until payment is cleared. Cash-on-delivery orders appear as soon as they are confirmed.</p>
<ul class="nav nav-pills mb-3">
    <?php foreach (['ready' => 'Ready', 'dispatched' => 'Dispatched', 'delivered' => 'Delivered'] as $key => $label): ?>
        <li class="nav-item"><a class="nav-link <?= $tab === $key ? 'active' : '' ?>" href="orders.php?tab=<?= e($key) ?>"><?= e($label) ?></a></li>
    <?php endforeach; ?>
</ul>
<form class="row g-2 mb-3" method="get">
    <input type="hidden" name="tab" value="<?= e($tab) ?>">
    <div class="col-md-4"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Order number"></div>
    <div class="col-md-2"><button class="btn btn-outline-dark" type="submit">Find</button></div>
</form>
<div class="table-responsive soft-card">
    <table class="table align-middle mb-0">
        <thead><tr><th>Order</th><th>Customer</th><th>City</th><th>Payment</th><th>Total</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['order_number']) ?><div class="small text-secondary"><?= e(delivery_label($row['delivery_type'])) ?></div></td>
                <td><?= e($row['full_name']) ?><div class="small"><?= e((string) $row['phone']) ?></div></td>
                <td><?= e($row['ship_city']) ?></td>
                <td><?= status_badge($row['payment_status']) ?></td>
                <td><?= e(money($row['total_amount'])) ?></td>
                <td><a class="btn btn-line btn-sm" href="order.php?id=<?= (int) $row['id'] ?>">Open</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6">No orders in this list.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/employee_footer.php'; ?>
