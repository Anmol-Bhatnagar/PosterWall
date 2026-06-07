<?php
require_once '../config.php';
header('Content-Type: application/json');
if (!loggedIn()) { echo json_encode(['error'=>'not_logged_in']); exit; }

$input  = json_decode(file_get_contents('php://input'), true);
$uid    = (int)$_SESSION['uid'];
$db     = db();

// Check wallet
$bal = wallet();
$powerUser = isPowerUser();
if (!$powerUser && $bal < PAGE_COST) { echo json_encode(['error'=>'no_balance','balance'=>$bal]); exit; }

$imageB64   = $input['image_base64'] ?? '';
$mime       = $input['mime_type']    ?? 'image/jpeg';
$regenToken = isset($input['regen_token']) ? $db->real_escape_string($input['regen_token']) : null;

if (!$imageB64) { echo json_encode(['error'=>'no_image']); exit; }

// ── STEP 1: Extract info from photo using Vision AI ───────────
$rawExtract = extractFromImage($imageB64, $mime);

if (!$rawExtract) {
    echo json_encode(['error' => 'vision_failed', 'msg' => 'Could not read image. Try a clearer photo.']);
    exit;
}

$info = extractJsonFromText($rawExtract);
if (!$info) {
    $info = repairJsonResponse($rawExtract);
}

if (!$info || !is_array($info)) {
    echo json_encode(['error' => 'extract_failed', 'raw' => $rawExtract]);
    exit;
}

$info = normalizeBusinessInfo($info);
$bizName = $info['business_name'] ?? 'My Business';
$bizType = $info['business_type'] ?? 'general';

// ── STEP 2: Generate beautiful HTML page using Claude ─────────
$htmlPage = generateHTMLPage($info, $imageB64, $mime);

if (!$htmlPage || strlen($htmlPage) < 300) {
    echo json_encode(['error' => 'html_generation_failed']);
    exit;
}

// Clean HTML
$htmlPage = cleanHTML($htmlPage);

// Ensure mobile-friendly viewport
if (strpos($htmlPage, 'viewport') === false) {
    $htmlPage = str_replace('<head>', '<head><meta name="viewport" content="width=device-width,initial-scale=1">', $htmlPage);
}



// ── Ensure PosterWall branding footer (always strip AI attempts, then inject cleanly) ──

// Step 1: Remove any <footer> tags the AI generated (we'll add our own)
$htmlPage = preg_replace('/<footer[\s\S]*?<\/footer>/i', '', $htmlPage);

// Step 2: Remove inner branding content from any nested div/p/span/address elements.
// Run twice — nested structures may need two passes (inner div first, then outer wrapper).
$brandingPattern = '/<(?:div|p|span|address)[^>]*>(?:[^<]|<(?!\/?(?:div|p|span|address)))*(?:posterwall\.in|TechEagles|Mahakumbrix|Created by|Powered by)[\s\S]*?<\/(?:div|p|span|address)>/i';
$htmlPage = preg_replace($brandingPattern, '', $htmlPage);
$htmlPage = preg_replace($brandingPattern, '', $htmlPage);

// Step 2b: Remove empty div shells left behind after inner content was stripped.
// Loop until stable — each pass may expose a new layer of empty wrappers.
$emptyDivPattern = '/<div[^>]*>\s*<\/div>/i';
$prev = null;
$iterations = 0;
while ($prev !== $htmlPage && $iterations < 10) {
    $prev = $htmlPage;
    $htmlPage = preg_replace($emptyDivPattern, '', $htmlPage);
    $iterations++;
}

// Step 3: Build one clean, standalone <footer> with the branding
$pwFooter = '<footer style="background:#1a1208;color:rgba(255,255,255,0.55);text-align:center;padding:28px 20px 24px;font-family:sans-serif;">'
    . '<address style="font-style:normal;font-size:0.82rem;line-height:1.9;">'
    . 'Created by <a href="https://posterwall.in" target="_blank" rel="noopener noreferrer" style="color:#FF6B00;text-decoration:none;font-weight:600;">PosterWall.in</a>'
    . '<p style="margin-top:4px;font-size:0.75rem;color:rgba(255,255,255,0.4);"><strong style="color:rgba(255,255,255,0.5);">A Product of TechEagles</strong> &nbsp;|&nbsp; Under Mahakumbrix Innovation</p>'
    . '</address>'
    . '</footer>';

// Step 4: Inject as last element before </body>
if (strpos($htmlPage, '</body>') !== false) {
    $htmlPage = str_replace('</body>', $pwFooter . '</body>', $htmlPage);
} else {
    $htmlPage .= $pwFooter;
}

// ── STEP 3: Store in session (pending — awaiting user confirmation) ──
$pendingKey = bin2hex(random_bytes(16));
$_SESSION['pending_pages'][$pendingKey] = [
    'uid'         => $uid,
    'info'        => $info,
    'html'        => $htmlPage,
    'regen_token' => $regenToken,
    'created_at'  => time(),
];

// Return detected info for user confirmation — do NOT save to DB yet
echo json_encode([
    'pending'        => true,
    'pending_key'    => $pendingKey,
    'detected_name'  => $bizName,
    'detected_type'  => $bizType,
    'info'           => $info,
]);
