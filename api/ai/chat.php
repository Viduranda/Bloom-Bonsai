<?php
// api/ai/chat.php — Real LLM Botanical AI Chatbot Endpoint (Connected to Live Database Inventory)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') exit(0);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/gemini_helper.php';

$userMsg = trim($body['message'] ?? '');
if (!$userMsg) respond(['error' => 'Message is required'], 400);

// Dynamically fetch live product inventory from MySQL database
$products = [];
try {
    $stmt = $pdo->query("SELECT p.name, p.price, c.name AS category, p.stock 
                         FROM products p 
                         LEFT JOIN categories c ON c.id = p.category_id 
                         WHERE p.stock > 0 
                         ORDER BY p.id DESC");
    if ($stmt) {
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {}

$catalogStr = !empty($products) 
    ? implode(", ", array_map(fn($p) => "{$p['name']} (" . ($p['category'] ?? 'Botanical') . " - Rs. {$p['price']})", $products))
    : "Ficus Bonsai (Rs. 1,299), Juniper Bonsai (Rs. 1,499), Peace Lily (Rs. 399), Anthurium (Rs. 499), Red Rose (Rs. 299)";

$systemInstruction = "You are Sprout 🌱, the AI Botanical Assistant & Customer Concierge at Bloom & Bonsai online store. " .
                    "You are warm, highly intelligent, friendly, and knowledgeable. " .
                    "You can answer ANY question customers ask — including plant care, watering, pruning, soil advice, order tracking, shipping policies, store hours, payment options (Cash on Delivery, Card), pricing in Sri Lankan Rupees (Rs.), as well as general knowledge, greetings, and customer inquiries! " .
                    "Our live store inventory currently features: [$catalogStr]. " .
                    "Always keep answers helpful, engaging, beautifully formatted with emojis, and quote prices in Sri Lankan Rupees (Rs.).";

$aiReply = null;
try {
    $aiReply = callGemini15Flash($userMsg, $systemInstruction);
} catch (Exception $e) {}

if ($aiReply) {
    respond([
        'success' => true,
        'data' => [
            'reply' => $aiReply,
            'source' => 'gemini-1.5-flash-live-catalog'
        ]
    ]);
}

function getFallbackBotanicalReply($msg, $catalogStr) {
    $m = strtolower($msg);
    if (str_contains($m, 'water') || str_contains($m, 'dry')) {
        return "Always test soil moisture 2 inches deep before watering. Bonsai prefer moist but well-drained organic soil! Water early in the morning for best root hydration.";
    }
    if (str_contains($m, 'yellow') || str_contains($m, 'leaf')) {
        return "Yellowing leaves are usually a sign of over-watering or poor drainage. Let top soil dry out, and ensure your pot has adequate drainage holes!";
    }
    if (str_contains($m, 'bonsai') || str_contains($m, 'ficus')) {
        return "Ficus Ginseng Bonsai & Juniper Bonsai thrive in bright indirect sunlight. Mist leaves 3x weekly to maintain optimal humidity.";
    }
    return "Welcome to Bloom & Bonsai! I can guide you on plant selection, soil care, sunlight needs, and disease treatments. We currently have these plants in stock: $catalogStr.";
}

respond([
    'success' => true,
    'data' => [
        'reply' => getFallbackBotanicalReply($userMsg, $catalogStr),
        'source' => 'local-botanist-engine'
    ]
]);
