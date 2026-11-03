<?php
require_once __DIR__ . '/includes/init.php';

$code = query_text('code', 7);
$picked = $code !== '' ? find_product_by_code($code) : null;
if ($picked && ($picked['status'] !== 'active' || $picked['category_status'] !== 'active')) {
    $picked = null;
}

$ranges = db()->query("SELECT id, name, slug, code_prefix, description FROM categories WHERE status = 'active' AND code_prefix IN ('05','06','07','08','09') ORDER BY code_prefix")->fetchAll();
$pageTitle = 'Customise gifts';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <p class="eyebrow">Made the way you ask</p>
    <h1>Customise a gift</h1>
    <p class="gift-lead">Pick a design, a colour, a name and a message. The same four finishes are on every gift: Classic &amp; Elegant, Cute &amp; Pastel, Luxury &amp; Gold, and Trendy &amp; Colourful. You can order only after you sign in.</p>

    <?php if ($picked): ?>
        <?php $photo = media_url('products', $picked['image'] ?? null); ?>
        <div class="row g-4 align-items-start mb-5">
            <div class="col-lg-5">
                <?php if ($photo): ?>
                    <img id="gift-preview" class="gallery-main" src="<?= e($photo) ?>" alt="<?= e($picked['name']) ?>">
                <?php else: ?>
                    <?= picture_or_fallback(null, $picked['name'], 'gallery-main') ?>
                <?php endif; ?>
            </div>
            <div class="col-lg-7">
                <p class="eyebrow"><?= e($picked['category_name']) ?> · <?= e($picked['product_code']) ?></p>
                <h2><?= e($picked['name']) ?></h2>
                <p class="price fs-3"><?= e(money($picked['price'])) ?></p>
                <p><?= nl2br(e($picked['description'])) ?></p>
                <?php if ((int) $picked['stock'] > 0): ?>
                    <form method="post" action="<?= e(base_url('cart.php')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_code" value="<?= e($picked['product_code']) ?>">
                        <input type="hidden" name="back" value="<?= e('customize.php?code=' . $picked['product_code']) ?>">
                        <?php gift_custom_fields(gift_custom_blank(), (string) ($picked['code_prefix'] ?? ''), $photo); ?>
                        <div class="d-flex gap-2 align-items-center flex-wrap mt-3">
                            <div class="qty-box">
                                <button type="button" data-qty="-1" aria-label="Decrease quantity">−</button>
                                <input type="number" name="quantity" value="1" min="1" max="<?= min(20, (int) $picked['stock']) ?>" aria-label="Quantity">
                                <button type="button" data-qty="1" aria-label="Increase quantity">+</button>
                            </div>
                            <button class="btn btn-clay" type="submit">Add to cart</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p>This gift is out of stock.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($ranges as $range): ?>
        <?php
        $items = db()->prepare(product_sql() . " WHERE p.status = 'active' AND c.status = 'active' AND p.category_id = ? ORDER BY p.product_code");
        $items->execute([(int) $range['id']]);
        $rows = $items->fetchAll();
        ?>
        <section class="mb-5">
            <div class="section-head">
                <div>
                    <p class="eyebrow"><?= e($range['code_prefix']) ?></p>
                    <h2><?= e($range['name']) ?></h2>
                </div>
                <a class="btn btn-line" href="<?= e(base_url('shop.php?category=' . urlencode($range['slug']))) ?>">Shop this range</a>
            </div>
            <?php if ($range['description']): ?><p><?= e($range['description']) ?></p><?php endif; ?>
            <?php if ($rows): ?>
                <div class="product-grid">
                    <?php foreach ($rows as $row): ?>
                        <article class="product-card">
                            <a class="product-media" href="<?= e(base_url('customize.php?code=' . urlencode($row['product_code']))) ?>">
                                <?= picture_or_fallback(media_url('products', $row['image'] ?? null), $row['name']) ?>
                            </a>
                            <div class="product-body">
                                <p class="eyebrow"><?= e($row['category_name']) ?></p>
                                <h3><a href="<?= e(base_url('customize.php?code=' . urlencode($row['product_code']))) ?>"><?= e($row['name']) ?></a></h3>
                                <div class="product-meta">
                                    <span class="price"><?= e(money($row['price'])) ?></span>
                                    <a class="btn btn-forest btn-sm" href="<?= e(base_url('customize.php?code=' . urlencode($row['product_code']))) ?>">Customise now</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>This range is being filled.</p>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</div>
<script>
document.querySelectorAll('input[name="design"]').forEach(function (input) {
    input.addEventListener('change', function () {
        var photo = input.getAttribute('data-design-photo');
        var main = document.getElementById('gift-preview') || document.getElementById('gallery-main');
        if (photo && main) {
            main.src = photo;
        }
    });
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
