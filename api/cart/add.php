<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();

$productId = (int)($body['product_id'] ?? 0);
$qty = max(1, (int)($body['quantity'] ?? 1));
if (!$productId) respond(['success' => false, 'error' => 'Product ID required'], 400);

$stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
$stmt->execute([$productId]);
$p = $stmt->fetch();
if (!$p) respond(['success' => false, 'error' => 'Product not found'], 404);
if ($p['stock'] < $qty) respond(['success' => false, 'error' => 'Not enough stock available'], 400);

$stmt = $pdo->prepare('SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?');
$stmt->execute([$user['user_id'], $productId]);
$existing = (int)($stmt->fetch()['quantity'] ?? 0);
if ($existing + $qty > (int)$p['stock']) respond(['success' => false, 'error' => 'Not enough stock available'], 400);
$stmt = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)
                       ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)');
$stmt->execute([$user['user_id'], $productId, $qty]);

$stmt = $pdo->prepare('SELECT COALESCE(SUM(quantity),0) AS count FROM cart_items WHERE user_id = ?');
$stmt->execute([$user['user_id']]);

respond(['success' => true, 'data' => ['count' => (int)$stmt->fetch()['count']], 'message' => 'Added to cart']);
