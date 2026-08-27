<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
requireAdmin();

$orderId = (int)($body['order_id'] ?? 0);
$status  = $body['status'] ?? '';
$note    = trim($body['note'] ?? '');
$allowed = ['pending','confirmed','packed','shipped','out_for_delivery','delivered','cancelled'];
if ($orderId <= 0 || !in_array($status, $allowed))
    respond(['success' => false, 'error' => 'Invalid order id or status'], 400);

$stmt = $pdo->prepare('SELECT id FROM orders WHERE id = ?');
$stmt->execute([$orderId]);
if (!$stmt->fetch()) respond(['success' => false, 'error' => 'Order not found'], 404);

$labels = [
    'pending' => 'Order received', 'confirmed' => 'Order confirmed',
    'packed' => 'Packed at the shop', 'shipped' => 'Handed to delivery partner',
    'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered',
    'cancelled' => 'Order cancelled',
];

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('UPDATE orders SET status = ?, status_updated_at = NOW() WHERE id = ?');
    $stmt->execute([$status, $orderId]);

    $stmt = $pdo->prepare('INSERT INTO order_status_history (order_id, status, note) VALUES (?, ?, ?)');
    $stmt->execute([$orderId, $status, $note !== '' ? $note : ($labels[$status] ?? $status)]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    respond(['success' => false, 'error' => 'Could not update status'], 500);
}

respond(['success' => true, 'data' => ['order_id' => $orderId, 'status' => $status, 'message' => 'Status updated']]);