<?php

function shop_occasions(): array
{
    return [
        'birthday' => [
            'name' => 'Birthday',
            'title' => 'Birthday Gifts',
            'line' => 'A card, flowers and something ready to wrap.',
            'image' => 'occ-birthday.jpg',
            'codes' => ['0400035', '0100011', '0400010', '0400013', '0400029', '0400030', '0100012', '0100013'],
        ],
        'aqiqah' => [
            'name' => 'Aqiqah',
            'title' => 'Aqiqah Gifts',
            'line' => 'A full baby hamper, small clothes and an envelope for eidi.',
            'image' => 'occ-aqiqah.jpg',
            'codes' => ['0100014', '0100015', '0100016', '0100017', '0100018', '0100019'],
        ],
        'graduation' => [
            'name' => 'Graduation',
            'title' => 'Graduation Gifts',
            'line' => 'A congratulations card, flowers and a small wrapped gift.',
            'image' => 'occ-graduation.jpg',
            'codes' => ['0100020', '0100021', '0100022', '0400017', '0400018', '0100023'],
        ],
        'mothers-day' => [
            'name' => "Mother's Day",
            'title' => "Mother's Day Gifts",
            'line' => 'A card, roses, a mug and a hamper from the counter.',
            'image' => 'occ-mothers.jpg',
            'codes' => ['0100024', '0100025', '0100026', '0400061', '0400047', '0100027'],
        ],
        'fathers-day' => [
            'name' => "Father's Day",
            'title' => "Father's Day Gifts",
            'line' => 'A card, a tie, cologne, a wallet and a hamper.',
            'image' => 'occ-fathers.jpg',
            'codes' => ['0100028', '0400028', '0400027', '0400073', '0100029', '0100030'],
        ],
        'anniversary' => [
            'name' => 'Wedding Anniversary',
            'title' => 'Wedding Anniversary Gifts',
            'line' => 'A card, flowers and a hamper for the day.',
            'image' => 'occ-anniversary.jpg',
            'codes' => ['0100031', '0100032', '0100033', '0400014', '0400023', '0400019'],
        ],
        'independence-day' => [
            'name' => 'Independence Day',
            'title' => 'Independence Day Gifts',
            'line' => 'Green and white gifts for 14 August.',
            'image' => 'occ-independence.jpg',
            'codes' => ['0100034', '0100035', '0100036', '0100037', '0100038', '0100039', '0400058', '0400020', '0100040', '0100041'],
        ],
        'eid' => [
            'name' => 'Eid-ul-Fitr',
            'title' => 'Eid-ul-Fitr Gifts',
            'line' => 'Envelopes, clothes for men and women, and a greeting card.',
            'image' => 'occ-eid.jpg',
            'codes' => ['0100042', '0100043', '0100056', '0100057', '0100058', '0100059'],
        ],
    ];
}

function occasion_by_slug(string $slug): ?array
{
    $all = shop_occasions();
    if (!isset($all[$slug])) {
        return null;
    }
    $row = $all[$slug];
    $row['slug'] = $slug;
    return $row;
}

function occasion_icon(string $slug): string
{
    $marks = [
        'birthday' => '<rect x="4" y="9" width="16" height="11" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M4 13h16M12 9v11" stroke="currentColor" stroke-width="1.6"/>',
        'aqiqah' => '<circle cx="12" cy="10" r="3" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M7 19c.6-2.4 2.4-3.6 5-3.6s4.4 1.2 5 3.6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        'graduation' => '<path d="M3 10l9-4 9 4-9 4z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M7 12v4c2 1.4 8 1.4 10 0v-4" fill="none" stroke="currentColor" stroke-width="1.6"/>',
        'mothers-day' => '<path d="M12 19s-6-3.4-6-8a3.2 3.2 0 0 1 6-1 3.2 3.2 0 0 1 6 1c0 4.6-6 8-6 8z" fill="none" stroke="currentColor" stroke-width="1.6"/>',
        'fathers-day' => '<path d="M8 5l8 4-8 4V5zM8 13v6M6 19h8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
        'anniversary' => '<circle cx="9" cy="13" r="3.2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="15" cy="11" r="3.2" fill="none" stroke="currentColor" stroke-width="1.6"/>',
        'independence-day' => '<path d="M12 3l1.6 4.6H18l-3.8 2.8 1.5 4.6L12 12.2 8.3 15l1.5-4.6L6 7.6h4.4z" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>',
        'eid' => '<path d="M16 5a7 7 0 1 0 0 14 6 6 0 0 1 0-14z" fill="none" stroke="currentColor" stroke-width="1.6"/>',
    ];
    $inner = $marks[$slug] ?? $marks['birthday'];
    return '<svg viewBox="0 0 24 24" aria-hidden="true">' . $inner . '</svg>';
}

function occasion_products(array $codes): array
{
    $codes = array_values(array_filter($codes, static function ($code): bool {
        return (bool) preg_match('/^\d{7}$/', (string) $code);
    }));
    if (!$codes) {
        return [];
    }
    $marks = implode(',', array_fill(0, count($codes), '?'));
    $stmt = db()->prepare(product_sql() . " WHERE p.status = 'active' AND c.status = 'active' AND p.product_code IN ($marks)");
    $stmt->execute($codes);
    $byCode = [];
    foreach ($stmt->fetchAll() as $row) {
        $byCode[$row['product_code']] = $row;
    }
    $ordered = [];
    foreach ($codes as $code) {
        if (isset($byCode[$code])) {
            $ordered[] = $byCode[$code];
        }
    }
    return $ordered;
}

function occasion_buy_card(array $product, string $slug): void
{
    $url = base_url('product.php?code=' . urlencode($product['product_code']));
    $image = media_url('products', $product['image'] ?? null);
    $stock = (int) $product['stock'];
    ?>
    <article class="product-card">
        <a class="product-media" href="<?= e($url) ?>">
            <?= picture_or_fallback($image, $product['name']) ?>
        </a>
        <div class="product-body">
            <p class="eyebrow"><?= e($product['category_name']) ?></p>
            <h3><a href="<?= e($url) ?>"><?= e($product['name']) ?></a></h3>
            <div class="product-meta">
                <span class="price"><?= e(money($product['price'])) ?></span>
                <?php if ($stock > 0): ?>
                    <form method="post" action="<?= e(base_url('cart.php')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_code" value="<?= e($product['product_code']) ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="back" value="<?= e('occasion.php?slug=' . $slug) ?>">
                        <button class="btn btn-clay btn-sm occasion-buy" type="submit">Add to cart</button>
                    </form>
                <?php else: ?>
                    <button class="btn btn-sm occasion-buy" type="button" disabled>Out of stock</button>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
}

function render_occasion_carousel(): void
{
    $occasions = shop_occasions();
    ?>
    <section class="occasion-board" aria-labelledby="occasion-heading">
        <div class="section-head">
            <div>
                <h2 id="occasion-heading">Gifts for Every Occasion</h2>
                <p class="occasion-line">Make every special moment a little more memorable.</p>
            </div>
        </div>
        <div class="occasion-run" data-occasion-run>
            <button class="occasion-arrow prev" type="button" data-occasion-prev aria-label="Previous occasions">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="occasion-viewport">
                <div class="occasion-track">
                    <?php foreach ([false, true] as $copy): ?>
                        <?php foreach ($occasions as $slug => $occasion): ?>
                            <?php $shot = base_url('assets/images/occasions/' . rawurlencode($occasion['image'])); ?>
                            <a class="occasion-card" href="<?= e(base_url('occasion.php?slug=' . urlencode($slug))) ?>"<?= $copy ? ' data-occasion-clone tabindex="-1" aria-hidden="true"' : '' ?>>
                                <span class="occasion-photo">
                                    <img src="<?= e($shot) ?>" alt="<?= e($occasion['name']) ?>"<?= $copy ? '' : ' loading="lazy"' ?>>
                                </span>
                                <span class="occasion-label">
                                    <?= occasion_icon($slug) ?>
                                    <strong><?= e($occasion['name']) ?></strong>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="occasion-arrow next" type="button" data-occasion-next aria-label="Next occasions">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </section>
    <?php
}
