<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!loggedIn()) { echo json_encode(['error' => 'not_logged_in']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$uid   = (int)$_SESSION['uid'];
$db    = db();

$action = $input['action'] ?? '';

// ── PREVIEW: Generate modified HTML via OpenRouter ───────────
if ($action === 'preview') {
    $token  = $db->real_escape_string($input['token'] ?? '');
    $prompt = trim($input['prompt'] ?? '');

    if (!$token || !$prompt) {
        echo json_encode(['error' => 'missing_params']); exit;
    }

    // Fetch the page — must belong to the logged-in user
    $row = $db->query("SELECT html_content, business_name FROM pages WHERE token='$token' AND user_id=$uid AND is_active=1")->fetch_assoc();
    if (!$row) {
        echo json_encode(['error' => 'page_not_found']); exit;
    }

    $currentHtml = $row['html_content'];
    $bizName     = $row['business_name'];

    $systemPrompt = "You are an expert HTML/CSS developer. You will receive a complete HTML page and a user's change request.\n"
        . "Apply ONLY the changes the user asks for — do not restructure the entire page unless explicitly asked.\n"
        . "Return ONLY the complete modified HTML — no explanation, no markdown fences, no preamble.\n"
        . "Preserve all existing functionality (QR codes, WhatsApp links, menus, etc).\n"
        . "The page already has a branding section at the bottom saying 'Created by PosterWall.in' and 'A Product of TechEagles | Under Mahakumbrix Innovation' — keep it exactly as-is, do not move, duplicate, or remove it.";

    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user',   'content' =>
            "Here is the current page HTML:\n\n" . $currentHtml .
            "\n\n---\n\nPlease make the following changes:\n" . $prompt
        ],
    ];

    $modifiedHtml = callOpenRouter($messages, OR_HTML_MODEL, 8000);

    if (!$modifiedHtml || str_starts_with($modifiedHtml, 'openrouter_error:')) {
        echo json_encode(['error' => 'ai_failed', 'detail' => $modifiedHtml]); exit;
    }

    // Strip accidental markdown fences
    $modifiedHtml = preg_replace('/^```html\s*/i', '', trim($modifiedHtml));
    $modifiedHtml = preg_replace('/```\s*$/', '', $modifiedHtml);
    $modifiedHtml = trim($modifiedHtml);

    if (strlen($modifiedHtml) < 200) {
        echo json_encode(['error' => 'empty_response']); exit;
    }

    echo json_encode([
        'success'       => true,
        'modified_html' => $modifiedHtml,
        'business_name' => $bizName,
    ]);
    exit;
}

// ── SAVE: Commit the accepted changes to DB ───────────────────
if ($action === 'save') {
    $token        = $db->real_escape_string($input['token'] ?? '');
    $modifiedHtml = $input['modified_html'] ?? '';

    if (!$token || strlen($modifiedHtml) < 200) {
        echo json_encode(['error' => 'missing_params']); exit;
    }

    // Verify ownership
    $exists = $db->query("SELECT id FROM pages WHERE token='$token' AND user_id=$uid")->num_rows;
    if (!$exists) {
        echo json_encode(['error' => 'page_not_found']); exit;
    }

    $safeHtml = $db->real_escape_string($modifiedHtml);
    $db->query("UPDATE pages SET html_content='$safeHtml', updated_at=NOW() WHERE token='$token' AND user_id=$uid");

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'invalid_action']);
