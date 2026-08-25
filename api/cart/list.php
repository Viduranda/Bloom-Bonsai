<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();

$stmt = $pdo->prepare('SELECT ci.id, ci.product_id, ci.quantity, p.name, p.price, p.image, p.stock
                       FROM cart_items ci JOIN products p ON p.id = ci.product_id
                       WHERE ci.user_id = ? ORDER BY ci.added_at DESC');
$stmt->execute([$user['user_id']]);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach ($items as &$it) $subtotal += $it['price'] * $it['quantity'];
$shipping = calcShipping($subtotal);

respond(['success' => true, 'data' => [
    'items' => $items, 'subtotal' => $subtotal,
    'shipping' => $shipping, 'total' => $subtotal + $shipping
]]);