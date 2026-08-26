<?php
// ============================================================
// api/coupons/apply.php — Validate & Apply Coupon Code
// ============================================================
require_once __DIR__ . '/../config.php';

$code  = strtoupper(trim($body['code'] ?? $_POST['code'] ?? ''));
$total = (float)($body['total'] ?? $_POST['total'] ?? 0);

if (!$code) {
    respond(['success' => false, 'error' => 'Coupon code is required'], 400);
}

$stmt = $pdo->prepare('SELECT * FROM coupons WHERE code = ? AND is_active = 1');
$stmt->execute([$code]);
$coupon = $stmt->fetch();

if (!$coupon) {
    respond(['success' => false, 'error' => 'Invalid or expired promo code'], 404);
}

if ($coupon['expiry_date'] && strtotime($coupon['expiry_date']) < time()) {
    respond(['success' => false, 'error' => 'This promo code has expired'], 400);
}

$minSpend = (float)($coupon['min_spend'] ?? 0);
if ($total > 0 && $total < $minSpend) {
    respond(['success' => false, 'error' => 'Minimum order amount for code ' . $code . ' is Rs. ' . number_format($minSpend, 2)], 400);
}

$discount = 0;
if ((float)$coupon['discount_percent'] > 0) {
    $discount = ($total * (float)$coupon['discount_percent']) / 100;
} else if ((float)$coupon['discount_amount'] > 0) {
    $discount = (float)$coupon['discount_amount'];
}

respond([
    'success' => true,
    'data' => [
        'code' => $coupon['code'],
        'discount_percent' => (float)$coupon['discount_percent'],
        'discount_amount' => (float)$coupon['discount_amount'],
        'calculated_discount' => round($discount, 2)
    ]
]);
