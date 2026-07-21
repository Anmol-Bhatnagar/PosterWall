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
:root{
  --p:#FF6B00;--pd:#e05a00;--p2:#FFD700;--p3:#FF6B00;
  --bg:#ffffff;--bg2:#f7f9fc;
  --card:#ffffff;
  --text:#1a1a2e;--muted:#6c7b94;
  --border:rgba(0,0,0,0.08);
  --glow:rgba(255,107,0,0.25);--glow2:rgba(255,107,0,0.04);
  --nav-bg:rgba(255,255,255,0.85);
  --toggle-bg:rgba(255,107,0,0.08);
  --h1-grad:linear-gradient(135deg,var(--text),var(--text));
  --logo-g1:#FF6B00;--logo-g2:#FFD700;--logo-g3:#FFD700;
  --orb-color1:rgba(255,107,0,0.04);
  --orb-color2:rgba(255,107,0,0.02);
  --btn-bg-hover:rgba(255,107,0,0.08);
  --back-btn-bg:rgba(255,107,0,0.08);
  --alert-bg:rgba(255,107,0,0.06);
  --alert-border:rgba(255,107,0,0.2);
  --bot-nav-bg:rgba(255,255,255,0.9);
  --toast-bg:rgba(255,255,255,0.95);
}
[data-theme="dark"]{
  --p:#7C3AED;--pd:#6D28D9;--p2:#A855F7;--p3:#C084FC;
  --bg:#05050f;--bg2:#0d0d1f;
  --card:rgba(20,14,50,0.65);
  --text:#ede9ff;--muted:#8B7FAB;
  --border:rgba(124,58,237,0.22);
  --glow:rgba(124,58,237,0.35);--glow2:rgba(124,58,237,0.15);
  --nav-bg:rgba(5,5,15,0.78);
  --toggle-bg:rgba(124,58,237,0.12);
  --h1-grad:linear-gradient(135deg,#fff 30%,var(--p3));
  --logo-g1:#A855F7;--logo-g2:#C084FC;--logo-g3:#e879f9;
  --orb-color1:rgba(124,58,237,0.15);
  --orb-color2:rgba(168,85,247,0.1);
  --btn-bg-hover:rgba(124,58,237,0.18);
  --back-btn-bg:rgba(124,58,237,0.1);
  --alert-bg:rgba(124,58,237,0.12);
  --alert-border:rgba(124,58,237,0.25);
  --bot-nav-bg:rgba(5,5,15,0.82);
  --toast-bg:rgba(13,13,31,0.95);
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
  background:radial-gradient(circle,var(--orb-color1) 0%,transparent 70%);
  pointer-events:none;z-index:0;
}
a{text-decoration:none;color:inherit;}
nav{
  background:var(--nav-bg);backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  padding:0 16px;position:sticky;top:0;z-index:100;
}
.nav-in{max-width:960px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:58px;}
.logo{
  font-family:'Baloo 2',cursive;font-size:1.4rem;font-weight:800;
  background:linear-gradient(135deg,var(--logo-g1),var(--logo-g2),var(--logo-g3));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.logo span{-webkit-text-fill-color:unset;}
.con{max-width:960px;margin:0 auto;padding:28px 16px;min-height:calc(100svh - 200px);position:relative;z-index:1;}
.card{
  background:var(--card);backdrop-filter:blur(20px);
  border:1px solid var(--border);
  border-radius:24px;padding:36px;
  position:relative;overflow:hidden;
  box-shadow:var(--glow);
}
.card::before{
  content:'';position:absolute;top:-30%;right:-10%;
  width:300px;height:300px;border-radius:50%;
  background:radial-gradient(circle,rgba(168,85,247,0.15),transparent);
  pointer-events:none;
}
.title{
  font-family:'Baloo 2',cursive;font-size:1.85rem;font-weight:800;margin-bottom:8px;
  background:var(--h1-grad);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.subtitle{color:var(--muted);font-size:.95rem;margin-bottom:22px;line-height:1.5;}
.balance{
  font-family:'Baloo 2',cursive;font-size:3rem;
  background:var(--h1-grad);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  margin-bottom:16px;
  filter:drop-shadow(0 0 20px var(--glow2));
}
.info{color:var(--muted);font-size:.92rem;line-height:1.7;margin-bottom:24px;}
.btn{
  display:inline-flex;align-items:center;gap:10px;
  padding:14px 22px;border-radius:50px;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;font-weight:700;border:none;
  cursor:pointer;font-size:.95rem;
  box-shadow:0 6px 24px var(--glow);transition:all .3s;
}
.btn:hover{transform:translateY(-2px);box-shadow:0 10px 36px var(--glow);}
.btn-secondary{
  display:inline-flex;align-items:center;gap:10px;
  padding:10px 16px;border-radius:50px;
  background:var(--back-btn-bg);
  border:1px solid var(--border);
  color:var(--p3);cursor:pointer;font-size:.9rem;font-weight:600;
  transition:all .3s;
}
.btn-secondary:hover{background:var(--btn-bg-hover);}
.alert{
  background:var(--alert-bg);backdrop-filter:blur(10px);
  border:1px solid var(--alert-border);
  border-radius:14px;padding:16px;margin-bottom:20px;color:var(--text);
}
.wallet-row{display:grid;gap:14px;}
.bot-nav{position:fixed;bottom:0;left:0;right:0;background:var(--bot-nav-bg);backdrop-filter:blur(20px);border-top:1px solid var(--border);display:flex;z-index:200;}
.bot-nav a{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;padding:10px 4px;color:var(--muted);font-size:.58rem;font-weight:500;transition:all .25s;}
.bot-nav a:hover{color:var(--p3);}
.bot-nav a.on{color:var(--p3);}
.bot-nav a.on i{filter:drop-shadow(0 0 6px var(--p2));}
.bot-nav a i{font-size:1.1rem;}
.toast{position:fixed;bottom:90px;left:50%;transform:translateX(-50%);background:var(--toast-bg);backdrop-filter:blur(16px);border:1px solid var(--border);padding:12px 20px;border-radius:50px;font-size:.88rem;z-index:999;white-space:nowrap;box-shadow:0 8px 24px rgba(0,0,0,.3);}
.toast.ok{color:#34d399;border-color:rgba(16,185,129,.4);}
.toast.err{color:#f87171;border-color:rgba(239,68,68,.4);}
.toast.info{color:var(--p3);border-color:rgba(124,58,237,.4);}
.theme-toggle{width:40px;height:40px;border-radius:10px;background:var(--toggle-bg);border:1px solid var(--border);color:var(--p3);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.9rem;transition:all .3s;}
.theme-toggle:hover{background:var(--btn-bg-hover);}
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
  <div style="background:rgba(13,13,31,.97);backdrop-filter:blur(20px);border:1px solid var(--border);border-radius:24px;padding:28px 24px;width:100%;max-width:420px;">
    <button class="modal-close" onclick="document.getElementById('pay-modal').style.display='none'" style="float:right;background:rgba(124,58,237,.15);border:1px solid var(--border);color:var(--muted);font-size:1rem;cursor:pointer;border-radius:8px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;">✕</button>
    <h3 style="font-family:'Baloo 2',cursive;font-size:1.2rem;margin-bottom:12px;color:#fff;">💰 Wallet Recharge</h3>
    <p style="color:var(--muted);font-size:.92rem;margin-bottom:16px;">Current Balance: <strong style="color:#A855F7;">₹<?= number_format($bal,2) ?></strong></p>
    <button onclick="pay(9)" class="btn" style="width:100%;margin-bottom:12px;"><i class="fas fa-bolt"></i> ₹9 — 1 Page</button>
    <button onclick="pay(79)" class="btn" style="width:100%;margin-bottom:12px;background:rgba(124,58,237,.2);box-shadow:none;">₹79 — 10 Pages</button>
    <button onclick="pay(179)" class="btn" style="width:100%;margin-bottom:12px;background:rgba(124,58,237,.2);box-shadow:none;">₹179 — 25 Pages</button>
    <button onclick="pay(499)" class="btn" style="width:100%;background:rgba(124,58,237,.2);box-shadow:none;">₹499 — Unlimited Month</button>
  </div>
</div>
<nav class="bot-nav">
  <a href="<?= siteUrl('dashboard/') ?>"><i class="fas fa-home"></i>Home</a>
  <a href="<?= siteUrl('dashboard/orders.php') ?>"><i class="fas fa-shopping-bag"></i>Orders</a>
  <a href="<?= siteUrl('dashboard/create.php') ?>"><i class="fas fa-plus-circle"></i>New Page</a>
  <a href="<?= siteUrl('m/') ?>"><i class="fas fa-search"></i>Find</a>
  <a href="<?= siteUrl('dashboard/wallet.php') ?>" class="on"><i class="fas fa-wallet"></i>Wallet</a>
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
    },prefill:{name:'<?= addslashes($_SESSION['name'] ?? '') ?>',email:'<?= addslashes($_SESSION['email'] ?? '') ?>'},theme:{color:'#7C3AED'}}).open();
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
