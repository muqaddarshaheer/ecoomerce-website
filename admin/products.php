<?php
require_once __DIR__ . '/../includes/init.php';
$admin = require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) post('id', 10);
    try {
        $used = $pdo->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
        $used->execute([$id]);
        if ((int) $used->fetchColumn() > 0) {
            $pdo->prepare("UPDATE products SET status = 'inactive', updated_at = ? WHERE id = ?")->execute([date('Y-m-d H:i:s'), $id]);
            set_flash('warning', 'This product is on an order, so it was deactivated instead of deleted.');
        } else {
            $images = $pdo->prepare('SELECT file_name FROM product_images WHERE product_id = ?');
            $images->execute([$id]);
            foreach ($images as $image) {
                delete_upload('products', $image['file_name']);
            }
            $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
            set_flash('success', 'Product deleted.');
        }
    } catch (Throwable $e) {
        app_log($e->getMessage());
        set_flash('danger', 'The product could not be deleted.');
    }
    redirect('admin/products.php');
}

$q = query_text('q', 80);
$where = '1=1';
$params = [];
if ($q !== '') {
    $where = "(p.name LIKE ? ESCAPE '\\\\' OR p.product_code LIKE ? ESCAPE '\\\\')";
    $like = like_term($q);
    $params = [$like, $like];
}
$stmt = $pdo->prepare(product_sql() . " WHERE $where ORDER BY p.id DESC");
$stmt->execute($params);
$rows = $stmt->fetchAll();
$pageTitle = 'Products';
$active = 'products';
require __DIR__ . '/../includes/admin_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Products</h1>
    <a class="btn btn-forest" href="product-form.php">Add new item</a>
</div>
<form class="row g-2 mb-3" method="get">
    <div class="col-md-6"><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="Name or product ID"></div>
    <div class="col-md-2"><button class="btn btn-outline-dark" type="submit">Search</button></div>
</form>
<div class="table-responsive soft-card">
    <table class="table align-middle mb-0">
        <thead><tr><th></th><th>Product ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?php $src = media_url('products', $row['image']); if ($src): ?><img class="thumb-sm" src="<?= e($src) ?>" alt=""><?php endif; ?></td>
                <td><?= e($row['product_code']) ?></td>
                <td><?= e($row['name']) ?><div class="small text-secondary"><?= e($row['category_name']) ?></div></td>
                <td><?= e(money($row['price'])) ?></td>
                <td><?= (int) $row['stock'] ?></td>
                <td><?= status_badge($row['status']) ?></td>
                <td class="text-nowrap">
                    <a href="product-form.php?id=<?= (int) $row['id'] ?>">Edit</a>
                    <a href="<?= e(base_url('product.php?code=' . urlencode($row['product_code']))) ?>">View</a>
                    <form class="d-inline" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                        <button class="btn btn-link text-danger p-0" data-confirm="Delete this product?" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="7">No products yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
