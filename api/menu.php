<?php
require_once '../config.php';
header('Content-Type: application/json');
if (!loggedIn()) { echo json_encode(['error' => 'not_logged_in']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    echo json_encode(['error' => 'invalid_request']);
    exit;
}

$action = $input['action'] ?? '';
if ($action !== 'save') {
    echo json_encode(['error' => 'invalid_action']);
    exit;
}

$uid = (int) $_SESSION['uid'];
$token = preg_replace('/[^a-f0-9A-F]/', '', $input['token'] ?? '');
if (!$token) {
    echo json_encode(['error' => 'invalid_token']);
    exit;
}

$rawMenu = $input['menu_items'] ?? [];
$cleanMenu = [];
if (is_array($rawMenu)) {
    foreach ($rawMenu as $section) {
        if (!is_array($section)) {
            continue;
        }
        $category = trim($section['category'] ?? '') ?: 'Menu';
        $items = [];
        foreach ((array) ($section['items'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $name = trim($item['name'] ?? '');
            $price = trim((string) ($item['price'] ?? ''));
            if ($name === '') {
                continue;
            }
            $items[] = ['name' => $name, 'price' => $price ?: null];
        }
        if (!empty($items)) {
            $cleanMenu[] = ['category' => $category, 'items' => $items];
        }
    }
}

$db = db();
$menuJson = $cleanMenu ? $db->real_escape_string(json_encode($cleanMenu, JSON_UNESCAPED_UNICODE)) : 'NULL';
$stmt = "UPDATE pages SET menu_items=" . ($cleanMenu ? "'{$menuJson}'" : 'NULL') . ", updated_at=NOW() WHERE token='$token' AND user_id=$uid";
$db->query($stmt);
if ($db->affected_rows === 0) {
    echo json_encode(['error' => 'save_failed', 'message' => 'Page not found or not owned']);
    exit;
}

echo json_encode(['success' => true, 'menu_items' => $cleanMenu]);
