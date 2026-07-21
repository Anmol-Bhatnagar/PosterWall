<?php
require_once '../config.php';
requireAuth();
$user  = me();
$bal   = wallet();
$power = isPowerUser($user);
$uid   = (int)$_SESSION['uid'];
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Naya Page Banao — PosterWall</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{
  --p:#7C3AED;--p2:#A855F7;--p3:#C084FC;
  --bg:#05050f;--bg2:#0d0d1f;
  --card:rgba(20,14,50,0.65);
  --text:#ede9ff;--muted:#8B7FAB;
  --border:rgba(124,58,237,0.22);
  --glow:rgba(124,58,237,0.35);--glow2:rgba(124,58,237,0.15);
}
*{margin:0;padding:0;box-sizing:border-box;}
body{
  font-family:'Poppins',sans-serif;
  background:var(--bg);color:var(--text);
  min-height:100svh;padding-bottom:90px;
  overflow-x:hidden;
}
body::before{
  content:'';position:fixed;
  top:-20%;left:-10%;
  width:60vw;height:60vw;max-width:700px;max-height:700px;
  border-radius:50%;
  background:radial-gradient(circle,rgba(124,58,237,0.18) 0%,transparent 70%);
  pointer-events:none;z-index:0;
}
body::after{
  content:'';position:fixed;
  bottom:-15%;right:-10%;
  width:50vw;height:50vw;max-width:500px;max-height:500px;
  border-radius:50%;
  background:radial-gradient(circle,rgba(168,85,247,0.12) 0%,transparent 70%);
  pointer-events:none;z-index:0;
}
a{text-decoration:none;color:inherit;}

/* Nav */
nav.top-nav{
  background:rgba(5,5,15,0.78);
  backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  padding:0 16px;position:sticky;top:0;z-index:100;
}
.nav-in{max-width:700px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:58px;}
.logo{font-family:'Baloo 2',cursive;font-size:1.4rem;font-weight:800;background:linear-gradient(135deg,var(--p2),var(--p3),#e879f9);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.nav-r{display:flex;align-items:center;gap:10px;}
.nav-av{width:36px;height:36px;border-radius:50%;border:2px solid var(--p2);object-fit:cover;box-shadow:0 0 14px var(--glow2);}
.back-btn{
  display:flex;align-items:center;gap:6px;
  padding:8px 14px;border-radius:20px;
  border:1px solid var(--border);
  background:rgba(124,58,237,0.1);
  color:var(--muted);font-size:.82rem;font-weight:600;
  cursor:pointer;transition:all .25s;
}
.back-btn:hover{border-color:var(--p2);color:var(--p3);}
.theme-toggle{
  width:38px;height:38px;border-radius:10px;
  background:rgba(124,58,237,0.12);
  border:1px solid var(--border);
  color:var(--p3);cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  font-size:.9rem;transition:all .3s;
}
.theme-toggle:hover{background:rgba(124,58,237,0.3);}

/* Main container */
.con{max-width:700px;margin:0 auto;padding:28px 16px;position:relative;z-index:1;}

/* Page header */
.page-header{text-align:center;margin-bottom:32px;}
.page-header h1{
  font-family:'Baloo 2',cursive;
  font-size:2rem;font-weight:800;
  background:linear-gradient(135deg,#fff 30%,var(--p3));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  background-clip:text;margin-bottom:8px;
}
.page-header p{color:var(--muted);font-size:.9rem;line-height:1.6;}

/* Balance pill */
.bal-pill{
  display:inline-flex;align-items:center;gap:8px;
  background:<?= $power ? 'linear-gradient(135deg,#7c3aed,#a855f7)' : 'linear-gradient(135deg,#7C3AED,#A855F7)' ?>;
  color:#fff;padding:8px 18px;border-radius:999px;
  font-size:.82rem;font-weight:600;margin-bottom:28px;
  box-shadow:0 6px 24px var(--glow);
}

/* Steps */
.steps{display:flex;align-items:center;gap:0;margin-bottom:32px;counter-reset:step;}
.step{flex:1;text-align:center;position:relative;}
.step-num{
  width:36px;height:36px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:.82rem;font-weight:700;
  margin:0 auto 6px;transition:all .3s;
}
.step-num.done{background:var(--p);color:#fff;box-shadow:0 4px 12px var(--glow);}
.step-num.active{background:rgba(124,58,237,0.2);border:2px solid var(--p2);color:var(--p3);}
.step-num.pending{background:rgba(124,58,237,0.06);border:2px solid var(--border);color:var(--muted);}
.step-label{font-size:.65rem;color:var(--muted);font-weight:500;}
.step-line{position:absolute;top:18px;left:50%;right:-50%;height:2px;background:var(--border);z-index:-1;}
.step-line.done{background:linear-gradient(90deg,var(--p),var(--p2));}
.step:last-child .step-line{display:none;}

/* Upload card */
.upload-card{
  background:rgba(20,14,50,0.5);
  backdrop-filter:blur(12px);
  border:1.5px dashed rgba(124,58,237,0.4);
  border-radius:20px;padding:40px 24px;
  text-align:center;cursor:pointer;
  position:relative;transition:all .35s;
  margin-bottom:20px;
}
.upload-card:hover,.upload-card.drag{
  border-color:var(--p2);
  box-shadow:0 8px 40px var(--glow2);
  background:rgba(124,58,237,0.08);
}
.upload-card input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.upload-icon{font-size:3.5rem;margin-bottom:16px;display:block;filter:drop-shadow(0 0 16px rgba(168,85,247,.5));}
.upload-title{
  font-family:'Baloo 2',cursive;font-size:1.3rem;font-weight:800;margin-bottom:6px;
  background:linear-gradient(135deg,#fff,var(--p3));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.upload-sub{color:var(--muted);font-size:.83rem;margin-bottom:20px;}
.upload-hint-btn{
  display:inline-flex;align-items:center;gap:8px;
  padding:12px 24px;border-radius:50px;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;font-weight:700;font-size:.9rem;
  pointer-events:none;box-shadow:0 6px 24px var(--glow);
}
.upload-preview-wrap{display:none;position:relative;margin-bottom:16px;}
.upload-preview-wrap img{width:100%;max-height:220px;object-fit:cover;border-radius:14px;display:block;}
.preview-change{
  position:absolute;bottom:10px;right:10px;
  background:rgba(0,0,0,.7);color:#fff;
  border:none;border-radius:8px;padding:7px 14px;
  font-size:.75rem;font-weight:600;cursor:pointer;
}

/* Tips */
.tips{
  background:rgba(124,58,237,0.08);
  border:1px solid rgba(124,58,237,0.2);
  border-radius:14px;padding:18px 20px;margin-bottom:24px;
}
.tips-title{font-weight:700;font-size:.83rem;margin-bottom:12px;color:var(--p3);}
.tips ul{list-style:none;display:flex;flex-direction:column;gap:8px;}
.tips li{font-size:.8rem;color:var(--muted);display:flex;align-items:flex-start;gap:8px;}
.tips li::before{content:'✅';flex-shrink:0;}

/* Progress */
.prog-wrap{
  background:rgba(20,14,50,0.6);
  backdrop-filter:blur(12px);
  border:1px solid var(--border);
  border-radius:14px;padding:20px;margin-bottom:20px;display:none;
}
.prog-bar-bg{background:rgba(124,58,237,0.15);border-radius:999px;height:8px;overflow:hidden;margin-bottom:10px;}
.prog-bar{height:100%;width:0%;background:linear-gradient(90deg,var(--p),var(--p2),var(--p3));border-radius:999px;transition:width .35s ease;}
.prog-text{font-size:.82rem;color:var(--muted);text-align:center;}

/* Generate button */
.gen-btn-main{
  width:100%;padding:16px;border-radius:50px;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;font-weight:800;border:none;cursor:pointer;
  font-size:1rem;display:none;
  align-items:center;justify-content:center;gap:10px;
  margin-bottom:24px;box-shadow:0 8px 32px var(--glow);
  letter-spacing:.02em;transition:all .3s;
}
.gen-btn-main.show{display:flex;}
.gen-btn-main:hover{transform:translateY(-2px);box-shadow:0 14px 42px var(--glow);}
.gen-btn-main:disabled{opacity:.7;cursor:not-allowed;transform:none;}

/* Wallet warning */
.bal-warn{
  background:rgba(124,58,237,0.08);
  border:1px solid rgba(124,58,237,0.3);
  border-radius:14px;padding:18px 20px;margin-bottom:20px;display:none;
}
.bal-warn p{font-size:.85rem;color:var(--muted);margin-bottom:14px;}
.recharge-btn{
  display:inline-flex;align-items:center;gap:8px;
  padding:11px 22px;border-radius:50px;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;font-weight:700;font-size:.88rem;border:none;cursor:pointer;
  box-shadow:0 6px 24px var(--glow);
}

/* Bottom nav */
.bot-nav{position:fixed;bottom:0;left:0;right:0;background:rgba(5,5,15,0.82);backdrop-filter:blur(20px);border-top:1px solid var(--border);display:flex;z-index:200;}
.bot-nav a{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;padding:10px 4px;color:var(--muted);font-size:.58rem;font-weight:500;transition:all .25s;}
.bot-nav a:hover{color:var(--p3);}
.bot-nav a.on{color:var(--p3);}
.bot-nav a.on i{filter:drop-shadow(0 0 6px var(--p2));}
.bot-nav a i{font-size:1.1rem;}

/* Modal */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:500;display:flex;align-items:flex-end;justify-content:center;backdrop-filter:blur(8px);}
.modal{background:rgba(13,13,31,0.95);backdrop-filter:blur(20px);border:1px solid var(--border);border-radius:24px 24px 0 0;padding:28px 22px;width:100%;max-height:88svh;overflow-y:auto;}
@media(min-width:600px){.overlay{align-items:center;padding:20px;}.modal{border-radius:24px;max-width:480px;}}
.modal h3{font-family:'Baloo 2',cursive;font-size:1.25rem;margin-bottom:14px;color:var(--text);}
.modal-close{float:right;background:rgba(124,58,237,0.15);border:1px solid var(--border);color:var(--muted);font-size:1rem;cursor:pointer;border-radius:8px;width:30px;height:30px;display:flex;align-items:center;justify-content:center;}
.inp{width:100%;padding:12px 14px;border-radius:12px;background:rgba(20,14,50,0.7);border:1px solid var(--border);color:var(--text);font-family:'Poppins',sans-serif;font-size:.92rem;margin-bottom:10px;transition:border-color .25s;}
.inp:focus{outline:none;border-color:var(--p2);box-shadow:0 0 0 3px rgba(124,58,237,0.15);}
.btn-p{width:100%;padding:13px;border-radius:50px;background:linear-gradient(135deg,var(--p),var(--p2));color:#fff;font-weight:700;border:none;cursor:pointer;font-size:.9rem;margin-bottom:8px;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 6px 24px var(--glow);transition:all .3s;}
.btn-p:hover{transform:translateY(-1px);}
.btn-s{width:100%;padding:11px;border-radius:50px;background:rgba(124,58,237,0.1);border:1px solid var(--border);color:var(--text);font-weight:600;cursor:pointer;font-size:.88rem;transition:all .3s;}
.btn-s:hover{background:rgba(124,58,237,0.2);}

/* Toast */
.toast{position:fixed;bottom:76px;left:50%;transform:translateX(-50%);background:var(--card);border:1px solid var(--border);padding:10px 18px;border-radius:10px;font-size:.82rem;z-index:9999;white-space:nowrap;}
.tok{border-color:rgba(0,212,100,.4);color:#00d464;}.ter{border-color:rgba(255,85,85,.4);color:#ff5555;}.tin{border-color:rgba(255,107,0,.4);color:var(--p);}

/* Spin */
@keyframes sp{to{transform:rotate(360deg);}}
.spin{width:18px;height:18px;border:2px solid rgba(255,255,255,.35);border-top-color:#fff;border-radius:50%;animation:sp .7s linear infinite;display:inline-block;}

/* Global top progress bar */
@keyframes pw-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
@keyframes pw-spin{to{transform:rotate(360deg)}}
</style>
</head>
<body>

<!-- Global AI progress bar -->
<div id="pw-ai-progress-wrap" style="display:none;position:fixed;top:0;left:0;right:0;z-index:99999;pointer-events:none;">
  <div style="height:3px;background:rgba(255,107,0,.15);">
    <div id="pw-ai-progress-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#FF6B00,#FFC258,#FF6B00);background-size:200% 100%;animation:pw-shimmer 1.4s linear infinite;transition:width .35s ease;"></div>
  </div>
  <div id="pw-ai-progress-label" style="background:rgba(255,107,0,.92);backdrop-filter:blur(8px);color:#fff;font-size:.72rem;font-weight:600;padding:5px 14px 5px 12px;display:inline-flex;align-items:center;gap:7px;border-radius:0 0 10px 0;letter-spacing:.02em;white-space:nowrap;">
    <span style="display:inline-block;width:10px;height:10px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:pw-spin .8s linear infinite;"></span>
    <span id="pw-ai-progress-text">AI kaam kar raha hai...</span>
    <span id="pw-ai-progress-pct" style="margin-left:4px;opacity:.8;font-size:.68rem;">0%</span>
  </div>
</div>

<!-- Top Nav -->
<nav class="top-nav">
  <div class="nav-in">
    <a href="<?= siteUrl('dashboard/') ?>" class="logo">🦅 Poster<span style="-webkit-text-fill-color:unset;">Wall</span></a>
    <div class="nav-r">
      <button class="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">🌙</button>
      <img src="<?= htmlspecialchars($user['avatar']??'') ?>" class="nav-av" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=FF6B00&color=fff'">
      <a href="<?= siteUrl('auth/logout.php') ?>" style="padding:9px 14px;border-radius:10px;background:linear-gradient(135deg,#FF6B00,#e05a00);color:#fff;font-weight:600;font-size:.85rem;cursor:pointer;box-shadow:0 4px 12px rgba(255,107,0,.2);">Logout</a>
    </div>
  </div>
</nav>

<div class="con">

  <!-- Page Header -->
  <div class="page-header">
    <h1>📸 Naya Page Banao</h1>
    <p>Apni dukaan ya business ki photo upload karo — AI 30 seconds mein ek beautiful digital page bana dega!</p>
  </div>

  <!-- Balance Pill -->
  <div style="text-align:center;margin-bottom:28px;">
    <?php if($power): ?>
    <span class="bal-pill">⚡ Unlimited Access — Free</span>
    <?php else: ?>
    <span class="bal-pill">💰 Balance: ₹<?= number_format($bal,2) ?> &nbsp;·&nbsp; Cost: ₹9/page</span>
    <?php endif; ?>
  </div>

  <!-- Steps indicator -->
  <div class="steps" id="steps-indicator">
    <div class="step">
      <div class="step-num active" id="step-1-num">1</div>
      <div class="step-label">Photo Upload</div>
      <div class="step-line" id="step-1-line"></div>
    </div>
    <div class="step">
      <div class="step-num pending" id="step-2-num">2</div>
      <div class="step-label">AI Generate</div>
      <div class="step-line" id="step-2-line"></div>
    </div>
    <div class="step">
      <div class="step-num pending" id="step-3-num">3</div>
      <div class="step-label">Confirm</div>
      <div class="step-line"></div>
    </div>
  </div>

  <!-- Upload Card -->
  <div class="upload-card" id="upload-card">
    <input type="file" id="photo-inp" accept="image/*" onchange="handlePhoto(event)">
    <div id="upload-placeholder">
      <span class="upload-icon">📷</span>
      <div class="upload-title">Photo Upload Karo</div>
      <div class="upload-sub">Dukaan, dhaba, clinic — kisi bhi business ki photo chalegi</div>
      <div class="upload-hint-btn"><i class="fas fa-camera"></i> Photo Chuno</div>
    </div>
    <div class="upload-preview-wrap" id="preview-wrap">
      <img id="up-prev" src="" alt="preview">
      <button class="preview-change" onclick="changePhoto(event)"><i class="fas fa-camera"></i> Photo Badlo</button>
    </div>
  </div>

  <!-- Tips -->
  <div class="tips" id="tips-box">
    <div class="tips-title">📌 Achha Result ke liye:</div>
    <ul>
      <li>Dukaan ke bahar ki clear photo lo — signboard clearly dikhna chahiye</li>
      <li>Acchi roshni mein photo lo — andhera mat rakhna</li>
      <li>Business name, menu ya products clearly visible hon</li>
      <li>Blurry ya bahut dark photos avoid karo</li>
    </ul>
  </div>

  <!-- Wallet warning (shown if insufficient balance) -->
  <?php if(!$power && $bal < 9): ?>
  <div class="bal-warn" id="bal-warn" style="display:block;">
    <p>⚠️ Aapke wallet mein sirf <strong>₹<?= number_format($bal,2) ?></strong> hai. Ek page banane ke liye ₹9 chahiye.</p>
    <button class="recharge-btn" onclick="document.getElementById('pay-modal').style.display='flex'"><i class="fas fa-plus"></i> Wallet Recharge Karo</button>
  </div>
  <?php endif; ?>

  <!-- Progress bar (shown during AI generation) -->
  <div class="prog-wrap" id="prog-wrap">
    <div class="prog-bar-bg"><div class="prog-bar" id="prog-bar"></div></div>
    <div class="prog-text" id="prog-text">AI kaam kar raha hai...</div>
  </div>

  <!-- Generate Button -->
  <button class="gen-btn-main" id="gen-btn" onclick="generate()">
    <i class="fas fa-magic"></i> <?= $power ? 'AI se Page Banao — Free!' : 'AI se Page Banao — ₹9' ?>
  </button>

</div><!-- .con -->

<!-- Bottom Nav -->
<nav class="bot-nav">
  <a href="<?= siteUrl('dashboard/') ?>"><i class="fas fa-home"></i>Home</a>
  <a href="<?= siteUrl('dashboard/orders.php') ?>"><i class="fas fa-shopping-bag"></i>Orders</a>
  <a href="<?= siteUrl('dashboard/create.php') ?>" class="on"><i class="fas fa-plus-circle"></i>New Page</a>
  <a href="<?= siteUrl('m/') ?>"><i class="fas fa-search"></i>Find</a>
  <a href="<?= siteUrl('dashboard/wallet.php') ?>"><i class="fas fa-wallet"></i>Wallet</a>
</nav>

<!-- PAY MODAL -->
<div id="pay-modal" class="overlay" style="display:none;">
  <div class="modal">
    <button class="modal-close" onclick="document.getElementById('pay-modal').style.display='none'">✕</button>
    <h3>💰 Wallet Recharge</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:16px;">Current Balance: <strong style="color:var(--p);">₹<?= number_format($bal,2) ?></strong></p>
    <button onclick="pay(9)"   class="btn-p"><i class="fas fa-bolt"></i> ₹9 — 1 Page</button>
    <button onclick="pay(79)"  class="btn-s" style="margin-bottom:8px;">₹79 — 10 Pages (save ₹11)</button>
    <button onclick="pay(179)" class="btn-s" style="margin-bottom:8px;">₹179 — 25 Pages (save ₹46)</button>
    <button onclick="pay(499)" class="btn-s">₹499 — Unlimited Month</button>
  </div>
</div>

<!-- CONFIRM MODAL -->
<div id="confirm-modal" class="overlay" style="display:none;">
  <div class="modal" style="max-width:460px;">
    <h3 style="margin-bottom:6px;">✅ Business Verify Karo</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:18px;">AI ne neeche diya naam detect kiya hai. Confirm karke save karo.</p>
    <label style="font-size:.8rem;font-weight:600;color:var(--muted);display:block;margin-bottom:6px;">Business Name <span style="color:#e05a00;">*</span></label>
    <input id="confirm-name-inp" class="inp" type="text" placeholder="Business ka naam">
    <label style="font-size:.8rem;font-weight:600;color:var(--muted);display:block;margin-bottom:6px;">📱 Mobile Number <span style="color:var(--muted);font-weight:400;">(optional)</span></label>
    <input id="confirm-mob-inp" class="inp" type="tel" placeholder="10-digit mobile (optional)" maxlength="10">
    <p id="confirm-validation-msg" style="color:#e05a00;font-size:.8rem;min-height:18px;margin-bottom:14px;display:none;"></p>
    <div style="background:rgba(255,107,0,.07);border:1px solid rgba(255,107,0,.2);border-radius:12px;padding:12px 14px;font-size:.82rem;color:var(--muted);margin-bottom:18px;line-height:1.5;">
      💡 <strong style="color:var(--text);">Name se dhundhe jaoge:</strong> Customers aapko business name se search kar sakenge.<br>
      💡 <strong style="color:var(--text);">Mobile se dhundhe jaoge:</strong> Customers aapko number dalke bhi page khol sakenge.
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <button class="btn-p" id="confirm-save-btn" onclick="confirmAndSave()" style="flex:1;"><i class="fas fa-check"></i> Haan, Save Karo</button>
      <button class="btn-s" onclick="cancelConfirm()" style="flex:1;">Cancel</button>
    </div>
  </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
let photoB64=null, photoMime=null, pendingKey=null;
let bal=<?= $bal ?>;
const IS_POWER_USER=<?= $power ? 'true' : 'false' ?>;
const USER_NAME='<?= addslashes($user['name']) ?>';
const USER_EMAIL='<?= addslashes($user['email']) ?>';
const SITE_URL='<?= SITE_URL ?>';

// ── Theme ───────────────────────────────────────────────────────────────────
function initTheme(){
  const t=localStorage.getItem('pw_theme')||'light';
  document.documentElement.setAttribute('data-theme',t);
  document.querySelector('.theme-toggle').textContent=t==='dark'?'☀️':'🌙';
}
function toggleTheme(){
  const cur=document.documentElement.getAttribute('data-theme')||'light';
  const next=cur==='dark'?'light':'dark';
  document.documentElement.setAttribute('data-theme',next);
  localStorage.setItem('pw_theme',next);
  document.querySelector('.theme-toggle').textContent=next==='dark'?'☀️':'🌙';
}
initTheme();

// ── Global progress bar ─────────────────────────────────────────────────────
window.pwProgress=(function(){
  let _i=null,_p=0;
  const w=()=>document.getElementById('pw-ai-progress-wrap');
  const b=()=>document.getElementById('pw-ai-progress-bar');
  const t=()=>document.getElementById('pw-ai-progress-text');
  const p=()=>document.getElementById('pw-ai-progress-pct');
  function _set(pct,msg){_p=Math.min(Math.max(pct,0),100);if(b())b().style.width=_p+'%';if(p())p().textContent=Math.round(_p)+'%';if(msg&&t())t().textContent=msg;}
  return{
    start:function(msg,s){if(_i){clearInterval(_i);_i=null;}_p=s||5;if(w())w().style.display='block';_set(_p,msg||'AI kaam kar raha hai...');_i=setInterval(function(){var step=_p<50?3:_p<75?1.5:0.6;_set(Math.min(_p+step*(0.5+Math.random()),90),null);},800);},
    update:function(pct,msg){_set(pct,msg);},
    done:function(msg){if(_i){clearInterval(_i);_i=null;}_set(100,msg||'Ho gaya! ✅');setTimeout(function(){if(w())w().style.display='none';},900);},
    error:function(msg){if(_i){clearInterval(_i);_i=null;}if(b())b().style.background='#ef4444';_set(_p,msg||'Kuch gadbad! ❌');setTimeout(function(){if(w())w().style.display='none';if(b())b().style.background='';},2000);}
  };
})();

// ── Steps ───────────────────────────────────────────────────────────────────
function setStep(n){
  for(let i=1;i<=3;i++){
    const el=document.getElementById('step-'+i+'-num');
    el.className='step-num '+(i<n?'done':i===n?'active':'pending');
    const line=document.getElementById('step-'+i+'-line');
    if(line) line.className='step-line'+(i<n?' done':'');
  }
}

// ── Photo handling ──────────────────────────────────────────────────────────
function handlePhoto(e){
  const f=e.target.files[0]; if(!f)return;
  const r=new FileReader();
  r.onload=(ev)=>{
    photoB64=ev.target.result.split(',')[1]; photoMime=f.type;
    document.getElementById('upload-placeholder').style.display='none';
    const wrap=document.getElementById('preview-wrap');
    document.getElementById('up-prev').src=ev.target.result;
    wrap.style.display='block';
    document.getElementById('tips-box').style.display='none';
    document.getElementById('gen-btn').classList.add('show');
    setStep(2);
    toast('Photo ready! ✅','tok');
    // Show progress hint
    document.getElementById('prog-wrap').style.display='block';
    setProgStatus(0,'Photo taiyar hai — "AI se Page Banao" dabao 👆');
  };
  r.readAsDataURL(f);
}

function changePhoto(e){
  e.stopPropagation();
  document.getElementById('photo-inp').click();
}

// ── Progress ────────────────────────────────────────────────────────────────
let genInterval=null;
function setProgStatus(pct,msg){
  const bar=document.getElementById('prog-bar');
  const txt=document.getElementById('prog-text');
  if(bar)bar.style.width=Math.min(Math.max(pct,0),100)+'%';
  if(txt)txt.textContent=msg;
}
function resetProg(){
  if(genInterval){clearInterval(genInterval);genInterval=null;}
  document.getElementById('prog-wrap').style.display='none';
}

// ── Generate ────────────────────────────────────────────────────────────────
function generate(){
  if(!photoB64){toast('Photo upload karo pehle!','ter');return;}
  if(!IS_POWER_USER && bal<9){document.getElementById('pay-modal').style.display='flex';return;}
  const btn=document.getElementById('gen-btn');
  btn.disabled=true; btn.innerHTML='<div class="spin"></div> AI Page Bana Raha Hai...';
  document.getElementById('prog-wrap').style.display='block';
  setProgStatus(15,'AI abhi aapka page bana raha hai — thoda intezaar karo');
  pwProgress.start('📸 Photo se page ban raha hai...', 10);
  setStep(2);
  let prog=15;
  genInterval=setInterval(()=>{
    prog=Math.min(prog+Math.random()*7,90);
    setProgStatus(prog,'AI page banta ja raha hai — bas thoda aur ⚡');
  },900);

  fetch(SITE_URL+'/api/generate.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({image_base64:photoB64,mime_type:photoMime})
  }).then(r=>r.json()).then(res=>{
    clearInterval(genInterval); genInterval=null;
    btn.disabled=false; btn.innerHTML='<i class="fas fa-magic"></i> <?= $power ? 'AI se Page Banao — Free!' : 'AI se Page Banao — ₹9' ?>';
    if(res.pending){
      pwProgress.done('Page taiyar hai! ✅');
      setProgStatus(100,'Page taiyar hai! ✅ Neeche confirm karo');
      setStep(3);
      pendingKey=res.pending_key;
      document.getElementById('confirm-name-inp').value=res.detected_name||'';
      document.getElementById('confirm-mob-inp').value='';
      document.getElementById('confirm-validation-msg').style.display='none';
      setTimeout(()=>document.getElementById('confirm-modal').style.display='flex', 300);
      return;
    }
    pwProgress.error('Kuch gadbad!');
    setProgStatus(0,'Kuch gadbad ho gayi — dobara try karo ❌');
    if(res.error==='no_balance'){document.getElementById('pay-modal').style.display='flex';}
    else toast(res.error||'Kuch gadbad!','ter');
  }).catch(()=>{
    clearInterval(genInterval); genInterval=null;
    btn.disabled=false; btn.innerHTML='<i class="fas fa-magic"></i> <?= $power ? 'AI se Page Banao — Free!' : 'AI se Page Banao — ₹9' ?>';
    pwProgress.error('Network error!');
    setProgStatus(0,'Network error — dobara try karo ❌');
    toast('Network error!','ter');
  });
}

// ── Confirm & Save ──────────────────────────────────────────────────────────
function cancelConfirm(){
  document.getElementById('confirm-modal').style.display='none';
  pendingKey=null;
}

function confirmAndSave(){
  const name=document.getElementById('confirm-name-inp').value.trim();
  const mob=document.getElementById('confirm-mob-inp').value.replace(/\D/g,'');
  const msg=document.getElementById('confirm-validation-msg');
  if(!name&&!mob){msg.textContent='Business name ya mobile number mein se ek zaroori hai.';msg.style.display='block';return;}
  if(mob&&mob.length!==10){msg.textContent='Valid 10-digit mobile number dalein.';msg.style.display='block';return;}
  msg.style.display='none';
  const btn=document.getElementById('confirm-save-btn');
  btn.disabled=true; btn.innerHTML='<div class="spin"></div> Save ho raha hai...';
  fetch(SITE_URL+'/api/confirm.php',{
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({pending_key:pendingKey,business_name:name,mobile:mob})
  }).then(r=>r.json()).then(res=>{
    btn.disabled=false; btn.innerHTML='<i class="fas fa-check"></i> Haan, Save Karo';
    if(res.success){
      document.getElementById('confirm-modal').style.display='none';
      bal=res.balance;
      toast('Page save ho gaya! 🎉','tok');
      setTimeout(()=>window.location.href=SITE_URL+'/p/'+res.token, 600);
    } else {
      msg.textContent=res.msg||res.error||'Save mein dikkat aayi.';
      msg.style.display='block';
      if(res.error==='no_balance'){document.getElementById('confirm-modal').style.display='none';document.getElementById('pay-modal').style.display='flex';}
      if(res.error==='expired'){document.getElementById('confirm-modal').style.display='none';toast('Session expire ho gaya — dobara try karo','ter');}
    }
  }).catch(()=>{
    btn.disabled=false; btn.innerHTML='<i class="fas fa-check"></i> Haan, Save Karo';
    msg.textContent='Network error — dobara try karo.'; msg.style.display='block';
  });
}

// ── Pay ─────────────────────────────────────────────────────────────────────
function pay(amount){
  document.getElementById('pay-modal').style.display='none';
  fetch(SITE_URL+'/api/payment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'create_order',amount})})
  .then(r=>r.json()).then(data=>{
    if(!data.order_id){toast('Order fail!','ter');return;}
    new Razorpay({key:'<?= RZP_KEY_ID ?>',amount:amount*100,currency:'INR',name:'PosterWall',
      order_id:data.order_id,
      handler:(r)=>{
        fetch(SITE_URL+'/api/payment.php',{method:'POST',headers:{'Content-Type':'application/json'},
          body:JSON.stringify({action:'verify',razorpay_payment_id:r.razorpay_payment_id,razorpay_order_id:r.razorpay_order_id,razorpay_signature:r.razorpay_signature})
        }).then(res=>res.json()).then(v=>{if(v.success){bal+=amount;toast('₹'+amount+' add ho gaya! ✅','tok');setTimeout(()=>location.reload(),1000);}});
      },
      prefill:{name:USER_NAME,email:USER_EMAIL},
      theme:{color:'#FF6B00'}
    }).open();
  });
}

// ── Toast ────────────────────────────────────────────────────────────────────
function toast(msg,type='tin'){
  const d=document.createElement('div');d.className='toast '+type;d.textContent=msg;
  document.body.appendChild(d);setTimeout(()=>d.remove(),2800);
}

// ── Drag & drop highlight ───────────────────────────────────────────────────
const card=document.getElementById('upload-card');
card.addEventListener('dragover',e=>{e.preventDefault();card.classList.add('drag');});
card.addEventListener('dragleave',()=>card.classList.remove('drag'));
card.addEventListener('drop',e=>{e.preventDefault();card.classList.remove('drag');const f=e.dataTransfer.files[0];if(f){const dt=new DataTransfer();dt.items.add(f);document.getElementById('photo-inp').files=dt.files;handlePhoto({target:{files:[f]}});}});
</script>
</body>
</html>
