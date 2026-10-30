<?php
require_once __DIR__ . '/includes/init.php';

$code = query_text('code', 7);
$product = find_product_by_code($code);
if (!$product || $product['status'] !== 'active' || $product['category_status'] !== 'active') {
    http_response_code(404);
    $pageTitle = 'Product not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container py-5"><div class="empty-state"><h2>Product not found</h2><p><a href="' . e(base_url('shop.php')) . '">Back to the shop</a></p></div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$gallery = product_gallery((int) $product['id']);
$relatedStmt = db()->prepare(product_sql() . " WHERE p.status = 'active' AND c.status = 'active' AND p.category_id = ? AND p.id <> ? ORDER BY p.id DESC LIMIT 4");
$relatedStmt->execute([(int) $product['category_id'], (int) $product['id']]);
$related = $relatedStmt->fetchAll();
$pageTitle = $product['name'];
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <p class="eyebrow mb-3"><a href="<?= e(base_url('shop.php')) ?>">Shop</a> / <a href="<?= e(base_url('shop.php?category=' . urlencode($product['category_slug']))) ?>"><?= e($product['category_name']) ?></a></p>
    <div class="row g-4 product-stage">
        <div class="col-lg-6">
            <?php $main = $gallery[0]['file_name'] ?? ($product['image'] ?? null); ?>
            <?php $mainUrl = media_url('products', $main); ?>
            <?php if ($mainUrl): ?>
                <img id="gallery-main" class="gallery-main" src="<?= e($mainUrl) ?>" alt="<?= e($product['name']) ?>">
            <?php else: ?>
                <?= picture_or_fallback(null, $product['name'], 'gallery-main') ?>
            <?php endif; ?>
            <?php if (count($gallery) > 1): ?>
                <div class="thumbs">
                    <?php foreach ($gallery as $index => $image): ?>
                        <?php $src = media_url('products', $image['file_name']); if (!$src) continue; ?>
                        <button type="button" data-gallery-src="<?= e($src) ?>" class="<?= $index === 0 ? 'is-selected' : '' ?>" aria-label="Show photo <?= $index + 1 ?>">
                            <img src="<?= e($src) ?>" alt="">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-lg-6">
            <p class="eyebrow">Product ID <?= e($product['product_code']) ?></p>
            <h1><?= e($product['name']) ?></h1>
            <p class="price fs-3"><?= e(money($product['price'])) ?></p>
            <p><?= (int) $product['stock'] > 0 ? (int) $product['stock'] . ' in stock' : 'Out of stock' ?></p>
            <p><strong>Warranty:</strong> <?= e($product['warranty_info']) ?></p>
            <div><?= nl2br(e($product['description'])) ?></div>
            <div id="ajax-note" class="alert d-none mt-3" role="status"></div>
            <?php if ((int) $product['stock'] > 0): ?>
                <form id="add-cart-form" class="mt-3" method="post" action="<?= e(base_url('cart.php')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_code" value="<?= e($product['product_code']) ?>">
                    <input type="hidden" name="back" value="<?= e('product.php?code=' . $product['product_code']) ?>">
                    <div class="d-flex gap-2 align-items-center">
                        <div class="qty-box">
                            <button type="button" data-qty="-1" aria-label="Decrease quantity">−</button>
                            <input type="number" name="quantity" value="1" min="1" max="<?= min(20, (int) $product['stock']) ?>" aria-label="Quantity">
                            <button type="button" data-qty="1" aria-label="Increase quantity">+</button>
                        </div>
                        <button class="btn btn-clay" type="submit">Add to cart</button>
                    </div>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary mt-3" type="button" disabled>Out of stock</button>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($related): ?>
        <div class="section-head"><h2>More in <?= e($product['category_name']) ?></h2></div>
        <div class="product-grid">
            <?php foreach ($related as $item) { product_card($item); } ?>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
