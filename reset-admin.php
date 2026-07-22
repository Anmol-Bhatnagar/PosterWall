<?php
require_once 'config.php';

// Restrict access to CLI or local environment (localhost) only
if (php_sapi_name() !== 'cli' && !isLocalHost()) {
    http_response_code(403);
    die('403 Forbidden: Admin reset is only allowed from CLI or local environment.');
}

$hash = password_hash('PosterWall@Admin2025', PASSWORD_BCRYPT);
$db   = db();
$esc  = $db->real_escape_string($hash);
$db->query("UPDATE users SET password='$esc',role='admin' WHERE email='posterwall.in@gmail.com'");
if ($db->affected_rows===0) {
    $db->query("INSERT INTO users (name,email,password,avatar,role) VALUES ('PosterWall Admin','posterwall.in@gmail.com','$esc','https://ui-avatars.com/api/?name=Admin&background=FF6B00&color=fff','admin') ON DUPLICATE KEY UPDATE password='$esc',role='admin'");
    $uid = $db->insert_id ?: $db->query("SELECT id FROM users WHERE email='posterwall.in@gmail.com'")->fetch_assoc()['id'];
    $db->query("INSERT INTO wallets (user_id,balance) VALUES ($uid,999) ON DUPLICATE KEY UPDATE balance=999");
}
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:sans-serif;background:#0a0a14;color:#f0f0f0;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;}.b{background:#1a2540;border:1px solid rgba(255,107,0,.3);border-radius:16px;padding:40px;max-width:440px;}.h{color:#FF6B00;font-size:1.5rem;font-weight:800;margin-bottom:16px;}.i{background:#12192b;border-radius:10px;padding:16px;margin:16px 0;font-size:.9rem;line-height:2;text-align:left;}.w{color:#ff5555;font-size:.82rem;margin-top:12px;}a{color:#FF6B00;}</style></head><body><div class="b"><div class="h">✅ Admin Reset Done!</div><div class="i">📧 Email: posterwall.in@gmail.com<br>🔑 Password: PosterWall@Admin2025<br>🌐 Login: <a href="admin/login.php">admin/login.php</a></div><div class="w">⚠️ DELETE this file now!<br><code>rm /var/www/html/reset-admin.php</code></div><br><a href="admin/login.php" style="display:inline-block;margin-top:8px;background:#FF6B00;color:#fff;padding:12px 24px;border-radius:10px;font-weight:700;">Go to Admin →</a></div></body></html>';
?>
