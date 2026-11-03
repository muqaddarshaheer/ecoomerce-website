<?php

/*
 * Guests keep quantities in the session. A logged-in customer keeps them in
 * cart_items. On login the session lines merge into that table. Prices are
 * read from products when the cart is shown. The browser never sends a price.
 * Checkout reads this cart again on the server before it writes an order.
 */

function gift_row_custom(array $row): array
{
    return [
        'design' => (string) ($row['design'] ?? 'classic'),
        'color' => (string) ($row['color_name'] ?? ($row['color'] ?? 'Ivory')),
        'custom_name' => (string) ($row['custom_name'] ?? ''),
        'message' => (string) ($row['gift_message'] ?? ($row['message'] ?? '')),
        'note' => (string) ($row['custom_note'] ?? ($row['note'] ?? '')),
    ];
}

function session_lines(): array
{
    if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id => $qty) {
            $id = (int) $id;
            $qty = (int) $qty;
            if ($id > 0 && $qty > 0) {
                $custom = gift_custom_blank();
                $_SESSION['cart_lines'][$id . ':' . gift_line_token($custom)] = [
                    'product_id' => $id,
                    'qty' => min(20, $qty),
                    'custom' => $custom,
                ];
            }
        }
        unset($_SESSION['cart']);
    }
    $raw = $_SESSION['cart_lines'] ?? [];
    if (!is_array($raw)) {
        $raw = [];
    }
    $lines = [];
    foreach ($raw as $key => $line) {
        if (!is_array($line)) {
            continue;
        }
        $productId = (int) ($line['product_id'] ?? 0);
        $qty = (int) ($line['qty'] ?? 0);
        $custom = gift_row_custom(is_array($line['custom'] ?? null) ? $line['custom'] : []);
        $token = gift_line_token($custom);
        if ($productId < 1 || $qty < 1 || !preg_match('/^[a-f0-9]{16}$/', $token)) {
            continue;
        }
        $lines[] = [
            'product_id' => $productId,
            'qty' => min(20, $qty),
            'token' => $token,
            'custom' => $custom,
            'key' => $productId . ':' . $token,
        ];
    }
    $stored = [];
    foreach ($lines as $line) {
        $stored[$line['key']] = [
            'product_id' => $line['product_id'],
            'qty' => $line['qty'],
            'custom' => $line['custom'],
        ];
    }
    $_SESSION['cart_lines'] = $stored;
    return $lines;
}

function customer_cart_lines(int $userId): array
{
    $stmt = db()->prepare('SELECT ci.product_id, ci.quantity, ci.design, ci.color_name, ci.custom_name, ci.gift_message, ci.custom_note, ci.line_token FROM cart_items ci JOIN carts c ON c.id = ci.cart_id WHERE c.user_id = ? ORDER BY ci.id');
    $stmt->execute([$userId]);
    $lines = [];
    foreach ($stmt as $row) {
        $custom = gift_row_custom($row);
        $token = (string) $row['line_token'];
        if (!preg_match('/^[a-f0-9]{16}$/', $token)) {
            $token = gift_line_token($custom);
        }
        $lines[] = [
            'product_id' => (int) $row['product_id'],
            'qty' => (int) $row['quantity'],
            'token' => $token,
            'custom' => $custom,
            'key' => (int) $row['product_id'] . ':' . $token,
        ];
    }
    return $lines;
}

function cart_lines(): array
{
    $user = current_user();
    if ($user && $user['role'] === 'customer') {
        return customer_cart_lines((int) $user['id']);
    }
    return session_lines();
}

function cart_count(): int
{
    $count = 0;
    foreach (cart_lines() as $line) {
        $count += (int) $line['qty'];
    }
    return $count;
}

function customer_cart_id(int $userId): int
{
    $pdo = db();
    $pdo->prepare('INSERT INTO carts (user_id) VALUES (?) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP')->execute([$userId]);
    $find = $pdo->prepare('SELECT id FROM carts WHERE user_id = ?');
    $find->execute([$userId]);
    return (int) $find->fetchColumn();
}

function save_customer_line(int $userId, int $productId, array $custom, int $qty): void
{
    $pdo = db();
    $cartId = customer_cart_id($userId);
    $token = gift_line_token($custom);
    $item = $pdo->prepare('SELECT id FROM cart_items WHERE cart_id = ? AND product_id = ? AND line_token = ?');
    $item->execute([$cartId, $productId, $token]);
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
    $pdo->prepare('INSERT INTO cart_items (cart_id, product_id, quantity, design, color_name, custom_name, gift_message, custom_note, line_token) VALUES (?,?,?,?,?,?,?,?,?)')
        ->execute([$cartId, $productId, $qty, $custom['design'], $custom['color'], $custom['custom_name'], $custom['message'], $custom['note'], $token]);
}

function persist_line(int $productId, array $custom, int $qty): void
{
    $user = current_user();
    if ($user && $user['role'] === 'customer') {
        save_customer_line((int) $user['id'], $productId, $custom, $qty);
        return;
    }
    $token = gift_line_token($custom);
    $key = $productId . ':' . $token;
    if ($qty <= 0) {
        unset($_SESSION['cart_lines'][$key]);
        return;
    }
    $_SESSION['cart_lines'][$key] = [
        'product_id' => $productId,
        'qty' => $qty,
        'custom' => $custom,
    ];
}

function product_qty_in_cart(int $productId, ?string $exceptToken = null): int
{
    $total = 0;
    foreach (cart_lines() as $line) {
        if ($line['product_id'] !== $productId) {
            continue;
        }
        if ($exceptToken !== null && $line['token'] === $exceptToken) {
            continue;
        }
        $total += (int) $line['qty'];
    }
    return $total;
}

function add_to_cart(string $code, int $qty, ?array $custom = null): string
{
    $qty = max(1, min(20, $qty));
    $custom = $custom ?? gift_custom_blank();
    $product = find_product_by_code($code);
    if (!$product || $product['status'] !== 'active' || $product['category_status'] !== 'active') {
        throw new AppException('That product is not available.');
    }
    if ((int) $product['stock'] < 1) {
        throw new AppException('That product is out of stock.');
    }
    $token = gift_line_token($custom);
    $already = 0;
    foreach (cart_lines() as $line) {
        if ($line['product_id'] === (int) $product['id'] && $line['token'] === $token) {
            $already = (int) $line['qty'];
        }
    }
    $newQty = min(20, $already + $qty);
    $others = product_qty_in_cart((int) $product['id'], $token);
    if ($others + $newQty > (int) $product['stock']) {
        throw new AppException($product['name'] . ' has only ' . (int) $product['stock'] . ' in stock.');
    }
    persist_line((int) $product['id'], $custom, $newQty);
    return $product['name'];
}

function update_cart_line(int $productId, int $qty, string $token = ''): void
{
    $product = find_product($productId);
    $lines = array_values(array_filter(cart_lines(), static function ($line) use ($productId) {
        return $line['product_id'] === $productId;
    }));
    if ($token === '') {
        if (count($lines) !== 1) {
            throw new AppException('Choose which design to update.');
        }
        $token = $lines[0]['token'];
    }
    $current = null;
    foreach ($lines as $line) {
        if ($line['token'] === $token) {
            $current = $line;
        }
    }
    if (!$product || $product['status'] !== 'active' || $product['category_status'] !== 'active') {
        if ($current) {
            persist_line($productId, $current['custom'], 0);
        }
        throw new AppException('A product is no longer available and was removed from the cart.');
    }
    if (!$current) {
        return;
    }
    if ($qty <= 0) {
        persist_line($productId, $current['custom'], 0);
        return;
    }
    $qty = min(20, $qty);
    $others = product_qty_in_cart($productId, $token);
    if ($others + $qty > (int) $product['stock']) {
        throw new AppException($product['name'] . ' has only ' . (int) $product['stock'] . ' in stock.');
    }
    persist_line($productId, $current['custom'], $qty);
}

function merge_session_cart(int $userId): void
{
    foreach (session_lines() as $line) {
        $product = find_product((int) $line['product_id']);
        if (!$product || $product['status'] !== 'active' || $product['category_status'] !== 'active') {
            continue;
        }
        $existing = 0;
        foreach (customer_cart_lines($userId) as $saved) {
            if ($saved['product_id'] === (int) $line['product_id'] && $saved['token'] === $line['token']) {
                $existing = (int) $saved['qty'];
            }
        }
        $used = 0;
        foreach (customer_cart_lines($userId) as $saved) {
            if ($saved['product_id'] === (int) $line['product_id'] && $saved['token'] !== $line['token']) {
                $used += (int) $saved['qty'];
            }
        }
        $combined = min(20, max(0, (int) $product['stock'] - $used), $existing + (int) $line['qty']);
        if ($combined > 0) {
            save_customer_line($userId, (int) $line['product_id'], $line['custom'], $combined);
        }
    }
    unset($_SESSION['cart'], $_SESSION['cart_lines']);
}

function cart_details(): array
{
    $lines = cart_lines();
    $items = [];
    $warnings = [];
    $subtotal = 0;
    if (!$lines) {
        return [
            'items' => [],
            'warnings' => [],
            'subtotal' => '0.00',
            'shipping' => '0.00',
            'total' => '0.00',
            'ready' => false,
        ];
    }

    $ids = array_values(array_unique(array_map(static function ($line) {
        return (int) $line['product_id'];
    }, $lines)));
    $marks = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(product_sql() . " WHERE p.id IN ($marks)");
    $stmt->execute($ids);
    $rows = [];
    foreach ($stmt as $row) {
        $rows[(int) $row['id']] = $row;
    }

    foreach ($lines as $line) {
        $productId = (int) $line['product_id'];
        $qty = (int) $line['qty'];
        if (!isset($rows[$productId])) {
            $warnings[] = 'A saved product is no longer in the catalogue.';
            continue;
        }
        $row = $rows[$productId];
        $available = $row['status'] === 'active' && $row['category_status'] === 'active';
        $problem = '';
        $held = product_qty_in_cart($productId);
        if (!$available) {
            $problem = 'No longer available';
            $warnings[] = $row['name'] . ' is no longer available. Remove it before checkout.';
        } elseif ($held > (int) $row['stock']) {
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
            'token' => $line['token'],
            'design' => $line['custom']['design'],
            'color_name' => $line['custom']['color'],
            'custom_name' => $line['custom']['custom_name'],
            'gift_message' => $line['custom']['message'],
            'custom_note' => $line['custom']['note'],
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

function save_customer_qty(int $userId, int $productId, int $qty): void
{
    save_customer_line($userId, $productId, gift_custom_blank(), $qty);
}

function clear_db_cart(PDO $pdo, int $userId): void
{
    $pdo->prepare('DELETE ci FROM cart_items ci INNER JOIN carts c ON c.id = ci.cart_id WHERE c.user_id = ?')->execute([$userId]);
}
