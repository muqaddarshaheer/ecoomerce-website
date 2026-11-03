<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Home';
$bodyClass = 'home-page';

$categories = active_categories();
$arrivals = db()->query(product_sql() . " WHERE p.status = 'active' AND c.status = 'active' ORDER BY p.created_at DESC, p.id DESC LIMIT 8")->fetchAll();
$sellCodes = ['0200005', '0100004', '0100007', '0300003', '0300004', '0400006', '0400007', '0200010'];
$movers = db()->query(product_sql() . " WHERE p.status = 'active' AND c.status = 'active' AND EXISTS (
    SELECT 1 FROM order_items oi JOIN orders o ON o.id = oi.order_id
    WHERE oi.product_id = p.id AND o.order_status <> 'cancelled'
) ORDER BY (
    SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN orders o ON o.id = oi.order_id
    WHERE oi.product_id = p.id AND o.order_status <> 'cancelled'
) DESC LIMIT 8")->fetchAll();
$freshIds = array_map('intval', array_column($arrivals, 'id'));
$freshSql = $freshIds ? ' AND p.id NOT IN (' . implode(',', $freshIds) . ')' : '';
$shelfSkip = " AND c.code_prefix IN ('01','02','03','04') AND p.product_code NOT BETWEEN '0100044' AND '0100049' AND p.product_code NOT BETWEEN '0100050' AND '0100059' AND p.product_code NOT BETWEEN '0200011' AND '0200100'";
$shelfCount = (int) db()->query("SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE p.status = 'active' AND c.status = 'active'" . $freshSql . $shelfSkip)->fetchColumn();
$shelfOffset = $shelfCount > 0 ? ((int) date('z') * 4) % $shelfCount : 0;
$todayShelf = $shelfCount > 0
    ? db()->query(product_sql() . ' WHERE p.status = \'active\' AND c.status = \'active\'' . $freshSql . $shelfSkip . ' ORDER BY p.product_code LIMIT 4 OFFSET ' . $shelfOffset)->fetchAll()
    : [];
if ($shelfCount > 4 && count($todayShelf) < 4) {
    $todayShelf = array_merge(
        $todayShelf,
        db()->query(product_sql() . ' WHERE p.status = \'active\' AND c.status = \'active\'' . $shelfSkip . ' ORDER BY p.product_code LIMIT ' . (4 - count($todayShelf)))->fetchAll()
    );
}
$selling = [];
$seenSell = [];
foreach ($movers as $mover) {
    $selling[] = $mover;
    $seenSell[$mover['product_code']] = true;
}
$sellNeed = array_values(array_filter($sellCodes, static function ($code) use ($seenSell) {
    return empty($seenSell[$code]);
}));
if ($sellNeed && count($selling) < 8) {
    $sellStmt = db()->prepare(product_sql() . ' WHERE p.status = \'active\' AND c.status = \'active\' AND p.product_code IN (' . implode(',', array_fill(0, count($sellNeed), '?')) . ')');
    $sellStmt->execute($sellNeed);
    $sellByCode = [];
    foreach ($sellStmt->fetchAll() as $sellRow) {
        $sellByCode[$sellRow['product_code']] = $sellRow;
    }
    foreach ($sellNeed as $sellCode) {
        if (isset($sellByCode[$sellCode]) && count($selling) < 8) {
            $selling[] = $sellByCode[$sellCode];
        }
    }
}
$dealMidnight = (new DateTime('tomorrow', new DateTimeZone(APP_TIMEZONE)))->getTimestamp();
$offerPlan = [
    ['02', 'Deal', 'Stationery'],
    ['01', 'Offer', 'Gifts'],
    ['03', 'Sale', 'Bags'],
    ['04', 'Deal', 'Beauty'],
];
$offerBoxes = [];
$offerDay = (int) date('z');
foreach ($offerPlan as $offerSlot) {
    $offerCategory = null;
    foreach ($categories as $offerCat) {
        if (($offerCat['code_prefix'] ?? '') === $offerSlot[0]) {
            $offerCategory = $offerCat;
            break;
        }
    }
    if (!$offerCategory) {
        continue;
    }
    $offerStmt = db()->prepare(product_sql() . " WHERE p.status = 'active' AND c.status = 'active' AND p.category_id = ? ORDER BY p.product_code");
    $offerStmt->execute([(int) $offerCategory['id']]);
    $offerRows = $offerStmt->fetchAll();
    if (!$offerRows) {
        continue;
    }
    $offerBoxes[] = [
        'badge' => $offerSlot[1],
        'aisle' => $offerSlot[2],
        'category' => $offerCategory,
        'product' => $offerRows[$offerDay % count($offerRows)],
    ];
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
            <p class="eyebrow line-in">WRAP • SURPRISE • SMILE</p>
            <h1 class="line-in">Things worth <span class="wrap-word">wrapping.</span></h1>
            <p class="lead line-in">Paperlane keeps greeting cards, gifts, handbags and everyday accessories in one shop. Browse freely. Create an account only when you are ready to order.</p>
            <div class="d-flex gap-2 flex-wrap line-in">
                <a class="btn btn-clay" href="<?= e(base_url('shop.php')) ?>">Shop now <span aria-hidden="true">→</span></a>
                <a class="btn btn-line" href="<?= e(base_url('brands.php')) ?>">See the brands</a>
            </div>
        </div>
        <div class="banner-stage">
            <img class="banner-main" src="<?= e(base_url('assets/images/hero-gift.jpg')) ?>" alt="Two people exchanging a gift wrapped with a sage ribbon" fetchpriority="high">
        </div>
    </section>
    <section class="trust-bar" aria-label="Shop promises">
        <div class="trust-row">
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.3-5.7M4 4v5h5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg> Returns within 7 days of delivery</span>
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7z" fill="none" stroke="currentColor" stroke-width="1.6"/></svg> Prices on the shelf are the checkout prices</span>
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="7" cy="17" r="1.4" fill="currentColor"/><circle cx="18" cy="17" r="1.4" fill="currentColor"/></svg> Free delivery over <?= e(money(FREE_SHIPPING_OVER)) ?></span>
            <span><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="6" width="18" height="12" rx="2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M3 10h18" fill="none" stroke="currentColor" stroke-width="1.6"/></svg> Cash on delivery in Karachi</span>
            <a class="trust-top" href="#main" aria-label="Back to top">↑</a>
        </div>
    </section>

    <div class="hello-pop" id="hello-pop" hidden>
        <button class="hello-shade" type="button" data-hello-close aria-label="Close welcome note"></button>
        <div class="hello-sheet" role="dialog" aria-labelledby="hello-title">
            <p class="eyebrow">The counter is open</p>
            <h2 id="hello-title">Come in and look around.</h2>
            <p>Cards, shawls, jewellery and the school shelf. Browse first. Sign in only when you are ready to order.</p>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-clay" href="<?= e(base_url('shop.php')) ?>" data-hello-close>Shop now</a>
                <button class="btn btn-line" type="button" data-hello-close>Keep browsing</button>
            </div>
        </div>
    </div>

    <?php
    $newInRow = db()->query(product_sql() . " WHERE p.status = 'active' AND c.status = 'active' ORDER BY p.created_at DESC, p.id DESC LIMIT 1")->fetch();
    $newInPhoto = media_url('products', $newInRow['image'] ?? '');
    if (!$newInPhoto) {
        $newInPhoto = base_url('assets/images/aisles/new-in.jpg');
    }
    $aisles = [
        ['New in', 'shop.php?sort=newest', $newInPhoto, (string) ($newInRow['name'] ?? 'New in')],
        ['Cards', 'shop.php?line=cards', base_url('assets/images/aisles/cards-circle.jpg'), 'Illustrated greeting card'],
        ['Stationery', 'shop.php?category=dolls-files-stationery', base_url('assets/images/aisles/stationery.jpg'), 'Pencils and notebooks'],
        ['Bags', 'shop.php?category=handbags-wallets', base_url('assets/images/aisles/bag.jpg'), 'Red handbag'],
        ['Fragrances', 'shop.php?category=beauty-accessories&kind=perfumes', media_url('products', 'perfume-no5.jpg'), 'Perfume bottle'],
        ['Earrings', 'shop.php?category=beauty-accessories&kind=earrings', media_url('products', 'earrings.jpg'), 'Blue stone earrings'],
        ['Necklaces', 'shop.php?category=beauty-accessories&kind=necklaces', media_url('products', 'neck-clover.jpg'), 'Clover necklace'],
        ['Makeup', 'shop.php?category=beauty-accessories&kind=makeup', base_url('assets/images/aisles/makeup.jpg'), 'Red lipstick'],
        ['Hampers', 'shop.php?line=hampers', base_url('assets/images/aisles/hampers-circle.jpg'), 'A gift hamper'],
        ['Wallets', 'shop.php?category=beauty-accessories&kind=wallets', base_url('assets/images/aisles/wallet.jpg'), 'Brown leather wallet'],
    ];
    $giftStmt = db()->prepare("SELECT name, price, stock FROM products WHERE product_code = '0100006' AND status = 'active'");
    $giftStmt->execute();
    $giftBox = $giftStmt->fetch() ?: null;
    $giftDraft = $_SESSION['gift_draft'] ?? ['phone' => '', 'address' => '', 'quantity' => 1, 'color' => 'Black', 'size' => 'Medium', 'shape' => 'Rectangle'];
    $giftUser = current_user();
    if ($giftUser && $giftUser['role'] === 'customer' && ($giftDraft['phone'] ?? '') === '' && !empty($giftUser['phone'])) {
        $giftDraft['phone'] = $giftUser['phone'];
    }
    $giftShip = $giftBox ? from_cents(shipping_cents(to_cents($giftBox['price']))) : 0;
    $openGift = isset($_GET['gift']);
    ?>
    <section class="aisle-board">
        <div class="section-head">
            <div>
                <p class="eyebrow">All in one place</p>
                <div class="brand-title-row">
                    <h2>Shop the aisles</h2>
                    <span class="brand-rule" aria-hidden="true"></span>
                </div>
            </div>
        </div>
    <nav class="round-row" aria-label="Shop aisles">
        <?php foreach ($aisles as $aisle): ?>
            <?php if (!$aisle[2]) { continue; } ?>
            <a class="round-link" href="<?= e(base_url($aisle[1])) ?>">
                <span class="round-photo"><img src="<?= e($aisle[2]) ?>" alt="<?= e($aisle[3]) ?>"></span>
                <?= e($aisle[0]) ?>
            </a>
        <?php endforeach; ?>
        <a class="round-link" href="<?= e(base_url('shop.php?q=Ribboned+Gift+Box')) ?>">
            <span class="round-photo"><img src="<?= e(base_url('assets/images/aisles/gift-box-circle.jpg')) ?>" alt="A pale pink gift box with its lid"></span>
            Gift box
        </a>
    </nav>
    </section>

    <div class="hello-pop" id="gift-pop" <?= $openGift ? '' : 'hidden' ?>>
        <button class="hello-shade" type="button" data-gift-close aria-label="Close gift box"></button>
        <div class="hello-sheet gift-sheet" role="dialog" aria-labelledby="gift-title">
            <img class="gift-shot" src="<?= e(base_url('assets/images/aisles/gift-box.jpg')) ?>" alt="Black and gold gift boxes">
            <p class="eyebrow">Cash on delivery</p>
            <h2 id="gift-title"><?= $giftBox ? e($giftBox['name']) : 'Gift box' ?></h2>
            <?php if ($giftBox): ?>
                <p id="gift-sum" data-unit="<?= e((string) $giftBox['price']) ?>" data-ship="<?= e((string) $giftShip) ?>" data-free="<?= e((string) FREE_SHIPPING_OVER) ?>">Each box is <?= e(money($giftBox['price'])) ?>. Delivery <?= $giftShip > 0 ? e(money($giftShip)) : 'is free on this order' ?> until the boxes pass <?= e(money(FREE_SHIPPING_OVER)) ?>. Cash on delivery in Karachi.</p>
            <?php else: ?>
                <p>This box is not on the shelf right now.</p>
            <?php endif; ?>
            <?php if ($giftBox && (int) $giftBox['stock'] > 0): ?>
                <form method="post" action="<?= e(base_url('gift-box.php')) ?>">
                    <?= csrf_field() ?>
                    <div class="gift-picks">
                        <label class="form-label" for="gift-qty">Quantity</label>
                        <input class="form-control" id="gift-qty" name="quantity" type="number" min="1" max="<?= (int) min(10, (int) $giftBox['stock']) ?>" value="<?= (int) ($giftDraft['quantity'] ?? 1) ?>" required>
                        <span class="form-label">Colour</span>
                        <div class="swatch-row">
                            <?php foreach (['Black' => '#1a1a1a', 'Gold' => '#c9a15b', 'Ivory' => '#f3ead7'] as $swatchName => $swatchHex): ?>
                                <label class="swatch"><input type="radio" name="box_color" value="<?= e($swatchName) ?>" <?= (($giftDraft['color'] ?? 'Black') === $swatchName) ? 'checked' : '' ?>><i style="background:<?= e($swatchHex) ?>"></i><?= e($swatchName) ?></label>
                            <?php endforeach; ?>
                        </div>
                        <label class="form-label" for="gift-size">Size</label>
                        <select class="form-select" id="gift-size" name="box_size">
                            <?php foreach (['Small', 'Medium', 'Large'] as $giftSize): ?>
                                <option value="<?= e($giftSize) ?>" <?= (($giftDraft['size'] ?? 'Medium') === $giftSize) ? 'selected' : '' ?>><?= e($giftSize) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-label">Shape</span>
                        <div class="shape-row">
                            <?php foreach (['Rectangle' => 'rect', 'Square' => 'square', 'Round' => 'round'] as $shapeName => $shapeClass): ?>
                                <label class="shape"><input type="radio" name="box_shape" value="<?= e($shapeName) ?>" <?= (($giftDraft['shape'] ?? 'Rectangle') === $shapeName) ? 'checked' : '' ?>><b class="shape-<?= e($shapeClass) ?>"></b><?= e($shapeName) ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <label class="form-label" for="gift-phone">Contact number</label>
                    <input class="form-control mb-3" id="gift-phone" name="phone" inputmode="tel" required value="<?= e((string) ($giftDraft['phone'] ?? '')) ?>">
                    <label class="form-label" for="gift-address">Address</label>
                    <textarea class="form-control mb-3" id="gift-address" name="address_line" rows="3" required><?= e((string) ($giftDraft['address'] ?? '')) ?></textarea>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-clay" type="submit">Send this box</button>
                        <button class="btn btn-line" type="button" data-gift-close>Not now</button>
                    </div>
                </form>
            <?php else: ?>
                <button class="btn btn-line" type="button" data-gift-close>Close</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($todayShelf): ?>
    <section class="time-deal" aria-labelledby="counter-title">
        <div class="time-head">
            <div class="time-intro">
                <p class="eyebrow">Until midnight, Karachi</p>
                <div class="brand-title-row">
                    <h2 id="counter-title">On the counter today</h2>
                    <span class="brand-rule" aria-hidden="true"></span>
                </div>
                <p class="time-note">These four pieces change after midnight. Each price is the real shelf price.</p>
            </div>
            <p class="deal-clock" data-deal-clock data-until="<?= (int) $dealMidnight ?>">--:--:--</p>
        </div>
        <div class="counter-run" data-occasion-run>
            <button class="counter-arrow prev" type="button" data-occasion-prev aria-label="Previous pieces">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="counter-viewport">
                <div class="occasion-track counter-track">
                    <?php foreach ([false, true] as $clone): ?>
                        <?php foreach ($todayShelf as $todayItem): ?>
                            <div class="counter-slide"<?= $clone ? ' data-occasion-clone inert aria-hidden="true"' : '' ?>>
                                <?php product_card($todayItem); ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="counter-arrow next" type="button" data-occasion-next aria-label="Next pieces">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </section>
    <?php endif; ?>

    <div class="section-head">
        <div>
            <p class="eyebrow">Just landed</p>
            <div class="brand-title-row">
                <h2>New arrivals</h2>
                <span class="brand-rule" aria-hidden="true"></span>
            </div>
        </div>
        <a class="btn btn-line" href="<?= e(base_url('shop.php?sort=newest')) ?>">See the newest</a>
    </div>
    <?php if ($arrivals): ?>
        <div class="product-grid">
            <?php foreach ($arrivals as $arrival) { product_card($arrival); } ?>
        </div>
    <?php endif; ?>

    <div class="section-head rise">
        <div>
            <p class="eyebrow">Departments</p>
            <div class="brand-title-row">
                <h2>Shop by category</h2>
                <span class="brand-rule" aria-hidden="true"></span>
            </div>
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

    <?php require_once __DIR__ . '/includes/occasions.php'; render_occasion_carousel(); ?>

    <section class="brand-board">
    <div class="section-head">
        <div>
            <p class="eyebrow">Fifteen names</p>
            <div class="brand-title-row">
                <h2>Shop by brand</h2>
                <span class="brand-rule" aria-hidden="true"></span>
            </div>
        </div>
        <a class="btn btn-line" href="<?= e(base_url('brands.php')) ?>">All brands</a>
    </div>
    <?php require_once __DIR__ . '/includes/brands.php';
    $homeBrands = shop_brands();
    ?>
    <div class="brand-run" aria-label="Brand shelves">
        <div class="brand-run-track">
            <?php foreach ([false, true] as $brandCopy): ?>
                <?php foreach ($homeBrands as $homeBrand): ?>
                    <a class="shelf-card" href="<?= e(base_url('shop.php?brand=' . urlencode($homeBrand['slug']))) ?>"<?= $brandCopy ? ' aria-hidden="true" tabindex="-1"' : '' ?>>
                        <?= brand_icon($homeBrand['slug'], $homeBrand['name']) ?>
                        <span class="logo-name"><?= e($homeBrand['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
    </section>

    <?php if ($selling): ?>
    <div class="section-head">
        <div>
            <p class="eyebrow">Stationery, gifts, bags and beauty</p>
            <h2>Selling now</h2>
        </div>
        <a class="btn btn-line" href="<?= e(base_url('shop.php')) ?>">Full catalogue</a>
    </div>
    <div class="product-grid">
        <?php foreach ($selling as $mover) { product_card($mover); } ?>
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
