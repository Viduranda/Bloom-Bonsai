<?php
// api/coupons/active.php — Return all active store coupons for customer collection & checkout
require_once __DIR__ . '/../config.php';

// Auto-create coupons table if it does not exist yet in database
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        discount_percent DECIMAL(5,2) DEFAULT 0,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        min_spend DECIMAL(10,2) DEFAULT 0,
        expiry_date DATE DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    $count = (int)$pdo->query("SELECT COUNT(*) FROM coupons")->fetchColumn();
    if ($count === 0) {
        $pdo->exec("INSERT INTO coupons (code, discount_percent, min_spend, is_active) VALUES
            ('BLOOM10', 10.00, 500.00, 1),
            ('BONSAI20', 20.00, 1500.00, 1);");
    }
} catch (Exception $e) {}

$coupons = [];
try {
    $stmt = $pdo->query("SELECT id, code, discount_percent, discount_amount, min_spend, expiry_date FROM coupons WHERE is_active = 1 AND (expiry_date IS NULL OR expiry_date >= CURDATE()) ORDER BY id DESC");
    $coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

respond([
    'success' => true,
    'data' => [
        'coupons' => $coupons,
        'count' => count($coupons)
    ]
]);
