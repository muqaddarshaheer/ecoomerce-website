<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/beauty_kinds.php';
require_once __DIR__ . '/includes/brands.php';
require_once __DIR__ . '/includes/shelves.php';

$q = query_text('q', 80);
$category = query_text('category', 120);
$minRaw = query_text('min', 12);
$maxRaw = query_text('max', 12);
$sort = query_text('sort', 20);
$allowedSorts = ['newest', 'price_asc', 'price_desc', 'name'];
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'newest';
}
$stockOnly = ($_GET['stock'] ?? '') === '1';
$page = max(1, (int) ($_GET['page'] ?? 1));
$beautySlug = 'beauty-accessories';
$beautyKinds = beauty_kinds();
$kind = query_text('kind', 40);
if (!isset($beautyKinds[$kind])) {
    $kind = '';
}
$brandSlug = query_text('brand', 40);
$brandRow = brand_by_slug($brandSlug);
if (!$brandRow) {
    $brandSlug = '';
}
$lineKey = query_text('line', 20);
$lineRow = shop_line($lineKey);
if (!$lineRow) {
    $lineKey = '';
}
$perPage = ($category === $beautySlug || $brandSlug !== '' || $lineRow) ? 24 : 12;

$where = ["p.status = 'active'", "c.status = 'active'"];
$params = [];
if ($q !== '') {
    $where[] = "(p.name LIKE ? ESCAPE '\\\\' OR p.description LIKE ? ESCAPE '\\\\' OR p.product_code LIKE ? ESCAPE '\\\\')";
    $like = like_term($q);
    array_push($params, $like, $like, $like);
}
if ($category !== '') {
    $where[] = 'c.slug = ?';
    $params[] = $category;
}
if ($minRaw !== '' && is_numeric($minRaw)) {
    $where[] = 'p.price >= ?';
    $params[] = round((float) $minRaw, 2);
}
if ($maxRaw !== '' && is_numeric($maxRaw)) {
    $where[] = 'p.price <= ?';
    $params[] = round((float) $maxRaw, 2);
}
if ($stockOnly) {
    $where[] = 'p.stock > 0';
}
if ($category === $beautySlug && $kind !== '') {
    $kindCodes = $beautyKinds[$kind]['codes'];
    $where[] = 'p.product_code IN (' . implode(',', array_fill(0, count($kindCodes), '?')) . ')';
    foreach ($kindCodes as $kindCode) {
        $params[] = $kindCode;
    }
}
if ($brandRow) {
    $where[] = 'p.product_code IN (' . implode(',', array_fill(0, count($brandRow['codes']), '?')) . ')';
    foreach ($brandRow['codes'] as $brandCode) {
        $params[] = $brandCode;
    }
}
if ($lineRow) {
    $where[] = 'p.product_code IN (' . implode(',', array_fill(0, count($lineRow['codes']), '?')) . ')';
    foreach ($lineRow['codes'] as $lineCode) {
        $params[] = $lineCode;
    }
}

$orderSql = [
    'price_asc' => 'p.price ASC, p.id DESC',
    'price_desc' => 'p.price DESC, p.id DESC',
    'name' => 'p.name ASC',
    'newest' => 'p.created_at DESC, p.id DESC',
][$sort];
$orderParams = [];
if ($category === $beautySlug && $sort === 'newest') {
    $beautyOrder = [];
    foreach ($beautyKinds as $beautyGroup) {
        foreach ($beautyGroup['codes'] as $beautyCode) {
            $beautyOrder[] = $beautyCode;
        }
    }
    if ($beautyOrder) {
        $orderSql = 'FIELD(p.product_code, ' . implode(',', array_fill(0, count($beautyOrder), '?')) . '), p.id DESC';
        $orderParams = $beautyOrder;
    }
}
if ($brandRow && $sort === 'newest') {
    $orderSql = 'FIELD(p.product_code, ' . implode(',', array_fill(0, count($brandRow['codes']), '?')) . ')';
    $orderParams = $brandRow['codes'];
}
if ($lineRow && $sort === 'newest') {
    $orderSql = 'FIELD(p.product_code, ' . implode(',', array_fill(0, count($lineRow['codes']), '?')) . ')';
    $orderParams = $lineRow['codes'];
}

$whereSql = implode(' AND ', $where);
$countStmt = db()->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON c.id = p.category_id WHERE $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
if ($page > $pages) {
    $page = $pages;
}
$offset = ($page - 1) * $perPage;

$list = db()->prepare(product_sql() . " WHERE $whereSql ORDER BY $orderSql LIMIT ? OFFSET ?");
$bindIndex = 1;
foreach (array_merge($params, $orderParams) as $value) {
    $list->bindValue($bindIndex++, $value);
}
$list->bindValue($bindIndex++, $perPage, PDO::PARAM_INT);
$list->bindValue($bindIndex, $offset, PDO::PARAM_INT);
$list->execute();
$products = $list->fetchAll();

$pageTitle = 'Shop';
$categories = active_categories();
$query = [
    'q' => $q,
    'category' => $category,
    'min' => $minRaw,
    'max' => $maxRaw,
    'sort' => $sort,
    'stock' => $stockOnly ? '1' : '',
    'kind' => $kind,
    'brand' => $brandSlug,
    'line' => $lineKey,
];
$shopHeading = 'Shop';
if ($lineRow) {
    $shopHeading = $lineRow['name'];
} elseif ($brandRow) {
    $shopHeading = $brandRow['name'];
} elseif ($category !== '') {
    foreach (active_categories() as $catRow) {
        if ($catRow['slug'] === $category) {
            $shopHeading = $catRow['name'];
            break;
        }
    }
}
if ($category === $beautySlug) {
    $bodyClass = trim(($bodyClass ?? '') . ' beauty-page');
}
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <div class="section-head">
        <div>
            <p class="eyebrow"><?php if ($lineRow): ?><?= e($lineRow['line']) ?> · <?php elseif ($brandRow): ?><?= e($brandRow['line']) ?> · <?php endif; ?><?= $total ?> product<?= $total === 1 ? '' : 's' ?></p>
            <h2><?= e($shopHeading) ?></h2>
            <?php if ($brandRow): ?><p class="brand-shelf"><?= e($brandRow['about']) ?></p><?php endif; ?>
        </div>
    </div>
    <div class="shop-layout">
        <div>
            <?php if ($category === $beautySlug): ?>
                <div class="kind-row">
                    <a class="kind-btn<?= $kind === '' ? ' is-on' : '' ?>" href="<?= e(base_url('shop.php?category=' . urlencode($beautySlug))) ?>">All</a>
                    <?php foreach ($beautyKinds as $kindKey => $kindRow): ?>
                        <a class="kind-btn<?= $kind === $kindKey ? ' is-on' : '' ?>" href="<?= e(base_url('shop.php?category=' . urlencode($beautySlug) . '&kind=' . urlencode($kindKey))) ?>"><?= e($kindRow['label']) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php if (in_array($kind, ['bracelets', 'watches', 'rings', 'perfumes', 'shawls', 'wallets'], true) || $kind === ''): ?>
                    <p class="pair-note">Where a piece is made for both, you will see two cards. The label on the photo says Men or Women.</p>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!$products): ?>
                <div class="empty-state">
                    <h3>Nothing matched</h3>
                    <p>Try another word, a wider price range, or a different category.</p>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product) { product_card($product, $brandRow !== null || $lineRow !== null); } ?>
                </div>
                <?= pagination_html($page, $pages, 'shop.php', array_filter($query, fn($v) => $v !== '')) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
