<?php
$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
$categories = [];
$navCategories = [];
$navBrands = [];
$navPicks = [];
try {
    $categories = active_categories();
    $navCategories = $categories;
    usort($navCategories, static fn(array $a, array $b): int => strcmp((string) $a['code_prefix'], (string) $b['code_prefix']));
    require_once __DIR__ . '/brands.php';
    $navBrands = shop_brands();
    $navPicks = category_menu_picks();
} catch (Throwable $e) {
    $categories = [];
    $navCategories = [];
}
?>
<!doctype html>
<html lang="en">
<head>
<?php page_head($pageTitle); ?>
</head>
<body class="shop<?= !empty($bodyClass) ? ' ' . e($bodyClass) : '' ?>">
<a class="visually-hidden-focusable" href="#main">Skip to content</a>
<div class="nav-stack">
    <div class="announce">
        <div class="container announce-row">
            <span>Free delivery over <?= e(money(FREE_SHIPPING_OVER)) ?></span>
            <span class="announce-mid">Make Every Moment Special</span>
            <span class="announce-end">Cash on delivery in Karachi
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M3 7h11v8H3zM14 10h4l3 3v2h-7z" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="7" cy="17" r="1.6" fill="currentColor"/><circle cx="18" cy="17" r="1.6" fill="currentColor"/></svg>
            </span>
        </div>
    </div>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="brand" href="<?= e(base_url('index.php')) ?>">
                    <img src="<?= e(base_url('assets/images/logo.svg')) ?>" alt="">
                    <span>
                        <span class="brand-name"><?= e(APP_NAME) ?></span>
                        <small>Gifts • Happiness • Always</small>
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
                            <div class="mega mega-cats" data-cat-menu>
                                <div class="cat-rail" aria-label="Departments">
                                    <?php foreach ($navCategories as $catIndex => $navCategory): ?>
                                        <a class="cat-tab<?= $catIndex === 0 ? ' is-on' : '' ?>" href="<?= e(base_url('shop.php?category=' . urlencode($navCategory['slug']))) ?>" data-cat-tab="<?= e($navCategory['slug']) ?>">
                                            <?= picture_or_fallback(media_url('categories', $navCategory['image'] ?? null), $navCategory['name']) ?>
                                            <span><?= e($navCategory['name']) ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                                <div class="cat-stage">
                                    <?php foreach ($navCategories as $catIndex => $navCategory): ?>
                                        <?php $catPicks = $navPicks[(int) $navCategory['id']] ?? []; ?>
                                        <div class="cat-panel<?= $catIndex === 0 ? ' is-on' : '' ?>" role="tabpanel" data-cat-panel="<?= e($navCategory['slug']) ?>">
                                            <div class="cat-panel-head">
                                                <p><?= e($navCategory['name']) ?></p>
                                                <a href="<?= e(base_url('shop.php?category=' . urlencode($navCategory['slug']))) ?>">Shop all</a>
                                            </div>
                                            <div class="cat-picks">
                                                <?php foreach ($catPicks as $pick): ?>
                                                    <a class="cat-pick" href="<?= e(base_url('product.php?code=' . urlencode($pick['product_code']))) ?>">
                                                        <?= picture_or_fallback(media_url('products', $pick['image'] ?? null), $pick['name']) ?>
                                                        <span><?= e($pick['name']) ?></span>
                                                        <small><?= e(money($pick['price'])) ?></small>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item has-menu">
                            <button class="nav-link" type="button" aria-expanded="false" aria-haspopup="true">Brands</button>
                            <div class="mega mega-brands" data-brand-menu>
                                <div class="brand-menu-head">
                                    <p>Shop by brand</p>
                                    <span class="brand-rule" aria-hidden="true"></span>
                                    <span data-brand-count><?= count($navBrands) ?> brands</span>
                                    <input class="brand-find" type="search" data-brand-find placeholder="Search a brand" aria-label="Search by brand">
                                </div>
                                <div class="brand-logo-grid">
                                    <?php foreach ($navBrands as $navBrand): ?>
                                        <a class="logo-tile" data-brand-name="<?= e(strtolower($navBrand['name'] . ' ' . $navBrand['line'])) ?>" href="<?= e(base_url('shop.php?brand=' . urlencode($navBrand['slug']))) ?>">
                                            <?= brand_icon($navBrand['slug'], $navBrand['name']) ?>
                                            <span class="logo-name"><?= e($navBrand['name']) ?></span>
                                            <small><?= e($navBrand['line']) ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                    <a class="logo-more" href="<?= e(base_url('brands.php')) ?>">All brands</a>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(base_url('about.php')) ?>">About</a></li>
                        <li class="nav-item"><button class="nav-link" type="button" data-open-feedback>Feedback</button></li>
                        <li class="nav-item"><a class="nav-link<?= $navHere === 'customize.php' ? ' active' : '' ?>" href="<?= e(base_url('customize.php')) ?>">Customise</a></li>
                    </ul>
                    <form class="nav-search d-flex" method="get" action="<?= e(base_url('shop.php')) ?>">
                        <input class="form-control" type="search" name="q" placeholder="Search the shop" value="<?= e(query_text('q')) ?>" aria-label="Search products">
                        <button class="search-go" type="submit" aria-label="Search">
                            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><circle cx="11" cy="11" r="6.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M16 16l4 4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        </button>
                    </form>
                    <div class="nav-tools d-flex align-items-center">
                        <?php
                        $accountHref = base_url('auth/login.php');
                        $accountLabel = 'Log in';
                        if ($user && $user['role'] === 'customer') {
                            $accountHref = base_url('customer/orders.php');
                            $accountLabel = 'My account';
                        } elseif ($user && $user['role'] === 'admin') {
                            $accountHref = base_url('admin/index.php');
                            $accountLabel = 'Admin panel';
                        } elseif ($user && $user['role'] === 'employee') {
                            $accountHref = base_url('employee/index.php');
                            $accountLabel = 'Employee panel';
                        }
                        ?>
                        <?php if (!$user): ?>
                            <a class="tool-link" href="<?= e(base_url('auth/register.php')) ?>">
                                <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="M12 12a3.2 3.2 0 1 0-3.2-3.2A3.2 3.2 0 0 0 12 12z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M5.5 19c1-3 3.2-4.5 6.5-4.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M17 14v6M14 17h6" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                                <span>Join</span>
                            </a>
                        <?php endif; ?>
                        <a class="tool-link" href="<?= e($accountHref) ?>">
                            <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><circle cx="12" cy="8" r="3.2" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M5.5 19c1-3 3.2-4.5 6.5-4.5s5.5 1.5 6.5 4.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                            <span><?= e($accountLabel) ?></span>
                        </a>
                        <a class="tool-link cart-link" href="<?= e(base_url('cart.php')) ?>">
                            <svg class="cart-icon" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
                                <path d="M6.5 7h13l-1.4 8.2a1 1 0 0 1-1 .8H8.2a1 1 0 0 1-1-.8L5.6 4.8H3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="9" cy="20" r="1.25" fill="currentColor"/>
                                <circle cx="17" cy="20" r="1.25" fill="currentColor"/>
                            </svg>
                            <span>Cart</span>
                            <b id="cart-count"><?php try { echo (int) cart_count(); } catch (Throwable $e) { echo '0'; } ?></b>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
</div>
<?php render_flash(); ?>
<main id="main">
