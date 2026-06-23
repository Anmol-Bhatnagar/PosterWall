<?php
require_once '../config.php';
header('Content-Type: application/json');
if (!loggedIn()) { echo json_encode(['error'=>'not_logged_in']); exit; }

$input  = json_decode(file_get_contents('php://input'), true);
$uid    = (int)$_SESSION['uid'];
$db     = db();

// Check wallet
$bal = wallet();
if ($bal < PAGE_COST) { echo json_encode(['error'=>'no_balance','balance'=>$bal]); exit; }

$imageB64   = $input['image_base64'] ?? '';
$mime       = $input['mime_type']    ?? 'image/jpeg';
$mobile     = preg_replace('/\D/', '', $input['mobile'] ?? '');
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

// Ensure PosterWall branding
if (strpos($htmlPage, 'posterwall.in') === false) {
    $htmlPage = str_replace('</body>',
        '<div style="text-align:center;padding:20px;font-size:.78rem;color:#888;border-top:1px solid #eee;margin-top:32px;">Powered by <a href="https://posterwall.in" style="color:#FF6B00;font-weight:700;">PosterWall.in</a> — Apna Design. Apna Brand. Apni Pehchaan.</div></body>',
        $htmlPage);
}

// ── STEP 3: Save to database ──────────────────────────────────
// Generate unique 8-char token
do {
    $token = substr(bin2hex(random_bytes(8)), 0, 8);
    $exists = $db->query("SELECT id FROM pages WHERE token='$token'")->num_rows;
} while ($exists > 0);

$description = substr($info['description'] ?? '', 0, 300);
$phoneVal    = $info['phone'] ?? '';
$waVal       = $info['whatsapp'] ?? $info['phone'] ?? '';
$addrVal     = $info['address'] ?? '';

if ($regenToken) {
    // Update existing page using prepared statements
    $stmt = $db->prepare("UPDATE pages SET html_content=?, business_name=?, business_type=?, meta_desc=?, phone_no=?, whatsapp_no=?, address=?, updated_at=NOW() WHERE token=? AND user_id=?");
    $stmt->bind_param("ssssssssi", $htmlPage, $bizName, $bizType, $description, $phoneVal, $waVal, $addrVal, $regenToken, $uid);
    $stmt->execute();
    $token = $regenToken;
} else {
    // Insert new page using prepared statements
    $mobVal = $mobile ?: null;
    $stmt = $db->prepare("INSERT INTO pages (user_id, token, mobile, business_type, business_name, html_content, meta_title, meta_desc, phone_no, whatsapp_no, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssssss", $uid, $token, $mobVal, $bizType, $bizName, $htmlPage, $bizName, $description, $phoneVal, $waVal, $addrVal);
    $stmt->execute();
    if ($mobile) {
        $stmt_user = $db->prepare("UPDATE users SET mobile=? WHERE id=?");
        $stmt_user->bind_param("si", $mobile, $uid);
        $stmt_user->execute();
    }
}

// Deduct ₹9 from wallet
$db->query("UPDATE wallets SET balance=balance-" . PAGE_COST . " WHERE user_id=$uid AND balance>=" . PAGE_COST);
$db->query("INSERT INTO transactions (user_id,amount,type,status,note) VALUES ($uid," . PAGE_COST . ",'debit','success','Page generated: $bizN')");
$db->query("INSERT INTO generations (user_id,type,cost) VALUES ($uid,'photo_to_page'," . PAGE_COST . ")");

// QR code URL
$pageUrl = SITE_URL . '/p/' . $token;
$qrUrl   = 'https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=' . urlencode($pageUrl) . '&color=FF6B00&bgcolor=ffffff&format=png';

echo json_encode([
    'success'    => true,
    'token'      => $token,
    'url'        => $pageUrl,
    'mobile_url' => $mobile ? SITE_URL . '/m/' . $mobile : null,
    'qr_url'     => $qrUrl,
    'business'   => $bizName,
    'type'       => $bizType,
    'balance'    => round($bal - PAGE_COST, 2),
    'info'       => $info,
]);
