<?php
require_once __DIR__ . '/../includes/init.php';
$admin = require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) post('user_id', 10);
    $status = post('status') === 'inactive' ? 'inactive' : 'active';
    $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'customer'")->execute([$status, $id]);
    set_flash('success', 'Customer status updated.');
    redirect('admin/customers.php');
}

$q = query_text('q', 80);
$where = "u.role = 'customer'";
$params = [];
if ($q !== '') {
    $where .= " AND (u.full_name LIKE ? ESCAPE '\\\\' OR u.email LIKE ? ESCAPE '\\\\' OR u.phone LIKE ? ESCAPE '\\\\')";
    $like = like_term($q);
    $params = [$like, $like, $like];
}
$stmt = $pdo->prepare("SELECT u.*, c.id AS customer_id, (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.id) AS order_count FROM users u JOIN customers c ON c.user_id = u.id WHERE $where ORDER BY u.id DESC");
$stmt->execute($params);
$rows = $stmt->fetchAll();
$viewId = (int) ($_GET['id'] ?? 0);
$view = null;
$viewOrders = [];
if ($viewId) {
    foreach ($rows as $row) {
        if ((int) $row['customer_id'] === $viewId) {
            $view = $row;
        }
    }
    if (!$view) {
        $one = $pdo->prepare("SELECT u.*, c.id AS customer_id FROM users u JOIN customers c ON c.user_id = u.id WHERE c.id = ? AND u.role = 'customer'");
        $one->execute([$viewId]);
        $view = $one->fetch();
    }
    if ($view) {
        $orders = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC');
        $orders->execute([(int) $view['customer_id']]);
        $viewOrders = $orders->fetchAll();
        $addresses = $pdo->prepare('SELECT * FROM customer_addresses WHERE customer_id = ?');
        $addresses->execute([(int) $view['customer_id']]);
        $viewAddresses = $addresses->fetchAll();
    }
}
$pageTitle = 'Customers';
$active = 'customers';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Customers</h1>
<form class="row g-2 mb-3" method="get">
    <div class="col-md-6"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Name, email or phone"></div>
    <div class="col-md-2"><button class="btn btn-outline-dark" type="submit">Search</button></div>
</form>
<?php if (!empty($view)): ?>
    <div class="soft-card mb-3">
        <h2 class="h5"><?= e($view['full_name']) ?> <?= status_badge($view['status']) ?></h2>
        <p><?= e($view['email']) ?> · <?= e((string) $view['phone']) ?></p>
        <?php foreach ($viewAddresses as $address): ?>
            <p class="mb-1"><?= e($address['address_line']) ?>, <?= e($address['city']) ?>, <?= e($address['state']) ?> <?= e($address['postal_code']) ?></p>
        <?php endforeach; ?>
        <table class="table mb-0">
            <thead><tr><th>Order</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($viewOrders as $order): ?>
                <tr>
                    <td><?= e($order['order_number']) ?></td>
                    <td><?= e(money($order['total_amount'])) ?></td>
                    <td><?= status_badge($order['order_status']) ?></td>
                    <td><a class="btn btn-line btn-sm" href="order.php?id=<?= (int) $order['id'] ?>">Open</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$viewOrders): ?><tr><td colspan="4">No orders.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<div class="table-responsive soft-card">
    <table class="table align-middle mb-0">
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['full_name']) ?></td>
                <td><?= e($row['email']) ?></td>
                <td><?= e((string) $row['phone']) ?></td>
                <td><?= (int) $row['order_count'] ?></td>
                <td><?= status_badge($row['status']) ?></td>
                <td class="row-acts">
                    <a class="btn btn-line btn-sm" href="customers.php?id=<?= (int) $row['customer_id'] ?>">View</a>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="user_id" value="<?= (int) $row['id'] ?>">
                        <input type="hidden" name="status" value="<?= $row['status'] === 'active' ? 'inactive' : 'active' ?>">
                        <button class="btn btn-sm <?= $row['status'] === 'active' ? 'btn-outline-danger' : 'btn-forest' ?>" type="submit"><?= $row['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6">No customers found.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
