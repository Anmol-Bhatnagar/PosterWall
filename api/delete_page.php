<?php
require_once '../config.php';
requireAuth();

header('Content-Type: application/json');

$data  = json_decode(file_get_contents('php://input'), true);
$token = preg_replace('/[^a-f0-9A-F]/', '', $data['token'] ?? '');

if (!$token) {
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

$uid = (int)$_SESSION['uid'];
$db  = db();
$tok = $db->real_escape_string($token);

// Only allow the owner to delete their own page
$res = $db->query("SELECT id FROM pages WHERE token='$tok' AND user_id=$uid AND is_active=1");
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Page not found or not yours']);
    exit;
}

// Soft-delete: set is_active=0
$db->query("UPDATE pages SET is_active=0 WHERE token='$tok' AND user_id=$uid");

echo json_encode(['success' => true]);
