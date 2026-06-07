<?php
require_once '../config.php';
if (!isset($_GET['code'])) {
    header('Location: ' . siteUrl('auth/login.php?error=' . urlencode('No code')));
    exit;
}

if (!isset($_GET['state'], $_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], $_GET['state'])) {
    unset($_SESSION['oauth_state']);
    header('Location: ' . siteUrl('auth/login.php?error=' . urlencode('Invalid session state')));
    exit;
}
unset($_SESSION['oauth_state']);

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS => http_build_query([
        'code' => $_GET['code'],
        'client_id' => G_CLIENT_ID,
        'client_secret' => G_CLIENT_SECRET,
        'redirect_uri' => G_REDIRECT,
        'grant_type' => 'authorization_code',
    ]),
    CURLOPT_TIMEOUT => 20,
]);
$tokRaw = curl_exec($ch);
$curlErr = curl_error($ch);
if (PHP_VERSION_ID < 80500) {
    curl_close($ch);
}

if ($tokRaw === false || $curlErr) {
    header('Location: ' . siteUrl('auth/login.php?error=' . urlencode('Token request failed')));
    exit;
}

$tok = json_decode($tokRaw, true);
if (!is_array($tok) || !isset($tok['access_token'])) {
    $error = $tok['error_description'] ?? $tok['error'] ?? 'Token failed';
    header('Location: ' . siteUrl('auth/login.php?error=' . urlencode($error)));
    exit;
}

$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $tok['access_token']],
    CURLOPT_TIMEOUT => 20,
]);
$infoRaw = curl_exec($ch);
$curlErr = curl_error($ch);
if (PHP_VERSION_ID < 80500) {
    curl_close($ch);
}

if ($infoRaw === false || $curlErr) {
    header('Location: ' . siteUrl('auth/login.php?error=' . urlencode('User info request failed')));
    exit;
}

$info = json_decode($infoRaw, true);

if (!is_array($info) || !isset($info['email'])) {
    $error = $info['error_description'] ?? $info['error'] ?? 'User info failed';
    header('Location: ' . siteUrl('auth/login.php?error=' . urlencode($error)));
    exit;
}

$db     = db();
$email  = $db->real_escape_string($info['email']);
$name   = $db->real_escape_string($info['name']);
$gid    = $db->real_escape_string($info['id']);
$avatar = $db->real_escape_string($info['picture'] ?? '');

$res = $db->query("SELECT id, role, is_power_user FROM users WHERE email='$email'");
if ($res->num_rows > 0) {
    $u = $res->fetch_assoc();
    $db->query("UPDATE users SET name='$name',google_id='$gid',avatar='$avatar' WHERE id={$u['id']}");
    $uid = $u['id'];
} else {
    $db->query("INSERT INTO users (name,email,google_id,avatar) VALUES ('$name','$email','$gid','$avatar')");
    $uid = $db->insert_id;
    // Free ₹9 welcome credit
    $db->query("INSERT INTO wallets (user_id,balance) VALUES ($uid,9) ON DUPLICATE KEY UPDATE balance=balance+9");
    $db->query("INSERT INTO transactions (user_id,amount,type,status,note) VALUES ($uid,9,'credit','success','Welcome bonus — 1 free page generation')");
}

$_SESSION['uid']    = $uid;
$_SESSION['name']   = $info['name'];
$_SESSION['email']  = $info['email'];
$_SESSION['avatar'] = $info['picture'] ?? '';

$redirect = siteUrl('dashboard/');
if (!empty($_SESSION['oauth_next'])) {
    $next = $_SESSION['oauth_next'];
    unset($_SESSION['oauth_next']);
    if (strpos($next, '/') === 0 && strpos($next, '//') === false) {
        $redirect = siteUrl(ltrim($next, '/'));
    }
}

header('Location: ' . $redirect);
exit;
?>
