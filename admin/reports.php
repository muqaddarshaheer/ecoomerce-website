<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

$from = query_text('from', 10);
$to = query_text('to', 10);
$range = [];
$whereDate = '';
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
    $whereDate .= ' AND %s >= ?';
    $range[] = $from . ' 00:00:00';
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    $whereDate .= ' AND %s <= ?';
    $range[] = $to . ' 23:59:59';
}
$apply = function (string $column) use ($whereDate): string {
    return sprintf($whereDate, $column, $column);
};

$payments = db()->prepare('SELECT p.*, o.order_number, o.delivery_type FROM payments p JOIN orders o ON o.id = p.order_id WHERE 1=1 ' . $apply('p.created_at') . ' ORDER BY p.id DESC');
$payments->execute($range);
$paymentRows = $payments->fetchAll();

$dispatch = db()->prepare("SELECT o.order_number, o.dispatch_date, o.order_status, o.payment_status, u.full_name FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = c.user_id WHERE o.dispatch_date IS NOT NULL " . $apply('o.dispatch_date') . ' ORDER BY o.dispatch_date DESC');
$dispatch->execute($range);
$dispatchRows = $dispatch->fetchAll();

$delivery = db()->prepare("SELECT o.order_number, o.delivery_date, o.payment_status, u.full_name FROM orders o JOIN customers c ON c.id = o.customer_id JOIN users u ON u.id = c.user_id WHERE o.delivery_date IS NOT NULL " . $apply('o.delivery_date') . ' ORDER BY o.delivery_date DESC');
$delivery->execute($range);
$deliveryRows = $delivery->fetchAll();

$stock = db()->query('SELECT product_code, name, stock, price, status FROM products ORDER BY stock ASC, name')->fetchAll();

if (($_GET['export'] ?? '') === 'csv') {
    $kind = query_text('kind', 20);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="paperlane-' . $kind . '.csv"');
    $out = fopen('php://output', 'w');
    if ($kind === 'payments') {
        fputcsv($out, ['Order', 'Method', 'Amount', 'Status', 'Date']);
        foreach ($paymentRows as $row) {
            fputcsv($out, [$row['order_number'], $row['method'], $row['amount'], $row['status'], $row['created_at']]);
        }
    } elseif ($kind === 'dispatch') {
        fputcsv($out, ['Order', 'Customer', 'Dispatched', 'Order status', 'Payment']);
        foreach ($dispatchRows as $row) {
            fputcsv($out, [$row['order_number'], $row['full_name'], $row['dispatch_date'], $row['order_status'], $row['payment_status']]);
        }
    } elseif ($kind === 'delivery') {
        fputcsv($out, ['Order', 'Customer', 'Delivered', 'Payment']);
        foreach ($deliveryRows as $row) {
            fputcsv($out, [$row['order_number'], $row['full_name'], $row['delivery_date'], $row['payment_status']]);
        }
    } else {
        fputcsv($out, ['Product ID', 'Name', 'Stock', 'Price', 'Status']);
        foreach ($stock as $row) {
            fputcsv($out, [$row['product_code'], $row['name'], $row['stock'], $row['price'], $row['status']]);
        }
    }
    fclose($out);
    exit;
}

$pageTitle = 'Reports';
$active = 'reports';
require __DIR__ . '/../includes/admin_header.php';
$exportBase = 'reports.php?from=' . urlencode($from) . '&to=' . urlencode($to) . '&export=csv&kind=';
?>
<div class="d-flex justify-content-between align-items-center">
    <h1>Reports</h1>
    <button class="btn btn-outline-dark no-print" type="button" onclick="window.print()">Print</button>
</div>
<form class="row g-2 mb-3 no-print" method="get">
    <div class="col-md-3"><input class="form-control" type="date" name="from" value="<?= e($from) ?>"></div>
    <div class="col-md-3"><input class="form-control" type="date" name="to" value="<?= e($to) ?>"></div>
    <div class="col-md-2"><button class="btn btn-forest" type="submit">Filter dates</button></div>
</form>
<h2 class="h5">Payments <a class="small" href="<?= e($exportBase . 'payments') ?>">CSV</a></h2>
<div class="table-responsive soft-card mb-4">
    <table class="table mb-0"><thead><tr><th>Order</th><th>Method</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($paymentRows as $row): ?><tr><td><?= e($row['order_number']) ?></td><td><?= e(payment_method_label($row['method'])) ?></td><td><?= e(money($row['amount'])) ?></td><td><?= status_badge($row['status']) ?></td><td><?= e(format_date($row['created_at'])) ?></td></tr><?php endforeach; ?>
    <?php if (!$paymentRows): ?><tr><td colspan="5">No payments in this range.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<h2 class="h5">Dispatch <a class="small" href="<?= e($exportBase . 'dispatch') ?>">CSV</a></h2>
<div class="table-responsive soft-card mb-4">
    <table class="table mb-0"><thead><tr><th>Order</th><th>Customer</th><th>Dispatched</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($dispatchRows as $row): ?><tr><td><?= e($row['order_number']) ?></td><td><?= e($row['full_name']) ?></td><td><?= e(format_date($row['dispatch_date'])) ?></td><td><?= status_badge($row['order_status']) ?></td></tr><?php endforeach; ?>
    <?php if (!$dispatchRows): ?><tr><td colspan="4">No dispatches in this range.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<h2 class="h5">Delivery <a class="small" href="<?= e($exportBase . 'delivery') ?>">CSV</a></h2>
<div class="table-responsive soft-card mb-4">
    <table class="table mb-0"><thead><tr><th>Order</th><th>Customer</th><th>Delivered</th><th>Payment</th></tr></thead><tbody>
    <?php foreach ($deliveryRows as $row): ?><tr><td><?= e($row['order_number']) ?></td><td><?= e($row['full_name']) ?></td><td><?= e(format_date($row['delivery_date'])) ?></td><td><?= status_badge($row['payment_status']) ?></td></tr><?php endforeach; ?>
    <?php if (!$deliveryRows): ?><tr><td colspan="4">No deliveries in this range.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<h2 class="h5">Stock <a class="small" href="<?= e($exportBase . 'stock') ?>">CSV</a></h2>
<div class="table-responsive soft-card">
    <table class="table mb-0"><thead><tr><th>Product ID</th><th>Name</th><th>Stock</th><th>Price</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($stock as $row): ?><tr class="<?= (int) $row['stock'] <= 5 ? 'table-warning' : '' ?>"><td><?= e($row['product_code']) ?></td><td><?= e($row['name']) ?></td><td><?= (int) $row['stock'] ?></td><td><?= e(money($row['price'])) ?></td><td><?= status_badge($row['status']) ?></td></tr><?php endforeach; ?>
    <?php if (!$stock): ?><tr><td colspan="5">No products.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
