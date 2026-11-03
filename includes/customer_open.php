<?php
$accountActive = $accountActive ?? '';
$accountUser = $accountUser ?? current_user();
?>
<div class="account-shell container">
    <aside class="account-side">
        <p class="lane-chip">Customer</p>
        <p class="account-who"><?= e($accountUser['full_name'] ?? 'My account') ?></p>
        <p class="account-help">Orders, returns and your saved details live here.</p>
        <p class="side-label">My account</p>
        <a class="side-btn<?= $accountActive === 'profile' ? ' active' : '' ?>" href="profile.php">Edit my details</a>
        <p class="side-label">Orders</p>
        <a class="side-btn<?= $accountActive === 'orders' ? ' active' : '' ?>" href="orders.php">My orders</a>
        <a class="side-btn<?= $accountActive === 'returns' ? ' active' : '' ?>" href="returns.php">Returns</a>
        <p class="side-label">Shop</p>
        <a class="side-btn" href="<?= e(base_url('shop.php')) ?>">Continue shopping</a>
        <a class="side-btn" href="<?= e(base_url('cart.php')) ?>">My cart</a>
        <a class="side-btn" href="<?= e(base_url('auth/logout.php')) ?>">Log out</a>
    </aside>
    <div class="account-main">
