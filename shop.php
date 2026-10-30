<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/beauty_kinds.php';
require_once __DIR__ . '/includes/brands.php';

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
$perPage = ($category === $beautySlug || $brandSlug !== '') ? 24 : 12;

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
];
$shopHeading = 'Shop';
if ($brandRow) {
    $shopHeading = $brandRow['name'];
} elseif ($category === $beautySlug) {
    $shopHeading = 'Beauty & Accessories';
}
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <div class="section-head">
        <div>
            <p class="eyebrow"><?php if ($brandRow): ?><?= e($brandRow['line']) ?> · <?php endif; ?><?= $total ?> product<?= $total === 1 ? '' : 's' ?></p>
            <h2><?= e($shopHeading) ?></h2>
            <?php if ($brandRow): ?><p class="brand-shelf"><?= e($brandRow['about']) ?></p><?php endif; ?>
        </div>
    </div>
    <div class="shop-layout">
        <form class="filter-card" method="get" action="shop.php">
            <h2 class="h5">Filters</h2>
            <label class="form-label" for="q">Search</label>
            <input class="form-control mb-3" id="q" name="q" value="<?= e($q) ?>">
            <label class="form-label" for="category">Category</label>
            <?php if ($kind !== ''): ?>
                <input type="hidden" name="kind" value="<?= e($kind) ?>">
            <?php endif; ?>
            <?php if ($brandSlug !== ''): ?>
                <input type="hidden" name="brand" value="<?= e($brandSlug) ?>">
            <?php endif; ?>
            <select class="form-select mb-3" id="category" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $row): ?>
                    <option value="<?= e($row['slug']) ?>" <?= $category === $row['slug'] ? 'selected' : '' ?>><?= e($row['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label" for="min">Min price</label>
                    <input class="form-control" id="min" name="min" inputmode="decimal" value="<?= e($minRaw) ?>">
                </div>
                <div class="col-6">
                    <label class="form-label" for="max">Max price</label>
                    <input class="form-control" id="max" name="max" inputmode="decimal" value="<?= e($maxRaw) ?>">
                </div>
            </div>
            <label class="form-label mt-3" for="sort">Sort</label>
            <select class="form-select" id="sort" name="sort">
                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: low to high</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option>
            </select>
            <div class="form-check mt-3">
                <input class="form-check-input" type="checkbox" name="stock" value="1" id="stock" <?= $stockOnly ? 'checked' : '' ?>>
                <label class="form-check-label" for="stock">In stock only</label>
            </div>
            <button class="btn btn-forest w-100 mt-3" type="submit">Apply filters</button>
            <a class="btn btn-link w-100" href="shop.php">Clear</a>
        </form>
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
                    <?php foreach ($products as $product) { product_card($product); } ?>
                </div>
                <?= pagination_html($page, $pages, 'shop.php', array_filter($query, fn($v) => $v !== '')) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
