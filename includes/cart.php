<?php

/*
 * Guests keep quantities in the session. A logged-in customer keeps them in
 * cart_items. On login the session lines merge into that table. Prices are
 * read from products when the cart is shown. The browser never sends a price.
 * Checkout reads this cart again on the server before it writes an order.
 */

function session_cart(): array
{
    $raw = $_SESSION['cart'] ?? [];
    if (!is_array($raw)) {
        $raw = [];
    }
    $cart = [];
    foreach ($raw as $id => $qty) {
        $id = (int) $id;
        $qty = (int) $qty;
        if ($id > 0 && $qty > 0) {
            $cart[$id] = min(20, $qty);
        }
    }
    $_SESSION['cart'] = $cart;
    return $cart;
}

function customer_cart_map(int $userId): array
{
    $stmt = db()->prepare('SELECT ci.product_id, ci.quantity FROM cart_items ci JOIN carts c ON c.id = ci.cart_id WHERE c.user_id = ?');
    $stmt->execute([$userId]);
    $cart = [];
    foreach ($stmt as $row) {
        $cart[(int) $row['product_id']] = (int) $row['quantity'];
    }
    return $cart;
}

function cart_quantities(): array
{
    $user = current_user();
    if ($user && $user['role'] === 'customer') {
        return customer_cart_map((int) $user['id']);
    }
    return session_cart();
}

function cart_count(): int
{
    return array_sum(cart_quantities());
}

function save_customer_qty(int $userId, int $productId, int $qty): void
{
    $pdo = db();
    $pdo->prepare('INSERT INTO carts (user_id) VALUES (?) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP')->execute([$userId]);
    $find = $pdo->prepare('SELECT id FROM carts WHERE user_id = ?');
    $find->execute([$userId]);
    $cartId = (int) $find->fetchColumn();

    $item = $pdo->prepare('SELECT id FROM cart_items WHERE cart_id = ? AND product_id = ?');
    $item->execute([$cartId, $productId]);
    $itemId = $item->fetchColumn();

    if ($qty <= 0) {
        if ($itemId) {
            $pdo->prepare('DELETE FROM cart_items WHERE id = ?')->execute([(int) $itemId]);
        }
        return;
    }
    if ($itemId) {
        $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?')->execute([$qty, (int) $itemId]);
        return;
    }
    $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?,?,?)')->execute([$cartId, $productId, $qty]);
}

function persist_qty(int $productId, int $qty): void
{
    $user = current_user();
    if ($user && $user['role'] === 'customer') {
        save_customer_qty((int) $user['id'], $productId, $qty);
        return;
    }
    if ($qty <= 0) {
        unset($_SESSION['cart'][$productId]);
        return;
    }
    $_SESSION['cart'][$productId] = $qty;
}

function add_to_cart(string $code, int $qty): string
{
    $qty = max(1, min(20, $qty));
    $product = find_product_by_code($code);
    if (!$product || $product['status'] !== 'active' || $product['category_status'] !== 'active') {
        throw new AppException('That product is not available.');
    }
    if ((int) $product['stock'] < 1) {
        throw new AppException('That product is out of stock.');
    }
    $map = cart_quantities();
    $newQty = min(20, ($map[(int) $product['id']] ?? 0) + $qty);
    if ($newQty > (int) $product['stock']) {
        throw new AppException($product['name'] . ' has only ' . (int) $product['stock'] . ' in stock.');
    }
    persist_qty((int) $product['id'], $newQty);
    return $product['name'];
}

function update_cart_line(int $productId, int $qty): void
{
    $product = find_product($productId);
    if (!$product || $product['status'] !== 'active' || $product['category_status'] !== 'active') {
        persist_qty($productId, 0);
        throw new AppException('A product is no longer available and was removed from the cart.');
    }
    if ($qty <= 0) {
        persist_qty($productId, 0);
        return;
    }
    $qty = min(20, $qty);
    if ($qty > (int) $product['stock']) {
        throw new AppException($product['name'] . ' has only ' . (int) $product['stock'] . ' in stock.');
    }
    persist_qty($productId, $qty);
}

function merge_session_cart(int $userId): void
{
    foreach (session_cart() as $productId => $qty) {
        $product = find_product((int) $productId);
        if (!$product || $product['status'] !== 'active' || $product['category_status'] !== 'active') {
            continue;
        }
        $existing = customer_cart_map($userId);
        $combined = min(20, (int) $product['stock'], ($existing[(int) $productId] ?? 0) + (int) $qty);
        if ($combined > 0) {
            save_customer_qty($userId, (int) $productId, $combined);
        }
    }
    unset($_SESSION['cart']);
}

function cart_details(): array
{
    $map = cart_quantities();
    $items = [];
    $warnings = [];
    $subtotal = 0;
    if (!$map) {
        return [
            'items' => [],
            'warnings' => [],
            'subtotal' => '0.00',
            'shipping' => '0.00',
            'total' => '0.00',
            'ready' => false,
        ];
    }

    $ids = array_keys($map);
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(product_sql() . " WHERE p.id IN ($marks)");
    $stmt->execute($ids);
    $rows = [];
    foreach ($stmt as $row) {
        $rows[(int) $row['id']] = $row;
    }

    foreach ($map as $productId => $qty) {
        $qty = (int) $qty;
        if (!isset($rows[$productId])) {
            $warnings[] = 'A saved product is no longer in the catalogue.';
            continue;
        }
        $row = $rows[$productId];
        $available = $row['status'] === 'active' && $row['category_status'] === 'active';
        $problem = '';
        if (!$available) {
            $problem = 'No longer available';
            $warnings[] = $row['name'] . ' is no longer available. Remove it before checkout.';
        } elseif ($qty > (int) $row['stock']) {
            $problem = 'Only ' . (int) $row['stock'] . ' in stock';
            $warnings[] = $row['name'] . ' has only ' . (int) $row['stock'] . ' left.';
        }
        $cents = $problem === '' ? to_cents($row['price']) * $qty : 0;
        $subtotal += $cents;
        $items[] = [
            'id' => (int) $row['id'],
            'product_code' => $row['product_code'],
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $qty,
            'stock' => (int) $row['stock'],
            'image' => $row['image'],
            'line_total' => from_cents($cents),
            'problem' => $problem,
        ];
    }

    $shipping = shipping_cents($subtotal);
    return [
        'items' => $items,
        'warnings' => $warnings,
        'subtotal' => from_cents($subtotal),
        'shipping' => from_cents($shipping),
        'total' => from_cents($subtotal + $shipping),
        'ready' => $items && !$warnings,
    ];
}

function clear_db_cart(PDO $pdo, int $userId): void
{
    $pdo->prepare('DELETE ci FROM cart_items ci INNER JOIN carts c ON c.id = ci.cart_id WHERE c.user_id = ?')->execute([$userId]);
}
