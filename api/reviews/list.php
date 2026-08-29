<?php
// ============================================================
// api/reviews/list.php — Fetch reviews and average star rating
// ============================================================
require_once __DIR__ . '/../config.php';

$productId = (int)($_GET['product_id'] ?? 0);

if ($productId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM product_reviews WHERE product_id = ? ORDER BY created_at DESC');
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll();

    $avgStmt = $pdo->prepare('SELECT AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM product_reviews WHERE product_id = ?');
    $avgStmt->execute([$productId]);
    $stats = $avgStmt->fetch();

    respond([
        'success' => true,
        'data' => [
            'reviews' => $reviews,
            'avg_rating' => round((float)($stats['avg_rating'] ?? 5.0), 1),
            'review_count' => (int)($stats['review_count'] ?? 0)
        ]
    ]);
}

// Global ratings map for all products
$allStmt = $pdo->query('SELECT product_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count FROM product_reviews GROUP BY product_id');
$ratingsMap = [];
while ($row = $allStmt->fetch()) {
    $ratingsMap[$row['product_id']] = [
        'avg_rating' => round((float)$row['avg_rating'], 1),
        'review_count' => (int)$row['review_count']
    ];
}

respond(['success' => true, 'data' => $ratingsMap]);
