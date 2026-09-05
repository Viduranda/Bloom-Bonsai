# 🎓 Student 3: AI Systems Specialist & Botanical Content Lead

**Role:** Generative AI Integration, Vision Models, LLM Prompt Engineering, & Botanical Research  
**Project:** Bloom & Bonsai — Luxury Botanical E-Commerce Platform  

---

## 📂 1. Assigned Modules & File Ownership

| File / Component | Type | Responsibility |
| :--- | :--- | :--- |
| `api/ai/diagnose.php` | AI Vision | AI Leaf Health Doctor endpoint powered by Google Gemini 1.5 Flash Vision |
| `api/ai/chat.php` | AI Chat LLM | Sprout AI Botanical Customer Assistant connected to store inventory |
| `api/ai/designer.php` | AI Architect | Multimodal 8K Garden Designer & Pollinations FLUX.1 Realism Render Engine |
| `api/ai/gemini_helper.php` | AI Helper | 3-Key Failover API pool & cURL HTTP request handler |
| `gardendesigner.html` | AI Frontend | Interactive Garden Designer interface, room orientation quiz, & 2D floorplan canvas |
| `docs/student3_ai_specialist.md` | Module Docs | Student 3 technical documentation & architecture specifications |

---

## 🔬 2. Technical Contributions & Feature Implementations

### A. AI Leaf Scanner & Disease Doctor (`api/ai/diagnose.php`)
* **Vision Model Integration:** Connected Google Gemini 1.5 Flash Vision API to inspect base64-encoded plant leaf photos uploaded by users.
* **Structured Diagnostic Output:** Engineered multimodal prompts returning structured JSON detailing identified plant species, confidence score, diagnosed disease/deficiency, immediate action steps, and preventive care.

### B. Sprout AI Botanical Assistant (`api/ai/chat.php`)
* **Store-Aware LLM Chatbot:** Integrated Gemini LLM system instructions with real-time MySQL product inventory.
* **Context Injection:** Injected active catalog pricing (in LKR), watering needs, and stock availability so the chatbot answers customer care queries while recommending matching store products.

### C. AI Garden Designer & 2D Blueprint Engine (`api/ai/designer.php`, `gardendesigner.html`)
* **8K Visual Concept Generation:** Developed landscape transformation engine using Pollinations FLUX.1 Realism Engine with camera angle & aspect ratio preservation (`1024x576` landscape / `576x1024` portrait).
* **2D Floorplan HTML5 Canvas Fallback:** Programmed interactive 2D Floorplan Blueprint canvas renderer displaying Zone A (Sunlight Hotspot), Zone B (Midground Stand), and Zone C (Shaded Floor Base) if image generation times out.
* **Image Optimization (`optimizeImageForAI`):** Built GD library image downscaling pipeline compressing heavy 10MB space photos to 1024px JPEGs (~120KB) for sub-2-second response times.

---

## 🔍 3. Verification & Key Code Snippets

```php
// 3-Key Failover Pool Engine (api/ai/gemini_helper.php)
function callGemini15Flash($prompt, $systemInstruction = '', $base64Image = null, $imageMime = 'image/jpeg') {
    $keys = getGeminiApiKeys();
    foreach ($keys as $apiKey) {
        $response = executeGeminiCurl($apiKey, $prompt, $systemInstruction, $base64Image, $imageMime);
        if ($response !== null) return $response;
    }
    return null;
}
```

```javascript
// Interactive 2D Blueprint Floorplan Rendering (script.js / gardendesigner.html)
window.draw2DBlueprintFloorplan = function(canvasId, style) {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = '#11291b';
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  // Draws room boundaries, sunlight vectors, and plant zones
};
```
