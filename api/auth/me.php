<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

$authData = requireAuth();

$stmt = $pdo->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
$stmt->execute([$authData['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    respond(['success' => false, 'error' => 'User not found.'], 404);
}

respond([
    'success' => true,
    'user'    => $user
]);
