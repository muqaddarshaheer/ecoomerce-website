<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/brands.php';
$pageTitle = 'About';
$bodyClass = 'about-page';

$departments = [];
$loved = [];
$lovedTitle = 'What customers have ordered';
$lovedNote = 'These pieces come from real orders that were not cancelled.';
$stats = ['pieces' => 0, 'departments' => 0, 'brands' => count(shop_brands()), 'orders' => 0];
try {
    $departments = db()->query("SELECT c.name, c.slug, c.image, COUNT(p.id) AS pieces
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active'
        WHERE c.status = 'active'
        GROUP BY c.id, c.name, c.slug, c.image
        ORDER BY c.name")->fetchAll();
    $stats['pieces'] = (int) db()->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    $stats['departments'] = count($departments);
    $stats['orders'] = (int) db()->query("SELECT COUNT(*) FROM orders WHERE order_status <> 'cancelled'")->fetchColumn();
    $loved = db()->query("SELECT p.product_code, p.name, p.price, SUM(oi.quantity) AS qty,
            (SELECT pi.file_name FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id LIMIT 1) AS image
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id AND o.order_status <> 'cancelled'
        JOIN products p ON p.product_code = oi.product_code AND p.status = 'active'
        GROUP BY p.id, p.product_code, p.name, p.price
        ORDER BY qty DESC, p.name
        LIMIT 4")->fetchAll();
    if (!$loved) {
        $lovedTitle = 'New on the shelf';
        $lovedNote = 'Orders will replace this row once customers buy. These are the newest active pieces.';
        $loved = db()->query("SELECT p.product_code, p.name, p.price, 0 AS qty,
                (SELECT pi.file_name FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id LIMIT 1) AS image
            FROM products p
            WHERE p.status = 'active'
            ORDER BY p.id DESC
            LIMIT 4")->fetchAll();
    }
} catch (Throwable $e) {
    $departments = [];
    $loved = [];
}
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4 about-wrap">
    <section class="about-hero">
        <div>
            <p class="eyebrow">About Paperlane</p>
            <h1>A Karachi counter for cards, gifts and the things people carry home.</h1>
            <p class="lead">Paperlane sells greeting cards, gifts, stationery, handbags and everyday accessories from one catalogue. Look freely. Sign in only when you are ready to order. The price on the shelf is the price at checkout.</p>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-clay" href="<?= e(base_url('shop.php')) ?>">Start shopping</a>
                <a class="btn btn-line" href="<?= e(base_url('cart.php')) ?>">Open the cart</a>
            </div>
        </div>
        <figure class="about-photo">
            <img src="<?= e(base_url('assets/images/hero-gift.jpg')) ?>" alt="Hands exchanging a wrapped gift">
            <figcaption>18 Lakeview Road, Karachi</figcaption>
        </figure>
    </section>

    <section class="about-stats" aria-label="Shop at a glance">
        <article class="about-stat">
            <strong data-count-to="<?= (int) $stats['pieces'] ?>">0</strong>
            <span>Pieces on the shelf</span>
        </article>
        <article class="about-stat">
            <strong data-count-to="<?= (int) $stats['departments'] ?>">0</strong>
            <span>Departments</span>
        </article>
        <article class="about-stat">
            <strong data-count-to="<?= (int) $stats['brands'] ?>">0</strong>
            <span>Brand shelves</span>
        </article>
        <article class="about-stat">
            <strong data-count-to="<?= (int) $stats['orders'] ?>">0</strong>
            <span>Orders placed</span>
        </article>
    </section>

    <?php if ($departments): ?>
    <section class="about-block">
        <div class="about-head">
            <p class="eyebrow">The shop</p>
            <h2>What we keep</h2>
            <span class="brand-rule" aria-hidden="true"></span>
        </div>
        <div class="about-depts">
            <?php foreach ($departments as $department): ?>
                <a class="about-dept" href="<?= e(base_url('shop.php?category=' . urlencode($department['slug']))) ?>">
                    <?= picture_or_fallback(media_url('categories', $department['image'] ?? null), $department['name']) ?>
                    <span>
                        <strong><?= e($department['name']) ?></strong>
                        <small><?= (int) $department['pieces'] ?> piece<?= (int) $department['pieces'] === 1 ? '' : 's' ?></small>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <section class="about-block">
        <div class="about-head">
            <p class="eyebrow">Moving now</p>
            <h2><?= e($lovedTitle) ?></h2>
            <span class="brand-rule" aria-hidden="true"></span>
        </div>
        <p class="about-note"><?= e($lovedNote) ?></p>
        <?php if ($loved): ?>
            <div class="about-picks">
                <?php foreach ($loved as $piece): ?>
                    <?php $shot = media_url('products', $piece['image'] ?? null); ?>
                    <a class="about-pick" href="<?= e(base_url('product.php?code=' . urlencode($piece['product_code']))) ?>">
                        <?= picture_or_fallback($shot, $piece['name']) ?>
                        <span>
                            <strong><?= e($piece['name']) ?></strong>
                            <em><?= e(money($piece['price'])) ?></em>
                            <?php if ((int) $piece['qty'] > 0): ?>
                                <small>Ordered <?= (int) $piece['qty'] ?> time<?= (int) $piece['qty'] === 1 ? '' : 's' ?></small>
                            <?php endif; ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="about-steps">
        <div class="about-head">
            <p class="eyebrow">How to order</p>
            <h2>Five steps, then the parcel is yours.</h2>
            <span class="brand-rule" aria-hidden="true"></span>
        </div>
        <ol>
            <li><strong>Browse</strong><span>Open Shop, a department, or a brand. No account is needed to look.</span></li>
            <li><strong>Add to cart</strong><span>The cart keeps the shelf price. Delivery is <?= e(money(SHIPPING_FEE)) ?>, free from <?= e(money(FREE_SHIPPING_OVER)) ?>.</span></li>
            <li><strong>Sign in</strong><span>Create an account or log in. You only see your own orders.</span></li>
            <li><strong>Pay</strong><span>Credit card (simulated), cheque, or cash on delivery in Karachi.</span></li>
            <li><strong>Follow it</strong><span>Cancel before dispatch. After delivery, ask for a return or replacement within <?= (int) ORDER_RETURN_DAYS ?> days.</span></li>
        </ol>
    </section>

    <section class="about-visit">
        <div>
            <p class="eyebrow">Visit and write</p>
            <h2>18 Lakeview Road, Karachi</h2>
            <p>Card and cheque parcels wait until payment is cleared. Employees pack confirmed orders. They do not change prices. A note about the shop goes to Feedback, and an administrator reads it.</p>
        </div>
        <button class="btn btn-clay" type="button" data-open-feedback>Leave feedback</button>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
