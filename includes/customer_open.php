<?php
$accountActive = $accountActive ?? '';
$accountUser = $accountUser ?? current_user();
?>
<div class="account-shell container">
    <aside class="account-side">
        <p class="lane-chip">Customer</p>
        <img class="side-face" src="<?= e(base_url('assets/images/look/lahore-green.jpg')) ?>" alt="Woman in a mint outfit and white dupatta">
        <p class="account-who"><?= e($accountUser['full_name'] ?? 'My account') ?></p>
        <p class="side-label">My account</p>
        <a class="<?= $accountActive === 'profile' ? 'active' : '' ?>" href="profile.php">Edit my details</a>
        <p class="side-label">Orders</p>
        <a class="<?= $accountActive === 'orders' ? 'active' : '' ?>" href="orders.php">My orders</a>
        <a class="<?= $accountActive === 'returns' ? 'active' : '' ?>" href="returns.php">Returns</a>
        <p class="side-label">Shop</p>
        <a href="<?= e(base_url('shop.php')) ?>">Continue shopping</a>
        <a href="<?= e(base_url('cart.php')) ?>">My cart</a>
        <a href="<?= e(base_url('auth/logout.php')) ?>">Log out</a>
    </aside>
    <div class="account-main">
