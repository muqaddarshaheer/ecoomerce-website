<?php
require_once __DIR__ . '/includes/init.php';
$checks = [];
$checks[] = ['PHP 8 or newer', PHP_VERSION_ID >= 80000, PHP_VERSION];
$checks[] = ['PDO MySQL', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'loaded' : 'missing'];
$connected = false;
$tables = [];
try {
    $pdo = db();
    $connected = true;
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $connected = false;
}
$checks[] = ['Database connection', $connected, $connected ? DB_NAME : 'Check config/database.php and start MySQL'];
$checks[] = ['Tables imported', $connected && count($tables) >= 15, $connected ? count($tables) . ' tables' : 'Import database.sql'];
$uploadOk = is_dir(ROOT_PATH . '/uploads/products') && is_writable(ROOT_PATH . '/uploads/products');
$checks[] = ['Product uploads writable', $uploadOk, ROOT_PATH . '/uploads/products'];
$pageTitle = 'Setup check';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4" style="max-width:760px">
    <h1>Setup check</h1>
    <p>This page does not create accounts or change data. Login details are in the README.</p>
    <ul class="list-group">
        <?php foreach ($checks as $check): ?>
            <li class="list-group-item d-flex justify-content-between"><span><?= e($check[0]) ?></span><span><?= $check[1] ? 'OK' : 'Needs attention' ?> · <?= e((string) $check[2]) ?></span></li>
        <?php endforeach; ?>
    </ul>
    <p class="mt-3"><a class="btn btn-forest" href="<?= e(base_url('index.php')) ?>">Open the shop</a> <a class="btn btn-outline-dark" href="<?= e(base_url('admin/login.php')) ?>">Admin login</a></p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
