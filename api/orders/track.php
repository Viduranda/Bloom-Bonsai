<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();

$orderId = (int)($body['order_id'] ?? $_GET['order_id'] ?? 0);
if (!$orderId) respond(['success' => false, 'error' => 'Order ID required'], 400);

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, $user['user_id']]);
$order = $stmt->fetch();
if (!$order) respond(['success' => false, 'error' => 'Order not found'], 404);

$hist = $pdo->prepare('SELECT status, note, created_at FROM order_status_history
                       WHERE order_id = ? ORDER BY created_at ASC');
$hist->execute([$orderId]);
$order['history'] = $hist->fetchAll();

respond(['success' => true, 'data' => $order]);