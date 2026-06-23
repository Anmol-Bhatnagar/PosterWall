<?php
require_once '../config.php';
header('Content-Type: application/json');
if (!loggedIn()) { echo json_encode(['error'=>'not_logged_in']); exit; }

$input      = json_decode(file_get_contents('php://input'), true);
$uid        = (int)$_SESSION['uid'];
$db         = db();

$pendingKey    = $input['pending_key']    ?? '';
$confirmedName = trim($input['business_name'] ?? '');
$mobile        = preg_replace('/\D/', '', $input['mobile'] ?? '');

// Validate we have at least a name or mobile
if ($confirmedName === '' && $mobile === '') {
    echo json_encode(['error' => 'name_or_mobile_required', 'msg' => 'Business name ya mobile number mein se ek zaroori hai.']);
    exit;
}

// Retrieve pending page from session
if (empty($_SESSION['pending_pages'][$pendingKey])) {
    echo json_encode(['error' => 'expired', 'msg' => 'Session expired — photo dobara upload karo.']);
    exit;
}

$pending    = $_SESSION['pending_pages'][$pendingKey];
unset($_SESSION['pending_pages'][$pendingKey]); // consume it

// Security: ensure session belongs to this user
if ((int)$pending['uid'] !== $uid) {
    echo json_encode(['error' => 'forbidden']);
    exit;
}

// Stale session (>30 min)
if (time() - $pending['created_at'] > 1800) {
    echo json_encode(['error' => 'expired', 'msg' => 'Session timeout — photo dobara upload karo.']);
    exit;
}

$bal       = wallet();
$powerUser = isPowerUser();
if (!$powerUser && $bal < PAGE_COST) {
    echo json_encode(['error'=>'no_balance','balance'=>$bal]);
    exit;
}

$info        = $pending['info'];
$htmlPage    = $pending['html'];
$regenToken  = $pending['regen_token'];

// Use confirmed name (user may have edited it)
$bizName = $confirmedName ?: ($info['business_name'] ?? 'My Business');
$bizType = $info['business_type'] ?? 'general';
$info['business_name'] = $bizName;

$mobVal  = $mobile ? "'{$mobile}'" : 'NULL';
$bizN    = $db->real_escape_string($bizName);
$bizT    = $db->real_escape_string($bizType);
$html    = $db->real_escape_string($htmlPage);
$menuJson = $db->real_escape_string(json_encode($info['menu_items'] ?? [], JSON_UNESCAPED_UNICODE));
$metaD   = $db->real_escape_string(substr($info['description'] ?? '', 0, 300));
$phone   = $db->real_escape_string($info['phone']    ?? '');
$wa      = $db->real_escape_string($info['whatsapp'] ?? $info['phone'] ?? '');
$addr    = $db->real_escape_string($info['address']  ?? '');

if ($regenToken) {
    $db->query("UPDATE pages SET html_content='$html', menu_items='$menuJson', business_name='$bizN', business_type='$bizT', meta_desc='$metaD', phone_no='$phone', whatsapp_no='$wa', address='$addr', updated_at=NOW() WHERE token='$regenToken' AND user_id=$uid");
    $token = $regenToken;
    // Update mobile on the page row if provided
    if ($mobile) {
        $db->query("UPDATE pages SET mobile='$mobile' WHERE token='$token' AND user_id=$uid");
    }
} else {
    // Generate unique 8-char token
    do {
        $token = substr(bin2hex(random_bytes(8)), 0, 8);
        $exists = $db->query("SELECT id FROM pages WHERE token='$token'")->num_rows;
    } while ($exists > 0);

    $db->query("INSERT INTO pages (user_id,token,mobile,business_type,business_name,html_content,menu_items,meta_title,meta_desc,phone_no,whatsapp_no,address) VALUES ($uid,'$token',$mobVal,'$bizT','$bizN','$html','$menuJson','$bizN','$metaD','$phone','$wa','$addr')");
    if ($mobile) $db->query("UPDATE users SET mobile='$mobile' WHERE id=$uid");
}

// Deduct from wallet
if (!$powerUser) {
    $db->query("UPDATE wallets SET balance=balance-" . PAGE_COST . " WHERE user_id=$uid AND balance>=" . PAGE_COST);
    $db->query("INSERT INTO transactions (user_id,amount,type,status,note) VALUES ($uid," . PAGE_COST . ",'debit','success','Page generated: $bizN')");
}
$db->query("INSERT INTO generations (user_id,type,cost) VALUES ($uid,'photo_to_page'," . ($powerUser ? 0 : PAGE_COST) . ")");

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
    'balance'    => $powerUser ? $bal : round($bal - PAGE_COST, 2),
    'info'       => $info,
]);
