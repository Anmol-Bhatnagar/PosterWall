<?php
require_once '../config.php';
requireAuth();
$bal = wallet();
$rechargeMode = isset($_GET['recharge']);
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Wallet — PosterWall</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--p:#FF6B00;--bg:#ffffff;--bg2:#f7f9fc;--card:#ffffff;--text:#1a1a2e;--muted:#6c7b94;--border:rgba(0,0,0,0.08);}
[data-theme="dark"]{--bg:#0a0e27;--bg2:#111d3a;--card:#1a2d4f;--text:#f0f2f5;--muted:#8892a4;--border:rgba(255,255,255,0.08);}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);min-height:100svh;padding-bottom:90px;transition:background .3s ease,color .3s ease;}
a{text-decoration:none;color:inherit;}
nav{background:rgba(255,255,255,.85);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-bottom:1px solid var(--border);padding:0 16px;position:sticky;top:0;z-index:100;}
[data-theme="dark"] nav{background:rgba(10,14,39,.88);}
.nav-in{max-width:900px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:56px;}
.logo{font-family:'Baloo 2',cursive;font-size:1.35rem;font-weight:800;background:linear-gradient(135deg,var(--p),#FFD700);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.logo span{-webkit-text-fill-color:unset;}
.con{max-width:900px;margin:0 auto;padding:28px 16px;min-height:calc(100svh - 200px);}
.card{background:rgba(255,255,255,.5);backdrop-filter:blur(10px);border:1.5px solid var(--border);border-radius:18px;padding:28px;}
[data-theme="dark"] .card{background:rgba(26,45,79,.3);}
.title{font-family:'Baloo 2',cursive;font-size:1.85rem;font-weight:800;margin-bottom:8px;}
.subtitle{color:var(--muted);font-size:.95rem;margin-bottom:22px;line-height:1.5;}
.balance{font-family:'Baloo 2',cursive;font-size:2.8rem;background:linear-gradient(135deg,var(--p),#FFD700);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:16px;}
.info{color:var(--muted);font-size:.92rem;line-height:1.7;margin-bottom:24px;}
.btn{display:inline-flex;align-items:center;gap:10px;padding:14px 22px;border-radius:12px;background:linear-gradient(135deg,var(--p),#e05a00);color:#fff;font-weight:700;border:none;cursor:pointer;font-size:.95rem;box-shadow:0 6px 20px rgba(255,107,0,.2);transition:all .3s;}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(255,107,0,.3);}
.btn-secondary{display:inline-flex;align-items:center;gap:10px;padding:10px 16px;border-radius:10px;background:transparent;border:1.5px solid var(--border);color:var(--text);cursor:pointer;font-size:.9rem;font-weight:600;transition:all .3s;}
.btn-secondary:hover{border-color:var(--p);background:rgba(255,107,0,.08);}
.alert{background:linear-gradient(135deg,rgba(255,107,0,.12),rgba(255,215,0,.08));backdrop-filter:blur(10px);border:1.5px solid rgba(255,107,0,.25);border-radius:14px;padding:16px;margin-bottom:20px;color:var(--text);}
.wallet-row{display:grid;gap:14px;}
.bot-nav{position:fixed;top:auto;bottom:0;left:0;right:0;background:var(--bg2);border-top:1px solid var(--border);display:flex;z-index:200;}
.bot-nav a{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;padding:10px 4px;color:var(--muted);font-size:.6rem;font-weight:500;transition:all .2s;}
.bot-nav a:hover{color:var(--p);}
.bot-nav a.on{color:var(--p);}
.bot-nav a i{font-size:1.05rem;}
.toast{position:fixed;bottom:90px;left:50%;transform:translateX(-50%);background:rgba(255,255,255,.9);backdrop-filter:blur(10px);border:1.5px solid var(--border);padding:12px 18px;border-radius:10px;font-size:.88rem;z-index:999;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.1);}
.toast.ok{color:#10b981;} .toast.err{color:#ef4444;} .toast.info{color:var(--p);}
.theme-toggle{width:40px;height:40px;border-radius:10px;background:transparent;border:1.5px solid var(--border);color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.95rem;transition:all .3s;}
.theme-toggle:hover{border-color:var(--p);background:rgba(255,107,0,.1);}
</style>
</head>
<body>
<nav>
  <div class="nav-in">
    <a href="<?= siteUrl('dashboard/') ?>" class="logo">🦅 Poster<span>Wall</span></a>
    <div style="display:flex;gap:10px;align-items:center;">
      <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">🌙</button>
      <a href="<?= siteUrl('auth/logout.php') ?>" class="btn-secondary">Logout</a>
    </div>
  </div>
</nav>
<div class="con">
  <div class="card">
    <div class="title">Wallet Balance</div>
    <div class="subtitle">Aapka balance yahan dikhega. ₹9 se kam hua to page banana possible nahi hoga.</div>
    <?php if ($rechargeMode): ?>
    <div class="alert">Aapke wallet mein paise kam hain. Recharge karo aur phir se page banao.</div>
    <?php endif; ?>
    <div class="wallet-row">
      <div class="balance">₹<?= number_format($bal, 2) ?></div>
      <div class="info">Har page generation ₹9 ka hota hai. Agar paise kam ho to neeche se recharge karo.</div>
      <button class="btn" onclick="pay(9)"><i class="fas fa-rupee-sign"></i> ₹9 — 1 page</button>
      <button class="btn" onclick="pay(79)"><i class="fas fa-rupee-sign"></i> ₹79 — 10 pages</button>
      <button class="btn" onclick="pay(179)"><i class="fas fa-rupee-sign"></i> ₹179 — 25 pages</button>
      <button class="btn" onclick="pay(499)"><i class="fas fa-rupee-sign"></i> ₹499 — Unlimited month</button>
    </div>
  </div>
</div>
<div id="pay-modal" class="overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:999;align-items:center;justify-content:center;padding:20px;">
  <div style="background:var(--card);border:1px solid var(--border);border-radius:20px;padding:24px 20px;width:100%;max-width:420px;">
    <button class="modal-close" onclick="document.getElementById('pay-modal').style.display='none'" style="float:right;background:none;border:none;color:var(--muted);font-size:1.2rem;cursor:pointer;">✕</button>
    <h3 style="font-family:'Baloo 2',cursive;font-size:1.2rem;margin-bottom:12px;color:#fff;">💰 Wallet Recharge</h3>
    <p style="color:var(--muted);font-size:.92rem;margin-bottom:16px;">Current Balance: <strong style="color:#FF6B00;">₹<?= number_format($bal,2) ?></strong></p>
    <button onclick="pay(9)" class="btn" style="width:100%;margin-bottom:12px;"><i class="fas fa-bolt"></i> ₹9 — 1 Page</button>
    <button onclick="pay(79)" class="btn" style="width:100%;margin-bottom:12px;background:rgba(255,255,255,.08);color:#fff;border:1px solid var(--border);">₹79 — 10 Pages</button>
    <button onclick="pay(179)" class="btn" style="width:100%;margin-bottom:12px;background:rgba(255,255,255,.08);color:#fff;border:1px solid var(--border);">₹179 — 25 Pages</button>
    <button onclick="pay(499)" class="btn" style="width:100%;background:rgba(255,255,255,.08);color:#fff;border:1px solid var(--border);">₹499 — Unlimited Month</button>
  </div>
</div>
<nav class="bot-nav">
  <a href="<?= siteUrl('dashboard/') ?>" class="on"><i class="fas fa-home"></i>Home</a>
  <a href="<?= siteUrl('') ?>"><i class="fas fa-plus-circle"></i>New Page</a>
  <a href="<?= siteUrl('m/') ?>"><i class="fas fa-search"></i>Find</a>
  <a href="<?= siteUrl('dashboard/wallet.php') ?>"><i class="fas fa-wallet"></i>Wallet</a>
</nav>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function toast(msg, type='info'){
  const d=document.createElement('div'); d.className='toast '+type; d.textContent=msg; document.body.appendChild(d);
  setTimeout(()=>d.remove(),3000);
}
function pay(amount){
  document.getElementById('pay-modal').style.display='none';
  fetch('<?= SITE_URL ?>/api/payment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'create_order',amount})})
  .then(r=>r.json()).then(data=>{
    if(!data.order_id){toast('Order fail!','err');return;}
    new Razorpay({key:'<?= RZP_KEY_ID ?>',amount:amount*100,currency:'INR',name:'PosterWall',order_id:data.order_id,handler:(r)=>{
      fetch('<?= SITE_URL ?>/api/payment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'verify',razorpay_payment_id:r.razorpay_payment_id,razorpay_order_id:r.razorpay_order_id,razorpay_signature:r.razorpay_signature})})
      .then(res=>res.json()).then(v=>{if(v.success){toast('₹'+amount+' add ho gaya!','ok');setTimeout(()=>location.reload(),1500);}});
    },prefill:{name:'<?= addslashes($_SESSION['name'] ?? '') ?>',email:'<?= addslashes($_SESSION['email'] ?? '') ?>'},theme:{color:'#FF6B00'}}).open();
  });
}

// ===== THEME TOGGLE =====
function initTheme() {
  const savedTheme = localStorage.getItem('theme') || 'light';
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const theme = savedTheme === 'system' ? (prefersDark ? 'dark' : 'light') : savedTheme;
  applyTheme(theme);
}

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('theme', theme);
  const toggle = document.getElementById('theme-toggle');
  if (toggle) toggle.textContent = theme === 'dark' ? '☀️' : '🌙';
}

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') || 'light';
  const newTheme = current === 'dark' ? 'light' : 'dark';
  applyTheme(newTheme);
}

document.addEventListener('DOMContentLoaded', initTheme);
</script>
<footer style="text-align:center;padding:16px 20px 80px;font-size:.7rem;color:var(--muted);border-top:1px solid var(--border);margin-top:8px;">
  <strong style="color:var(--p);">A Product of TechEagles</strong> &nbsp;|&nbsp; Under Mahakumbrix Innovation
</footer>
</body>
</html>
