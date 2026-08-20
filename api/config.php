<?php
// Helper function to read env vars safely from getenv, $_ENV, or $_SERVER
function getEnvVar($key, $default = '') {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    return $default;
}

// Load local .env files if present (check both api/.env and root .env)
$possibleEnvFiles = [
    __DIR__ . '/.env',
    dirname(__DIR__) . '/.env'
];
foreach ($possibleEnvFiles as $envFile) {
    if (file_exists($envFile)) {
        $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines) {
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#')) continue;
                if (strpos($line, '=') !== false) {
                    list($key, $val) = explode('=', $line, 2);
                    $key = trim($key);
                    $val = trim($val, " \t\n\r\0\x0B\"'");
                    if (!empty($key)) {
                        putenv("$key=$val");
                        $_ENV[$key] = $val;
                        $_SERVER[$key] = $val;
                    }
                }
            }
        }
    }
}

// Global Environment-Aware Exception Handler
set_exception_handler(function($e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    $env = getEnvVar('APP_ENV', 'local');
    $errMsg = ($env === 'production') ? 'An unexpected server error occurred.' : 'Server Error: ' . $e->getMessage();
    
    // Log exception to file
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    @file_put_contents($logDir . '/error.log', sprintf("[%s] %s in %s:%d\nStack Trace:\n%s\n", date('Y-m-d H:i:s'), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()), FILE_APPEND);
    
    echo json_encode(['success' => false, 'error' => $errMsg]);
    exit;
});

// Global Request Body Parser ($body for JSON & POST requests across all API endpoints)
$rawInput = file_get_contents('php://input');
$body = json_decode($rawInput, true);
if (!is_array($body)) {
    $body = $_POST ?? [];
}

// Restricted CORS Header Setup
$requestOrigin = rtrim($_SERVER['HTTP_ORIGIN'] ?? '', '/');
$allowedDomains = [
    'http://bloom-bonsai.alwaysdata.net',
    'https://bloom-bonsai.alwaysdata.net',
    'http://localhost',
    'http://127.0.0.1'
];

$isAllowedOrigin = false;
foreach ($allowedDomains as $domain) {
    if ($requestOrigin === $domain || str_starts_with($requestOrigin, 'http://localhost:') || str_starts_with($requestOrigin, 'http://127.0.0.1:')) {
        $isAllowedOrigin = true;
        break;
    }
}

if ($requestOrigin !== '' && $isAllowedOrigin) {
    header('Access-Control-Allow-Origin: ' . $requestOrigin);
    header('Vary: Origin');
} else {
    header('Access-Control-Allow-Origin: *');
}
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') exit(0);

// Multi-Tier Database Connection Strategy
$host = getEnvVar('DB_HOST', 'mysql-bloom-bonsai.alwaysdata.net');
$db   = getEnvVar('DB_NAME', 'bloom-bonsai_db');
$user = getEnvVar('DB_USER', 'bloom-bonsai');
$pass = getEnvVar('DB_PASSWORD', '');

$pdo = null;
$connected = false;

// Tier 1: Primary Database Config (Env or AlwaysData)
if (!empty($pass)) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $connected = true;
    } catch (PDOException $e) {}
}

// Tier 2: Try AlwaysData / Local Socket with environment user
if (!$connected && !empty($user) && !empty($pass)) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $connected = true;
    } catch (PDOException $e) {}
}

// Tier 3: Local XAMPP/WAMP Fallback (Development Only)
if (!$connected) {
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=bloom_bonsai;charset=utf8mb4", "root", "", [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $connected = true;
    } catch (PDOException $localErr) {}
}

if (!function_exists('respond')) {
    function respond($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
}

// Global Shipping Calculation Helper
if (!function_exists('calcShipping')) {
    function calcShipping($subtotal) {
        $subtotal = floatval($subtotal);
        if ($subtotal >= 5000 || $subtotal <= 0) return 0.00;
        return 350.00; // Flat rate LKR 350 for orders below LKR 5000
    }
}

// Global Image Upload Helper for Admin & Product Management
if (!function_exists('uploadImage')) {
    function uploadImage($fileKey, $pdo = null) {
        if (empty($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $file = $_FILES[$fileKey];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowedExts  = ['jpg', 'jpeg', 'png', 'webp'];
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $allowedTypes) || !in_array($ext, $allowedExts)) {
            return null;
        }
        if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
            return null;
        }
        
        $uploadDir = __DIR__ . '/../uploads/products';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        $filename = 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/products/' . $filename;
        }
        return null;
    }
}

// Global Order Email Helper (Safe Fallback to Local Logging)
if (!function_exists('sendOrderEmail')) {
    function sendOrderEmail($orderData, $type = 'confirmation') {
        $to = $orderData['email'] ?? '';
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $orderId = $orderData['id'] ?? ($orderData['order_id'] ?? 'N/A');
        $subject = "Bloom & Bonsai - Order #" . $orderId . " Update";
        $statusStr = $orderData['status'] ?? 'confirmed';
        
        $message = "Dear " . htmlspecialchars($orderData['customer_name'] ?? 'Customer') . ",\n\n";
        $message .= "Thank you for shopping at Bloom & Bonsai! Your order #" . $orderId . " status has been updated to: " . strtoupper($statusStr) . ".\n\n";
        $message .= "Order Details:\n";
        $message .= "- Total Amount: LKR " . number_format(floatval($orderData['total_amount'] ?? $orderData['total'] ?? 0), 2) . "\n";
        $message .= "- Delivery Address: " . ($orderData['address'] ?? ($orderData['shipping_address'] ?? 'N/A')) . "\n\n";
        $message .= "You can view your order status online at Bloom & Bonsai.\n\n";
        $message .= "Warm regards,\nBloom & Bonsai Team";
        
        $headers = "From: no-reply@bloombonsai.com\r\nReply-To: support@bloombonsai.com\r\nX-Mailer: PHP/" . phpversion();
        
        // Log email dispatch locally as fallback
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        @file_put_contents($logDir . '/email.log', sprintf("[%s] TO: %s | TYPE: %s | ORDER: %s\n", date('Y-m-d H:i:s'), $to, $type, $orderId), FILE_APPEND);
        
        return @mail($to, $subject, $message, $headers);
    }
}
