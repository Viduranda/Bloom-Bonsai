<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';
requireAdmin();

// Auto-migrate missing columns in products table schema if they don't exist yet
try {
    $cols = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('image2', $cols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN image2 VARCHAR(255) DEFAULT NULL;");
    }
    if (!in_array('image3', $cols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN image3 VARCHAR(255) DEFAULT NULL;");
    }
    if (!in_array('care_plan', $cols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN care_plan TEXT DEFAULT NULL;");
    }
    if (!in_array('is_deleted', $cols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN is_deleted TINYINT(1) DEFAULT 0;");
    }
} catch (Exception $e) {}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $name = trim($body['name'] ?? '');
    $cat  = (int)($body['category_id'] ?? 0);
    $price = (float)($body['price'] ?? 0);
    if (!$name || !$cat || $price <= 0) respond(['success' => false, 'error' => 'Name, category and valid price required'], 400);

    $carePlanRaw = $body['care_plan'] ?? null;
    $carePlanJson = is_array($carePlanRaw) ? json_encode($carePlanRaw) : (is_string($carePlanRaw) && $carePlanRaw !== '' ? $carePlanRaw : null);

    $stmt = $pdo->prepare('INSERT INTO products (
        category_id, name, scientific_name, description, price, old_price, image, image2, image3, stock, badge,
        plant_age, max_height, bloom_time, light_needs, water_needs, soil_type, care_level, care_plan
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

    $stmt->execute([
        $cat,
        $name,
        trim($body['scientific_name'] ?? '') ?: null,
        trim($body['description'] ?? '') ?: null,
        $price,
        !empty($body['old_price']) ? (float)$body['old_price'] : null,
        trim($body['image'] ?? '') ?: null,
        trim($body['image2'] ?? '') ?: null,
        trim($body['image3'] ?? '') ?: null,
        isset($body['stock']) ? (int)$body['stock'] : 10,
        trim($body['badge'] ?? '') ?: null,
        trim($body['plant_age'] ?? '') ?: null,
        trim($body['max_height'] ?? '') ?: null,
        trim($body['bloom_time'] ?? '') ?: null,
        trim($body['light_needs'] ?? '') ?: null,
        trim($body['water_needs'] ?? '') ?: null,
        trim($body['soil_type'] ?? '') ?: null,
        in_array($body['care_level'] ?? '', ['Easy','Moderate','Expert']) ? $body['care_level'] : 'Moderate',
        $carePlanJson
    ]);

    respond(['success' => true, 'data' => ['id' => $pdo->lastInsertId(), 'message' => 'Product created successfully']], 201);
}

if ($method === 'PUT') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) respond(['success' => false, 'error' => 'Product ID required'], 400);

    $carePlanRaw = $body['care_plan'] ?? null;
    $carePlanJson = is_array($carePlanRaw) ? json_encode($carePlanRaw) : (is_string($carePlanRaw) && $carePlanRaw !== '' ? $carePlanRaw : null);

    $stmt = $pdo->prepare('UPDATE products SET 
        category_id = ?,
        name = ?,
        scientific_name = ?,
        description = ?,
        price = ?,
        old_price = ?,
        image = ?,
        image2 = ?,
        image3 = ?,
        stock = ?,
        badge = ?,
        plant_age = ?,
        max_height = ?,
        bloom_time = ?,
        light_needs = ?,
        water_needs = ?,
        soil_type = ?,
        care_level = ?,
        care_plan = ?
        WHERE id = ?');

    $stmt->execute([
        (int)($body['category_id'] ?? 1),
        trim($body['name'] ?? ''),
        trim($body['scientific_name'] ?? '') ?: null,
        trim($body['description'] ?? '') ?: null,
        (float)($body['price'] ?? 0),
        !empty($body['old_price']) ? (float)$body['old_price'] : null,
        trim($body['image'] ?? '') ?: null,
        trim($body['image2'] ?? '') ?: null,
        trim($body['image3'] ?? '') ?: null,
        (int)($body['stock'] ?? 0),
        trim($body['badge'] ?? '') ?: null,
        trim($body['plant_age'] ?? '') ?: null,
        trim($body['max_height'] ?? '') ?: null,
        trim($body['bloom_time'] ?? '') ?: null,
        trim($body['light_needs'] ?? '') ?: null,
        trim($body['water_needs'] ?? '') ?: null,
        trim($body['soil_type'] ?? '') ?: null,
        in_array($body['care_level'] ?? '', ['Easy','Moderate','Expert']) ? $body['care_level'] : 'Moderate',
        $carePlanJson,
        $id
    ]);

    respond(['success' => true, 'data' => ['message' => 'Product updated successfully']]);
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) respond(['success' => false, 'error' => 'Product ID required'], 400);

    // Check if product is referenced in customer orders
    $chk = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $chk->execute([$id]);
    $orderCount = (int)$chk->fetchColumn();

    if ($orderCount > 0) {
        // Soft delete to protect past customer orders and invoice integrity
        $pdo->prepare("UPDATE products SET is_deleted = 1 WHERE id = ?")->execute([$id]);
    } else {
        // Hard delete if never purchased
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    }

    respond(['success' => true, 'data' => ['message' => 'Product deleted successfully']]);
}

respond(['success' => false, 'error' => 'Method not allowed'], 405);
