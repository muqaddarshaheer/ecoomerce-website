<?php

/*
 * Order rules for the shopping cart.
 * Stock changes only inside a database transaction, with the product row locked,
 * so two customers cannot buy the last piece. The order number is 16 characters:
 * delivery digit 1 card, 2 cheque or 3 cash on delivery, then the lowest product
 * code in the order, then an 8-digit sequence from order_counters.
 * Card and cheque orders stay in the shop until payment is cleared. Cash on
 * delivery may leave sooner, and the cash is marked at delivery. A customer can
 * cancel only before dispatch. A return is one open request for the whole order,
 * and only within seven days after delivery. A refund row is a shop record.
 * Employees see only fulfilable orders and never the cheque number or card last four.
 */

function require_transaction(PDO $pdo): void
{
    if (!$pdo->inTransaction()) {
        throw new RuntimeException('This stock change must run inside a transaction.');
    }
}

function change_stock(PDO $pdo, int $productId, int $delta, string $reason, ?string $refType, ?int $refId, ?int $userId, ?string $note): void
{
    require_transaction($pdo);
    if ($delta === 0) {
        return;
    }
    $stmt = $pdo->prepare('SELECT stock, name FROM products WHERE id = ? FOR UPDATE');
    $stmt->execute([$productId]);
    $row = $stmt->fetch();
    if (!$row) {
        throw new AppException('A product in this order no longer exists.');
    }
    $newStock = (int) $row['stock'] + $delta;
    if ($newStock < 0) {
        throw new AppException($row['name'] . ' does not have enough stock.');
    }
    $pdo->prepare('UPDATE products SET stock = ? WHERE id = ?')->execute([$newStock, $productId]);
    $pdo->prepare('INSERT INTO stock_movements (product_id, change_qty, balance_after, reason, reference_type, reference_id, note, created_by) VALUES (?,?,?,?,?,?,?,?)')
        ->execute([$productId, $delta, $newStock, $reason, $refType, $refId, $note, $userId]);
}

function lock_order(PDO $pdo, int $orderId): ?array
{
    require_transaction($pdo);
    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? FOR UPDATE');
    $stmt->execute([$orderId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function order_items(int $orderId): array
{
    $stmt = db()->prepare("SELECT oi.*, (SELECT pi.file_name FROM product_images pi JOIN products p ON p.id = pi.product_id WHERE p.product_code = oi.product_code ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS image FROM order_items oi WHERE oi.order_id = ? ORDER BY oi.product_code");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function order_payment(int $orderId): ?array
{
    $stmt = db()->prepare('SELECT * FROM payments WHERE order_id = ?');
    $stmt->execute([$orderId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function order_timeline_records(int $orderId): array
{
    $stmt = db()->prepare('SELECT d.*, u.full_name FROM dispatch_records d JOIN users u ON u.id = d.actor_user_id WHERE d.order_id = ? ORDER BY d.id');
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function order_return_rows(int $orderId): array
{
    $stmt = db()->prepare('SELECT * FROM return_requests WHERE order_id = ? ORDER BY id DESC');
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function order_refund_rows(int $orderId): array
{
    $stmt = db()->prepare('SELECT * FROM refunds WHERE order_id = ? ORDER BY id DESC');
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function can_cancel(array $order): bool
{
    return in_array($order['order_status'], ['pending', 'confirmed'], true) && empty($order['dispatch_date']);
}

function can_dispatch(array $order): bool
{
    if ($order['order_status'] !== 'confirmed') {
        return false;
    }
    if ($order['delivery_type'] === '3') {
        return true;
    }
    return $order['payment_status'] === 'cleared';
}

function can_deliver(array $order): bool
{
    return $order['order_status'] === 'dispatched';
}

function can_request_return(array $order): bool
{
    if ($order['order_status'] !== 'delivered' || empty($order['delivery_date'])) {
        return false;
    }
    $deadline = strtotime($order['delivery_date'] . ' +' . ORDER_RETURN_DAYS . ' days');
    return $deadline !== false && time() <= $deadline;
}

function return_deadline(array $order): ?string
{
    if (empty($order['delivery_date'])) {
        return null;
    }
    $time = strtotime($order['delivery_date'] . ' +' . ORDER_RETURN_DAYS . ' days');
    return $time ? date('d M Y, h:i A', $time) : null;
}

function employee_can_view(array $order): bool
{
    $visible = ['confirmed', 'dispatched', 'delivered'];
    if (!in_array($order['order_status'], $visible, true)) {
        return false;
    }
    if ($order['delivery_type'] === '3') {
        return true;
    }
    return $order['payment_status'] === 'cleared';
}

function grouped_item_qty(int $orderId): array
{
    $qty = [];
    foreach (order_items($orderId) as $item) {
        $id = (int) $item['product_id'];
        $qty[$id] = ($qty[$id] ?? 0) + (int) $item['quantity'];
    }
    ksort($qty);
    return $qty;
}

function restore_order_stock(PDO $pdo, int $orderId, ?int $userId, string $note): void
{
    foreach (grouped_item_qty($orderId) as $productId => $qty) {
        change_stock($pdo, (int) $productId, (int) $qty, 'cancel', 'order', $orderId, $userId, $note);
    }
}

function ensure_refund(PDO $pdo, int $orderId, ?int $returnId, string $amount, string $reason): void
{
    $check = $pdo->prepare("SELECT id FROM refunds WHERE order_id = ? AND status IN ('pending','issued')");
    $check->execute([$orderId]);
    if ($check->fetch()) {
        return;
    }
    $pdo->prepare('INSERT INTO refunds (return_id, order_id, amount, status, reason) VALUES (?,?,?,?,?)')
        ->execute([$returnId, $orderId, $amount, 'pending', $reason]);
}

function next_order_number(PDO $pdo, string $deliveryType, string $productCode): string
{
    require_transaction($pdo);
    if (!preg_match('/^[123]$/', $deliveryType) || !preg_match('/^\d{7}$/', $productCode)) {
        throw new AppException('The order number could not be prepared.');
    }
    // Lock the single counter row. LAST_INSERT_ID keeps the new value for this connection only.
    $pdo->prepare('UPDATE order_counters SET last_seq = LAST_INSERT_ID(last_seq + 1) WHERE id = 1')->execute();
    $seq = (int) $pdo->query('SELECT LAST_INSERT_ID()')->fetchColumn();
    if ($seq < 1 || $seq > 99999999) {
        throw new AppException('The order sequence is full. Contact the administrator.');
    }
    $number = $deliveryType . $productCode . str_pad((string) $seq, 8, '0', STR_PAD_LEFT);
    if (!preg_match('/^\d{16}$/', $number)) {
        throw new AppException('The order number could not be generated.');
    }
    return $number;
}

function place_order(int $userId, int $customerId, array $address, string $deliveryType, array $payment, bool $saveAddress): array
{
    $address = clean_address($address);
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $map = customer_cart_map($userId);
        if (!$map) {
            throw new AppException('Your cart is empty.');
        }
        $ids = array_keys($map);
        sort($ids, SORT_NUMERIC);
        $lines = [];
        foreach ($ids as $productId) {
            $stmt = $pdo->prepare('SELECT id, product_code, name, price, stock, status, category_id FROM products WHERE id = ? FOR UPDATE');
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            if ($product) {
                $cat = $pdo->prepare('SELECT status FROM categories WHERE id = ?');
                $cat->execute([(int) $product['category_id']]);
                $product['category_status'] = (string) $cat->fetchColumn();
            }
            $qty = (int) $map[$productId];
            if (!$product || $product['status'] !== 'active' || $product['category_status'] !== 'active') {
                throw new AppException('A product in your cart is no longer available.');
            }
            if ($qty < 1 || $qty > 20 || $qty > (int) $product['stock']) {
                throw new AppException($product['name'] . ' has only ' . (int) $product['stock'] . ' in stock.');
            }
            $lineCents = to_cents($product['price']) * $qty;
            $lines[] = [
                'id' => (int) $product['id'],
                'product_code' => $product['product_code'],
                'name' => $product['name'],
                'price' => from_cents(to_cents($product['price'])),
                'quantity' => $qty,
                'line_total' => from_cents($lineCents),
                'line_cents' => $lineCents,
            ];
        }

        $codes = array_column($lines, 'product_code');
        sort($codes, SORT_STRING);
        $primary = $codes[0];
        $subtotal = 0;
        foreach ($lines as $line) {
            $subtotal += $line['line_cents'];
        }
        $shipping = shipping_cents($subtotal);
        $total = $subtotal + $shipping;
        $number = next_order_number($pdo, $deliveryType, $primary);
        $orderStatus = $deliveryType === '2' ? 'pending' : 'confirmed';
        $paymentStatus = $deliveryType === '1' ? 'cleared' : 'pending';

        $pdo->prepare('INSERT INTO orders (order_number, customer_id, delivery_type, primary_product_code, ship_name, ship_phone, ship_address, ship_city, ship_state, ship_postal, subtotal, shipping_fee, total_amount, order_status, payment_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([
                $number,
                $customerId,
                $deliveryType,
                $primary,
                $address['full_name'],
                $address['phone'],
                $address['address_line'],
                $address['city'],
                $address['state'],
                $address['postal_code'],
                from_cents($subtotal),
                from_cents($shipping),
                from_cents($total),
                $orderStatus,
                $paymentStatus,
            ]);
        $orderId = (int) $pdo->lastInsertId();

        $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_code, product_name, unit_price, quantity, line_total) VALUES (?,?,?,?,?,?,?)');
        foreach ($lines as $line) {
            $itemStmt->execute([$orderId, $line['id'], $line['product_code'], $line['name'], $line['price'], $line['quantity'], $line['line_total']]);
            change_stock($pdo, $line['id'], -$line['quantity'], 'order', 'order', $orderId, $userId, 'Order ' . $number);
        }

        $pdo->prepare('INSERT INTO payments (order_id, method, amount, status, card_last4, card_brand, txn_reference, cheque_number, cheque_bank, cheque_date, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([
                $orderId,
                $payment['method'],
                from_cents($total),
                $payment['status'],
                $payment['last4'],
                $payment['brand'],
                $payment['txn'],
                $payment['cheque_number'],
                $payment['cheque_bank'],
                $payment['cheque_date'],
                $payment['notes'],
            ]);

        if ($saveAddress) {
            $count = $pdo->prepare('SELECT COUNT(*) FROM customer_addresses WHERE customer_id = ?');
            $count->execute([$customerId]);
            $isDefault = (int) $count->fetchColumn() === 0 ? 1 : 0;
            $pdo->prepare('INSERT INTO customer_addresses (customer_id, full_name, phone, address_line, city, state, postal_code, is_default) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$customerId, $address['full_name'], $address['phone'], $address['address_line'], $address['city'], $address['state'], $address['postal_code'], $isDefault]);
        }

        clear_db_cart($pdo, $userId);
        $pdo->commit();
        return ['id' => $orderId, 'order_number' => $number];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('The order could not be placed. Please try again.');
    }
}

function place_gift_box(int $userId, int $customerId, string $recipientName, string $phone, string $addressLine, int $quantity, string $color, string $size, string $shape): array
{
    $address = clean_address([
        'full_name' => $recipientName,
        'phone' => $phone,
        'address_line' => $addressLine,
        'city' => 'Karachi',
        'state' => 'Sindh',
        'postal_code' => '75500',
    ]);
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT id, product_code, name, price, stock, status, category_id FROM products WHERE product_code = ? FOR UPDATE');
        $stmt->execute(['0100006']);
        $product = $stmt->fetch();
        if (!$product || $product['status'] !== 'active') {
            throw new AppException('The gift box is not on the shelf right now.');
        }
        $cat = $pdo->prepare('SELECT status FROM categories WHERE id = ?');
        $cat->execute([(int) $product['category_id']]);
        if ((string) $cat->fetchColumn() !== 'active') {
            throw new AppException('The gift box is not on the shelf right now.');
        }
        if ($quantity < 1 || $quantity > 10) {
            throw new AppException('Choose between 1 and 10 boxes.');
        }
        $colors = ['Black', 'Gold', 'Ivory'];
        $sizes = ['Small', 'Medium', 'Large'];
        $shapes = ['Rectangle', 'Square', 'Round'];
        if (!in_array($color, $colors, true) || !in_array($size, $sizes, true) || !in_array($shape, $shapes, true)) {
            throw new AppException('Choose a colour, a size and a shape.');
        }
        if ((int) $product['stock'] < $quantity) {
            throw new AppException('Only ' . (int) $product['stock'] . ' gift boxes are in stock.');
        }
        $lineCents = to_cents($product['price']) * $quantity;
        $shipping = shipping_cents($lineCents);
        $total = $lineCents + $shipping;
        $number = next_order_number($pdo, '3', $product['product_code']);
        $pdo->prepare('INSERT INTO orders (order_number, customer_id, delivery_type, primary_product_code, ship_name, ship_phone, ship_address, ship_city, ship_state, ship_postal, subtotal, shipping_fee, total_amount, order_status, payment_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([
                $number,
                $customerId,
                '3',
                $product['product_code'],
                $address['full_name'],
                $address['phone'],
                $address['address_line'],
                $address['city'],
                $address['state'],
                $address['postal_code'],
                from_cents($lineCents),
                from_cents($shipping),
                from_cents($total),
                'confirmed',
                'pending',
            ]);
        $orderId = (int) $pdo->lastInsertId();
        $unit = from_cents(to_cents($product['price']));
        $label = $product['name'] . ' · ' . $color . ', ' . $size . ', ' . $shape;
        $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_code, product_name, unit_price, quantity, line_total) VALUES (?,?,?,?,?,?,?)')
            ->execute([$orderId, (int) $product['id'], $product['product_code'], $label, $unit, $quantity, from_cents($lineCents)]);
        change_stock($pdo, (int) $product['id'], -$quantity, 'order', 'order', $orderId, $userId, 'Gift box ' . $number);
        $pdo->prepare('INSERT INTO payments (order_id, method, amount, status, card_last4, card_brand, txn_reference, cheque_number, cheque_bank, cheque_date, notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$orderId, 'vpp', from_cents($total), 'pending', null, null, null, null, null, null, 'Gift box ' . $color . ', ' . $size . ', ' . $shape . ', qty ' . $quantity]);
        $pdo->commit();
        return ['id' => $orderId, 'order_number' => $number];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('The gift box could not be ordered. Please try again.');
    }
}

function cancel_order(int $orderId, int $customerId, string $reason): void
{
    $reason = trim($reason);
    if ($reason === '') {
        $reason = 'Cancelled by customer';
    }
    if (strlen($reason) > 255) {
        $reason = substr($reason, 0, 255);
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $order = lock_order($pdo, $orderId);
        if (!$order || (int) $order['customer_id'] !== $customerId) {
            throw new AppException('Order not found.');
        }
        if (!can_cancel($order)) {
            throw new AppException('This order can only be cancelled before it is dispatched.');
        }
        $pdo->prepare("UPDATE orders SET order_status = 'cancelled', cancel_reason = ? WHERE id = ?")->execute([$reason, $orderId]);
        restore_order_stock($pdo, $orderId, null, 'Cancelled before dispatch');
        if ($order['payment_status'] === 'cleared') {
            ensure_refund($pdo, $orderId, null, $order['total_amount'], 'Order cancelled before dispatch');
        } else {
            $pdo->prepare("UPDATE payments SET status = 'failed' WHERE order_id = ?")->execute([$orderId]);
            $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")->execute([$orderId]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('The order could not be cancelled.');
    }
}

function settle_cheque(int $orderId, string $action, int $adminId, string $notes): void
{
    if (!in_array($action, ['verified', 'cleared', 'failed'], true)) {
        throw new AppException('Unknown payment action.');
    }
    $notes = trim($notes);
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $order = lock_order($pdo, $orderId);
        $payStmt = $pdo->prepare('SELECT * FROM payments WHERE order_id = ? FOR UPDATE');
        $payStmt->execute([$orderId]);
        $payment = $payStmt->fetch();
        if (!$order || !$payment || $payment['method'] !== 'cheque') {
            throw new AppException('This order has no cheque payment.');
        }
        if (in_array($order['order_status'], ['dispatched', 'delivered', 'cancelled'], true)) {
            throw new AppException('This payment can no longer be changed.');
        }
        $now = date('Y-m-d H:i:s');
        if ($action === 'verified') {
            if ($payment['status'] !== 'pending') {
                throw new AppException('Only a pending cheque can be marked verified.');
            }
            $pdo->prepare("UPDATE payments SET status = 'verified', verified_by = ?, verified_at = ?, notes = ? WHERE id = ?")
                ->execute([$adminId, $now, $notes !== '' ? $notes : 'Cheque received and verified.', $payment['id']]);
            $pdo->prepare("UPDATE orders SET payment_status = 'verified' WHERE id = ?")->execute([$orderId]);
        } elseif ($action === 'cleared') {
            if ($payment['status'] !== 'verified') {
                throw new AppException('Verify the cheque before marking it cleared.');
            }
            $pdo->prepare("UPDATE payments SET status = 'cleared', verified_by = ?, verified_at = ?, notes = ? WHERE id = ?")
                ->execute([$adminId, $now, $notes !== '' ? $notes : 'Cheque cleared.', $payment['id']]);
            $pdo->prepare("UPDATE orders SET payment_status = 'cleared', order_status = 'confirmed' WHERE id = ?")->execute([$orderId]);
        } else {
            if ($payment['status'] === 'cleared') {
                throw new AppException('A cleared cheque cannot be failed from this screen.');
            }
            $pdo->prepare("UPDATE orders SET order_status = 'cancelled', payment_status = 'failed', cancel_reason = ? WHERE id = ?")
                ->execute(['Cheque was not cleared', $orderId]);
            $pdo->prepare("UPDATE payments SET status = 'failed', verified_by = ?, verified_at = ?, notes = ? WHERE id = ?")
                ->execute([$adminId, $now, $notes !== '' ? $notes : 'Cheque was rejected.', $payment['id']]);
            restore_order_stock($pdo, $orderId, $adminId, 'Cheque rejected');
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('The cheque could not be updated.');
    }
}

function mark_cash_collected(int $orderId, int $actorId): void
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $order = lock_order($pdo, $orderId);
        $payStmt = $pdo->prepare('SELECT * FROM payments WHERE order_id = ? FOR UPDATE');
        $payStmt->execute([$orderId]);
        $payment = $payStmt->fetch();
        if (!$order || !$payment || $payment['method'] !== 'vpp') {
            throw new AppException('This is not a cash-on-delivery order.');
        }
        if ($payment['status'] === 'cleared') {
            $pdo->commit();
            return;
        }
        if (!in_array($order['order_status'], ['dispatched', 'delivered'], true)) {
            throw new AppException('Record dispatch before collecting cash.');
        }
        $now = date('Y-m-d H:i:s');
        $pdo->prepare("UPDATE payments SET status = 'cleared', verified_by = ?, verified_at = ?, notes = 'Cash collected on delivery.' WHERE id = ?")
            ->execute([$actorId, $now, $payment['id']]);
        $pdo->prepare("UPDATE orders SET payment_status = 'cleared' WHERE id = ?")->execute([$orderId]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('The cash collection could not be saved.');
    }
}

function record_dispatch(int $orderId, int $actorId, ?int $employeeId, string $notes): void
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $order = lock_order($pdo, $orderId);
        if (!$order || !can_dispatch($order)) {
            throw new AppException('This order cannot be dispatched yet. Card and cheque payments must be cleared first.');
        }
        $now = date('Y-m-d H:i:s');
        $pdo->prepare("UPDATE orders SET order_status = 'dispatched', dispatch_date = ? WHERE id = ? AND order_status = 'confirmed'")
            ->execute([$now, $orderId]);
        $pdo->prepare('INSERT INTO dispatch_records (order_id, employee_id, actor_user_id, action, notes, action_date) VALUES (?,?,?,?,?,?)')
            ->execute([$orderId, $employeeId, $actorId, 'dispatched', trim($notes) !== '' ? trim($notes) : 'Parcel handed to delivery.', $now]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('Dispatch could not be saved.');
    }
}

function record_delivery(int $orderId, int $actorId, ?int $employeeId, string $notes, bool $delivered, bool $cashCollected): void
{
    $notes = trim($notes);
    if (strlen($notes) < 5) {
        throw new AppException('Write a short delivery report of at least 5 characters.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $order = lock_order($pdo, $orderId);
        if (!$order || !can_deliver($order)) {
            throw new AppException('Only a dispatched order can be updated with a delivery report.');
        }
        $now = date('Y-m-d H:i:s');
        if (!$delivered) {
            $pdo->prepare('INSERT INTO dispatch_records (order_id, employee_id, actor_user_id, action, notes, action_date) VALUES (?,?,?,?,?,?)')
                ->execute([$orderId, $employeeId, $actorId, 'delivery_failed', $notes, $now]);
            $pdo->commit();
            return;
        }
        $pdo->prepare("UPDATE orders SET order_status = 'delivered', delivery_date = ? WHERE id = ? AND order_status = 'dispatched'")
            ->execute([$now, $orderId]);
        $pdo->prepare('INSERT INTO dispatch_records (order_id, employee_id, actor_user_id, action, notes, action_date) VALUES (?,?,?,?,?,?)')
            ->execute([$orderId, $employeeId, $actorId, 'delivered', $notes, $now]);
        if ($cashCollected && $order['delivery_type'] === '3') {
            $payStmt = $pdo->prepare('SELECT * FROM payments WHERE order_id = ? FOR UPDATE');
            $payStmt->execute([$orderId]);
            $payment = $payStmt->fetch();
            if ($payment && $payment['status'] !== 'cleared') {
                $pdo->prepare("UPDATE payments SET status = 'cleared', verified_by = ?, verified_at = ?, notes = 'Cash collected on delivery.' WHERE id = ?")
                    ->execute([$actorId, $now, $payment['id']]);
                $pdo->prepare("UPDATE orders SET payment_status = 'cleared' WHERE id = ?")->execute([$orderId]);
            }
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('The delivery report could not be saved.');
    }
}

function create_return_request(int $customerId, int $orderId, string $type, string $reason): void
{
    if (!in_array($type, ['return', 'replacement'], true)) {
        throw new AppException('Choose return or replacement.');
    }
    $reason = trim($reason);
    if (strlen($reason) < 10) {
        throw new AppException('Describe the request in at least 10 characters.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $order = lock_order($pdo, $orderId);
        if (!$order || (int) $order['customer_id'] !== $customerId) {
            throw new AppException('Order not found.');
        }
        if (!can_request_return($order)) {
            throw new AppException('A return or replacement can be requested within 7 days after delivery.');
        }
        $open = $pdo->prepare("SELECT id FROM return_requests WHERE order_id = ? AND status <> 'rejected'");
        $open->execute([$orderId]);
        if ($open->fetch()) {
            throw new AppException('This order already has a return or replacement request.');
        }
        $pdo->prepare('INSERT INTO return_requests (order_id, customer_id, request_type, reason, status) VALUES (?,?,?,?,\'pending\')')
            ->execute([$orderId, $customerId, $type, $reason]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('The request could not be saved.');
    }
}

function review_return(int $returnId, string $decision, int $adminId, string $notes): void
{
    if (!in_array($decision, ['approved', 'rejected'], true)) {
        throw new AppException('Choose approve or reject.');
    }
    $notes = trim($notes);
    if ($decision === 'rejected' && strlen($notes) < 3) {
        throw new AppException('Add a short note when rejecting a request.');
    }
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('SELECT * FROM return_requests WHERE id = ? FOR UPDATE');
        $stmt->execute([$returnId]);
        $request = $stmt->fetch();
        if (!$request || $request['status'] !== 'pending') {
            throw new AppException('This request is no longer pending.');
        }
        $order = lock_order($pdo, (int) $request['order_id']);
        if (!$order) {
            throw new AppException('Order not found.');
        }
        $now = date('Y-m-d H:i:s');
        if ($decision === 'rejected') {
            $pdo->prepare("UPDATE return_requests SET status = 'rejected', admin_notes = ?, processed_by = ?, processed_at = ? WHERE id = ?")
                ->execute([$notes, $adminId, $now, $returnId]);
            $pdo->commit();
            return;
        }

        if ($request['request_type'] === 'replacement') {
            $qty = grouped_item_qty((int) $order['id']);
            foreach ($qty as $productId => $count) {
                change_stock($pdo, (int) $productId, (int) $count, 'return', 'return', $returnId, $adminId, 'Replacement restock');
            }
            foreach ($qty as $productId => $count) {
                change_stock($pdo, (int) $productId, -1 * (int) $count, 'replacement', 'return', $returnId, $adminId, 'Replacement issued');
            }
            $pdo->prepare("UPDATE return_requests SET status = 'completed', admin_notes = ?, processed_by = ?, processed_at = ? WHERE id = ?")
                ->execute([$notes !== '' ? $notes : 'Replacement issued from returned stock.', $adminId, $now, $returnId]);
        } else {
            foreach (grouped_item_qty((int) $order['id']) as $productId => $count) {
                change_stock($pdo, (int) $productId, (int) $count, 'return', 'return', $returnId, $adminId, 'Return approved');
            }
            if ($order['payment_status'] === 'cleared') {
                ensure_refund($pdo, (int) $order['id'], $returnId, $order['total_amount'], 'Approved return');
                $status = 'approved';
                $note = $notes !== '' ? $notes : 'Return approved. Refund is waiting to be issued.';
            } else {
                $status = 'completed';
                $note = $notes !== '' ? $notes : 'Return approved. No refund was due because payment was not cleared.';
            }
            $pdo->prepare('UPDATE return_requests SET status = ?, admin_notes = ?, processed_by = ?, processed_at = ? WHERE id = ?')
                ->execute([$status, $note, $adminId, $now, $returnId]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('The request could not be updated.');
    }
}

function issue_refund(int $refundId, int $adminId, string $notes): void
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("SELECT * FROM refunds WHERE id = ? FOR UPDATE");
        $stmt->execute([$refundId]);
        $refund = $stmt->fetch();
        if (!$refund || $refund['status'] !== 'pending') {
            throw new AppException('This refund is not waiting to be issued.');
        }
        $order = lock_order($pdo, (int) $refund['order_id']);
        if (!$order || $order['payment_status'] !== 'cleared') {
            throw new AppException('A refund can be issued only after payment was cleared.');
        }
        $now = date('Y-m-d H:i:s');
        $pdo->prepare("UPDATE refunds SET status = 'issued', issued_by = ?, issued_at = ?, notes = ? WHERE id = ?")
            ->execute([$adminId, $now, trim($notes) !== '' ? trim($notes) : 'Marked issued by administrator. This project does not send the refund to a bank.', $refundId]);
        $pdo->prepare("UPDATE payments SET status = 'refunded' WHERE order_id = ?")->execute([$order['id']]);
        $pdo->prepare("UPDATE orders SET payment_status = 'refunded' WHERE id = ?")->execute([$order['id']]);
        if (!empty($refund['return_id'])) {
            $pdo->prepare("UPDATE return_requests SET status = 'completed' WHERE id = ? AND status = 'approved'")
                ->execute([(int) $refund['return_id']]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($e instanceof AppException) {
            throw $e;
        }
        app_log($e->getMessage());
        throw new AppException('The refund could not be issued.');
    }
}

function generate_product_code(PDO $pdo, int $categoryId): string
{
    require_transaction($pdo);
    $cat = $pdo->prepare('SELECT code_prefix FROM categories WHERE id = ? FOR UPDATE');
    $cat->execute([$categoryId]);
    $prefix = $cat->fetchColumn();
    if (!$prefix || !preg_match('/^\d{2}$/', (string) $prefix)) {
        throw new AppException('Choose a category with a 2-digit code.');
    }
    $last = $pdo->prepare('SELECT product_code FROM products WHERE product_code LIKE ? ORDER BY product_code DESC LIMIT 1');
    $last->execute([$prefix . '%']);
    $code = $last->fetchColumn();
    $next = 1;
    if ($code && str_starts_with((string) $code, (string) $prefix)) {
        $next = (int) substr((string) $code, 2) + 1;
    }
    if ($next > 99999) {
        throw new AppException('No product numbers are left in this category.');
    }
    return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
}
