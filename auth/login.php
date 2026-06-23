<?php
// auth/login.php
require_once '../config.php';
if (loggedIn()) { header('Location: ' . siteUrl('dashboard/')); exit; }

if (!empty($_GET['next'])) {
    $next = $_GET['next'];
    $parsed = @parse_url($next);
    if (strpos($next, '/') === 0 && isset($parsed['path']) && strpos($next, '//') === false) {
        $_SESSION['oauth_next'] = strtok($next, "\r\n");
    }
}

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => G_CLIENT_ID,
    'redirect_uri' => G_REDIRECT,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
]);
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — PosterWall</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:#0a0a14;color:#f0f0f0;min-height:100svh;display:flex;align-items:center;justify-content:center;padding:20px;
  background-image:radial-gradient(ellipse at 50% 30%,rgba(255,107,0,.12) 0%,transparent 60%);}
.card{background:#1a2540;border:1px solid rgba(255,255,255,.07);border-radius:20px;padding:40px 28px;width:100%;max-width:400px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.4);}
.logo{font-family:'Baloo 2',cursive;font-size:2rem;font-weight:800;margin-bottom:6px;}
.logo span{color:#FF6B00;}
.sub{color:#8892a4;font-size:.875rem;margin-bottom:32px;line-height:1.6;}
.g-btn{display:flex;align-items:center;justify-content:center;gap:12px;width:100%;padding:14px;border-radius:12px;background:#fff;color:#333;font-weight:600;font-size:.95rem;border:none;cursor:pointer;text-decoration:none;box-shadow:0 4px 16px rgba(0,0,0,.3);transition:all .2s;}
.g-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.4);}
.g-btn img{width:22px;}
.free-badge{margin-top:20px;background:rgba(255,107,0,.1);border:1px solid rgba(255,107,0,.2);border-radius:10px;padding:14px;font-size:.82rem;color:#8892a4;}
.free-badge strong{color:#FF6B00;}
.back{margin-top:20px;font-size:.8rem;color:#8892a4;}
.back a{color:#FF6B00;}
</style>
</head>
<body>
<div class="card">
  <div class="logo">🦅 Poster<span>Wall</span></div>
  <p class="sub">Apna Design. Apna Brand. Apni Pehchaan.<br>AI se digital page banao sirf ₹9 mein!</p>
  <?php if(isset($_GET['error'])): ?>
  <div style="background:rgba(255,85,85,.1);border:1px solid rgba(255,85,85,.3);color:#ff5555;padding:12px;border-radius:10px;font-size:.85rem;margin-bottom:16px;"><?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>
  <a href="<?= $url ?>" class="g-btn">
    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="G">
    Google se Login Karo
  </a>
  <div class="free-badge">🎁 Pehla page <strong>FREE</strong> — ₹9 wallet mein milega signup pe!</div>
  <div class="back"><a href="<?= siteUrl('') ?>">← Wapas Jao</a></div>
</div>
</body>
</html>
