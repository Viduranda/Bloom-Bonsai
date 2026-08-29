<?php
// ============================================================
// api/reviews/create.php — Submit a verified customer review
// ============================================================
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
$user = requireAuth();

$productId = (int)($body['product_id'] ?? $_POST['product_id'] ?? 0);
$rating    = (int)($body['rating'] ?? $_POST['rating'] ?? 5);
$comment   = trim($body['comment'] ?? $_POST['comment'] ?? '');

if ($productId <= 0) {
    respond(['success' => false, 'error' => 'Product ID is required'], 400);
}
if ($rating < 1 || $rating > 5) {
    respond(['success' => false, 'error' => 'Rating must be between 1 and 5 stars'], 400);
}
if (!$comment) {
    respond(['success' => false, 'error' => 'Review comment cannot be empty'], 400);
}

// Fetch user name
$uStmt = $pdo->prepare('SELECT name FROM users WHERE id = ?');
$uStmt->execute([$user['user_id']]);
$userName = $uStmt->fetchColumn() ?: 'Customer';

$stmt = $pdo->prepare('INSERT INTO product_reviews (product_id, user_id, user_name, rating, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
$stmt->execute([$productId, $user['user_id'], $userName, $rating, $comment]);

respond(['success' => true, 'message' => 'Review submitted successfully! ⭐']);
