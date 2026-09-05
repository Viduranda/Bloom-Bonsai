<?php
// api/ai/designer.php — Real LLM & Vision Landscape Architect with Dimension & Angle Preservation
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') exit(0);

// Helper respond function if config.php fails to load
if (!function_exists('respond')) {
    function respond($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
}

// Try loading config safely without dying on DB error
try {
    ob_start();
    @include_once __DIR__ . '/../config.php';
    ob_end_clean();
} catch (Throwable $t) {}

require_once __DIR__ . '/gemini_helper.php';

$rawInput = file_get_contents('php://input');
$body = json_decode($rawInput, true) ?? [];

$style       = trim($body['style'] ?? $_POST['style'] ?? 'tropical');
$userPrompt  = trim($body['prompt'] ?? $_POST['prompt'] ?? '');
$orientation = trim($body['orientation'] ?? $_POST['orientation'] ?? 'East-facing (Bright Morning Sun)');
$hours       = trim($body['hours'] ?? $_POST['hours'] ?? '4-6 Hours Daily');
$mode        = trim($body['mode'] ?? $_POST['mode'] ?? 'enhance'); // 'enhance' (keep existing) vs 'clean_slate' (redesign all)

$base64Image = null;
$savedImagePublicUrl = null;
$origW = 1024;
$origH = 576;
$aspectRatioDesc = "16:9 landscape aspect ratio";

if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmpPath = $_FILES['image']['tmp_name'];
    $imgData = file_get_contents($tmpPath);
    if ($imgData !== false) {
        $base64Image = base64_encode($imgData);
        
        // Extract original image pixel dimensions & aspect ratio
        $imgSize = @getimagesize($tmpPath);
        if ($imgSize && $imgSize[0] > 0 && $imgSize[1] > 0) {
            $w = $imgSize[0];
            $h = $imgSize[1];
            
            // Scale to max dimension 1024 while maintaining exact original aspect ratio (rounded to multiples of 32)
            $maxDim = 1024;
            if ($w >= $h) {
                $origW = $maxDim;
                $origH = max(384, (int)round(($h / $w * $maxDim) / 32) * 32);
                $aspectRatioDesc = "horizontal landscape aspect ratio ({$w}x{$h} pixels)";
            } else {
                $origH = $maxDim;
                $origW = max(384, (int)round(($w / $h * $maxDim) / 32) * 32);
                $aspectRatioDesc = "vertical portrait aspect ratio ({$w}x{$h} pixels), tall camera angle looking down the space";
            }
        }

        // Save image to public uploads folder
        $uploadDir = __DIR__ . '/../../uploads/designer';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        $filename = 'space_' . time() . '_' . rand(100, 999) . '.jpg';
        $targetFile = $uploadDir . '/' . $filename;
        if (@file_put_contents($targetFile, $imgData)) {
            // Build full HTTP URL if running under web server
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $basePath = (strpos($_SERVER['REQUEST_URI'] ?? '', '/bloom-bonsai/') !== false) ? '/bloom-bonsai/' : '/';
            $savedImagePublicUrl = $protocol . '://' . $host . $basePath . 'uploads/designer/' . $filename;
        }
    }
} elseif (!empty($_POST['image'])) {
    $base64Image = $_POST['image'];
} elseif (!empty($body['image'])) {
    $base64Image = $body['image'];
}

// Fetch available store products from database
$products = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT id, name, category, price, image FROM products WHERE stock > 0 ORDER BY id ASC LIMIT 16");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

if (empty($products)) {
    $products = [
        ['id' => 1, 'name' => 'Bonsai Ficus Microcarpa', 'category' => 'Bonsai', 'price' => 4500, 'image' => 'bonsayi.avif'],
        ['id' => 2, 'name' => 'Anthurium Red Flowering', 'category' => 'Flowering', 'price' => 2200, 'image' => 'flowers.jpg'],
        ['id' => 3, 'name' => 'Crape Jasmine (Wathusudda)', 'category' => 'Flowering', 'price' => 1800, 'image' => 'flowers.jpg'],
        ['id' => 4, 'name' => 'Snake Plant (Sansevieria)', 'category' => 'Foliage', 'price' => 1500, 'image' => 'gardenitems.jpg'],
        ['id' => 5, 'name' => 'Peace Lily (Spathiphyllum)', 'category' => 'Flowering', 'price' => 1950, 'image' => 'flowers.jpg'],
        ['id' => 6, 'name' => 'Hibiscus Rosasinensis', 'category' => 'Flowering', 'price' => 1600, 'image' => 'flowers.jpg']
    ];
}

function optimizeImageForAI($base64Img, $maxDim = 1024) {
    if (empty($base64Img)) return [null, 'image/jpeg'];
    $mime = 'image/jpeg';
    if (preg_match('/^data:(image\/[a-zA-Z]+);base64,/', $base64Img, $m)) {
        $mime = $m[1];
        $base64Img = substr($base64Img, strpos($base64Img, ',') + 1);
    }
    $rawBytes = base64_decode($base64Img);
    if (!$rawBytes) return [$base64Img, $mime];
    if (function_exists('imagecreatefromstring')) {
        $srcImg = @imagecreatefromstring($rawBytes);
        if ($srcImg) {
            $w = imagesx($srcImg);
            $h = imagesy($srcImg);
            if ($w > $maxDim || $h > $maxDim) {
                $ratio = min($maxDim / $w, $maxDim / $h);
                $newW = max(1, (int)($w * $ratio));
                $newH = max(1, (int)($h * $ratio));
                $dstImg = imagecreatetruecolor($newW, $newH);
                imagealphablending($dstImg, false);
                imagesavealpha($dstImg, true);
                imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $w, $h);
                ob_start();
                imagejpeg($dstImg, null, 85);
                $compressed = ob_get_clean();
                imagedestroy($srcImg);
                imagedestroy($dstImg);
                if ($compressed) return [base64_encode($compressed), 'image/jpeg'];
            } else {
                imagedestroy($srcImg);
            }
        }
    }
    return [$base64Img, $mime];
}

$catalogSummaryStr = implode("; ", array_map(fn($p) => "ID:{$p['id']} - {$p['name']} (LKR {$p['price']})", $products));

// MULTIMODAL PROMPT ENGINEERING SYSTEM
$systemInstruction = "You are a Master Prompt Engineer and Senior AI Landscape Architect at Bloom & Bonsai Sri Lanka. " .
                    "Your mission is to inspect the uploaded space photo, detect its exact camera perspective angle, camera orientation ({$aspectRatioDesc}), wall/floor materials, and furniture layout, then engineer a photorealistic render prompt matching that exact orientation and camera perspective. " .
                    "You MUST respond ONLY with a valid JSON object.";

if ($mode === 'clean_slate') {
    $modeInstruction = "MODE: COMPLETE CLEAN-SLATE REDESIGN. Ignore any clutter or damaged items in the uploaded photo. Digitally clear the space while preserving the EXACT camera angle, vanishing point, and perspective of the photo ({$aspectRatioDesc}). Design a fresh, renovated luxury {$style} garden space using Bloom & Bonsai plants and teak wooden stands.";
} else {
    $modeInstruction = "MODE: ENHANCE EXISTING SPACE. Inspect the uploaded space photo carefully. Preserve the EXACT camera angle, perspective, and aspect ratio ({$aspectRatioDesc}). Identify key existing furniture (e.g. wooden sun lounger daybed, chairs), wall texture (brick wall), flooring (teak deck), and railing (black railing with city view). Keep existing furniture intact and strategically layer Bloom & Bonsai plants around them.";
}

$prompt = "Act as an Expert AI Landscape Architect & Lead Prompt Engineer.\n" .
          "Task: Analyze space input and photo.\n" .
          "Design Parameters:\n" .
          "- Aesthetic Style: {$style}\n" .
          "- Redesign Mode: {$modeInstruction}\n" .
          "- Camera Perspective & Aspect Ratio: {$aspectRatioDesc}\n" .
          "- Window/Space Orientation: {$orientation}\n" .
          "- Direct Sunlight: {$hours}\n" .
          "- User Preferences: " . ($userPrompt ?: "None provided") . "\n" .
          "- Available Store Catalog: [{$catalogSummaryStr}]\n\n" .
          "Generate a JSON response with keys:\n" .
          "1. \"visual_concept_prompt\": A comprehensive 8K photorealistic image prompt describing the transformed garden space.\n" .
          "2. \"architectural_summary\": A 3-sentence architectural design summary explaining the layout.\n" .
          "3. \"zone_a\": Placement details for Zone A (Sunlight Hotspot, high-light plants).\n" .
          "4. \"zone_b\": Placement details for Zone B (Filtered Midground Stand, medium-light plants).\n" .
          "5. \"zone_c\": Placement details for Zone C (Shaded Floor Base, shade foliage).\n" .
          "6. \"recommended_product_ids\": Array of integer IDs matching products from the catalog provided.\n" .
          "7. \"care_strategy\": Hydration, soil aeration, and acclimation guidance.";

list($sendImage, $sendMime) = optimizeImageForAI($base64Image);
if (empty($sendImage)) $sendImage = $base64Image;

$aiReply = callGemini15Flash($prompt, $systemInstruction, $sendImage, $sendMime);

$parsedJSON = null;
if ($aiReply) {
    $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($aiReply));
    $parsedJSON = json_decode($cleanJson, true);
}

// Aspect ratio-aware fallback if JSON parse fails or Gemini API key is unconfigured
if (!$parsedJSON || empty($parsedJSON['visual_concept_prompt'])) {
    if ($mode === 'clean_slate') {
        $visualPromptFallback = "8k photorealistic architectural render of a completely cleared modern luxury {$style} outdoor balcony garden, {$aspectRatioDesc}, warm {$orientation} natural daylight streaming, brick wall texture, polished teak wood deck flooring, focal Bonsai Ficus microcarpa on reclaimed teak wooden stand, Anthurium andraeanum with vibrant red spathes in matte ceramic pots, Snake Plants lining balcony railing overlooking city skyline, ambient morning mist, Architectural Digest photo quality, shot on Hasselblad 70mm lens";
    } else {
        $visualPromptFallback = "8k photorealistic architectural render of an outdoor brick apartment balcony, {$aspectRatioDesc}, retaining wooden sun lounger daybed with white cushions on teak wood deck flooring, black metal railing overlooking city view, tastefully layering Bloom & Bonsai plants: focal Bonsai Ficus on teak stand beside lounger, potted Anthurium on side table, Snake Plants lining brick wall, warm {$orientation} natural sunlight, Architectural Digest photography style";
    }

    $parsedJSON = [
        'visual_concept_prompt' => $visualPromptFallback,
        'architectural_summary' => "A custom " . strtoupper($style) . " balcony layout matching your original photo perspective, designed for " . $orientation . " with " . $hours . " of natural daylight.",
        'zone_a' => "Place direct light species like Bonsai Ficus and Bougainvillea along the balcony railing for maximum sun exposure.",
        'zone_b' => "Position Anthurium and Crape Jasmine on teak plant stands beside the lounger for indirect light.",
        'zone_c' => "Surround pot bases with Snake Plants and Ferns along the brick wall base for lush green contrast.",
        'recommended_product_ids' => [1, 2, 3, 4],
        'care_strategy' => "Water Zone A twice weekly; mist Zone B foliage 3x weekly."
    ];
}

// Build Pollinations AI Render URL with clean short prompt & aspect ratio
$cleanPromptShort = substr(preg_replace('/[^a-zA-Z0-9\s,]/', '', $parsedJSON['visual_concept_prompt']), 0, 220);
$encodedVisualPrompt = urlencode("8k photorealistic architectural garden render, " . $cleanPromptShort);
$randomSeed = rand(1000, 9999);

$renderUrl = "https://image.pollinations.ai/prompt/{$encodedVisualPrompt}?width={$origW}&height={$origH}&seed={$randomSeed}&nologo=true";

// Only pass &image= URL if the server is publicly accessible (not localhost/127.0.0.1)
if ($savedImagePublicUrl && strpos($savedImagePublicUrl, 'localhost') === false && strpos($savedImagePublicUrl, '127.0.0.1') === false) {
    $renderUrl .= "&image=" . urlencode($savedImagePublicUrl);
}

// Filter recommended products details from catalog
$recommendedProductsList = [];
$recIds = $parsedJSON['recommended_product_ids'] ?? [1, 2, 3, 4];
foreach ($products as $p) {
    if (in_array((int)$p['id'], array_map('intval', $recIds))) {
        $recommendedProductsList[] = $p;
    }
}
if (empty($recommendedProductsList)) {
    $recommendedProductsList = array_slice($products, 0, 4);
}

respond([
    'success' => true,
    'data' => [
        'render_url' => $renderUrl,
        'visual_concept_prompt' => $parsedJSON['visual_concept_prompt'],
        'architectural_summary' => $parsedJSON['architectural_summary'],
        'mode' => $mode,
        'style' => $style,
        'original_dimensions' => [
            'width' => $origW,
            'height' => $origH
        ],
        'zones' => [
            'zone_a' => $parsedJSON['zone_a'],
            'zone_b' => $parsedJSON['zone_b'],
            'zone_c' => $parsedJSON['zone_c']
        ],
        'recommended_products' => $recommendedProductsList,
        'care_strategy' => $parsedJSON['care_strategy'],
        'source' => 'Gemini 1.5 Flash Vision Prompt Engine + Pollinations FLUX AI'
    ]
]);
