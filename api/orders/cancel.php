<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();

$orderId = (int)($body['order_id'] ?? $_POST['order_id'] ?? 0);
if (!$orderId) {
    respond(['success' => false, 'error' => 'Order ID is required'], 400);
}

// Fetch order for this user
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, $user['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    respond(['success' => false, 'error' => 'Order not found'], 404);
}

if ($order['status'] === 'cancelled') {
    respond(['success' => false, 'error' => 'Order is already cancelled'], 400);
}

if (in_array($order['status'], ['shipped', 'out_for_delivery', 'delivered'])) {
    respond(['success' => false, 'error' => 'Order cannot be cancelled as it is already ' . str_replace('_', ' ', $order['status'])], 400);
}

// 24-Hour Rule Check
$createdAt = strtotime($order['created_at']);
$now = time();
$hoursElapsed = ($now - $createdAt) / 3600;

if ($hoursElapsed > 24) {
    respond([
        'success' => false,
        'error' => 'Cancellation period expired. Orders can only be cancelled within 24 hours of placement (Placed ' . round($hoursElapsed, 1) . ' hours ago).'
    ], 400);
}

// Perform Cancellation
$update = $pdo->prepare('UPDATE orders SET status = "cancelled", status_updated_at = NOW() WHERE id = ?');
$update->execute([$orderId]);

$hist = $pdo->prepare('INSERT INTO order_status_history (order_id, status, note, created_at) VALUES (?, "cancelled", ?, NOW())');
$hist->execute([$orderId, 'Cancelled by customer within 24-hour window']);

// Send status email update
$order['status'] = 'cancelled';
sendOrderEmail($order, 'status_update');

respond([
    'success' => true,
    'data' => [
        'order_id' => $orderId,
        'message' => 'Order #' . $orderId . ' cancelled successfully.'
    ]
]);
