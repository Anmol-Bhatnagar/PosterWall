<?php
require_once '../config.php';

$token = preg_replace('/[^a-f0-9A-F]/', '', $_GET['t'] ?? basename(strtok($_SERVER['REQUEST_URI'], '?')));
if (!$token || strlen($token) < 6) { header('Location: ' . siteUrl('')); exit; }

$db  = db();
$tok = $db->real_escape_string($token);
$res = $db->query("SELECT * FROM pages WHERE token='$tok' AND is_active=1");

if ($res === false) {
    error_log("p/index.php query failed for token=$tok: " . $db->error);
    http_response_code(500);
    ?><!DOCTYPE html>
    <html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Error — PosterWall</title>
    <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;text-align:center;padding:20px;background:#fff;color:#333;}
    a{display:inline-block;background:#FF6B00;color:#fff;padding:12px 28px;border-radius:10px;font-weight:600;text-decoration:none;}</style>
    </head><body><h2 style="color:#FF6B00;">Kuch Galat Hua</h2>
    <p>Server mein thodi problem hai. Thodi der baad try karo.</p>
    <a href="<?= siteUrl('') ?>">PosterWall pe Jao →</a></body></html>
    <?php exit;
}

if ($res->num_rows === 0) {
    http_response_code(404);
    ?><!DOCTYPE html>
    <html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Page Not Found — PosterWall</title>
    <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;text-align:center;padding:20px;background:#fff;color:#333;}
    a{display:inline-block;background:#FF6B00;color:#fff;padding:12px 28px;border-radius:10px;font-weight:600;text-decoration:none;}</style>
    </head><body><h2 style="color:#FF6B00;">Page Nahi Mila</h2>
    <p>Yeh link expire ho gaya ya galat hai.</p>
    <a href="<?= siteUrl('') ?>">PosterWall pe Jao →</a></body></html>
    <?php exit;
}

$page = $res->fetch_assoc();
$db->query("UPDATE pages SET views=views+1 WHERE token='$tok'");

header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: SAMEORIGIN');
echo $page['html_content'];
