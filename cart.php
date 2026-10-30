<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ajax = post('ajax') === '1';
    $respond = function (bool $ok, string $message) use ($ajax): void {
        if ($ajax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => $ok, 'message' => $message, 'count' => $ok ? cart_count() : cart_count()]);
            exit;
        }
    };
    try {
        $sent = (string) ($_POST['csrf_token'] ?? '');
        $real = (string) ($_SESSION['csrf_token'] ?? '');
        if ($real === '' || $sent === '' || !hash_equals($real, $sent)) {
            if ($ajax) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => 'The form expired. Refresh the page.', 'count' => cart_count()]);
                exit;
            }
            set_flash('danger', 'The form expired. Please try again.');
            redirect('cart.php');
        }
        $action = post('action', 20);
        if ($action === 'add') {
            $name = add_to_cart(post('product_code', 7), (int) post('quantity', 3));
            $respond(true, $name . ' was added to your cart.');
            set_flash('success', $name . ' was added to your cart.');
        } elseif ($action === 'update') {
            update_cart_line((int) post('product_id', 10), (int) post('quantity', 3));
            set_flash('success', 'Cart updated.');
        } elseif ($action === 'remove') {
            update_cart_line((int) post('product_id', 10), 0);
            set_flash('success', 'Item removed.');
        }
    } catch (AppException $e) {
        $respond(false, $e->getMessage());
        set_flash('danger', $e->getMessage());
    }
    $back = safe_local_path(post('back', 180), 'cart.php');
    redirect($back);
}

$cart = cart_details();
$pageTitle = 'Cart';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <h1>Your cart</h1>
    <?php if (!$cart['items']): ?>
        <div class="empty-state">
            <h2>The cart is empty</h2>
            <p><a class="btn btn-clay" href="<?= e(base_url('shop.php')) ?>">Browse products</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($cart['warnings'] as $warning): ?>
            <div class="alert alert-warning"><?= e($warning) ?></div>
        <?php endforeach; ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="soft-card">
                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="d-flex gap-3 align-items-center border-bottom py-3 cart-table">
                            <?= picture_or_fallback(media_url('products', $item['image']), $item['name']) ?>
                            <div class="flex-grow-1">
                                <a href="<?= e(base_url('product.php?code=' . urlencode($item['product_code']))) ?>"><?= e($item['name']) ?></a>
                                <div class="text-secondary small">ID <?= e($item['product_code']) ?> · <?= e(money($item['price'])) ?></div>
                                <?php if ($item['problem']): ?><div class="text-danger small"><?= e($item['problem']) ?></div><?php endif; ?>
                            </div>
                            <form method="post" class="d-flex gap-2 align-items-center">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                <div class="qty-box">
                                    <button type="button" data-qty="-1" aria-label="Decrease">−</button>
                                    <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="1" max="<?= max(1, min(20, (int) $item['stock'])) ?>">
                                    <button type="button" data-qty="1" aria-label="Increase">+</button>
                                </div>
                                <button class="btn btn-outline-dark btn-sm" type="submit">Update</button>
                            </form>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                <button class="btn btn-link text-danger" type="submit" data-confirm="Remove this item?">Remove</button>
                            </form>
                            <strong><?= e(money($item['line_total'])) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="summary-box">
                    <h2 class="h5">Summary</h2>
                    <p class="d-flex justify-content-between"><span>Goods</span><span><?= e(money($cart['subtotal'])) ?></span></p>
                    <p class="d-flex justify-content-between"><span>Delivery</span><span><?= (float) $cart['shipping'] <= 0 ? 'Free' : e(money($cart['shipping'])) ?></span></p>
                    <p class="d-flex justify-content-between"><strong>Total</strong><strong><?= e(money($cart['total'])) ?></strong></p>
                    <p class="small text-secondary">Delivery is <?= e(money(SHIPPING_FEE)) ?>, and free from <?= e(money(FREE_SHIPPING_OVER)) ?>. The server calculates the total again at checkout.</p>
                    <?php if ($cart['ready']): ?>
                        <a class="btn btn-clay w-100" href="<?= e(base_url('checkout.php')) ?>">Checkout</a>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100" type="button" disabled>Fix the cart before checkout</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
