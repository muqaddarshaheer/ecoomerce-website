<?php
require_once __DIR__ . '/includes/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verify_csrf();
$phone = trim((string) ($_POST['phone'] ?? ''));
$addressLine = trim((string) ($_POST['address_line'] ?? ''));
$quantity = (int) ($_POST['quantity'] ?? 1);
$color = trim((string) ($_POST['box_color'] ?? ''));
$size = trim((string) ($_POST['box_size'] ?? ''));
$shape = trim((string) ($_POST['box_shape'] ?? ''));
$_SESSION['gift_draft'] = [
    'phone' => $phone,
    'address' => $addressLine,
    'quantity' => $quantity,
    'color' => $color,
    'size' => $size,
    'shape' => $shape,
];

if (!valid_phone($phone)) {
    set_flash('danger', 'Enter a valid contact number.');
    redirect('index.php?gift=1');
}
if ($addressLine === '' || strlen($addressLine) > 255) {
    set_flash('danger', 'Enter the delivery address.');
    redirect('index.php?gift=1');
}

$user = current_user();
if (!$user || $user['role'] !== 'customer') {
    $_SESSION['after_login'] = 'index.php?gift=1';
    set_flash('warning', 'Log in as a customer, then send the gift box.');
    redirect('auth/login.php');
}

$customer = customer_by_user((int) $user['id']);
if (!$customer) {
    set_flash('danger', 'Customer profile is missing.');
    redirect('index.php');
}

try {
    $placed = place_gift_box((int) $user['id'], (int) $customer['id'], (string) $user['full_name'], $phone, $addressLine, $quantity, $color, $size, $shape);
} catch (AppException $e) {
    set_flash('danger', $e->getMessage());
    redirect('index.php?gift=1');
}

unset($_SESSION['gift_draft']);
set_flash('success', 'Gift box ordered. Number ' . $placed['order_number'] . '. Pay cash when it arrives.');
redirect('customer/order.php?id=' . $placed['id']);
