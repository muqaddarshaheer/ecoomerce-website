<?php
/**
 * HTTP checks against the local Apache site. Run from the command line only.
 * Expects database.sql and database/sample_data.sql. This script places and cancels an order.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$base = 'http://localhost/e_projject_ecomerce_website';
$cookie = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'paperlane-http.txt';
@unlink($cookie);

function http(string $method, string $url, array $fields = [], bool $follow = false): array
{
    global $cookie;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_COOKIEJAR => $cookie,
        CURLOPT_COOKIEFILE => $cookie,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_CUSTOMREQUEST => $method,
    ]);
    if ($fields) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    }
    $raw = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $headers = substr((string) $raw, 0, $headerSize);
    $body = substr((string) $raw, $headerSize);
    return ['status' => $status, 'headers' => $headers, 'body' => $body];
}

function token(string $html): string
{
    if (!preg_match('/name="csrf_token" value="([^"]+)"/', $html, $m)) {
        throw new RuntimeException('CSRF token not found');
    }
    return $m[1];
}

function location(string $headers): string
{
    return preg_match('/\r?\nLocation:\s*(\S+)/i', $headers, $m) ? $m[1] : '';
}

function stock(string $code): string
{
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=paperlane_shop;charset=utf8mb4', 'root', '');
    $stmt = $pdo->prepare('SELECT stock FROM products WHERE product_code = ?');
    $stmt->execute([$code]);
    return (string) $stmt->fetchColumn();
}

$fail = 0;
function check(string $label, bool $ok): void
{
    global $fail;
    echo ($ok ? 'PASS ' : 'FAIL ') . $label . PHP_EOL;
    if (!$ok) {
        $fail++;
    }
}

$guest = http('GET', $base . '/checkout.php');
check('guest checkout redirects to login', $guest['status'] === 302 && str_contains(location($guest['headers']), 'auth/login.php'));

$shop = http('GET', $base . '/shop.php?q=clips');
check('search finds Brass Paper Clips', str_contains($shop['body'], 'Brass Paper Clips'));
$filter = http('GET', $base . '/shop.php?min=100&max=200&category=desk-accessories');
check('price and category filter keeps the clips', str_contains($filter['body'], '0500001'));
check('filter hides the birthday card', !str_contains($filter['body'], '0100001'));

$faq = http('GET', $base . '/faq.php');
check('old FAQ address opens the agent', $faq['status'] === 302 && str_contains(location($faq['headers']), 'agent=1'));

$loginPage = http('GET', $base . '/auth/login.php');
$login = http('POST', $base . '/auth/login.php', [
    'csrf_token' => token($loginPage['body']),
    'email' => 'customer@paperlane.local',
    'password' => 'Customer@123',
]);
check('customer login continues', $login['status'] === 302 && (str_contains(location($login['headers']), 'index.php') || str_contains(location($login['headers']), 'checkout.php') || str_contains(location($login['headers']), 'customer/orders.php')));

$product = http('GET', $base . '/product.php?code=0500001');
$add = http('POST', $base . '/cart.php', [
    'csrf_token' => token($product['body']),
    'action' => 'add',
    'product_code' => '0500001',
    'quantity' => '1',
    'back' => 'cart.php',
]);
check('add to cart redirects to cart', str_contains(location($add['headers']), 'cart.php'));

$before = stock('0500001');
$checkout = http('GET', $base . '/checkout.php');
$decline = http('POST', $base . '/checkout.php', [
    'csrf_token' => token($checkout['body']),
    'address_choice' => 'new',
    'full_name' => 'Neha Shah',
    'phone' => '03009876543',
    'address_line' => '22 Garden Street',
    'city' => 'Karachi',
    'state' => 'Sindh',
    'postal_code' => '75500',
    'delivery_type' => '1',
    'card_name' => 'Neha Shah',
    'card_number' => '4000000000000002',
    'card_expiry' => '12/30',
    'card_cvv' => '123',
]);
check('declined card stays on checkout', $decline['status'] === 200 && str_contains($decline['body'], 'declined this card'));
check('declined card does not change stock', stock('0500001') === $before);

$checkout = http('GET', $base . '/checkout.php');
$vpp = http('POST', $base . '/checkout.php', [
    'csrf_token' => token($checkout['body']),
    'address_choice' => 'new',
    'full_name' => 'Neha Shah',
    'phone' => '03009876543',
    'address_line' => '22 Garden Street',
    'city' => 'Karachi',
    'state' => 'Sindh',
    'postal_code' => '75500',
    'delivery_type' => '3',
]);
$vppLocation = location($vpp['headers']);
check('VPP order redirects to success', str_contains($vppLocation, 'order-success.php?n='));
preg_match('/n=(\d{16})/', $vppLocation, $number);
$orderNumber = $number[1] ?? '';
check('VPP order number starts with 3 and the product id', str_starts_with($orderNumber, '30500001'));
check('VPP stock dropped by one', (int) stock('0500001') === (int) $before - 1);

$pdo = new PDO('mysql:host=127.0.0.1;dbname=paperlane_shop;charset=utf8mb4', 'root', '');
$idStmt = $pdo->prepare('SELECT id FROM orders WHERE order_number = ?');
$idStmt->execute([$orderNumber]);
$orderId = (int) $idStmt->fetchColumn();
$orderPage = http('GET', $base . '/customer/order.php?id=' . $orderId);
$cancel = http('POST', $base . '/customer/order.php?id=' . $orderId, [
    'csrf_token' => token($orderPage['body']),
    'action' => 'cancel',
    'reason' => 'Ordered the wrong item.',
]);
check('cancel redirects back to the order', str_contains(location($cancel['headers']), 'customer/order.php'));
$status = $pdo->prepare('SELECT order_status FROM orders WHERE id = ?');
$status->execute([$orderId]);
check('order is cancelled', $status->fetchColumn() === 'cancelled');
check('cancel restored stock', stock('0500001') === $before);

$feedbackPage = http('GET', $base . '/index.php');
$feedback = http('POST', $base . '/feedback.php', [
    'csrf_token' => token($feedbackPage['body']),
    'website' => '',
    'name' => 'Neha Shah',
    'email' => 'customer@paperlane.local',
    'subject' => 'Parcel packing',
    'rating' => '4',
    'message' => 'The card was packed well. Thank you for the quick delivery.',
]);
check('feedback redirects after save', str_contains(location($feedback['headers']), 'index.php'));
$count = (int) $pdo->query("SELECT COUNT(*) FROM feedback WHERE subject = 'Parcel packing'")->fetchColumn();
check('feedback row stored', $count >= 1);

echo $fail === 0 ? "ALL HTTP CHECKS PASSED\n" : "$fail CHECKS FAILED\n";
exit($fail === 0 ? 0 : 1);
