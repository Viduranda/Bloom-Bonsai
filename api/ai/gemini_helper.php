<?php
// api/ai/gemini_helper.php — Gemini 1.5 Flash API Helper

function getGeminiApiKeys() {
    $keys = [];
    $envKey = getEnvVar('GEMINI_API_KEY', '');
    if (!empty($envKey)) $keys[] = $envKey;

    // Primary Garden Designer & Multimodal Key
    $keys[] = base64_decode('QVEuQWI4Uk42SXpOQ3BnM1FZeVBaay14eEV5N3BiOGZ3alpkOUlMNUUxWVFhOXRMV0tuQVE=');
    // Backup Key 2
    $keys[] = base64_decode('QVEuQWI4Uk42S2EzemhjYnprSEExRG5lYmlQQWpTcGtaSld0bHhyXzZMY081SE55dFZQWnc=');
    // Secondary Backup Key 3
    $keys[] = base64_decode('QVEuQWI4Uk42Sjh2ZmI1OU1aRE5JZm1hM2NFTjU4bTFYMUJzbEJWS3duLVAwZ081bXpCQ0E=');

    return array_unique(array_filter($keys));
}

function getGeminiApiKey() {
    $keys = getGeminiApiKeys();
    return $keys[0] ?? '';
}

function callGemini15Flash($prompt, $systemInstruction = '', $base64Image = null, $mimeType = 'image/jpeg') {
    $apiKeys = getGeminiApiKeys();

    if (empty($apiKeys)) {
        return null;
    }

    $modelsToTry = [
        'gemini-flash-lite-latest',
        'gemini-flash-latest'
    ];

    $parts = [];
    if (!empty($prompt)) {
        $parts[] = ['text' => $prompt];
    }
    if (!empty($base64Image)) {
        // Strip data URI header if present
        if (preg_match('/^data:(image\/[a-zA-Z]+);base64,/', $base64Image, $m)) {
            $mimeType = $m[1];
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
        }
        $parts[] = [
            'inlineData' => [
                'mimeType' => $mimeType,
                'data' => $base64Image
            ]
        ];
    }

    $payload = [
        'contents' => [
            ['parts' => $parts]
        ]
    ];

    if (!empty($systemInstruction)) {
        $payload['systemInstruction'] = [
            'parts' => [
                ['text' => $systemInstruction]
            ]
        ];
    }

    $payloadJson = json_encode($payload);
    $GLOBALS['GEMINI_LAST_ERROR'] = '';

    foreach ($apiKeys as $apiKey) {
        foreach ($modelsToTry as $modelName) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/" . urlencode($modelName) . ":generateContent?key=" . urlencode($apiKey);

            for ($attempt = 0; $attempt < 2; $attempt++) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_POSTFIELDS => $payloadJson,
                    CURLOPT_TIMEOUT => 25,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = curl_error($ch);
                curl_close($ch);

                if (!$err && $response && $httpCode === 200) {
                    $data = json_decode($response, true);
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        return trim($data['candidates'][0]['content']['parts'][0]['text']);
                    }
                }

                $snippet = $response ? substr($response, 0, 150) : ($err ? $err : 'No Response');
                $GLOBALS['GEMINI_LAST_ERROR'] = "Model $modelName HTTP $httpCode: $snippet";

                if ($httpCode === 429) {
                    sleep(2);
                } else {
                    break;
                }
            }
        }
    }

    return null;
}
