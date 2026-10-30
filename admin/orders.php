<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$from = query_text('from', 10);
$to = query_text('to', 10);
$delivery = query_text('delivery', 1);
$status = query_text('status', 20);
$customer = query_text('customer', 80);
$number = query_text('number', 16);
$pay = query_text('pay', 20);
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;

$where = ['1=1'];
$params = [];
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
    $where[] = 'o.created_at >= ?';
    $params[] = $from . ' 00:00:00';
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    $where[] = 'o.created_at <= ?';
    $params[] = $to . ' 23:59:59';
}
if (in_array($delivery, ['1', '2', '3'], true)) {
    $where[] = 'o.delivery_type = ?';
    $params[] = $delivery;
}
if (in_array($status, ['pending', 'confirmed', 'dispatched', 'delivered', 'cancelled'], true)) {
    $where[] = 'o.order_status = ?';
    $params[] = $status;
}
if ($customer !== '') {
    $where[] = "(u.full_name LIKE ? ESCAPE '\\\\' OR u.email LIKE ? ESCAPE '\\\\')";
    $like = like_term($customer);
    $params[] = $like;
    $params[] = $like;
}
if ($number !== '') {
    $where[] = "o.order_number LIKE ? ESCAPE '\\\\'";
    $params[] = like_term($number);
}
if ($pay === 'cheque') {
    $where[] = "p.method = 'cheque' AND p.status IN ('pending','verified')";
}

$whereSql = implode(' AND ', $where);
$fromSql = 'FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = c.user_id LEFT JOIN payments p ON p.order_id = o.id';
$count = db()->prepare("SELECT COUNT(*) $fromSql WHERE $whereSql");
$count->execute($params);
$total = (int) $count->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;
$sql = "SELECT o.*, u.full_name, u.email $fromSql WHERE $whereSql ORDER BY o.id DESC LIMIT ? OFFSET ?";
$stmt = db()->prepare($sql);
$i = 1;
foreach ($params as $value) {
    $stmt->bindValue($i++, $value);
}
$stmt->bindValue($i++, $perPage, PDO::PARAM_INT);
$stmt->bindValue($i, $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();
$query = compact('from', 'to', 'delivery', 'status', 'customer', 'number', 'pay');
$pageTitle = 'Orders';
$active = 'orders';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Orders</h1>
<form class="soft-card mb-3" method="get">
    <div class="row g-2">
        <div class="col-md-3"><label class="form-label">From</label><input class="form-control" type="date" name="from" value="<?= e($from) ?>"></div>
        <div class="col-md-3"><label class="form-label">To</label><input class="form-control" type="date" name="to" value="<?= e($to) ?>"></div>
        <div class="col-md-3"><label class="form-label">Delivery</label>
            <select class="form-select" name="delivery">
                <option value="">Any</option>
                <?php foreach (delivery_types() as $digit => $type): ?>
                    <option value="<?= e($digit) ?>" <?= $delivery === $digit ? 'selected' : '' ?>><?= e($type['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><label class="form-label">Order status</label>
            <select class="form-select" name="status">
                <option value="">Any</option>
                <?php foreach (['pending','confirmed','dispatched','delivered','cancelled'] as $item): ?>
                    <option value="<?= e($item) ?>" <?= $status === $item ? 'selected' : '' ?>><?= e(ucfirst($item)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Customer</label><input class="form-control" name="customer" value="<?= e($customer) ?>"></div>
        <div class="col-md-4"><label class="form-label">Order number</label><input class="form-control" name="number" value="<?= e($number) ?>"></div>
        <div class="col-md-4 d-flex align-items-end"><button class="btn btn-forest" type="submit">Search</button></div>
    </div>
</form>
<p><?= (int) $total ?> order<?= $total === 1 ? '' : 's' ?></p>
<div class="table-responsive soft-card">
    <table class="table align-middle mb-0">
        <thead><tr><th>Number</th><th>Date</th><th>Customer</th><th>Type</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['order_number']) ?></td>
                <td><?= e(format_day($row['created_at'])) ?></td>
                <td><?= e($row['full_name']) ?><div class="small text-secondary"><?= e($row['email']) ?></div></td>
                <td><?= e(delivery_label($row['delivery_type'])) ?></td>
                <td><?= e(money($row['total_amount'])) ?></td>
                <td><?= status_badge($row['payment_status']) ?></td>
                <td><?= status_badge($row['order_status']) ?></td>
                <td><a href="order.php?id=<?= (int) $row['id'] ?>">Open</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8">No orders matched.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?= pagination_html($page, $pages, 'admin/orders.php', array_filter($query, fn($v) => $v !== '')) ?>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
