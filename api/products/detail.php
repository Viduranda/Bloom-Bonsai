<?php
require_once __DIR__ . '/../config.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) respond(['success' => false, 'error' => 'Product ID required'], 400);

$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name, c.name AS category
                       FROM products p JOIN categories c ON c.id = p.category_id
                       WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) respond(['success' => false, 'error' => 'Product not found'], 404);

$plans = $pdo->prepare('SELECT week_number, title, content FROM plant_care_plans WHERE product_id = ? ORDER BY week_number');
$plans->execute([$id]);
$fetchedPlans = $plans->fetchAll();
if ($fetchedPlans && count($fetchedPlans) > 0) {
    $product['care_plan'] = $fetchedPlans;
} else if (!empty($product['care_plan'])) {
    $product['care_plan'] = is_string($product['care_plan']) ? json_decode($product['care_plan'], true) : $product['care_plan'];
} else {
    $product['care_plan'] = [];
}

try {
    $reviews = $pdo->prepare('SELECT r.id, r.rating, r.comment, r.created_at,
                                     COALESCE(u.name, "Verified Buyer") AS user_name,
                                     COALESCE(u.name, "Verified Buyer") AS name
                              FROM reviews r LEFT JOIN users u ON u.id = r.user_id
                              WHERE r.product_id = ? ORDER BY r.created_at DESC');
    $reviews->execute([$id]);
    $product['reviews'] = $reviews->fetchAll();
} catch (Exception $e) {
    $product['reviews'] = [];
}

respond(['success' => true, 'data' => $product]);