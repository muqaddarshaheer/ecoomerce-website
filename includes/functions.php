<?php

class AppException extends RuntimeException
{
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_log(string $message): void
{
    $dir = ROOT_PATH . '/logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $line = date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL;
    @file_put_contents($dir . '/app.log', $line, FILE_APPEND);
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        app_log('Database connection failed: ' . $e->getMessage());
        throw new AppException('Database connection failed. Start MySQL in XAMPP and check config/database.php.');
    }

    return $pdo;
}

function base_url(string $path = ''): string
{
    $base = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');
    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '' : $base) . '/' . $path;
}

function redirect(string $path): void
{
    $path = str_replace(["\r", "\n"], '', $path);
    if (preg_match('#^(https?:)?//#i', $path)) {
        $path = 'index.php';
    }
    header('Location: ' . base_url($path));
    exit;
}

function current_script(): string
{
    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    $base = rtrim(BASE_URL, '/');
    if ($base !== '' && str_starts_with(strtolower($script), strtolower($base))) {
        $script = substr($script, strlen($base));
    }
    return ltrim($script, '/');
}

function current_page(): string
{
    $path = current_script();
    $query = (string) ($_SERVER['QUERY_STRING'] ?? '');
    if ($query !== '' && preg_match('/^[A-Za-z0-9_&=%.\-]+$/', $query)) {
        return $path . '?' . $query;
    }
    return $path;
}

function safe_local_path(?string $path, string $default): string
{
    $path = str_replace(["\r", "\n", '\\'], '', (string) $path);
    if ($path === '' || str_contains($path, '..') || preg_match('#^(https?:)?//#i', $path)) {
        return $default;
    }
    if (!preg_match('#^[A-Za-z0-9_./?&=%\-]+$#', $path)) {
        return $default;
    }
    return ltrim($path, '/');
}

function set_flash(string $type, string $message): void
{
    $allowed = ['success', 'danger', 'warning', 'info'];
    $_SESSION['flash'] = [
        'type' => in_array($type, $allowed, true) ? $type : 'info',
        'message' => $message,
    ];
}

function take_flash(): ?array
{
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $sent = (string) ($_POST['csrf_token'] ?? '');
    $real = (string) ($_SESSION['csrf_token'] ?? '');
    if ($real === '' || $sent === '' || !hash_equals($real, $sent)) {
        set_flash('danger', 'The form expired. Please try again.');
        redirect(current_page());
    }
}

function post(string $key, int $max = 5000): string
{
    $value = trim((string) ($_POST[$key] ?? ''));
    return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
}

function query_text(string $key, int $max = 200): string
{
    $value = trim((string) ($_GET[$key] ?? ''));
    return function_exists('mb_substr') ? mb_substr($value, 0, $max) : substr($value, 0, $max);
}

function money($amount): string
{
    return CURRENCY . ' ' . number_format((float) $amount, 2);
}

function to_cents($amount): int
{
    return (int) round(((float) $amount) * 100);
}

function from_cents(int $cents): string
{
    $sign = $cents < 0 ? '-' : '';
    $cents = abs($cents);
    return $sign . number_format($cents / 100, 2, '.', '');
}

function shipping_cents(int $subtotalCents): int
{
    if ($subtotalCents <= 0 || $subtotalCents >= to_cents(FREE_SHIPPING_OVER)) {
        return 0;
    }
    return to_cents(SHIPPING_FEE);
}

function format_date(?string $value): string
{
    if (!$value) {
        return '—';
    }
    $time = strtotime($value);
    return $time ? date('d M Y, h:i A', $time) : '—';
}

function format_day(?string $value): string
{
    if (!$value) {
        return '—';
    }
    $time = strtotime($value);
    return $time ? date('d M Y', $time) : '—';
}

function valid_phone(string $phone): bool
{
    $digits = preg_replace('/\D/', '', $phone);
    return strlen($digits) >= 10 && strlen($digits) <= 15;
}

function valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 150;
}

function password_problem(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Use at least 8 characters.';
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        return 'Use at least one letter and one number.';
    }
    if (strlen($password) > 72) {
        return 'Use a password of 72 characters or fewer.';
    }
    return null;
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text === '' ? 'item' : $text;
}

function like_term(string $value): string
{
    $value = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    return '%' . $value . '%';
}

function status_badge(string $status): string
{
    $known = [
        'pending', 'confirmed', 'verified', 'cleared', 'dispatched', 'delivered',
        'cancelled', 'failed', 'refunded', 'approved', 'rejected', 'completed',
        'new', 'reviewed', 'active', 'inactive', 'issued',
    ];
    $key = in_array($status, $known, true) ? $status : 'other';
    $label = ucwords(str_replace('_', ' ', $status));
    return '<span class="state state-' . $key . '">' . e($label) . '</span>';
}

function state_key(): string
{
    $items = [
        'pending' => 'Waiting',
        'confirmed' => 'Accepted',
        'cleared' => 'Paid',
        'dispatched' => 'On the way',
        'delivered' => 'Arrived',
        'cancelled' => 'Stopped',
    ];
    $html = '<ul class="state-key">';
    foreach ($items as $status => $meaning) {
        $html .= '<li>' . status_badge($status) . '<span>' . e($meaning) . '</span></li>';
    }
    $html .= '</ul>';
    return $html;
}

function delivery_types(): array
{
    return [
        '1' => [
            'key' => 'credit_card',
            'label' => 'Credit card',
            'hint' => 'Dispatched only after the simulated payment is accepted.',
        ],
        '2' => [
            'key' => 'cheque',
            'label' => 'Cheque',
            'hint' => 'Dispatched only after staff verify the cheque and mark it cleared.',
        ],
        '3' => [
            'key' => 'vpp',
            'label' => 'VPP / Cash on delivery',
            'hint' => 'The order can leave the shop before cash is collected.',
        ],
    ];
}

function delivery_label(string $type): string
{
    $types = delivery_types();
    return $types[$type]['label'] ?? 'Unknown';
}

function payment_method_label(string $method): string
{
    return [
        'credit_card' => 'Credit card',
        'cheque' => 'Cheque',
        'vpp' => 'VPP / Cash on delivery',
    ][$method] ?? $method;
}

function order_number_parts(string $number): array
{
    return [
        'delivery' => substr($number, 0, 1),
        'product_code' => substr($number, 1, 7),
        'sequence' => substr($number, 8, 8),
    ];
}

function active_categories(): array
{
    return db()->query("SELECT id, name, slug, code_prefix, description, image FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
}

function media_url(string $folder, ?string $file): ?string
{
    if ($folder !== 'products' && $folder !== 'categories') {
        return null;
    }
    $file = basename((string) $file);
    if ($file === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $file)) {
        return null;
    }
    $path = ROOT_PATH . '/uploads/' . $folder . '/' . $file;
    if (!is_file($path)) {
        return null;
    }
    return base_url('uploads/' . $folder . '/' . rawurlencode($file));
}

function hero_url(): ?string
{
    return is_file(ROOT_PATH . '/assets/images/banners/hero.jpg')
        ? base_url('assets/images/banners/hero.jpg')
        : null;
}

function product_sql(): string
{
    return "SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.status AS category_status,
        (SELECT pi.file_name FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) AS image,
        (SELECT pi.file_name FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 0 ORDER BY pi.id ASC LIMIT 1) AS image_alt
        FROM products p
        JOIN categories c ON c.id = p.category_id";
}

function find_product(int $id): ?array
{
    $stmt = db()->prepare(product_sql() . ' WHERE p.id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function find_product_by_code(string $code): ?array
{
    if (!preg_match('/^\d{7}$/', $code)) {
        return null;
    }
    $stmt = db()->prepare(product_sql() . ' WHERE p.product_code = ?');
    $stmt->execute([$code]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function product_gallery(int $productId): array
{
    $stmt = db()->prepare('SELECT id, file_name, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC');
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function picture_or_fallback(?string $url, string $label, string $class = ''): string
{
    if ($url) {
        return '<img src="' . e($url) . '" alt="' . e($label) . '" class="' . e($class) . '" loading="lazy">';
    }
    $letter = strtoupper(substr($label, 0, 1));
    return '<div class="img-fallback ' . e($class) . '" role="img" aria-label="' . e($label) . '">' . e($letter) . '</div>';
}

function product_card(array $product): void
{
    $url = base_url('product.php?code=' . urlencode($product['product_code']));
    $image = media_url('products', $product['image'] ?? null);
    $imageAlt = media_url('products', $product['image_alt'] ?? null);
    if (!$imageAlt) {
        $imageAlt = $image;
    }
    $stock = (int) $product['stock'];
    $blurb = preg_replace('/\s+/', ' ', (string) ($product['description'] ?? ''));
    $blurb = function_exists('mb_substr') ? mb_substr($blurb, 0, 180) : substr($blurb, 0, 180);
    ?>
    <article class="product-card rise">
        <button type="button" class="product-media quick-open"
            data-name="<?= e($product['name']) ?>"
            data-price="<?= e(money($product['price'])) ?>"
            data-code="<?= e($product['product_code']) ?>"
            data-stock="<?= $stock ?>"
            data-image="<?= e((string) $image) ?>"
            data-warranty="<?= e($product['warranty_info'] ?? '') ?>"
            data-blurb="<?= e($blurb) ?>"
            data-url="<?= e($url) ?>"
            aria-label="Quick view <?= e($product['name']) ?>">
            <span class="shot shot-main"><?= picture_or_fallback($image, $product['name']) ?></span>
            <span class="shot shot-next<?= $imageAlt === $image ? ' shot-crop' : '' ?>"><?= picture_or_fallback($imageAlt, $product['name'] . ' another view') ?></span>
            <?php
            $wear = '';
            if (preg_match('/^Men\b/i', (string) $product['name'])) {
                $wear = 'Men';
            } elseif (preg_match('/^Women\b/i', (string) $product['name'])) {
                $wear = 'Women';
            }
            ?>
            <?php if ($wear !== ''): ?>
                <span class="wear-flag <?= strtolower($wear) ?>"><?= e($wear) ?></span>
            <?php endif; ?>
            <?php if ($stock < 1): ?>
                <span class="stock-flag">Out of stock</span>
            <?php elseif ($stock <= 5): ?>
                <span class="stock-flag low">Only <?= $stock ?> left</span>
            <?php endif; ?>
        </button>
        <div class="product-body">
            <p class="eyebrow"><?= e($product['category_name']) ?></p>
            <h3><a href="<?= e($url) ?>"><?= e($product['name']) ?></a></h3>
            <div class="product-meta">
                <span class="price"><?= e(money($product['price'])) ?></span>
                <button type="button" class="btn btn-forest btn-sm card-view quick-open"
                    data-name="<?= e($product['name']) ?>"
                    data-price="<?= e(money($product['price'])) ?>"
                    data-code="<?= e($product['product_code']) ?>"
                    data-stock="<?= $stock ?>"
                    data-image="<?= e((string) $image) ?>"
                    data-warranty="<?= e($product['warranty_info'] ?? '') ?>"
                    data-blurb="<?= e($blurb) ?>"
                    data-url="<?= e($url) ?>">View</button>
            </div>
        </div>
    </article>
    <?php
}

function pagination_html(int $page, int $pages, string $path, array $params): string
{
    if ($pages <= 1) {
        return '';
    }
    $start = max(1, $page - 3);
    $end = min($pages, $page + 3);
    $html = '<nav class="mt-4" aria-label="Pages"><ul class="pagination">';
    $link = function (int $number, string $label, bool $active = false, bool $disabled = false) use ($path, $params): string {
        $params['page'] = $number;
        $href = base_url($path) . '?' . http_build_query($params);
        $class = 'page-item' . ($active ? ' active' : '') . ($disabled ? ' disabled' : '');
        return '<li class="' . $class . '"><a class="page-link" href="' . e($href) . '">' . $label . '</a></li>';
    };
    $html .= $link(max(1, $page - 1), 'Prev', false, $page <= 1);
    for ($i = $start; $i <= $end; $i++) {
        $html .= $link($i, (string) $i, $i === $page);
    }
    $html .= $link(min($pages, $page + 1), 'Next', false, $page >= $pages);
    return $html . '</ul></nav>';
}

function store_uploaded_image(array $file, string $folder): string
{
    if ($folder !== 'products' && $folder !== 'categories') {
        throw new AppException('Invalid upload folder.');
    }
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE) {
        throw new AppException('Choose an image to upload.');
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new AppException('The image could not be uploaded.');
    }
    if ((int) ($file['size'] ?? 0) > MAX_UPLOAD_BYTES || (int) ($file['size'] ?? 0) < 1) {
        throw new AppException('Each image must be 2 MB or smaller.');
    }
    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new AppException('Invalid upload.');
    }
    $info = @getimagesize($tmp);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if ($info === false || !isset($types[$info[2] ?? 0])) {
        throw new AppException('Use a JPG, PNG, WEBP or GIF image.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $types[$info[2]];
    $dir = ROOT_PATH . '/uploads/' . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        throw new AppException('The upload folder is missing.');
    }
    if (!move_uploaded_file($tmp, $dir . '/' . $name)) {
        throw new AppException('The image could not be saved.');
    }
    return $name;
}

function normalize_files(array $files): array
{
    if (!isset($files['name']) || !is_array($files['name'])) {
        return isset($files['name']) ? [$files] : [];
    }
    $out = [];
    foreach ($files['name'] as $i => $name) {
        if ((int) ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $out[] = [
            'name' => $name,
            'type' => $files['type'][$i] ?? '',
            'tmp_name' => $files['tmp_name'][$i] ?? '',
            'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$i] ?? 0,
        ];
    }
    return $out;
}

function delete_upload(string $folder, string $file): void
{
    if ($folder !== 'products' && $folder !== 'categories') {
        return;
    }
    $file = basename($file);
    $path = ROOT_PATH . '/uploads/' . $folder . '/' . $file;
    if (is_file($path)) {
        @unlink($path);
    }
}

function clean_address(array $src): array
{
    $address = [
        'full_name' => trim((string) ($src['full_name'] ?? '')),
        'phone' => trim((string) ($src['phone'] ?? '')),
        'address_line' => trim((string) ($src['address_line'] ?? '')),
        'city' => trim((string) ($src['city'] ?? '')),
        'state' => trim((string) ($src['state'] ?? '')),
        'postal_code' => trim((string) ($src['postal_code'] ?? '')),
    ];
    if ($address['full_name'] === '' || strlen($address['full_name']) > 100) {
        throw new AppException('Enter the recipient name.');
    }
    if (!valid_phone($address['phone'])) {
        throw new AppException('Enter a valid phone number.');
    }
    if ($address['address_line'] === '' || strlen($address['address_line']) > 255) {
        throw new AppException('Enter the street address.');
    }
    if ($address['city'] === '' || strlen($address['city']) > 80 || $address['state'] === '' || strlen($address['state']) > 80) {
        throw new AppException('Enter the city and state.');
    }
    if (!preg_match('/^[A-Za-z0-9 \-]{3,12}$/', $address['postal_code'])) {
        throw new AppException('Enter a valid postal code.');
    }
    return $address;
}

function card_network(string $number): ?string
{
    if (preg_match('/^4\d{15}$/', $number)) {
        return 'Visa';
    }
    if (preg_match('/^5[1-5]\d{14}$/', $number)) {
        return 'Mastercard';
    }
    return null;
}

function luhn_valid(string $number): bool
{
    $sum = 0;
    $alt = false;
    for ($i = strlen($number) - 1; $i >= 0; $i--) {
        $n = (int) $number[$i];
        if ($alt) {
            $n *= 2;
            if ($n > 9) {
                $n -= 9;
            }
        }
        $sum += $n;
        $alt = !$alt;
    }
    return $sum % 10 === 0;
}

function prepare_payment(string $deliveryType, array $post): array
{
    $types = delivery_types();
    if (!isset($types[$deliveryType])) {
        throw new AppException('Choose a payment method.');
    }

    if ($deliveryType === '1') {
        $number = preg_replace('/\D/', '', (string) ($post['card_number'] ?? ''));
        $expiry = trim((string) ($post['card_expiry'] ?? ''));
        $cvv = trim((string) ($post['card_cvv'] ?? ''));
        $name = trim((string) ($post['card_name'] ?? ''));
        if ($name === '') {
            throw new AppException('Enter the name on the card.');
        }
        if (!preg_match('/^\d{16}$/', $number)) {
            throw new AppException('Enter a 16-digit card number.');
        }
        $chosen = strtolower(trim((string) ($post['card_brand'] ?? '')));
        $network = card_network($number);
        if ($network === null) {
            throw new AppException('This shop accepts only two cards. Visa numbers start with 4. Mastercard numbers start with 51 to 55.');
        }
        if ($chosen !== '' && $chosen !== strtolower($network)) {
            throw new AppException('You chose ' . ucfirst($chosen) . ', but this number starts like a ' . $network . ' card.');
        }
        if (!luhn_valid($number)) {
            throw new AppException('That number does not pass the card check. No order was created.');
        }
        if (in_array($number, ['4000000000000002', '4000000000009995'], true)) {
            throw new AppException('The simulated bank declined this card. No order was created. A Visa test number is 4242 4242 4242 4242. A Mastercard test number is 5555 5555 5555 4444.');
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry, $match)) {
            throw new AppException('Enter the expiry as MM/YY.');
        }
        $expEnd = strtotime(sprintf('%04d-%02d-01 +1 month', 2000 + (int) $match[2], (int) $match[1]));
        if ($expEnd === false || $expEnd <= time()) {
            throw new AppException('This card has expired.');
        }
        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            throw new AppException('Enter the card security code.');
        }
        $last4 = substr($number, -4);
        $brand = $network;
        $number = '';
        $cvv = '';
        return [
            'method' => 'credit_card',
            'status' => 'cleared',
            'last4' => $last4,
            'brand' => $brand,
            'txn' => 'SIM' . strtoupper(bin2hex(random_bytes(5))),
            'cheque_number' => null,
            'cheque_bank' => null,
            'cheque_date' => null,
            'notes' => 'Simulated card payment. The full card number and CVV were not stored.',
        ];
    }

    if ($deliveryType === '2') {
        $chequeNo = trim((string) ($post['cheque_number'] ?? ''));
        $bank = trim((string) ($post['cheque_bank'] ?? ''));
        $date = trim((string) ($post['cheque_date'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9\-]{6,20}$/', $chequeNo)) {
            throw new AppException('Enter a cheque number of 6 to 20 letters or digits.');
        }
        if (strlen($bank) < 2 || strlen($bank) > 100) {
            throw new AppException('Enter the bank name.');
        }
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            throw new AppException('Enter the cheque date.');
        }
        return [
            'method' => 'cheque',
            'status' => 'pending',
            'last4' => null,
            'brand' => null,
            'txn' => null,
            'cheque_number' => $chequeNo,
            'cheque_bank' => $bank,
            'cheque_date' => $date,
            'notes' => 'Waiting for staff to verify this cheque.',
        ];
    }

    return [
        'method' => 'vpp',
        'status' => 'pending',
        'last4' => null,
        'brand' => null,
        'txn' => null,
        'cheque_number' => null,
        'cheque_bank' => null,
        'cheque_date' => null,
        'notes' => 'Cash is collected on delivery.',
    ];
}

function render_flash(): void
{
    $flash = take_flash();
    if (!$flash) {
        return;
    }
    echo '<div class="container pt-3"><div class="alert alert-' . e($flash['type']) . ' mb-0">' . e($flash['message']) . '</div></div>';
}

function page_head(string $title): void
{
    ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · <?= e(APP_NAME) ?></title>
    <link rel="icon" href="<?= e(base_url('assets/images/logo.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,650&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(base_url('assets/vendor/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>?v=<?= (int) @filemtime(ROOT_PATH . '/assets/css/style.css') ?>">
    <?php
}

function page_scripts(): void
{
    ?>
    <script src="<?= e(base_url('assets/vendor/jquery.min.js')) ?>"></script>
    <script src="<?= e(base_url('assets/vendor/bootstrap.bundle.min.js')) ?>"></script>
    <script src="<?= e(base_url('assets/js/main.js')) ?>?v=<?= (int) @filemtime(ROOT_PATH . '/assets/js/main.js') ?>"></script>
    <?php
}
