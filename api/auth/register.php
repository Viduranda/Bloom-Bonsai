<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'error' => 'Method Not Allowed'], 405);
}

$input = $body;
$name  = trim($input['name'] ?? '');
$email = strtolower(trim($input['email'] ?? ''));
$pass  = trim($input['password'] ?? '');

if (empty($name) || empty($email) || empty($pass)) {
    respond(['success' => false, 'error' => 'Name, email, and password are required.'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(['success' => false, 'error' => 'Invalid email format.'], 400);
}

if (strlen($pass) < 6) {
    respond(['success' => false, 'error' => 'Password must be at least 6 characters.'], 400);
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE LOWER(email) = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    respond(['success' => false, 'error' => 'Email already registered.'], 409);
}

$role = (in_array($email, ['vidurandarukmal@gmail.com', 'admin@bloombonsai.com'])) ? 'admin' : 'customer';

$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
$stmt->execute([$name, $email, $hash, $role]);
$userId = $pdo->lastInsertId();

$user = [
    'id'    => $userId,
    'name'  => $name,
    'email' => $email,
    'role'  => $role
];

$token = createToken($userId, $role);

respond([
    'success' => true,
    'message' => 'Registration successful',
    'token'   => $token,
    'user'    => $user
]);
