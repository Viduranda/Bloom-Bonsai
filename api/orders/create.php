<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();

$items = $body['items'] ?? null;
if (!$items || !is_array($items) || count($items) === 0)
    respond(['success' => false, 'error' => 'Cart is empty'], 400);

$name    = trim($body['name'] ?? '');
$email   = strtolower(trim($body['email'] ?? ($user['email'] ?? '')));
$phone   = trim($body['phone'] ?? '');
$address = trim($body['address'] ?? '');
$city    = trim($body['city'] ?? '');
$pincode = trim($body['pincode'] ?? '');
$payment = strtolower(trim($body['payment_method'] ?? 'cod'));
$txnId   = trim($body['transaction_id'] ?? '');

if (!$name || !$phone || !$address || !$city || !$pincode)
    respond(['success' => false, 'error' => 'All shipping details are required'], 400);

$paymentStatus = ($payment === 'stripe' || $payment === 'payhere') ? 'paid' : 'pending';
if (!$txnId && $paymentStatus === 'paid') {
    $txnId = strtoupper($payment) . '_' . bin2hex(random_bytes(8));
}

$requested = [];
foreach ($items as $it) {
    $pid = (int)($it['product_id'] ?? 0); $qty = (int)($it['quantity'] ?? 0);
    if ($pid <= 0 || $qty <= 0) continue;
    $requested[$pid] = ($requested[$pid] ?? 0) + $qty;
}
if (!$requested) respond(['success' => false, 'error' => 'Cart is empty'], 400);

$pdo->beginTransaction();
try {
    $validated = []; $total = 0;
    $productStmt = $pdo->prepare('SELECT id, name, price, stock FROM products WHERE id = ? FOR UPDATE');
    foreach ($requested as $pid => $qty) {
        $productStmt->execute([$pid]);
        $p = $productStmt->fetch();
        if (!$p) throw new RuntimeException("Product #$pid not found");
        if ((int)$p['stock'] < $qty) throw new RuntimeException("Not enough stock for product #$pid");
        $validated[] = ['product_id' => $pid, 'name' => $p['name'], 'quantity' => $qty, 'price' => $p['price']];
        $total += $p['price'] * $qty;
    }

    // Coupon validation & discount calculation
    $couponCode = strtoupper(trim($body['coupon_code'] ?? ''));
    $discountVal = 0;

    if ($couponCode) {
        $cStmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
        $cStmt->execute([$couponCode]);
        $cData = $cStmt->fetch();
        if (!$cData) {
            throw new RuntimeException("Promo code '$couponCode' is invalid or disabled.");
        }
        $minSpend = floatval($cData['min_spend'] ?? 0);
        if ($minSpend > 0 && $total < $minSpend) {
            throw new RuntimeException("Coupon '$couponCode' requires a minimum order spend of Rs. " . number_format($minSpend, 2));
        }

        if (isset($cData['discount_percent']) && $cData['discount_percent'] > 0) {
            $discountVal = round(($total * floatval($cData['discount_percent'])) / 100, 2);
        } else if (isset($cData['discount_amount'])) {
            $discountVal = min($total, floatval($cData['discount_amount']));
        }
    }

    // Auto-migrate orders table schema if coupon columns are missing
    try {
        $chk = $pdo->query("SHOW COLUMNS FROM orders LIKE 'coupon_code'")->fetch();
        if (!$chk) {
            $pdo->exec("ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(50) DEFAULT NULL, ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00;");
        }
    } catch (Exception $e) {}

    $hasCouponCol = true;

    if ($couponCode && $hasCouponCol) {
        $usedStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND UPPER(coupon_code) = ? AND status != 'cancelled'");
        $usedStmt->execute([$user['user_id'], $couponCode]);
        if ((int)$usedStmt->fetchColumn() > 0) {
            throw new RuntimeException("You have already redeemed promo code '$couponCode'. Each coupon can only be used once per account.");
        }
    }

    $shipping = calcShipping($total);
    $grand = max(0, $total + $shipping - $discountVal);

    if ($hasCouponCol) {
        $stmt = $pdo->prepare('INSERT INTO orders
            (user_id, total, coupon_code, discount_amount, status, shipping_address, customer_name, phone, city, pincode,
             payment_method, expected_delivery, status_updated_at, created_at)
            VALUES (?, ?, ?, ?, "confirmed", ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 DAY), NOW(), NOW())');
        $stmt->execute([$user['user_id'], $grand, $couponCode, $discountVal, "$name, $address, $city - $pincode",
                        $name, $phone, $city, $pincode, $payment]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO orders
            (user_id, total, status, shipping_address, customer_name, phone, city, pincode,
             payment_method, expected_delivery, status_updated_at, created_at)
            VALUES (?, ?, "confirmed", ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 5 DAY), NOW(), NOW())');
        $stmt->execute([$user['user_id'], $grand, "$name, $address, $city - $pincode",
                        $name, $phone, $city, $pincode, $payment]);
    }
    $orderId = $pdo->lastInsertId();

    $stmt  = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)');
    $stock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
    $plantStmt = $pdo->prepare('INSERT INTO user_plants (user_id, plant_name, species, image_url, health_status) VALUES (?, ?, ?, ?, "healthy")');

    foreach ($validated as $v) {
        $stmt->execute([$orderId, $v['product_id'], $v['quantity'], $v['price']]);
        $stock->execute([$v['quantity'], $v['product_id']]);

        // Auto-add plant to customer's user_plants (ONLY for living plants, NOT accessories/soil/tools)
        $pInfo = $pdo->prepare('SELECT p.name, p.scientific_name, p.image, c.slug AS cat_slug, c.name AS cat_name
                                FROM products p
                                LEFT JOIN categories c ON c.id = p.category_id
                                WHERE p.id = ?');
        $pInfo->execute([$v['product_id']]);
        $prod = $pInfo->fetch();
        if ($prod) {
            $catSlug = strtolower($prod['cat_slug'] ?? '');
            $catName = strtolower($prod['cat_name'] ?? '');
            $isAccessory = ($catSlug === 'accessories') ||
                           (strpos($catName, 'accessori') !== false) ||
                           (strpos($catName, 'tool') !== false) ||
                           (strpos($catName, 'soil') !== false);
            if (!$isAccessory) {
                $plantStmt->execute([$user['user_id'], $prod['name'], $prod['scientific_name'] ?? '', $prod['image']]);
            }
        }
    }

    $stmt = $pdo->prepare('INSERT INTO order_status_history (order_id, status, note) VALUES (?, "confirmed", "Order placed successfully")');
    $stmt->execute([$orderId]);

    $pdo->commit();
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $message = $e instanceof RuntimeException ? $e->getMessage() : 'Could not place order';
    respond(['success' => false, 'error' => $message], $e instanceof RuntimeException ? 400 : 500);
}

// Send automated confirmation email
sendOrderEmail([
    'id' => $orderId,
    'email' => $email,
    'customer_name' => $name,
    'status' => 'confirmed',
    'payment_method' => $payment,
    'payment_status' => $paymentStatus,
    'transaction_id' => $txnId,
    'total_amount' => $grand,
    'address' => "$name, $address, $city - $pincode",
    'items' => $validated
], 'confirmation');

respond(['success' => true, 'data' => [
    'order_id' => $orderId, 'total' => $grand, 'status' => 'confirmed',
    'payment_method' => $payment, 'payment_status' => $paymentStatus, 'transaction_id' => $txnId,
    'expected_delivery' => date('Y-m-d', strtotime('+5 days')),
]], 201);
