<?php
// ============================================================
// POSTERWALL.IN v2.0 — DIGITAL IDENTITY PLATFORM
// Tech Eagles | Mahakumbrix Innovation
// ============================================================
session_start();

// ── Load .env file for production deployment ──────────────
function loadEnvFile(string $path = ''): void {
    if (empty($path)) {
        $path = __DIR__ . '/.env';
    }
    
    if (!file_exists($path)) {
        return; // .env is optional; use system environment variables
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') === false) {
            continue;
        }
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove quotes if present
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        
        // Only set if not already set in system environment
        if (!getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Load .env file
loadEnvFile();

function env(string $key, $default = null) {
    // Check system environment first
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }
    
    // Check $_ENV array
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    
    // Return default
    return $default;
}

function isLocalHost(): bool {
    if (php_sapi_name() === 'cli') {
        return true;
    }
    $host = $_SERVER['HTTP_HOST'] ?? '';
    return preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $host) === 1;
}

function defineConfig(string $name, $value): void {
    if (!defined($name)) {
        define($name, $value);
    }
}

// Optional local override file. Create posterwall2/config.local.php and return an array of values.
$localConfigPath = __DIR__ . '/config.local.php';
if (file_exists($localConfigPath)) {
    $localOverrides = include $localConfigPath;
    if (is_array($localOverrides)) {
        foreach ($localOverrides as $key => $value) {
            defineConfig($key, $value);
        }
    }
}

defineConfig('APP_ENV', env('APP_ENV', isLocalHost() ? 'development' : 'production'));
defineConfig('DEBUG_MODE', env('DEBUG_MODE', APP_ENV !== 'production'));

if (DEBUG_MODE) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Branding
defineConfig('SITE_NAME',    env('SITE_NAME', 'PosterWall'));
defineConfig('SITE_TAGLINE', env('SITE_TAGLINE', 'Apna Design. Apna Brand. Apni Pehchaan.'));
defineConfig('COMPANY',      env('COMPANY', 'Tech Eagles'));
defineConfig('PARENT',       env('PARENT', 'Mahakumbrix Innovation'));
defineConfig('SITE_URL',     env('SITE_URL', detectSiteUrl()));
defineConfig('SUPPORT_EMAIL',env('SUPPORT_EMAIL', 'support@posterwall.in'));
defineConfig('YEAR',         env('YEAR', '2025'));

// ── OpenRouter AI (Vision + HTML Generation) ──────────────────
// ⚠️  IMPORTANT: Set these in .env file, never hardcode in production!
defineConfig('OR_API_KEY',    env('OR_API_KEY', ''));
defineConfig('OR_API_URL',    env('OR_API_URL', 'https://openrouter.ai/api/v1/chat/completions'));
defineConfig('OR_SITE_URL',   env('OR_SITE_URL', SITE_URL));
defineConfig('OR_SITE_NAME',  env('OR_SITE_NAME', SITE_NAME));

// Vision model (reads photos) — cheap + accurate
defineConfig('OR_VISION_MODEL',   env('OR_VISION_MODEL', 'google/gemini-2.0-flash-exp:free'));
defineConfig('OR_FALLBACK_VISION',env('OR_FALLBACK_VISION', 'google/gemini-flash-1.5'));
// HTML generation model (creates beautiful pages)
defineConfig('OR_HTML_MODEL',     env('OR_HTML_MODEL', 'anthropic/claude-sonnet-4-5'));
defineConfig('OR_FALLBACK_HTML',  env('OR_FALLBACK_HTML', 'x-ai/grok-3-mini-beta'));
defineConfig('OR_MAX_TOKENS',     env('OR_MAX_TOKENS', 4096));

// ── Razorpay ──────────────────────────────────────────────────
// ⚠️  IMPORTANT: Set these in .env file, never hardcode in production!
defineConfig('RZP_KEY_ID',     env('RZP_KEY_ID', ''));
defineConfig('RZP_KEY_SECRET', env('RZP_KEY_SECRET', ''));

// ── Google OAuth ──────────────────────────────────────────────
// ⚠️  IMPORTANT: Set these in .env file, never hardcode in production!
defineConfig('G_CLIENT_ID',     env('G_CLIENT_ID', ''));
defineConfig('G_CLIENT_SECRET', env('G_CLIENT_SECRET', ''));
defineConfig('G_REDIRECT',      env('G_REDIRECT', SITE_URL . '/auth/callback.php'));

// ── Pricing ──────────────────────────────────────────────────
defineConfig('PAGE_COST', env('PAGE_COST', 9)); // ₹9 per page generation

// ── Database ──────────────────────────────────────────────────
defineConfig('DB_HOST', env('DB_HOST', 'localhost'));
defineConfig('DB_USER', env('DB_USER', 'root'));
defineConfig('DB_PASS', env('DB_PASS', ''));
defineConfig('DB_NAME', env('DB_NAME', 'posterwall2'));

// ── DB Connect ────────────────────────────────────────────────
function db() {
    static $conn = null;
    if ($conn === null) {
            mysqli_report(MYSQLI_REPORT_OFF);
        try {
            $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        } catch (mysqli_sql_exception $ex) {
            $error_msg = 'Database connection failed: ' . $ex->getMessage();
            error_log($error_msg);
            if (DEBUG_MODE) {
                die(json_encode(['error' => $error_msg]));
            }
            http_response_code(503);
            die(json_encode(['error' => 'Database connection failed. Please verify your DB credentials and configuration.']));
        }

        if ($conn->connect_error) {
            $error_msg = 'Database connection failed: ' . $conn->connect_error;
            error_log($error_msg);
            if (DEBUG_MODE) {
                die(json_encode(['error' => $error_msg]));
            }
            http_response_code(503);
            die(json_encode(['error' => 'Database connection failed. Please verify your DB credentials and configuration.']));
        }

        $conn->set_charset('utf8mb4');
        $conn->query("SET sql_mode='STRICT_TRANS_TABLES'");
        ensurePagesMenuColumn($conn);
        ensureUserPowerColumn($conn);
    }
    return $conn;
}

function ensurePagesMenuColumn(mysqli $conn): void {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;
    $result = $conn->query("SHOW COLUMNS FROM pages LIKE 'menu_items'");
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE pages ADD COLUMN menu_items LONGTEXT NULL AFTER html_content");
    }
}

function ensureUserPowerColumn(mysqli $conn): void {
    static $checked = false;
    if ($checked) return;
    $checked = true;
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_power_user'");
    if ($result && $result->num_rows === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN is_power_user TINYINT(1) NOT NULL DEFAULT 0");
    }
}

// ── Auth helpers ──────────────────────────────────────────────
function loggedIn()    { return isset($_SESSION['uid']); }
function siteUrl(string $path = ''): string { return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/'); }
function detectSiteUrl(): string {
    if (php_sapi_name() === 'cli') {
        return 'http://localhost/posterwall2';
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $configDir = realpath(__DIR__) ?: __DIR__;
    $relativePath = '/';
    if ($docRoot && $configDir && str_starts_with($configDir, $docRoot)) {
        $relativePath = substr($configDir, strlen($docRoot));
        $relativePath = str_replace('\\', '/', $relativePath);
    } elseif (!empty($_SERVER['SCRIPT_NAME'])) {
        $relativePath = dirname($_SERVER['SCRIPT_NAME']);
    }
    $relativePath = trim($relativePath, '/');
    return $scheme . '://' . $host . ($relativePath !== '' ? '/' . $relativePath : '');
}

function requireAuth() {
    if (!loggedIn()) {
        $current = $_SERVER['REQUEST_URI'] ?? '';
        $next = urlencode($current);
        header('Location: ' . siteUrl('auth/login.php?next=' . $next));
        exit;
    }
}
function me() {
    if (!loggedIn()) return null;
    $id = (int)$_SESSION['uid'];
    return db()->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
}
function wallet() {
    if (!loggedIn()) return 0;
    $id = (int)$_SESSION['uid'];
    $r  = db()->query("SELECT balance FROM wallets WHERE user_id=$id")->fetch_assoc();
    return $r ? (float)$r['balance'] : 0;
}
function isPowerUser(?array $u = null): bool {
    if ($u === null) $u = me();
    if (!$u) return false;
    if (($u['role'] ?? '') === 'admin') return true;
    return !empty($u['is_power_user']);
}

// ── OpenRouter API Call (Text + Vision) ───────────────────────
function callOpenRouter(array $messages, string $model, int $maxTokens = 4096): ?string {
    if (empty(OR_API_KEY)) {
        return 'openrouter_error: OR_API_KEY is not configured';
    }

    $payload = [
        'model'       => $model,
        'max_tokens'  => $maxTokens,
        'messages'    => $messages,
        'temperature' => 0.8,
    ];

    $ch = curl_init(OR_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OR_API_KEY,
            'HTTP-Referer: ' . OR_SITE_URL,
            'X-Title: ' . OR_SITE_NAME,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);

    $res  = curl_exec($ch);
    $err  = curl_error($ch);
    if (PHP_VERSION_ID < 80500) {
        curl_close($ch);
    }

    if ($err) {
        return 'openrouter_error: ' . $err;
    }

    $data = json_decode($res, true);
    if (!$data) {
        return 'openrouter_response: ' . $res;
    }

    if (isset($data['error'])) {
        return 'openrouter_error: ' . json_encode($data['error']);
    }

    if (isset($data['choices'][0]['message']['content'])) {
        return $data['choices'][0]['message']['content'];
    }

    if (isset($data['choices'][0]['text'])) {
        return $data['choices'][0]['text'];
    }

    if (isset($data['output'][0]['content'])) {
        $text = '';
        $content = $data['output'][0]['content'];
        if (is_string($content)) {
            $text = $content;
        } elseif (is_array($content)) {
            foreach ($content as $piece) {
                if (is_string($piece)) {
                    $text .= $piece;
                } elseif (is_array($piece) && isset($piece['text'])) {
                    $text .= $piece['text'];
                }
            }
        }
        if ($text !== '') {
            return $text;
        }
    }

    if (isset($data['text']) && is_string($data['text'])) {
        return $data['text'];
    }

    if (isset($data['result']) && is_string($data['result'])) {
        return $data['result'];
    }

    return 'openrouter_response: ' . json_encode($data);
}

function extractJsonFromText(string $text): ?array {
    $text = trim($text);
    $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
    $text = preg_replace('/\s*```$/i', '', $text);

    if (preg_match('/\{[\s\S]*\}/m', $text, $matches)) {
        $candidate = $matches[0];
        $candidate = str_replace(['“', '”', '‘', '’'], ['"', '"', '"', '"'], $candidate);
        $candidate = preg_replace('/,\s*([\]}])/m', '$1', $candidate);
        $candidate = preg_replace_callback('/([\{\[,]\s*)([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', function ($m) {
            return $m[1] . '"' . $m[2] . '":';
        }, $candidate);
        $data = json_decode($candidate, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            return $data;
        }
    }

    return null;
}

function repairJsonResponse(string $response): ?array {
    $parsed = extractJsonFromText($response);
    if ($parsed) {
        return $parsed;
    }

    if (trim($response) === '') {
        return null;
    }

    $messages = [
        ['role' => 'system', 'content' => 'You are a strict JSON parser. Extract ONLY a valid JSON object from the provided text. Remove any markdown, explanation, or extra text. Return a JSON object that can be parsed by a standard JSON parser.'],
        ['role' => 'user', 'content' => [[
            'type' => 'text',
            'text' => "Parse this response into valid JSON only:\n\n$response"
        ]]]
    ];

    $repaired = callOpenRouter($messages, OR_FALLBACK_VISION, 1200);
    return $repaired ? extractJsonFromText($repaired) : null;
}

function normalizeBusinessInfo(array $info): array {
    $clean = [];
    $clean['business_name'] = trim($info['business_name'] ?? '') ?: null;
    $clean['business_type'] = strtolower(trim($info['business_type'] ?? '')) ?: 'general';
    $allowedTypes = ['restaurant','shop','clinic','professional','salon','gym','school','repair','event','general'];
    if (!in_array($clean['business_type'], $allowedTypes, true)) {
        $clean['business_type'] = 'general';
    }

    $clean['tagline'] = trim($info['tagline'] ?? '') ?: null;
    $clean['phone'] = preg_replace('/\D+/', '', trim($info['phone'] ?? '')) ?: null;
    $clean['whatsapp'] = preg_replace('/\D+/', '', trim($info['whatsapp'] ?? $clean['phone'] ?? '')) ?: null;
    $clean['address'] = trim($info['address'] ?? '') ?: null;
    $clean['email'] = trim($info['email'] ?? '') ?: null;
    $clean['website'] = trim($info['website'] ?? '') ?: null;
    $clean['timings'] = trim($info['timings'] ?? '') ?: null;
    $clean['description'] = trim($info['description'] ?? '') ?: null;
    $clean['color_theme'] = trim($info['color_theme'] ?? '') ?: null;
    $clean['language'] = trim($info['language'] ?? '') ?: null;

    $clean['services'] = [];
    foreach ((array) ($info['services'] ?? []) as $service) {
        if (is_string($service) && trim($service) !== '') {
            $clean['services'][] = trim($service);
        }
    }

    $clean['specialities'] = [];
    foreach ((array) ($info['specialities'] ?? []) as $speciality) {
        if (is_string($speciality) && trim($speciality) !== '') {
            $clean['specialities'][] = trim($speciality);
        }
    }

    $clean['menu_items'] = [];
    $menuItems = $info['menu_items'] ?? [];
    if (is_string($menuItems)) {
        $menuItems = [$menuItems];
    }
    if (!is_array($menuItems)) {
        $menuItems = [];
    }

    foreach ($menuItems as $item) {
        if (is_string($item)) {
            $parsed = extractJsonFromText($item);
            if ($parsed) {
                $item = $parsed;
            }
        }

        if (!is_array($item)) {
            continue;
        }

        $category = trim($item['category'] ?? '') ?: 'Menu';
        $entries = [];
        foreach ((array) ($item['items'] ?? []) as $menuEntry) {
            if (!is_array($menuEntry)) {
                continue;
            }
            $name = trim($menuEntry['name'] ?? '');
            $price = trim((string) ($menuEntry['price'] ?? '')) ?: null;
            if ($name === '') {
                continue;
            }
            $entries[] = ['name' => $name, 'price' => $price];
        }

        if ($entries) {
            $clean['menu_items'][] = ['category' => $category, 'items' => $entries];
        }
    }

    if (empty($clean['menu_items']) && !empty($clean['services'])) {
        $clean['menu_items'][] = ['category' => 'Services', 'items' => array_map(function ($service) {
            return ['name' => $service, 'price' => null];
        }, $clean['services'])];
    }

    return $clean;
}

function renderMenuHtml(array $menuItems): string {
    if (empty($menuItems)) {
        return '';
    }

    $html = '<section id="pw-updated-menu" style="margin:0 auto 24px;max-width:1100px;padding:24px 18px;border-radius:26px;background:rgba(255,255,255,.92);box-shadow:0 32px 90px rgba(0,0,0,.08);font-family:Poppins,sans-serif;color:#111;">';
    $html .= '<div style="text-align:center;margin-bottom:18px;"><div style="font-family:Baloo 2,cursive;font-size:1.95rem;font-weight:800;color:#FF6B00;">📋 Updated Menu</div><p style="margin:10px auto 0;max-width:700px;color:#4b5563;font-size:0.95rem;">Yeh menu aapne manually update kiya hai. Agar yahan kuch hai, toh yeh section public page par dikhega.</p></div>';

    foreach ($menuItems as $section) {
        $category = trim($section['category'] ?? 'Menu');
        $items = (array) ($section['items'] ?? []);
        if (empty($items)) {
            continue;
        }

        $html .= '<div style="margin-bottom:20px;">';
        $html .= '<div style="font-size:1.15rem;font-weight:700;color:#1f2937;margin-bottom:12px;">' . htmlspecialchars($category, ENT_QUOTES, 'UTF-8') . '</div>';
        $html .= '<div style="display:grid;gap:10px;">';

        foreach ($items as $item) {
            $name = trim($item['name'] ?? '');
            $price = trim((string) ($item['price'] ?? ''));
            if ($name === '') {
                continue;
            }
            $priceHtml = $price !== '' ? htmlspecialchars($price, ENT_QUOTES, 'UTF-8') : '&nbsp;';
            $html .= '<div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-radius:16px;background:rgba(255,255,255,.98);border:1px solid rgba(226,232,240,.9);box-shadow:0 16px 30px rgba(15,23,42,.05);">';
            $html .= '<span style="font-weight:600;color:#111;">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</span>';
            $html .= '<span style="font-weight:700;color:#ef6c00;">' . $priceHtml . '</span>';
            $html .= '</div>';
        }

        $html .= '</div></div>';
    }

    $html .= '</section>';
    return $html;
}

function extractFromImage(string $base64Image, string $mimeType): ?string {
    $messages = [[
        'role'    => 'user',
        'content' => [
            [
                'type'      => 'image_url',
                'image_url' => [
                    'url'    => "data:{$mimeType};base64,{$base64Image}",
                    'detail' => 'high'
                ]
            ],
            [
                'type' => 'text',
                'text' => 'You are an expert at reading business signboards, menus, visiting cards, and shop boards from photos taken in India. Analyze this image VERY carefully.

Return ONLY valid JSON (no markdown, no explanation) and nothing else. Use standard JSON syntax. If a field is missing, set it to null.

The JSON object should use these exact fields:
{
  "business_name": "exact name or null",
  "business_type": "restaurant|shop|clinic|professional|salon|gym|school|repair|event|general",
  "tagline": "slogan if visible or null",
  "phone": "phone number or null",
  "whatsapp": "whatsapp number or null",
  "address": "full address or null",
  "email": "email or null",
  "website": "website or null",
  "timings": "opening hours or null",
  "services": ["service1","service2"],
  "menu_items": [{"category":"name","items":[{"name":"dish","price":"price"}]}],
  "specialities": ["special1"],
  "color_theme": "dominant color: orange|blue|green|red|yellow|purple|white|black or null",
  "language": "Hindi|English|English|Punjabi|Tamil|Mixed or null",
  "description": "2-3 line description of what this business does or null"
}

If a field is not visible, use null. Do not invent data. Return valid JSON only.'
            ]
        ]
    ]];

    // Try free vision model first
    $result = callOpenRouter($messages, OR_VISION_MODEL, 1500);

    // Fallback
    if (!$result) {
        $result = callOpenRouter($messages, OR_FALLBACK_VISION, 1500);
    }

    return $result;
}

// ── HTML Generation: Create beautiful page from business info ─────────────────────────────────
function generateHTMLPage(array $info, string $base64Image = '', string $mimeType = ''): ?string {
    $info = normalizeBusinessInfo($info);
    $infoJson = json_encode($info, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $imageContent = '';
    if ($base64Image) {
        $imageContent = "\n\nI'm also providing the original photo for visual reference and color inspiration.";
    }

    $systemPrompt = 'You are a world-class web designer who creates STUNNING, PRODUCTION-READY HTML pages for Indian businesses. You write complete, beautiful, mobile-first HTML with embedded CSS and JS. Your designs are memorable, professional, and uniquely crafted for each business type. You NEVER use generic templates. You always return only valid HTML, no markdown, no explanation.';

    $userContent = [];

    // Add image if available for color/style reference
    if ($base64Image) {
        $userContent[] = [
            'type'      => 'image_url',
            'image_url' => ['url' => "data:{$mimeType};base64,{$base64Image}", 'detail' => 'low']
        ];
    }

    $userContent[] = [
        'type' => 'text',
        'text' => "Create a STUNNING complete HTML page for this business.$imageContent

BUSINESS DATA:
$infoJson

REQUIREMENTS:
1. Complete HTML from <!DOCTYPE html> to </html>.
2. ALL CSS embedded in <style> inside <head>.
3. ALL JS embedded in <script> before </body>.
4. Use Google Fonts only, no external CSS frameworks.
5. Mobile-first, fully responsive, and clean on small screens.
6. Elegant spacing, clear typography, and polished visual hierarchy.
7. Professional color scheme based on business_type and color_theme.

DESIGN GUIDELINES BY BUSINESS TYPE:
- restaurant/food: warm, appetizing, rich oranges/reds, elegant food visuals.
- clinic/medical: clean, trustworthy, blues/whites, calming and easy to scan.
- shop/retail: vibrant, energetic, bold product showcase, bright accents.
- professional: minimal, premium, dark or soft neutral palette, luxury feeling.
- salon/beauty: modern, stylish, refined, gentle gradients or strong contrast.
- gym/fitness: dynamic, bold, dark background with sharp accent colors.
- school/coaching: friendly, trustworthy, bright blues/greens, easy hierarchy.
- repair/service: solid, clear, functional, dependable layout.
- general: warm orange/gold brand tones, confident modern styling.

MUST INCLUDE WHEN DATA IS AVAILABLE:
1. HERO with business name and tagline.
2. ABOUT section describing what makes them special.
3. SERVICES or MENU section with cards or lists.
4. CONTACT section with clickable phone, WhatsApp, email, and website.
5. LOCATION section with address and Google Maps search link.
6. TIMINGS or working hours.
7. A proper closing section after all content.

LINK RULES:
- Use tel: links only for valid numeric phone numbers.
- Use https://wa.me/91NUMBER for WhatsApp if valid.
- Use target='_blank' rel='noopener' for external links.

QUALITY RULES:
- NO Lorem ipsum or placeholders.
- DO NOT invent unrelated business details.
- If data is missing, omit that section gracefully.
- Use only the provided business data, and prefer exact text.
- Do not include any comments, explanations, or markdown.
- Output must be valid HTML and start with <!DOCTYPE html>.
- Add 80px of bottom padding to the <body> so content is never hidden behind the fixed share bar.

BRANDING — STRICTLY REQUIRED:
The last visible section of the page (just before </body>) MUST be a simple, full-width, centered text section that says exactly:
  Line 1: Created by PosterWall.in  (make PosterWall.in a clickable link to https://posterwall.in)
  Line 2: A Product of TechEagles | Under Mahakumbrix Innovation
Style it as plain centered text matching the page design — no card, no box, no shadow, no border-radius. Just clean text at the bottom of the page. Do not add this text anywhere else on the page.

If menu_items are missing but services exist, create a clean service section instead.
If contact details are missing, keep the page polished and do not show empty fields.
"
    ];

    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' => $userContent]
    ];

    // Use Claude for best HTML generation
    $result = callOpenRouter($messages, OR_HTML_MODEL, OR_MAX_TOKENS);

    // Fallback to Grok
    if (!$result || strlen($result) < 500 || stripos($result, '<!DOCTYPE html') === false) {
        $messages[1]['content'] = [['type' => 'text', 'text' => end($userContent)['text']]];
        $result = callOpenRouter($messages, OR_FALLBACK_HTML, OR_MAX_TOKENS);
    }

    if ($result && stripos($result, '<!DOCTYPE html') === false) {
        $result = "<!DOCTYPE html>\n<html lang=\"hi\">\n<head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width,initial-scale=1\"><title>{$info['business_name']}</title><style>body{font-family:sans-serif;background:#111;color:#fff;padding:24px;}</style></head><body><h1>" . htmlspecialchars($info['business_name'] ?? 'My Business', ENT_QUOTES) . "</h1><p>" . htmlspecialchars($info['description'] ?? '', ENT_QUOTES) . "</p></body></html>";
    }

    return $result;
}

// ── Clean HTML output ─────────────────────────────────────────
function cleanHTML(string $html): string {
    $html = trim($html);
    $html = preg_replace('/^```html\s*/i', '', $html);
    $html = preg_replace('/^```\s*/i',     '', $html);
    $html = preg_replace('/\s*```$/i',     '', $html);
    if (preg_match('/<!DOCTYPE html[\s\S]*<\/html>/i', $html, $matches)) {
        $html = $matches[0];
    }
    return trim($html);
}
?>
