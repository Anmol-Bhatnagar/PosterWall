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
:root{--p:#FF6B00;--bg:#ffffff;--bg2:#f7f9fc;--text:#1a1a2e;--muted:#6c7b94;--border:rgba(0,0,0,0.08);}
[data-theme="dark"]{--bg:#0a0e27;--bg2:#111d3a;--text:#f0f2f5;--muted:#8892a4;--border:rgba(255,255,255,0.08);}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);min-height:100svh;display:flex;align-items:center;justify-content:center;padding:20px;overflow:hidden;transition:background .3s ease;position:relative;}
body::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 50% 30%,rgba(255,107,0,.12) 0%,transparent 65%);pointer-events:none;}
.card{position:relative;z-index:1;background:rgba(255,255,255,.7);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1.5px solid var(--border);border-radius:24px;padding:44px 32px;width:100%;max-width:420px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.1);}
[data-theme="dark"] .card{background:rgba(26,45,79,.5);}
.logo{font-family:'Baloo 2',cursive;font-size:2.2rem;font-weight:800;margin-bottom:12px;background:linear-gradient(135deg,var(--p),#FFD700);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.logo span{-webkit-text-fill-color:unset;}
.sub{color:var(--muted);font-size:.9rem;margin-bottom:36px;line-height:1.7;}
.g-btn{display:flex;align-items:center;justify-content:center;gap:12px;width:100%;padding:16px;border-radius:14px;background:linear-gradient(135deg,var(--p),#e05a00);color:#fff;font-weight:600;font-size:.95rem;border:none;cursor:pointer;text-decoration:none;box-shadow:0 8px 24px rgba(255,107,0,.3);transition:all .3s;}
.g-btn:hover{transform:translateY(-3px);box-shadow:0 12px 32px rgba(255,107,0,.4);}
.g-btn img{width:22px;filter:brightness(0) invert(1);}
.free-badge{margin-top:24px;background:linear-gradient(135deg,rgba(255,107,0,.12),rgba(255,215,0,.08));border:1.5px solid rgba(255,107,0,.25);border-radius:12px;padding:16px;font-size:.82rem;color:var(--muted);}
.free-badge strong{color:var(--p);}
.back{margin-top:24px;font-size:.8rem;color:var(--muted);}
.back a{color:var(--p);font-weight:600;transition:color .2s;}
.back a:hover{color:#e05a00;}
.theme-toggle{position:fixed;top:20px;right:20px;width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.7);backdrop-filter:blur(10px);border:1.5px solid var(--border);color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.1rem;transition:all .3s;z-index:10;}
[data-theme="dark"] .theme-toggle{background:rgba(26,45,79,.5);}
.theme-toggle:hover{background:var(--p);color:white;}
.error{background:rgba(239,68,68,.1);border:1.5px solid rgba(239,68,68,.3);color:#ef4444;padding:12px 14px;border-radius:10px;font-size:.85rem;margin-bottom:20px;}
.divider{display:flex;align-items:center;gap:12px;margin:20px 0;color:var(--muted);font-size:.78rem;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}
.admin-link{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px;border-radius:14px;background:rgba(99,102,241,.1);border:1.5px solid rgba(99,102,241,.25);color:#6366f1;font-weight:600;font-size:.88rem;text-decoration:none;transition:all .3s;}
.admin-link:hover{background:rgba(99,102,241,.18);transform:translateY(-2px);box-shadow:0 6px 20px rgba(99,102,241,.2);}
.admin-link i{font-size:.9rem;}
.site-footer{text-align:center;margin-top:28px;padding-top:18px;border-top:1px solid var(--border);font-size:.72rem;color:var(--muted);line-height:1.9;}
.site-footer strong{color:var(--p);font-weight:600;}
</style>
</head>
<body>
<button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">🌙</button>
<div class="card">
  <div class="logo">🦅 Poster<span>Wall</span></div>
  <p class="sub">Apna Design. Apna Brand. Apni Pehchaan.<br>AI se digital page banao sirf ₹9 mein!</p>
  <?php if(isset($_GET['error'])): ?>
  <div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>
  <a href="<?= $url ?>" class="g-btn">
    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="G">
    Google se Login Karo
  </a>
  <div class="free-badge">🎁 Pehla page <strong>FREE</strong> — ₹9 wallet mein milega signup pe!</div>
  <div class="back"><a href="<?= siteUrl('') ?>">← Wapas Jao</a></div>
  <div class="divider">ya</div>
  <a href="<?= siteUrl('admin/login.php') ?>" class="admin-link">
    <i>🔐</i> Admin Login
  </a>
  <div class="site-footer">
    <strong>A Product of TechEagles</strong><br>
    Under Mahakumbrix Innovation
  </div>
</div>
<script>
function initTheme() {
  const savedTheme = localStorage.getItem('theme') || 'light';
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const theme = savedTheme === 'system' ? (prefersDark ? 'dark' : 'light') : savedTheme;
  applyTheme(theme);
}
function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('theme', theme);
  document.getElementById('theme-toggle').textContent = theme === 'dark' ? '☀️' : '🌙';
}
function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') || 'light';
  const newTheme = current === 'dark' ? 'light' : 'dark';
  applyTheme(newTheme);
}
document.addEventListener('DOMContentLoaded', initTheme);
</script>
</body>
</html>
