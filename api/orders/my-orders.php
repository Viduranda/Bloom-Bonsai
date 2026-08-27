<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();

$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['user_id']]);
$orders = $stmt->fetchAll();

$itemsStmt = $pdo->prepare('SELECT oi.*, p.name AS product_name, p.image
                            FROM order_items oi JOIN products p ON p.id = oi.product_id
                            WHERE oi.order_id = ?');
$histStmt = $pdo->prepare('SELECT status, note, created_at FROM order_status_history
                           WHERE order_id = ? ORDER BY created_at ASC');

foreach ($orders as &$o) {
    $itemsStmt->execute([$o['id']]);
    $o['items'] = $itemsStmt->fetchAll();
    $histStmt->execute([$o['id']]);
    $o['history'] = $histStmt->fetchAll();
}
respond(['success' => true, 'data' => $orders]);