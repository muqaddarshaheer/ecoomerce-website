<?php
require_once __DIR__ . '/../includes/init.php';
$admin = require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post('action', 20);
    try {
        if ($action === 'delete') {
            $id = (int) post('id', 10);
            $count = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
            $count->execute([$id]);
            if ((int) $count->fetchColumn() > 0) {
                throw new AppException('This category still has products, so it was not deleted.');
            }
            $row = $pdo->prepare('SELECT image FROM categories WHERE id = ?');
            $row->execute([$id]);
            $image = $row->fetchColumn();
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            if ($image) {
                delete_upload('categories', (string) $image);
            }
            set_flash('success', 'Category deleted.');
        } else {
            $id = (int) post('id', 10);
            $name = post('name', 100);
            $description = post('description', 500);
            $status = post('status', 20) === 'inactive' ? 'inactive' : 'active';
            $prefix = post('code_prefix', 2);
            if ($name === '') {
                throw new AppException('Enter a category name.');
            }
            if (!preg_match('/^\d{2}$/', $prefix)) {
                throw new AppException('The product code prefix must be 2 digits, for example 05.');
            }
            $current = null;
            if ($id) {
                $find = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
                $find->execute([$id]);
                $current = $find->fetch();
                if (!$current) {
                    throw new AppException('Category not found.');
                }
                $used = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
                $used->execute([$id]);
                if ((int) $used->fetchColumn() > 0) {
                    $prefix = $current['code_prefix'];
                }
            }
            $dup = $pdo->prepare('SELECT id FROM categories WHERE code_prefix = ? AND id <> ?');
            $dup->execute([$prefix, $id]);
            if ($dup->fetch()) {
                throw new AppException('That 2-digit code is already used.');
            }
            $slugBase = slugify($name);
            $slug = $slugBase;
            $n = 2;
            while (true) {
                $check = $pdo->prepare('SELECT id FROM categories WHERE slug = ? AND id <> ?');
                $check->execute([$slug, $id]);
                if (!$check->fetch()) {
                    break;
                }
                $slug = $slugBase . '-' . $n;
                $n++;
            }
            $imageName = is_array($current) ? ($current['image'] ?? null) : null;
            if (!empty($_FILES['image']['name'])) {
                $imageName = store_uploaded_image($_FILES['image'], 'categories');
                if (!empty($current['image'])) {
                    delete_upload('categories', (string) $current['image']);
                }
            }
            if ($id) {
                $pdo->prepare('UPDATE categories SET name=?, slug=?, code_prefix=?, description=?, image=?, status=? WHERE id=?')
                    ->execute([$name, $slug, $prefix, $description, $imageName, $status, $id]);
                set_flash('success', 'Category updated.');
            } else {
                $pdo->prepare('INSERT INTO categories (name, slug, code_prefix, description, image, status) VALUES (?,?,?,?,?,?)')
                    ->execute([$name, $slug, $prefix, $description, $imageName, $status]);
                set_flash('success', 'Category created.');
            }
        }
        redirect('admin/categories.php');
    } catch (AppException $e) {
        set_flash('danger', $e->getMessage());
        redirect('admin/categories.php');
    }
}

$editId = (int) ($_GET['edit'] ?? 0);
$edit = null;
if ($editId) {
    $stmt = $pdo->prepare('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count FROM categories c WHERE c.id = ?');
    $stmt->execute([$editId]);
    $edit = $stmt->fetch();
}
$rows = $pdo->query('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count FROM categories c ORDER BY c.code_prefix')->fetchAll();
$pageTitle = 'Categories';
$active = 'categories';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Categories</h1>
<div class="row g-4">
    <div class="col-lg-5">
        <form method="post" enctype="multipart/form-data" class="soft-card">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
            <h2 class="h5"><?= $edit ? 'Edit category' : 'Add category' ?></h2>
            <label class="form-label">Name</label>
            <input class="form-control mb-2" name="name" value="<?= e($edit['name'] ?? '') ?>" required>
            <label class="form-label">2-digit product code</label>
            <input class="form-control mb-2" name="code_prefix" value="<?= e($edit['code_prefix'] ?? '') ?>" <?= !empty($edit['product_count']) ? 'readonly' : '' ?> required>
            <p class="small text-secondary">Product IDs start with this code, for example 01 becomes 0100001. It stays fixed once products exist.</p>
            <label class="form-label">Description</label>
            <textarea class="form-control mb-2" name="description" rows="3"><?= e($edit['description'] ?? '') ?></textarea>
            <label class="form-label">Image</label>
            <input class="form-control mb-2" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
            <label class="form-label">Status</label>
            <select class="form-select mb-3" name="status">
                <option value="active">Active</option>
                <option value="inactive" <?= (($edit['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
            <button class="btn btn-forest" type="submit"><?= $edit ? 'Update' : 'Add category' ?></button>
            <?php if ($edit): ?><a class="btn btn-line" href="categories.php">Cancel</a><?php endif; ?>
        </form>
    </div>
    <div class="col-lg-7">
        <div class="table-responsive soft-card">
            <table class="table align-middle mb-0">
                <thead><tr><th></th><th>Code</th><th>Name</th><th>Products</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?php $src = media_url('categories', $row['image']); if ($src): ?><img class="thumb-sm" src="<?= e($src) ?>" alt=""><?php endif; ?></td>
                        <td><?= e($row['code_prefix']) ?></td>
                        <td><?= e($row['name']) ?></td>
                        <td><?= (int) $row['product_count'] ?></td>
                        <td><?= status_badge($row['status']) ?></td>
                        <td class="row-acts">
                            <a class="btn btn-line btn-sm" href="categories.php?edit=<?= (int) $row['id'] ?>">Edit</a>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                <button class="btn btn-outline-danger btn-sm" type="submit" data-confirm="Delete this category?">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
