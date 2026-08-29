<?php
require_once __DIR__ . '/../config.php';

$productId = (int)($_GET['product_id'] ?? 0);

if ($productId > 0) {
    $stmt = $pdo->prepare('SELECT r.*, COALESCE(r.user_name, u.name, "Verified Buyer") AS user_name
                           FROM reviews r LEFT JOIN users u ON u.id = r.user_id
                           WHERE r.product_id = ? ORDER BY r.created_at DESC');
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll();
} else {
    $reviews = $pdo->query('SELECT r.*, COALESCE(r.user_name, u.name, "Verified Buyer") AS user_name
                           FROM reviews r LEFT JOIN users u ON u.id = r.user_id
                           ORDER BY r.created_at DESC LIMIT 50')->fetchAll();
}

respond(['success' => true, 'data' => $reviews]);
