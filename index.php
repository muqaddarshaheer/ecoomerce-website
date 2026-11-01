<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Home';

$categories = active_categories();
$arrivals = db()->query(product_sql() . " WHERE p.status = 'active' AND c.status = 'active' ORDER BY p.created_at DESC, p.id DESC LIMIT 8")->fetchAll();
$sellCodes = ['0200005', '0100004', '0100007', '0300003', '0300004', '0400006', '0400007', '0200009'];
$movers = db()->query(product_sql() . " WHERE p.status = 'active' AND c.status = 'active' AND EXISTS (
    SELECT 1 FROM order_items oi JOIN orders o ON o.id = oi.order_id
    WHERE oi.product_id = p.id AND o.order_status <> 'cancelled'
) ORDER BY (
    SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN orders o ON o.id = oi.order_id
    WHERE oi.product_id = p.id AND o.order_status <> 'cancelled'
) DESC LIMIT 8")->fetchAll();
$freshIds = array_map('intval', array_column($arrivals, 'id'));
$freshSql = $freshIds ? ' AND p.id NOT IN (' . implode(',', $freshIds) . ')' : '';
$shelfCount = (int) db()->query("SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE p.status = 'active' AND c.status = 'active'" . $freshSql)->fetchColumn();
$shelfOffset = $shelfCount > 0 ? ((int) date('z') * 4) % $shelfCount : 0;
$todayShelf = $shelfCount > 0
    ? db()->query(product_sql() . ' WHERE p.status = \'active\' AND c.status = \'active\'' . $freshSql . ' ORDER BY p.product_code LIMIT 4 OFFSET ' . $shelfOffset)->fetchAll()
    : [];
if ($shelfCount > 4 && count($todayShelf) < 4) {
    $todayShelf = array_merge(
        $todayShelf,
        db()->query(product_sql() . ' WHERE p.status = \'active\' AND c.status = \'active\' ORDER BY p.product_code LIMIT ' . (4 - count($todayShelf)))->fetchAll()
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
            <p class="eyebrow line-in">Karachi · cards, shawls, jewellery and the school shelf</p>
            <h1 class="line-in">Things worth wrapping.</h1>
            <p class="lead line-in">Paperlane keeps greeting cards, files, handbags and everyday accessories in one shop. Browse freely. Create an account only when you are ready to order.</p>
            <div class="d-flex gap-2 flex-wrap line-in">
                <a class="btn btn-clay" href="<?= e(base_url('shop.php')) ?>">Shop now</a>
                <a class="btn btn-line" href="<?= e(base_url('brands.php')) ?>">See the brands</a>
            </div>
        </div>
        <div class="banner-stage">
            <img class="banner-main" src="<?= e(base_url('assets/images/look/lahore-green.jpg')) ?>" alt="A woman in Lahore wearing a mint outfit, a white dupatta and gold earrings" fetchpriority="high">
        </div>
    </section>

    <?php if ($offerBoxes): ?>
    <div class="offer-row">
        <?php foreach ($offerBoxes as $offerBox): ?>
            <?php
            $offerProduct = $offerBox['product'];
            $offerImg = media_url('products', $offerProduct['image'] ?? null);
            $offerNote = $offerBox['badge'] === 'Offer'
                ? 'Free delivery over ' . money(FREE_SHIPPING_OVER)
                : 'Shelf price until midnight';
            ?>
            <a class="offer-box" href="<?= e(base_url('product.php?code=' . urlencode($offerProduct['product_code']))) ?>">
                <span class="offer-photo">
                    <?php if ($offerImg): ?><img src="<?= e($offerImg) ?>" alt="<?= e($offerProduct['name']) ?>"><?php endif; ?>
                </span>
                <span class="offer-copy">
                    <span class="offer-badge offer-<?= e(strtolower($offerBox['badge'])) ?>"><?= e($offerBox['badge']) ?></span>
                    <span class="offer-aisle"><?= e($offerBox['aisle']) ?></span>
                    <strong><?= e($offerProduct['name']) ?></strong>
                    <span class="price"><?= e(money($offerProduct['price'])) ?></span>
                    <span class="offer-clock" data-deal-clock data-until="<?= (int) $dealMidnight ?>">--:--:--</span>
                    <small><?= e($offerNote) ?></small>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

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
    $aisles = [
        ['New in', 'shop.php?sort=newest', base_url('assets/images/aisles/gift-box.jpg'), 'Black and gold gift boxes'],
        ['Cards', 'shop.php?category=greeting-cards-gifts', base_url('assets/images/aisles/cards.jpg'), 'Greeting cards'],
        ['Stationery', 'shop.php?category=dolls-files-stationery', base_url('assets/images/aisles/stationery.jpg'), 'Pencils and notebooks'],
        ['Bags', 'shop.php?category=handbags-wallets', base_url('assets/images/aisles/bag.jpg'), 'Red handbag'],
        ['Shawls', 'shop.php?category=beauty-accessories&kind=shawls', base_url('assets/images/aisles/shawl.jpg'), 'Shawl fabric'],
        ['Fragrances', 'shop.php?category=beauty-accessories&kind=perfumes', media_url('products', 'perfume-no5.jpg'), 'Perfume bottle'],
        ['Earrings', 'shop.php?category=beauty-accessories&kind=earrings', media_url('products', 'earrings.jpg'), 'Blue stone earrings'],
        ['Necklaces', 'shop.php?category=beauty-accessories&kind=necklaces', media_url('products', 'neck-clover.jpg'), 'Clover necklace'],
        ['Makeup', 'shop.php?category=beauty-accessories&kind=makeup', base_url('assets/images/aisles/makeup.jpg'), 'Red lipstick'],
        ['Jhumkas', 'shop.php?category=beauty-accessories&kind=jhumkas', base_url('assets/images/aisles/jhumka.jpg'), 'Silver jhumka'],
        ['Baskets', 'shop.php?category=beauty-accessories&kind=baskets', base_url('assets/images/aisles/basket.jpg'), 'Candy gift baskets'],
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
                <h2>Shop the aisles</h2>
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
        <button class="round-link" type="button" data-gift-open>
            <span class="round-photo"><img src="<?= e(base_url('assets/images/aisles/gift-box.jpg')) ?>" alt="Black and gold gift boxes"></span>
            Gift box
        </button>
    </nav>
    </section>

    <div class="hello-pop" id="gift-pop" <?= $openGift ? '' : 'hidden' ?>>
        <button class="hello-shade" type="button" data-gift-close aria-label="Close gift box"></button>
        <div class="hello-sheet gift-sheet" role="dialog" aria-labelledby="gift-title">
            <img class="gift-shot" src="<?= e(base_url('assets/images/aisles/gift-box.jpg')) ?>" alt="Black and gold gift boxes">
            <p class="eyebrow">Cash on delivery</p>
            <h2 id="gift-title"><?= $giftBox ? e($giftBox['name']) : 'Gift box' ?></h2>
            <?php if ($giftBox): ?>
                <p id="gift-sum" data-unit="<?= e((string) $giftBox['price']) ?>" data-ship="<?= e((string) $giftShip) ?>" data-free="1500">Each box is <?= e(money($giftBox['price'])) ?>. Delivery <?= $giftShip > 0 ? e(money($giftShip)) : 'is free on this order' ?> until the boxes pass Rs 1,500.00. Cash on delivery in Karachi.</p>
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
    <section class="time-deal">
        <div class="time-copy">
            <p class="eyebrow">Until midnight, Karachi</p>
            <h2>On the counter today</h2>
            <p>These four pieces change after midnight. Each price is the real shelf price.</p>
            <p class="deal-clock" data-deal-clock data-until="<?= (int) $dealMidnight ?>">--:--:--</p>
        </div>
        <div class="product-grid deal-grid">
            <?php foreach ($todayShelf as $todayItem) { product_card($todayItem); } ?>
        </div>
    </section>
    <?php endif; ?>

    <div class="section-head">
        <div>
            <p class="eyebrow">Just landed</p>
            <h2>New arrivals</h2>
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
        <a class="btn btn-line" href="<?= e(base_url('shop.php?category=beauty-accessories')) ?>">Beauty &amp; accessories</a>
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

    <section class="brand-board">
    <div class="section-head">
        <div>
            <p class="eyebrow">Fifteen names</p>
            <h2>Shop by brand</h2>
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
                        <?= brand_wordmark($homeBrand['slug'], $homeBrand['name']) ?>
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
