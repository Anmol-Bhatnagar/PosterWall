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

// Ensure PosterWall branding footer (always inject/replace)
$pwFooter = '<div style="text-align:center;padding:22px 20px 18px;font-size:.78rem;color:#888;border-top:2px solid #FF6B00;margin-top:32px;background:#fff9f5;">'
    . '<div style="font-weight:700;color:#FF6B00;font-size:.9rem;margin-bottom:4px;">Created by <a href="https://posterwall.in" style="color:#FF6B00;text-decoration:none;">PosterWall.in</a></div>'
    . '<div style="color:#aaa;font-size:.72rem;"><strong style="color:#555;">A Product of TechEagles</strong> &nbsp;|&nbsp; Under Mahakumbrix Innovation</div>'
    . '</div>';
$htmlPage = preg_replace('/<div[^>]*>\s*(?:Powered by|Created by)[^<]*<a[^>]*posterwall\.in[^<]*<\/a>[^<]*<\/div>/i', '', $htmlPage);
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
