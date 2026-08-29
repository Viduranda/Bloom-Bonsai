<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

$user = getUserFromToken();

if (!$user) {
    respond(['success' => true, 'wishlist' => []]);
}

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM wishlist_items w
        JOIN products p ON p.id = w.product_id
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE w.user_id = ? AND p.is_deleted = 0
        ORDER BY w.added_at DESC
    ");
    $stmt->execute([$user['user_id']]);
    $products = $stmt->fetchAll();
    respond(['success' => true, 'wishlist' => $products]);
} catch (Exception $e) {
    respond(['success' => true, 'wishlist' => []]);
}
