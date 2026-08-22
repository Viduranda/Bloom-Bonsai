<?php
require_once __DIR__ . '/../config.php';

$category = $_GET['category'] ?? null;
$search   = $_GET['search'] ?? null;
$limit    = (int)($_GET['limit'] ?? 50);

// Check if optional columns exist in products table dynamically
$img2Select = '';
try {
    $checkImg2 = $pdo->query("SHOW COLUMNS FROM products LIKE 'image2'")->fetch();
    if ($checkImg2) {
        $img2Select .= ', p.image2, p.image3';
    }
    $checkCarePlan = $pdo->query("SHOW COLUMNS FROM products LIKE 'care_plan'")->fetch();
    if ($checkCarePlan) {
        $img2Select .= ', p.care_plan';
    }
} catch (Exception $e) {}

$sql = 'SELECT p.id, p.name, p.scientific_name, p.description, p.price, p.old_price, p.image' . $img2Select . ', p.stock, p.badge,
               p.plant_age, p.max_height, p.bloom_time, p.light_needs, p.water_needs, p.soil_type, p.care_level,
               c.name AS category, c.slug AS category_slug
        FROM products p JOIN categories c ON c.id = p.category_id WHERE 1=1';
$params = [];
if ($category) { 
    $sql .= ' AND (c.slug = ? OR LOWER(c.name) LIKE ?)'; 
    $params[] = strtolower($category); 
    $params[] = '%' . strtolower($category) . '%'; 
}
if ($search)   { $sql .= ' AND p.name LIKE ?'; $params[] = '%' . $search . '%'; }
$sql .= ' ORDER BY p.id DESC LIMIT ' . max(1, $limit);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
respond(['success' => true, 'data' => $stmt->fetchAll()]);
