<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

$user = getUserFromToken();

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$productId = (int)($input['product_id'] ?? 0);

if (!$productId) {
    respond(['success' => false, 'error' => 'Product ID is required.'], 400);
}

if (!$user) {
    respond(['success' => true, 'is_wishlisted' => true, 'message' => 'Saved to local wishlist']);
}

// Auto-create table if not exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `wishlist_items` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `product_id` INT NOT NULL,
          `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY `user_prod` (`user_id`, `product_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (Exception $e) {}

$stmt = $pdo->prepare("SELECT id FROM wishlist_items WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user['user_id'], $productId]);
$existing = $stmt->fetch();

if ($existing) {
    $del = $pdo->prepare("DELETE FROM wishlist_items WHERE id = ?");
    $del->execute([$existing['id']]);
    $isWishlisted = false;
    $msg = 'Removed from wishlist';
} else {
    $ins = $pdo->prepare("INSERT INTO wishlist_items (user_id, product_id) VALUES (?, ?)");
    $ins->execute([$user['user_id'], $productId]);
    $isWishlisted = true;
    $msg = 'Saved to wishlist';
}

respond([
    'success' => true,
    'is_wishlisted' => $isWishlisted,
    'message' => $msg
]);
