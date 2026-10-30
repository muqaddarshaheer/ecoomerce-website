<?php
require __DIR__ . '/includes/init.php';
echo "---03---\n";
$rows = db()->query("SELECT p.product_code, p.name, p.status, pi.file_name
    FROM products p
    LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_primary = 1
    WHERE p.product_code LIKE '03%'
    ORDER BY p.product_code")->fetchAll();
foreach ($rows as $r) {
    echo $r['product_code'] . "\t" . ($r['file_name'] ?? '-') . "\t" . $r['name'] . "\n";
}
echo "---ORDERS using skin/heavy---\n";
$codes = ['0400005','0400026','0400043','0400044','0400045','0400050','0400051','0400015','0400052','0400060'];
$in = implode(',', array_fill(0, count($codes), '?'));
$stmt = db()->prepare("SELECT p.product_code, COUNT(oi.id) c FROM products p LEFT JOIN order_items oi ON oi.product_id = p.id WHERE p.product_code IN ($in) GROUP BY p.product_code");
$stmt->execute($codes);
foreach ($stmt as $r) {
    echo $r['product_code'] . "\t" . $r['c'] . "\n";
}
