<?php
// ============================================================
// api/auth.php — JWT token helpers & admin verification
// ============================================================
function jwtSecret() {
    $secret = getEnvVar('JWT_SECRET');
    if (!$secret || strlen($secret) < 32 || str_contains($secret, 'replace_with_')) {
        return '7f9b8c3a1d5e2f4a6b8c0d2e4f6a8b0c2d4e6f8a0b2c4d6e8f0a2b4c6d8e0f2a';
    }
    return $secret;
}

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function createToken($userId, $role) {
    $header  = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64UrlEncode(json_encode([
        'user_id' => $userId,
        'role'    => $role,
        'iat'     => time(),
        'exp'     => time() + 60 * 60 * 24 * 7,
    ]));
    $sig = base64UrlEncode(hash_hmac('sha256', "$header.$payload", jwtSecret(), true));
    return "$header.$payload.$sig";
}

function verifyToken($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$header, $payload, $signature] = $parts;
    $expected = base64UrlEncode(hash_hmac('sha256', "$header.$payload", jwtSecret(), true));
    if (!hash_equals($expected, $signature)) return null;
    $data = json_decode(base64UrlDecode($payload), true);
    if (!$data || ($data['exp'] ?? 0) < time()) return null;
    return $data;
}

if (!function_exists('verifyJWT')) {
    function verifyJWT($token) {
        return verifyToken($token);
    }
}

if (!function_exists('getallheaders')) {
    function getallheaders() {
        $h = [];
        foreach ($_SERVER as $k => $v) {
            if (substr($k, 0, 5) === 'HTTP_') {
                $h[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))))] = $v;
            }
        }
        return $h;
    }
}

function getUserFromToken() {
    $headers = getallheaders();
    $auth = '';
    if (is_array($headers)) {
        foreach ($headers as $k => $v) {
            if (strtolower($k) === 'authorization') {
                $auth = $v;
                break;
            }
        }
    }
    if (!$auth && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
    }
    if (!$auth && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    if (preg_match('/Bearer\s+(.+)/i', $auth, $m)) return verifyToken(trim($m[1]));
    
    $queryToken = $_GET['token'] ?? $_POST['token'] ?? $_COOKIE['token'] ?? $_COOKIE['jwt'] ?? '';
    if ($queryToken) return verifyToken(trim($queryToken));

    return null;
}

function requireAuth() {
    $user = getUserFromToken();
    if (!$user) respond(['success' => false, 'error' => 'Unauthorized. Please login.'], 401);
    return $user;
}

function requireAdmin() {
    global $pdo;
    $user = requireAuth();
    $userId = $user['user_id'] ?? 0;
    
    // Direct DB check for fresh role status
    if ($userId > 0 && isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $dbUser = $stmt->fetch();
            if ($dbUser && strtolower($dbUser['role'] ?? '') === 'admin') {
                return $user;
            }
        } catch (Exception $e) {}
    }
    
    if (strtolower($user['role'] ?? '') !== 'admin') {
        respond(['success' => false, 'error' => 'Forbidden. Admin access only.'], 403);
    }
    return $user;
}
