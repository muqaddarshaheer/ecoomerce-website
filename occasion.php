<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/occasions.php';

$slug = query_text('slug', 40);
$occasion = occasion_by_slug($slug);
if (!$occasion) {
    http_response_code(404);
    $pageTitle = 'Occasion not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container py-5"><div class="empty-state"><h2>That occasion is not on the counter</h2><p><a class="btn btn-clay" href="' . e(base_url('index.php')) . '">Back to the shop</a></p></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$pieces = occasion_products($occasion['codes']);
$pageTitle = $occasion['title'];
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <p class="eyebrow mb-2"><a href="<?= e(base_url('index.php')) ?>">Home</a> / Gifts for Every Occasion</p>
    <h1><?= e($occasion['title']) ?></h1>
    <p class="occasion-line"><?= e($occasion['line']) ?></p>
    <?php if (!$pieces): ?>
        <div class="empty-state">
            <h2>These gifts are not on the shelf yet</h2>
            <p><a class="btn btn-clay" href="<?= e(base_url('shop.php')) ?>">Browse the catalogue</a></p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($pieces as $piece) {
                occasion_buy_card($piece, $occasion['slug']);
            } ?>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
