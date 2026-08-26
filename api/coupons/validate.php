<?php
// api/coupons/validate.php — Validate a promo coupon code against cart subtotal, expiry, and single-use limit
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

$code = strtoupper(trim($body['code'] ?? $_GET['code'] ?? ''));
$subtotal = floatval($body['subtotal'] ?? $_GET['subtotal'] ?? 0);

if (!$code) {
    respond(['success' => false, 'error' => 'Coupon code is required.'], 400);
}

$stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
$stmt->execute([$code]);
$coupon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$coupon) {
    respond(['success' => false, 'error' => "Invalid or inactive promo code '$code'."], 400);
}

// 1. Expiry date check
if ($coupon['expiry_date'] && strtotime($coupon['expiry_date']) < strtotime(date('Y-m-d'))) {
    respond(['success' => false, 'error' => "Promo code '$code' expired on " . date('d M Y', strtotime($coupon['expiry_date'])) . "."], 400);
}

// 2. Minimum spend check
$minSpend = floatval($coupon['min_spend'] ?? 0);
if ($minSpend > 0 && $subtotal < $minSpend) {
    respond([
        'success' => false, 
        'error' => "Coupon '$code' requires a minimum order spend of Rs. " . number_format($minSpend, 2) . "."
    ], 400);
}

// Auto-migrate orders table schema if coupon columns are missing
try {
    $chk = $pdo->query("SHOW COLUMNS FROM orders LIKE 'coupon_code'")->fetch();
    if (!$chk) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(50) DEFAULT NULL, ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00;");
    }
} catch (Exception $e) {}

// 3. One-Time Use Per Account Check
$user = getUserFromToken();
if ($user && !empty($user['user_id'])) {
    try {
        $usedStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND UPPER(coupon_code) = ? AND status != 'cancelled'");
        $usedStmt->execute([$user['user_id'], $code]);
        $alreadyUsedCount = (int)$usedStmt->fetchColumn();

        if ($alreadyUsedCount > 0) {
            respond([
                'success' => false, 
                'error' => "You have already redeemed promo code '$code'. Each coupon can only be used once per account."
            ], 400);
        }
    } catch (Exception $e) {}
}

// Calculate discount amount
$discountPercent = floatval($coupon['discount_percent'] ?? 0);
$discountAmountFixed = floatval($coupon['discount_amount'] ?? 0);

if ($discountPercent > 0) {
    $discountVal = round(($subtotal * $discountPercent) / 100, 2);
    $discountText = "{$discountPercent}% Off All Orders";
} else {
    $discountVal = min($subtotal, $discountAmountFixed);
    $discountText = "Flat Rs. " . number_format($discountAmountFixed, 2) . " Discount";
}

respond([
    'success' => true,
    'data' => [
        'code' => $coupon['code'],
        'discount_amount' => $discountVal,
        'discount_text' => $discountText,
        'min_spend' => $minSpend,
        'new_total' => max(0, $subtotal - $discountVal)
    ]
]);
