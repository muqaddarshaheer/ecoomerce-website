<?php
$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
$categories = [];
$navBrands = [];
$navBeauty = [];
try {
    $categories = active_categories();
    require_once __DIR__ . '/brands.php';
    require_once __DIR__ . '/beauty_kinds.php';
    $navBrands = shop_brands();
    $navBeauty = beauty_kinds();
} catch (Throwable $e) {
    $categories = [];
}
$aisleTypes = [
    '01' => [
        ['Gift boxes', 'gift'],
        ['Candles', 'candle'],
        ['Frames', 'frame'],
    ],
    '02' => [
        ['Pens', 'pen'],
        ['Notebooks', 'note'],
        ['Pencils', 'pencil'],
        ['Files', 'file'],
    ],
    '03' => [
        ['Handbags', 'bag'],
        ['Wallets', 'wallet'],
        ['Backpacks', 'pack'],
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
<?php page_head($pageTitle); ?>
</head>
<body class="shop">
<a class="visually-hidden-focusable" href="#main">Skip to content</a>
<div class="nav-stack">
    <div class="announce">
        <div class="container d-flex justify-content-between">
            <span>Free delivery over <?= e(money(FREE_SHIPPING_OVER)) ?></span>
            <span class="d-none d-md-inline">Card, cheque and cash on delivery</span>
        </div>
    </div>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="brand" href="<?= e(base_url('index.php')) ?>">
                    <img src="<?= e(base_url('assets/images/logo.svg')) ?>" alt="">
                    <span>
                        <span class="brand-name"><?= e(APP_NAME) ?></span>
                        <small><?= e(APP_TAGLINE) ?></small>
                    </span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Open menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav nav-pill">
                        <?php $navHere = basename($_SERVER['SCRIPT_NAME'] ?? ''); ?>
                        <li class="nav-item"><a class="nav-link<?= $navHere === 'index.php' ? ' active' : '' ?>" href="<?= e(base_url('index.php')) ?>">Home</a></li>
                        <li class="nav-item"><a class="nav-link<?= $navHere === 'shop.php' ? ' active' : '' ?>" href="<?= e(base_url('shop.php')) ?>">Shop</a></li>
                        <li class="nav-item has-menu">
                            <button class="nav-link" type="button" aria-expanded="false" aria-haspopup="true">Categories</button>
                            <div class="mega mega-cats">
                                <?php foreach ($categories as $navCategory): ?>
                                    <div class="mega-col">
                                        <a class="mega-cover" href="<?= e(base_url('shop.php?category=' . urlencode($navCategory['slug']))) ?>">
                                            <?= picture_or_fallback(media_url('categories', $navCategory['image'] ?? null), $navCategory['name']) ?>
                                            <span><?= e($navCategory['name']) ?></span>
                                        </a>
                                        <div class="mega-pills">
                                        <?php if (($navCategory['code_prefix'] ?? '') === '04'): ?>
                                            <?php foreach ($navBeauty as $kindKey => $kindRow): ?>
                                                <a class="mega-sub" href="<?= e(base_url('shop.php?category=' . urlencode($navCategory['slug']) . '&kind=' . urlencode($kindKey))) ?>"><?= e($kindRow['label']) ?></a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <?php foreach ($aisleTypes[$navCategory['code_prefix'] ?? ''] ?? [] as $aisleType): ?>
                                                <a class="mega-sub" href="<?= e(base_url('shop.php?category=' . urlencode($navCategory['slug']) . '&q=' . urlencode($aisleType[1]))) ?>"><?= e($aisleType[0]) ?></a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </li>
                        <li class="nav-item has-menu">
                            <button class="nav-link" type="button" aria-expanded="false" aria-haspopup="true">Brands</button>
                            <div class="mega mega-brands" data-brand-menu>
                                <div class="brand-menu-head">
                                    <p>Shop by brand</p>
                                    <input class="brand-find" type="search" data-brand-find placeholder="Search a brand" aria-label="Search by brand">
                                </div>
                                <div class="brand-logo-grid">
                                    <?php foreach ($navBrands as $navBrand): ?>
                                        <a class="logo-tile" data-brand-name="<?= e(strtolower($navBrand['name'])) ?>" href="<?= e(base_url('shop.php?brand=' . urlencode($navBrand['slug']))) ?>"><?= brand_wordmark($navBrand['slug'], $navBrand['name']) ?></a>
                                    <?php endforeach; ?>
                                    <a class="logo-more" href="<?= e(base_url('brands.php')) ?>">All brands</a>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('about.php')) ?>">About</a></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-open-feedback>Feedback</button></li>
                    </ul>
                    <form class="nav-search d-flex" method="get" action="<?= e(base_url('shop.php')) ?>">
                        <input class="form-control" type="search" name="q" placeholder="Search the shop" value="<?= e(query_text('q')) ?>" aria-label="Search products">
                    </form>
                    <div class="nav-tools d-flex align-items-center">
                        <button class="theme-switch" type="button" data-theme-switch aria-pressed="false" aria-label="Switch to light mode">
                            <span data-theme-label>Light</span>
                        </button>
                        <a class="icon-btn cart-link" href="<?= e(base_url('cart.php')) ?>" aria-label="Shopping cart">
                            <svg class="cart-icon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
                                <path d="M6.5 7h13l-1.4 8.2a1 1 0 0 1-1 .8H8.2a1 1 0 0 1-1-.8L5.6 4.8H3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="9" cy="20" r="1.25" fill="currentColor"/>
                                <circle cx="17" cy="20" r="1.25" fill="currentColor"/>
                            </svg>
                            <span id="cart-count"><?php try { echo (int) cart_count(); } catch (Throwable $e) { echo '0'; } ?></span>
                        </a>
                        <?php if ($user && $user['role'] === 'customer'): ?>
                            <a class="btn btn-login" href="<?= e(base_url('customer/orders.php')) ?>">My account</a>
                        <?php elseif ($user && $user['role'] === 'admin'): ?>
                            <a class="btn btn-login" href="<?= e(base_url('admin/index.php')) ?>">Admin panel</a>
                        <?php elseif ($user && $user['role'] === 'employee'): ?>
                            <a class="btn btn-login" href="<?= e(base_url('employee/index.php')) ?>">Employee panel</a>
                        <?php else: ?>
                            <a class="btn btn-login" href="<?= e(base_url('auth/login.php')) ?>">Log in</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>
</div>
<?php render_flash(); ?>
<main id="main">
