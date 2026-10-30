<?php
require_once __DIR__ . '/../includes/init.php';
$admin = require_admin();
$pdo = db();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$product = $id ? find_product($id) : null;
if ($id && !$product) {
    set_flash('danger', 'Product not found.');
    redirect('admin/products.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        if (post('action') === 'delete_image') {
            $imageId = (int) post('image_id', 10);
            $img = $pdo->prepare('SELECT * FROM product_images WHERE id = ? AND product_id = ?');
            $img->execute([$imageId, $id]);
            $image = $img->fetch();
            $count = $pdo->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ?');
            $count->execute([$id]);
            if (!$image) {
                throw new AppException('Image not found.');
            }
            if ((int) $count->fetchColumn() <= 1) {
                throw new AppException('A product needs at least one image.');
            }
            $pdo->prepare('DELETE FROM product_images WHERE id = ?')->execute([$imageId]);
            delete_upload('products', $image['file_name']);
            if ((int) $image['is_primary'] === 1) {
                $pdo->prepare('UPDATE product_images SET is_primary = 1 WHERE product_id = ? ORDER BY id LIMIT 1')->execute([$id]);
            }
            set_flash('success', 'Image removed.');
            redirect('admin/product-form.php?id=' . $id);
        }

        $name = post('name', 150);
        $description = post('description', 5000);
        $warranty = post('warranty_info', 255);
        $categoryId = (int) post('category_id', 10);
        $status = post('status') === 'inactive' ? 'inactive' : 'active';
        $featured = isset($_POST['featured']) ? 1 : 0;
        $priceRaw = post('price', 12);
        $stock = filter_var(post('stock', 10), FILTER_VALIDATE_INT);
        if ($name === '' || strlen($description) < 10) {
            throw new AppException('Enter a name and a description of at least 10 characters.');
        }
        if (!is_numeric($priceRaw) || (float) $priceRaw <= 0 || (float) $priceRaw > 1000000) {
            throw new AppException('Enter a valid price.');
        }
        if ($stock === false || $stock < 0 || $stock > 100000) {
            throw new AppException('Enter a stock quantity from 0 to 100000.');
        }
        if ($warranty === '') {
            $warranty = 'No warranty';
        }
        $price = from_cents(to_cents($priceRaw));
        $files = normalize_files($_FILES['images'] ?? []);
        if (!$product && !$files) {
            throw new AppException('Upload at least one product image.');
        }
        $existingCount = 0;
        if ($product) {
            $c = $pdo->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ?');
            $c->execute([$id]);
            $existingCount = (int) $c->fetchColumn();
        }
        if ($existingCount + count($files) > 4) {
            throw new AppException('A product can have up to 4 images.');
        }

        $saved = [];
        $workingId = $id;
        $pdo->beginTransaction();
        try {
            if (!$product) {
                $code = generate_product_code($pdo, $categoryId);
                $pdo->prepare('INSERT INTO products (product_code, category_id, name, description, price, warranty_info, stock, featured, status) VALUES (?,?,?,?,?,?,0,?,?)')
                    ->execute([$code, $categoryId, $name, $description, $price, $warranty, $featured, $status]);
                $workingId = (int) $pdo->lastInsertId();
                change_stock($pdo, $workingId, (int) $stock, 'opening', 'product', $workingId, (int) $admin['id'], 'Opening stock');
            } else {
                $lock = $pdo->prepare('SELECT stock FROM products WHERE id = ? FOR UPDATE');
                $lock->execute([$workingId]);
                $oldStock = (int) $lock->fetchColumn();
                $pdo->prepare('UPDATE products SET category_id=?, name=?, description=?, price=?, warranty_info=?, featured=?, status=?, updated_at=? WHERE id=?')
                    ->execute([$categoryId, $name, $description, $price, $warranty, $featured, $status, date('Y-m-d H:i:s'), $workingId]);
                change_stock($pdo, $workingId, (int) $stock - $oldStock, 'adjustment', 'product', $workingId, (int) $admin['id'], 'Stock edited by admin');
            }
            $hasPrimary = 0;
            if ($product) {
                $p = $pdo->prepare('SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_primary = 1');
                $p->execute([$workingId]);
                $hasPrimary = (int) $p->fetchColumn();
            }
            foreach ($files as $index => $file) {
                $stored = store_uploaded_image($file, 'products');
                $saved[] = $stored;
                $primary = $hasPrimary === 0 && $index === 0 ? 1 : 0;
                $pdo->prepare('INSERT INTO product_images (product_id, file_name, is_primary) VALUES (?,?,?)')->execute([$workingId, $stored, $primary]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            foreach ($saved as $fileName) {
                delete_upload('products', $fileName);
            }
            throw $e;
        }
        $savedProduct = find_product($workingId);
        set_flash('success', 'Product saved. Product ID ' . ($savedProduct['product_code'] ?? '') . '.');
        redirect('admin/product-form.php?id=' . $workingId);
    } catch (AppException $e) {
        set_flash('danger', $e->getMessage());
        redirect($id ? 'admin/product-form.php?id=' . $id : 'admin/product-form.php');
    } catch (Throwable $e) {
        app_log($e->getMessage());
        set_flash('danger', 'The product could not be saved.');
        redirect($id ? 'admin/product-form.php?id=' . $id : 'admin/product-form.php');
    }
}

$categories = $pdo->query("SELECT id, name, code_prefix FROM categories ORDER BY code_prefix")->fetchAll();
$images = $product ? product_gallery((int) $product['id']) : [];
$pageTitle = $product ? 'Edit product' : 'Add product';
$active = $product ? 'products' : 'add';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1><?= $product ? 'Edit product' : 'Add product' ?></h1>
<?php if ($product): ?><p>Product ID <strong><?= e($product['product_code']) ?></strong> does not change.</p><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="soft-card mb-3">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int) $id ?>">
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" value="<?= e($product['name'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Category</label>
            <select class="form-select" name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" <?= (int) ($product['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['code_prefix'] . ' ' . $category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="5" required><?= e($product['description'] ?? '') ?></textarea>
        </div>
        <div class="col-md-3">
            <label class="form-label">Price (Rs)</label>
            <input class="form-control" name="price" value="<?= e($product['price'] ?? '') ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Stock</label>
            <input class="form-control" name="stock" value="<?= e((string) ($product['stock'] ?? '0')) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="active">Active</option>
                <option value="inactive" <?= (($product['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Warranty</label>
            <input class="form-control" name="warranty_info" value="<?= e($product['warranty_info'] ?? 'No warranty') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Images</label>
            <input class="form-control" type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" <?= $product ? '' : 'required' ?> multiple>
            <div class="form-text">JPG, PNG, WEBP or GIF. Up to 2 MB each. Maximum 4 photos.</div>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            <label class="form-check"><input class="form-check-input" type="checkbox" name="featured" <?= !empty($product['featured']) ? 'checked' : '' ?>> Featured on the home page</label>
        </div>
    </div>
    <button class="btn btn-forest mt-3" type="submit">Save product</button>
    <a class="btn btn-link" href="products.php">Back</a>
</form>
<?php if ($images): ?>
    <div class="d-flex gap-3 flex-wrap">
        <?php foreach ($images as $image): ?>
            <?php $src = media_url('products', $image['file_name']); ?>
            <div>
                <?php if ($src): ?><img src="<?= e($src) ?>" alt="" class="thumb-sm" style="width:96px;height:96px"><?php endif; ?>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_image">
                    <input type="hidden" name="id" value="<?= (int) $id ?>">
                    <input type="hidden" name="image_id" value="<?= (int) $image['id'] ?>">
                    <button class="btn btn-link text-danger p-0" type="submit" data-confirm="Remove this image?">Remove</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
