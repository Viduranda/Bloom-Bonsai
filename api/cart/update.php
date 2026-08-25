<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();

$itemId = (int)($body['item_id'] ?? 0);
$qty    = (int)($body['quantity'] ?? 0);
if ($qty < 1) respond(['success' => false, 'error' => 'Quantity must be at least 1'], 400);

$stmt = $pdo->prepare('UPDATE cart_items ci JOIN products p ON p.id = ci.product_id
                       SET ci.quantity = ?
                       WHERE ci.id = ? AND ci.user_id = ?');
$check = $pdo->prepare('SELECT p.stock FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.id = ? AND ci.user_id = ?');
$check->execute([$itemId, $user['user_id']]);
$product = $check->fetch();
if (!$product) respond(['success' => false, 'error' => 'Cart item not found'], 404);
if ($qty > (int)$product['stock']) respond(['success' => false, 'error' => 'Not enough stock available'], 400);
$stmt->execute([$qty, $itemId, $user['user_id']]);

respond(['success' => true, 'message' => 'Cart updated']);
