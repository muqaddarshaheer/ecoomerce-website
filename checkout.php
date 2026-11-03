<?php
require_once __DIR__ . '/includes/init.php';

$user = require_customer();
$cart = cart_details();
if (!$cart['ready']) {
    set_flash('warning', 'Your cart needs attention before checkout.');
    redirect('cart.php');
}

$addressStmt = db()->prepare('SELECT * FROM customer_addresses WHERE customer_id = ? ORDER BY is_default DESC, id DESC');
$addressStmt->execute([(int) $user['customer_id']]);
$saved = $addressStmt->fetchAll();
$error = '';
$pickedPay = '3';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $choice = post('address_choice', 20);
        if ($choice !== '' && $choice !== 'new' && ctype_digit($choice)) {
            $one = db()->prepare('SELECT * FROM customer_addresses WHERE id = ? AND customer_id = ?');
            $one->execute([(int) $choice, (int) $user['customer_id']]);
            $row = $one->fetch();
            if (!$row) {
                throw new AppException('Choose a saved address.');
            }
            $address = $row;
            $save = false;
        } else {
            $address = $_POST;
            $save = isset($_POST['save_address']);
        }
        $delivery = post('delivery_type', 1);
        if (isset(delivery_types()[$delivery])) {
            $pickedPay = $delivery;
        }
        $payment = prepare_payment($delivery, $_POST);
        unset($_POST['card_number'], $_POST['card_cvv']);
        $result = place_order((int) $user['id'], (int) $user['customer_id'], $address, $delivery, $payment, $save);
        redirect('order-success.php?n=' . $result['order_number']);
    } catch (AppException $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Checkout';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <h1>Checkout</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="row g-4">
        <?= csrf_field() ?>
        <div class="col-lg-7">
            <div class="soft-card mb-3">
                <h2 class="h5">Delivery address</h2>
                <?php if ($saved): ?>
                    <?php $hasDefault = false; foreach ($saved as $row) { if ((int) $row['is_default'] === 1) { $hasDefault = true; } } ?>
                    <?php foreach ($saved as $rowIndex => $row): ?>
                        <label class="d-block border rounded p-2 mb-2">
                            <input type="radio" name="address_choice" value="<?= (int) $row['id'] ?>" <?= ((int) $row['is_default'] === 1 || (!$hasDefault && $rowIndex === 0)) ? 'checked' : '' ?>>
                            <strong><?= e($row['full_name']) ?></strong>, <?= e($row['phone']) ?><br>
                            <?= e($row['address_line']) ?>, <?= e($row['city']) ?>, <?= e($row['state']) ?> <?= e($row['postal_code']) ?>
                        </label>
                    <?php endforeach; ?>
                    <label class="d-block mb-2"><input type="radio" name="address_choice" value="new" <?= ($error !== '' && (string) ($_POST['address_choice'] ?? '') === 'new') ? 'checked' : '' ?>> Use a new address</label>
                <?php endif; ?>
                <?php
                $addressDraft = [
                    'full_name' => (string) $user['full_name'],
                    'phone' => (string) $user['phone'],
                    'address_line' => '',
                    'city' => 'Karachi',
                    'state' => 'Sindh',
                    'postal_code' => '',
                ];
                if ($error !== '') {
                    foreach ($addressDraft as $addressKey => $addressValue) {
                        if (isset($_POST[$addressKey])) {
                            $addressDraft[$addressKey] = (string) $_POST[$addressKey];
                        }
                    }
                }
                ?>
                <?php $showNewAddress = !$saved || ($error !== '' && (string) ($_POST['address_choice'] ?? '') === 'new'); ?>
                <?php $addressRequired = $showNewAddress ? ' required' : ''; ?>
                <div id="new-address" class="<?= $showNewAddress ? '' : 'd-none' ?>">
                    <p class="small text-secondary">Delivery is inside Karachi. <?= e(money(SHIPPING_FEE)) ?>, free from <?= e(money(FREE_SHIPPING_OVER)) ?>.</p>
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Recipient</label><input class="form-control" name="full_name" value="<?= e($addressDraft['full_name']) ?>"<?= $addressRequired ?>></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($addressDraft['phone']) ?>" inputmode="tel"<?= $addressRequired ?>></div>
                        <div class="col-12"><label class="form-label">Email</label><input class="form-control" value="<?= e($user['email'] ?? '') ?>" readonly></div>
                        <div class="col-12"><label class="form-label">Address</label><input class="form-control" name="address_line" value="<?= e($addressDraft['address_line']) ?>"<?= $addressRequired ?>></div>
                        <div class="col-md-4"><label class="form-label">City</label><input class="form-control" name="city" value="<?= e($addressDraft['city']) ?>"<?= $addressRequired ?>></div>
                        <div class="col-md-4"><label class="form-label">State</label><input class="form-control" name="state" value="<?= e($addressDraft['state']) ?>"<?= $addressRequired ?>></div>
                        <div class="col-md-4"><label class="form-label">Postal code</label><input class="form-control" name="postal_code" value="<?= e($addressDraft['postal_code']) ?>" placeholder="75500"<?= $addressRequired ?>></div>
                    </div>
                    <label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="save_address" value="1" checked> Save this address</label>
                </div>
            </div>
            <div class="soft-card">
                <h2 class="h5">How do you want to pay?</h2>
                <p class="lane-note">Pay on delivery is the quickest. Card and cheque wait until the shop clears the payment, then the parcel can leave.</p>
                <div class="pay-choices">
                <?php foreach (delivery_types() as $digit => $type): ?>
                    <label class="pay-choice">
                        <input type="radio" name="delivery_type" value="<?= e((string) $digit) ?>" <?= (string) $digit === $pickedPay ? 'checked' : '' ?>>
                        <strong><?= e($type['label']) ?></strong>
                        <span><?= (string) $digit === '3' ? 'Fastest. Pay the rider in cash.' : e($type['hint']) ?></span>
                    </label>
                <?php endforeach; ?>
                </div>
                <div data-pay-panel="1" class="mt-3">
                    <div class="alert alert-warning">Simulated card. Visa starts with 4. Mastercard starts with 51–55. Try <strong>4242 4242 4242 4242</strong> or <strong>5555 5555 5555 4444</strong>. The full number and CVV are not stored.</div>
                    <div class="row g-2">
                        <div class="col-12">
                            <span class="form-label d-block">Card</span>
                            <label class="me-3"><input type="radio" name="card_brand" value="visa"> Visa</label>
                            <label><input type="radio" name="card_brand" value="mastercard"> Mastercard</label>
                        </div>
                        <div class="col-12"><label class="form-label">Name on card</label><input class="form-control" name="card_name" autocomplete="off"></div>
                        <div class="col-12"><label class="form-label">Card number</label><input class="form-control" name="card_number" inputmode="numeric" autocomplete="off" placeholder="Starts with 4 or 51–55"></div>
                        <div class="col-6"><label class="form-label">Expiry</label><input class="form-control" name="card_expiry" placeholder="MM/YY" autocomplete="off"></div>
                        <div class="col-6"><label class="form-label">CVV</label><input class="form-control" name="card_cvv" inputmode="numeric" autocomplete="off"></div>
                    </div>
                </div>
                <div data-pay-panel="2" class="d-none mt-3">
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">Cheque number</label><input class="form-control" name="cheque_number"></div>
                        <div class="col-md-4"><label class="form-label">Bank</label><input class="form-control" name="cheque_bank"></div>
                        <div class="col-md-4"><label class="form-label">Cheque date</label><input class="form-control" type="date" name="cheque_date"></div>
                    </div>
                </div>
                <div data-pay-panel="3" class="d-none mt-3">
                    <p class="mb-0">Pay the delivery person in cash. The order can be dispatched before the cash is marked collected.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="summary-box">
                <h2 class="h5">Your order</h2>
                <?php foreach ($cart['items'] as $item): ?>
                    <div class="check-line">
                        <?= picture_or_fallback(media_url('products', $item['image'] ?? null), $item['name']) ?>
                        <span><?= e($item['name']) ?> × <?= (int) $item['quantity'] ?><?php $customText = gift_custom_summary($item); if ($customText !== '') { echo '<br><small>' . e($customText) . '</small>'; } ?></span>
                        <b><?= e(money($item['line_total'])) ?></b>
                    </div>
                <?php endforeach; ?>
                <hr>
                <p class="d-flex justify-content-between"><span>Goods</span><span><?= e(money($cart['subtotal'])) ?></span></p>
                <p class="d-flex justify-content-between"><span>Delivery</span><span><?= (float) $cart['shipping'] <= 0 ? 'Free' : e(money($cart['shipping'])) ?></span></p>
                <p class="d-flex justify-content-between"><strong>To pay</strong><strong><?= e(money($cart['total'])) ?></strong></p>
                <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                <button class="btn btn-clay w-100" type="submit">Place order</button>
                <p class="small text-secondary mt-2 mb-0">One step. The shop checks the address, the stock and the total again when you press this.</p>
            </div>
        </div>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
