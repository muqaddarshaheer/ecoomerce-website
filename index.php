<?php
require_once __DIR__ . '/includes/iniit.php';
$pageTitle = 'Home';

$categories = active_categories();
$featureCodes = ['0100004', '0100007', '0200005', '0200009', '0300003', '0300004', '0400006', '0400007'];
$featureStmt = db()->prepare(product_sql() . ' WHERE p.status = \'active\' AND c.status = \'active\' AND p.product_code IN (' . implode(',', array_fill(0, count($featureCodes), '?')) . ')');
$featureStmt->execute($featureCodes);
$featureRows = [];
foreach ($featureStmt->fetchAll() as $featureRow) {
    $featureRows[$featureRow['product_code']] = $featureRow;
}
$featured = [];
foreach ($featureCodes as $featureCode) {
    if (isset($featureRows[$featureCode])) {
        $featured[] = $featureRows[$featureCode];
    }
}
if (!$featured) {
    $featured = db()->query(product_sql() . " WHERE p.status = 'active' AND c.status = 'active' AND p.featured = 1 ORDER BY p.id DESC LIMIT 8")->fetchAll();
}
$giftSlug = '';
foreach ($categories as $giftCategory) {
    if (($giftCategory['code_prefix'] ?? '') === '01') {
        $giftSlug = $giftCategory['slug'];
    }
}
$hero = hero_url();
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <section class="banner">
        <div class="banner-copy">
            <p class="eyebrow">Karachi · cards, shawls, jewellery and the school shelf</p>
            <h1>Things worth wrapping.</h1>
            <p class="lead">Paperlane keeps greeting cards, files, handbags and everyday accessories in one shop. Browse freely. Create an account only when you are ready to order.</p>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-clay" href="<?= e(base_url('shop.php')) ?>">Shop now</a>
                <a class="btn btn-outline-dark" href="<?= e(base_url('brands.php')) ?>">See the brands</a>
            </div>
        </div>
        <div class="banner-stage">
            <img class="banner-main" src="<?= e(base_url('assets/images/look/lahore-green.jpg')) ?>" alt="A woman in Lahore wearing a mint outfit, a white dupatta and gold earrings" fetchpriority="high">
        </div>
    </section>

    <div class="section-head rise">
        <div>
            <p class="eyebrow">Departments</p>
            <h2>Shop by category</h2>
        </div>
    </div>
    <div class="category-grid">
        <?php foreach ($categories as $category): ?>
            <a class="category-tile rise" href="<?= e(base_url('shop.php?category=' . urlencode($category['slug']))) ?>">
                <?= picture_or_fallback(media_url('categories', $category['image']), $category['name']) ?>
                <span><?= e($category['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="section-head rise">
        <div>
            <p class="eyebrow">Worn in the lookbook</p>
            <h2>Shawls, earrings and gold</h2>
        </div>
        <a href="<?= e(base_url('shop.php?category=beauty-accessories')) ?>">Beauty &amp; accessories</a>
    </div>
    <div class="look-row">
        <a class="look-card rise" href="<?= e(base_url('shop.php?category=beauty-accessories&kind=shawls')) ?>">
            <img src="<?= e(base_url('assets/images/look/lahore-scarf.jpg')) ?>" alt="A woman in Lahore in an embroidered suit and dupatta" loading="lazy">
            <span>Shawls and dupattas</span>
        </a>
        <a class="look-card rise" href="<?= e(base_url('shop.php?category=beauty-accessories&kind=necklaces')) ?>">
            <img src="<?= e(base_url('assets/images/look/gold-jewelry.jpg')) ?>" alt="A woman wearing layered gold necklaces and earrings" loading="lazy">
            <span>Necklaces</span>
        </a>
        <a class="look-card rise" href="<?= e(base_url('shop.php?category=beauty-accessories&kind=jhumkas')) ?>">
            <img src="<?= e(base_url('assets/images/look/lahore-purple.jpg')) ?>" alt="A woman in Lahore holding a patterned dupatta, wearing hoops and pearls" loading="lazy">
            <span>Earrings and jhumkas</span>
        </a>
    </div>

    <div class="section-head rise">
        <div>
            <p class="eyebrow">Fifteen names</p>
            <h2>Shop by brand</h2>
        </div>
        <a href="<?= e(base_url('brands.php')) ?>">All brands</a>
    </div>
    <?php require_once __DIR__ . '/includes/brands.php'; ?>
    <div class="marquee" aria-label="Brand shelves">
        <div class="marquee-track">
            <?php foreach (shop_brands() as $homeBrand): ?>
                <a class="brand-chip" href="<?= e(base_url('shop.php?brand=' . urlencode($homeBrand['slug']))) ?>">
                    <?= brand_mark($homeBrand['slug'], $homeBrand['name']) ?>
                    <?= e($homeBrand['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="section-head rise">
        <div>
            <p class="eyebrow">On the table</p>
            <h2>Featured products</h2>
        </div>
        <a href="<?= e(base_url('shop.php')) ?>">See everything</a>
    </div>
    <?php if (!$featured): ?>
        <div class="empty-state">
            <h3>The shelves are ready</h3>
            <p>Products added by the administrator will appear here with their photos, prices and stock.</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($featured as $product) { product_card($product); } ?>
        </div>
    <?php endif; ?>

    <section class="gift-band rise">
        <div>
            <p class="eyebrow">Gifting</p>
            <h2>A small something, ready to wrap.</h2>
            <p>Greeting cards and gifts sit with the rest of the catalogue. The price on the product is the price at checkout.</p>
            <div class="d-flex gap-2 flex-wrap">
                <?php if ($giftSlug !== ''): ?>
                    <a class="btn btn-clay" href="<?= e(base_url('shop.php?category=' . urlencode($giftSlug))) ?>">Shop greeting cards</a>
                <?php endif; ?>
                <a class="btn btn-login" href="<?= e(base_url('shop.php')) ?>">Full catalogue</a>
            </div>
        </div>
        <img src="<?= e(base_url('assets/images/categories/cards.jpg')) ?>" alt="Greeting cards and gifts">
    </section>

    <div class="promise-grid">
        <article class="promise">
            <strong>Three ways to pay</strong>
            <p class="mb-0">Credit card (simulated), cheque, or cash on delivery. Card and cheque orders wait until payment is cleared.</p>
        </article>
        <article class="promise">
            <strong>Cancel before dispatch</strong>
            <p class="mb-0">You can cancel from your order page until the parcel is marked dispatched. Stock goes back on the shelf.</p>
        </article>
        <article class="promise">
            <strong>Seven days after delivery</strong>
            <p class="mb-0">Ask for a return or a replacement within seven days. A refund is issued only after an approved return.</p>
        </article>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
