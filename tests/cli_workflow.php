<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs';
$_SERVER['HTTPS'] = 'off';
$_SERVER['SCRIPT_NAME'] = '/e_projject_ecomerce_website/index.php';

require __DIR__ . '/../includes/init.php';

$failed = 0;
function check(string $name, bool $ok, string $detail = ''): void
{
    global $failed;
    echo ($ok ? 'PASS' : 'FAIL') . ' ' . $name . ($detail !== '' ? ' — ' . $detail : '') . PHP_EOL;
    if (!$ok) {
        $failed++;
    }
}

$admin = db()->prepare('SELECT password_hash FROM users WHERE email = ?');
$admin->execute(['admin@paperlane.local']);
check('admin password', password_verify('Admin@123', (string) $admin->fetchColumn()));

$customerUser = db()->prepare('SELECT u.id AS user_id, c.id AS customer_id FROM users u JOIN customers c ON c.user_id = u.id WHERE u.email = ?');
$customerUser->execute(['customer@paperlane.local']);
$customer = $customerUser->fetch();
check('sample customer', (bool) $customer);

$pen = find_product_by_code('0200004');
$card = find_product_by_code('0100001');
check('sample products', $pen && $card && is_file(ROOT_PATH . '/uploads/products/birthday-card.jpg'));
$penStock = (int) $pen['stock'];
$cardStock = (int) $card['stock'];

$address = [
    'full_name' => 'Neha Shah',
    'phone' => '03009876543',
    'address_line' => '18 Lakeview Road',
    'city' => 'Karachi',
    'state' => 'Sindh',
    'postal_code' => '75500',
];

$declined = false;
try {
    prepare_payment('1', [
        'card_name' => 'Neha Shah',
        'card_number' => '4000000000000002',
        'card_expiry' => '12/30',
        'card_cvv' => '123',
    ]);
} catch (AppException $e) {
    $declined = str_contains($e->getMessage(), 'declined');
}
check('declined card creates no order', $declined);
check('stock unchanged after decline', (int) find_product_by_code('0200004')['stock'] === $penStock);

try {
    save_customer_qty((int) $customer['user_id'], (int) $pen['id'], 50);
    place_order((int) $customer['user_id'], (int) $customer['customer_id'], $address, '3', prepare_payment('3', []), false);
    check('oversell blocked', false);
} catch (AppException $e) {
    check('oversell blocked', str_contains($e->getMessage(), 'in stock'));
}
check('stock unchanged after oversell', (int) find_product_by_code('0200004')['stock'] === $penStock);
save_customer_qty((int) $customer['user_id'], (int) $pen['id'], 0);

save_customer_qty((int) $customer['user_id'], (int) $card['id'], 2);
$vppPay = prepare_payment('3', []);
$vpp = place_order((int) $customer['user_id'], (int) $customer['customer_id'], $address, '3', $vppPay, false);
$number = $vpp['order_number'];
check('vpp order number', (bool) preg_match('/^3\d{15}$/', $number), $number);
$parts = order_number_parts($number);
check('primary product in number', $parts['product_code'] === '0100001', $parts['product_code']);
check('card stock deducted once', (int) find_product_by_code('0100001')['stock'] === $cardStock - 2);

$orderRow = db()->prepare('SELECT * FROM orders WHERE id = ?');
$orderRow->execute([(int) $vpp['id']]);
$order = $orderRow->fetch();
check('vpp can dispatch before cash', can_dispatch($order));
check('stored total is server total', $order['total_amount'] === '659.00' || $order['total_amount'] === '560.00' || (float) $order['total_amount'] === (2 * 280) + 99);

cancel_order((int) $vpp['id'], (int) $customer['customer_id'], 'Changed my mind');
$orderRow->execute([(int) $vpp['id']]);
$cancelled = $orderRow->fetch();
check('cancelled before dispatch', $cancelled['order_status'] === 'cancelled');
check('stock restored after cancel', (int) find_product_by_code('0100001')['stock'] === $cardStock);
check('cannot dispatch cancelled', !can_dispatch($cancelled));

save_customer_qty((int) $customer['user_id'], (int) $pen['id'], 1);
$cheque = place_order((int) $customer['user_id'], (int) $customer['customer_id'], $address, '2', prepare_payment('2', [
    'cheque_number' => 'CHQ123456',
    'cheque_bank' => 'Habib Bank',
    'cheque_date' => date('Y-m-d'),
]), false);
check('cheque number starts with 2', str_starts_with($cheque['order_number'], '2'));
$orderRow->execute([(int) $cheque['id']]);
$chequeOrder = $orderRow->fetch();
check('cheque blocked before clearance', !can_dispatch($chequeOrder));
$adminId = (int) db()->query("SELECT id FROM users WHERE email = 'admin@paperlane.local'")->fetchColumn();
settle_cheque((int) $cheque['id'], 'verified', $adminId, 'Cheque received');
$orderRow->execute([(int) $cheque['id']]);
check('still blocked after verify', !can_dispatch($orderRow->fetch()));
settle_cheque((int) $cheque['id'], 'cleared', $adminId, 'Cheque cleared');
$orderRow->execute([(int) $cheque['id']]);
$cleared = $orderRow->fetch();
check('dispatch allowed after clear', can_dispatch($cleared));
$employee = db()->query("SELECT e.id, u.id AS user_id FROM employees e JOIN users u ON u.id = e.user_id WHERE u.email = 'employee@paperlane.local'")->fetch();
record_dispatch((int) $cheque['id'], (int) $employee['user_id'], (int) $employee['id'], 'Given to rider');
record_delivery((int) $cheque['id'], (int) $employee['user_id'], (int) $employee['id'], 'Handed to Neha at the door', true, false);
$orderRow->execute([(int) $cheque['id']]);
$delivered = $orderRow->fetch();
check('delivered', $delivered['order_status'] === 'delivered' && $delivered['delivery_date']);
check('cancel blocked after dispatch', !can_cancel($delivered));
check('return window open', can_request_return($delivered));
create_return_request((int) $customer['customer_id'], (int) $cheque['id'], 'return', 'The pen leaked in the sleeve.');
$returnId = (int) db()->query('SELECT id FROM return_requests ORDER BY id DESC LIMIT 1')->fetchColumn();
$penAfterSale = (int) find_product_by_code('0200004')['stock'];
review_return($returnId, 'approved', $adminId, 'Approved after checking the pen.');
check('return restocked', (int) find_product_by_code('0200004')['stock'] === $penAfterSale + 1);
$refund = db()->prepare("SELECT * FROM refunds WHERE order_id = ? AND status = 'pending'");
$refund->execute([(int) $cheque['id']]);
$refund = $refund->fetch();
check('refund waits for admin', (bool) $refund);
issue_refund((int) $refund['id'], $adminId, 'Recorded in the shop ledger.');
$orderRow->execute([(int) $cheque['id']]);
check('payment refunded', $orderRow->fetch()['payment_status'] === 'refunded');

save_customer_qty((int) $customer['user_id'], (int) $card['id'], 1);
save_customer_qty((int) $customer['user_id'], (int) $pen['id'], 1);
$mixed = place_order((int) $customer['user_id'], (int) $customer['customer_id'], $address, '1', prepare_payment('1', [
    'card_name' => 'Neha Shah',
    'card_number' => '4242424242424242',
    'card_expiry' => '12/30',
    'card_cvv' => '123',
]), false);
check('card order starts with 1', str_starts_with($mixed['order_number'], '1'));
check('primary code is the lower product id', order_number_parts($mixed['order_number'])['product_code'] === '0100001');
$pay = order_payment((int) $mixed['id']);
check('card stored without full number', $pay['card_last4'] === '4242' && $pay['status'] === 'cleared' && strlen((string) $pay['txn_reference']) > 4);
check('sequence increased', (int) order_number_parts($mixed['order_number'])['sequence'] > (int) order_number_parts($cheque['order_number'])['sequence']);

echo $failed === 0 ? "ALL PASSED\n" : "FAILURES $failed\n";
exit($failed === 0 ? 0 : 1);
