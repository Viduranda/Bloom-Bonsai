<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'error' => 'Method Not Allowed'], 405);
}

$input = $body;
$email = strtolower(trim($input['email'] ?? ''));
$pass  = trim($input['password'] ?? '');

if (empty($email) || empty($pass)) {
    respond(['success' => false, 'error' => 'Email and password are required.'], 400);
}

// Find user by email (case-insensitive)
$stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE LOWER(email) = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($pass, $user['password_hash'])) {
    respond(['success' => false, 'error' => 'Invalid email or password.'], 401);
}

unset($user['password_hash']);
$token = createToken($user['id'], $user['role']);

respond([
    'success' => true,
    'message' => 'Login successful',
    'token'   => $token,
    'user'    => $user
]);
