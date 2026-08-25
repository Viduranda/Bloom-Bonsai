<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();

$stmt = $pdo->prepare(
    'SELECT ci.id, ci.product_id, ci.quantity, p.name, p.price, p.old_price, p.image,
            c.name AS category
     FROM cart_items ci
     JOIN products p ON p.id = ci.product_id
     JOIN categories c ON c.id = p.category_id
     WHERE ci.user_id = ? ORDER BY ci.id DESC'
);
$stmt->execute([$user['user_id']]);
$items = $stmt->fetchAll();

$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $items));
$shipping = calcShipping($subtotal);

respond(['success' => true, 'data' => [
    'items'    => $items,
    'subtotal' => $subtotal,
    'shipping' => $shipping,
    'total'    => $subtotal + $shipping,
]]);
