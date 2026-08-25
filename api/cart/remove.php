<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();

$itemId = (int)($body['item_id'] ?? $_GET['item_id'] ?? 0);
if ($itemId <= 0) respond(['success' => false, 'error' => 'Cart item ID required'], 400);
$stmt = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND user_id = ?');
$stmt->execute([$itemId, $user['user_id']]);

respond(['success' => true, 'message' => 'Item removed']);
