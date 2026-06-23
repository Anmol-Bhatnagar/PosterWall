<?php
require_once '../config.php';
requireAuth();
$user  = me();
$bal   = wallet();
$uid   = (int)$_SESSION['uid'];
$db    = db();
$pages = $db->query("SELECT * FROM pages WHERE user_id=$uid ORDER BY created_at DESC");
$total = $db->query("SELECT COUNT(*) as c FROM pages WHERE user_id=$uid")->fetch_assoc()['c'];
$views = $db->query("SELECT COALESCE(SUM(views),0) as v FROM pages WHERE user_id=$uid")->fetch_assoc()['v'];
$gens  = $db->query("SELECT COUNT(*) as c FROM generations WHERE user_id=$uid")->fetch_assoc()['c'];
$typeIcons=['restaurant'=>'🍽️','shop'=>'🛍️','clinic'=>'🏥','professional'=>'💼','salon'=>'💇','gym'=>'🏋️','school'=>'🏫','repair'=>'🔧','event'=>'🎉','general'=>'🦅'];
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — PosterWall</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--p:#FF6B00;--bg:#0a0a14;--bg2:#12192b;--card:#1a2540;--text:#f0f0f0;--muted:#8892a4;--border:rgba(255,255,255,0.07);}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);min-height:100svh;padding-bottom:90px;}
a{text-decoration:none;color:inherit;}
nav{background:rgba(10,10,20,.97);border-bottom:1px solid var(--border);padding:0 16px;position:sticky;top:0;z-index:100;}
.nav-in{max-width:900px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:54px;}
.logo{font-family:'Baloo 2',cursive;font-size:1.3rem;font-weight:800;}
.logo span{color:var(--p);}
.nav-r{display:flex;align-items:center;gap:10px;}
.nav-av{width:32px;height:32px;border-radius:50%;border:2px solid var(--p);object-fit:cover;}
.logout-btn{padding:8px 12px;border-radius:10px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);color:#fff;font-weight:600;font-size:.85rem;transition:all .2s;}
.logout-btn:hover{background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);}
.con{max-width:900px;margin:0 auto;padding:24px 16px;min-height:calc(100vh - 190px);}
.page-title{font-family:'Baloo 2',cursive;font-size:1.75rem;font-weight:800;margin-bottom:8px;}
.page-sub{color:var(--muted);font-size:.95rem;line-height:1.6;margin-bottom:22px;}

/* Stats */
.stats{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px;}
@media(min-width:600px){.stats{grid-template-columns:repeat(4,1fr);}}
.stat-c{background:var(--card);border:1px solid var(--border);border-radius:13px;padding:16px 14px;position:relative;overflow:hidden;}
.stat-c::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--p);}
.stat-c.g::before{background:#00d464;} .stat-c.b::before{background:#4f8ef7;} .stat-c.y::before{background:#FFD700;}
.stat-label{font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;}
.stat-val{font-family:'Baloo 2',cursive;font-size:1.8rem;font-weight:800;color:var(--p);}
.stat-c.g .stat-val{color:#00d464;} .stat-c.b .stat-val{color:#4f8ef7;} .stat-c.y .stat-val{color:#FFD700;}

/* New page CTA */
.new-cta{background:linear-gradient(135deg,rgba(255,107,0,.15),rgba(255,215,0,.06));border:1px solid rgba(255,107,0,.3);border-radius:16px;padding:24px;margin-bottom:24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap;}
.new-cta-text h3{font-family:'Baloo 2',cursive;font-size:1.1rem;margin-bottom:4px;}
.new-cta-text p{color:var(--muted);font-size:.82rem;}
.cta-btn{padding:12px 22px;border-radius:10px;background:var(--p);color:#fff;font-weight:700;font-size:.9rem;border:none;cursor:pointer;display:flex;align-items:center;gap:8px;white-space:nowrap;flex-shrink:0;}

/* Upload */
.upload-area{background:var(--card);border:2px dashed rgba(255,107,0,.3);border-radius:14px;padding:28px;text-align:center;cursor:pointer;transition:all .3s;display:none;margin-bottom:16px;position:relative;}
.upload-area.show{display:block;}
.upload-area:hover{border-color:var(--p);}
.upload-area input{position:absolute;inset:0;opacity:0;cursor:pointer;}
.upload-preview{width:100%;max-height:180px;object-fit:cover;border-radius:10px;margin-bottom:12px;display:none;}
.gen-btn{width:100%;padding:13px;border-radius:11px;background:var(--p);color:#fff;font-weight:700;border:none;cursor:pointer;font-size:.95rem;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:16px;display:none;}
.gen-btn.show{display:flex;}

/* Pages grid */
.sec-title{font-family:'Baloo 2',cursive;font-size:1.1rem;font-weight:800;margin-bottom:14px;}
.pages-grid{display:grid;grid-template-columns:1fr;gap:14px;}
@media(min-width:500px){.pages-grid{grid-template-columns:repeat(2,1fr);}}
@media(min-width:800px){.pages-grid{grid-template-columns:repeat(3,1fr);}}
.pg-card{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:all .25s;}
.pg-card:hover{border-color:rgba(255,107,0,.3);}
.pg-thumb{height:110px;background:var(--bg2);display:flex;align-items:center;justify-content:center;font-size:3rem;position:relative;}
.pg-badge{position:absolute;top:8px;right:8px;background:rgba(0,0,0,.6);color:#fff;padding:3px 8px;border-radius:6px;font-size:.68rem;}
.pg-info{padding:14px;}
.pg-name{font-weight:700;font-size:.9rem;margin-bottom:3px;}
.pg-type{color:var(--muted);font-size:.72rem;margin-bottom:10px;text-transform:capitalize;}
.pg-actions{display:flex;gap:6px;}
.pg-btn{flex:1;padding:7px;border-radius:8px;border:1px solid var(--border);background:none;color:var(--muted);cursor:pointer;font-size:.72rem;display:flex;align-items:center;justify-content:center;gap:4px;transition:all .2s;}
.pg-btn:hover{border-color:var(--p);color:var(--p);}
.pg-btn.view{background:rgba(255,107,0,.1);border-color:rgba(255,107,0,.3);color:var(--p);}

/* Wallet */
.wallet-bar{background:linear-gradient(135deg,var(--p),#e05a00);border-radius:13px;padding:18px 20px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
.wal-bal{font-family:'Baloo 2',cursive;font-size:1.8rem;font-weight:800;color:#fff;}
.wal-label{font-size:.75rem;color:rgba(255,255,255,.75);text-transform:uppercase;letter-spacing:1px;}
.wal-btn{padding:10px 18px;border-radius:9px;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3);color:#fff;font-weight:600;font-size:.85rem;cursor:pointer;}

/* Bottom nav */
.bot-nav{position:fixed;top:auto;bottom:0;left:0;right:0;background:var(--bg2);border-top:1px solid var(--border);display:flex;z-index:200;}
.bot-nav a{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;padding:10px 4px;color:var(--muted);font-size:.6rem;font-weight:500;}
.bot-nav a.on{color:var(--p);}
.bot-nav a i{font-size:1.05rem;}

/* Modal */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:500;display:flex;align-items:flex-end;justify-content:center;backdrop-filter:blur(4px);}
.modal{background:var(--card);border:1px solid var(--border);border-radius:20px 20px 0 0;padding:24px 20px;width:100%;max-height:85svh;overflow-y:auto;}
@media(min-width:600px){.overlay{align-items:center;padding:20px;}.modal{border-radius:20px;max-width:460px;}}
.modal h3{font-family:'Baloo 2',cursive;font-size:1.2rem;margin-bottom:12px;}
.modal-close{float:right;background:none;border:none;color:var(--muted);font-size:1.2rem;cursor:pointer;}
.inp{width:100%;padding:11px 14px;border-radius:10px;background:var(--bg2);border:1px solid var(--border);color:var(--text);font-family:'Poppins',sans-serif;font-size:.95rem;margin-bottom:10px;}
.inp:focus{outline:none;border-color:var(--p);}
.btn-p{width:100%;padding:13px;border-radius:10px;background:var(--p);color:#fff;font-weight:700;border:none;cursor:pointer;font-size:.9rem;margin-bottom:8px;}
.btn-s{width:100%;padding:11px;border-radius:10px;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text);font-weight:600;border:none;cursor:pointer;font-size:.88rem;}

/* Share modal content */
.soc-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:12px 0;}
.soc-btn{display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:9px;color:#fff;font-weight:600;font-size:.8rem;text-decoration:none;}

.spin{width:18px;height:18px;border:3px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:sp .7s linear infinite;display:inline-block;}
@keyframes sp{to{transform:rotate(360deg);}}
.toast{position:fixed;bottom:76px;left:50%;transform:translateX(-50%);background:var(--card);border:1px solid var(--border);padding:10px 18px;border-radius:10px;font-size:.82rem;z-index:999;white-space:nowrap;}
.tok{border-color:rgba(0,212,100,.4);color:#00d464;} .ter{border-color:rgba(255,85,85,.4);color:#ff5555;} .tin{border-color:rgba(255,107,0,.4);color:var(--p);}
</style>
</head>
<body>

<nav>
  <div class="nav-in">
    <a href="<?= siteUrl('') ?>" class="logo">🦅 Poster<span>Wall</span></a>
    <div class="nav-r">
      <img src="<?= htmlspecialchars($user['avatar']??'') ?>" class="nav-av" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=FF6B00&color=fff'">
      <a href="<?= siteUrl('auth/logout.php') ?>" class="logout-btn">Logout</a>
    </div>
  </div>
</nav>

<div class="con">
  <div class="page-title">Namaste, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?>! 👋</div>
  <div class="page-sub">Apne saare digital pages manage karo</div>

  <!-- Wallet -->
  <div class="wallet-bar">
    <div>
      <div class="wal-label">Wallet Balance</div>
      <div class="wal-bal">₹<?= number_format($bal,2) ?></div>
    </div>
    <button class="wal-btn" onclick="document.getElementById('pay-modal').style.display='flex'">+ Recharge</button>
  </div>

  <!-- Stats -->
  <div class="stats">
    <div class="stat-c"><div class="stat-label">My Pages</div><div class="stat-val"><?= $total ?></div></div>
    <div class="stat-c b"><div class="stat-label">Total Views</div><div class="stat-val"><?= number_format($views) ?></div></div>
    <div class="stat-c g"><div class="stat-label">Generations</div><div class="stat-val"><?= $gens ?></div></div>
    <div class="stat-c y"><div class="stat-label">Cost/Page</div><div class="stat-val">₹9</div></div>
  </div>

  <!-- New Page -->
  <div class="new-cta">
    <div class="new-cta-text">
      <h3>📸 Naya Digital Page Banao</h3>
      <p>Photo upload karo — AI 30 seconds mein beautiful page banayega!</p>
    </div>
    <button class="cta-btn" onclick="toggleUpload()"><i class="fas fa-plus"></i> New Page (₹9)</button>
  </div>

  <!-- Upload Area -->
  <div class="upload-area" id="upload-area">
    <input type="file" id="photo-inp" accept="image/*" onchange="handlePhoto(event)">
    <img id="up-prev" class="upload-preview">
    <div id="up-hint"><div style="font-size:2.2rem;margin-bottom:8px;">📸</div><div style="font-weight:600;margin-bottom:4px;">Dukaan/Dhaba ki photo upload karo</div><div style="color:var(--muted);font-size:.8rem;">Click ya photo khींcho</div></div>
  </div>

  <div id="gen-status" style="display:none;margin:16px 0;">
    <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:999px;overflow:hidden;height:14px;">
      <div id="gen-status-bar" style="width:0%;height:100%;background:linear-gradient(90deg, #FF6B00, #FFC258);transition:width .3s ease;"></div>
    </div>
    <div id="gen-status-text" style="margin-top:10px;color:#fff;font-size:.95rem;line-height:1.4;">AI page ban raha hai...</div>
  </div>

  <input type="tel" id="mob-inp" class="inp" placeholder="📱 Mobile number (optional — log number se dhundh sakenge)" maxlength="10" style="display:none;" oninput="this.value = this.value.replace(/\D/g, '')">
  <button class="gen-btn" id="gen-btn" onclick="generate()"><i class="fas fa-magic"></i> AI se Page Banao — ₹9</button>

  <!-- My Pages -->
  <div class="sec-title">🖼️ Mere Pages</div>

  <?php if($pages->num_rows > 0): ?>
  <div class="pages-grid">
    <?php while($pg=$pages->fetch_assoc()):
      $icon  = $typeIcons[$pg['business_type']] ?? '🦅';
      $url   = SITE_URL . '/p/' . $pg['token'];
      $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='.urlencode($url).'&color=FF6B00&bgcolor=0a0a14';
    ?>
    <div class="pg-card">
      <div class="pg-thumb">
        <?= $icon ?>
        <div class="pg-badge"><i class="fas fa-eye"></i> <?= number_format($pg['views']) ?></div>
      </div>
      <div class="pg-info">
        <div class="pg-name"><?= htmlspecialchars($pg['business_name'] ?: 'My Page') ?></div>
        <div class="pg-type"><?= $pg['business_type'] ?> &nbsp;•&nbsp; <?= date('d M Y', strtotime($pg['created_at'])) ?></div>
        <div class="pg-actions">
          <a href="<?= SITE_URL . '/p/' . $pg['token'] ?>" target="_blank" class="pg-btn view"><i class="fas fa-eye"></i> View</a>
          <button class="pg-btn" onclick="openShare('<?= $pg['token'] ?>','<?= addslashes($pg['business_name']) ?>','<?= $url ?>','<?= $qrUrl ?>','<?= $pg['mobile'] ?>','<?= $pg['whatsapp_no'] ?>')"><i class="fas fa-share-alt"></i> Share</button>
          <button class="pg-btn" onclick="regenPage('<?= $pg['token'] ?>')" title="Regenerate"><i class="fas fa-sync"></i></button>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <?php else: ?>
  <div style="text-align:center;padding:60px 20px;color:var(--muted);">
    <div style="font-size:3.5rem;margin-bottom:16px;">📸</div>
    <div style="font-family:'Baloo 2',cursive;font-size:1.2rem;font-weight:800;margin-bottom:8px;">Koi Page Nahi Hai</div>
    <div style="font-size:.875rem;margin-bottom:20px;">Pehla digital page banao — sirf ₹9 mein!</div>
    <button class="cta-btn" onclick="toggleUpload()" style="margin:0 auto;padding:12px 24px;border-radius:10px;background:var(--p);color:#fff;font-weight:700;border:none;cursor:pointer;"><i class="fas fa-magic"></i> Abhi Banao</button>
  </div>
  <?php endif; ?>
</div>

<!-- Bottom Nav -->
<nav class="bot-nav">
  <a href="<?= siteUrl('dashboard/') ?>" class="on"><i class="fas fa-home"></i>Home</a>
  <a href="<?= siteUrl('') ?>" onclick="setTimeout(()=>toggleUpload(),500)"><i class="fas fa-plus-circle"></i>New Page</a>
  <a href="<?= siteUrl('m/') ?>"><i class="fas fa-search"></i>Find</a>
  <a href="<?= siteUrl('dashboard/wallet.php') ?>"><i class="fas fa-wallet"></i>Wallet</a>
</nav>

<!-- SHARE MODAL -->
<div id="share-modal" class="overlay" style="display:none;">
  <div class="modal">
    <button class="modal-close" onclick="document.getElementById('share-modal').style.display='none'">✕</button>
    <h3 id="share-title">📤 Share Page</h3>
    <div style="margin-bottom:12px;">
      <div style="font-size:.75rem;color:var(--muted);margin-bottom:4px;">🔗 Page Link</div>
      <div style="display:flex;gap:8px;">
        <input type="text" id="share-url" class="inp" readonly style="margin:0;font-size:.78rem;flex:1;">
        <button onclick="copyUrl()" style="padding:10px 14px;background:var(--p);color:#fff;border:none;border-radius:9px;cursor:pointer;"><i class="fas fa-copy"></i></button>
      </div>
    </div>
    <div style="text-align:center;margin-bottom:14px;">
      <img id="share-qr" src="" style="width:140px;height:140px;border-radius:10px;border:2px solid var(--p);margin:0 auto 8px;display:block;">
      <a id="qr-dl" href="#" download="qr.png" style="font-size:.75rem;color:var(--p);"><i class="fas fa-download"></i> QR Download Karo</a>
    </div>
    <div class="soc-row">
      <a id="sh-wa" href="#" target="_blank" class="soc-btn" style="background:#25D366;"><i class="fab fa-whatsapp"></i> WhatsApp</a>
      <a id="sh-tg" href="#" target="_blank" class="soc-btn" style="background:#0088cc;"><i class="fab fa-telegram"></i> Telegram</a>
      <a id="sh-tw" href="#" target="_blank" class="soc-btn" style="background:#1da1f2;"><i class="fab fa-x-twitter"></i> Twitter</a>
      <a id="sh-fb" href="#" target="_blank" class="soc-btn" style="background:#1877f2;"><i class="fab fa-facebook"></i> Facebook</a>
    </div>
    <div id="mob-url-row" style="display:none;margin-top:10px;background:rgba(255,107,0,.08);border:1px solid rgba(255,107,0,.2);border-radius:10px;padding:12px;font-size:.8rem;">
      📱 Mobile URL: <a id="mob-url-link" href="#" style="color:var(--p);font-weight:600;"></a>
    </div>
  </div>
</div>

<!-- PAY MODAL -->
<div id="pay-modal" class="overlay" style="display:none;">
  <div class="modal">
    <button class="modal-close" onclick="document.getElementById('pay-modal').style.display='none'">✕</button>
    <h3>💰 Wallet Recharge</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:16px;">Current Balance: <strong style="color:var(--p);">₹<?= number_format($bal,2) ?></strong></p>
    <button onclick="pay(9)"   class="btn-p"><i class="fas fa-bolt"></i> ₹9 — 1 Page</button>
    <button onclick="pay(79)"  class="btn-s">₹79 — 10 Pages (save ₹11)</button>
    <button onclick="pay(179)" class="btn-s">₹179 — 25 Pages (save ₹46)</button>
    <button onclick="pay(499)" class="btn-s">₹499 — Unlimited Month</button>
  </div>
</div>

<!-- REGEN MODAL -->
<div id="regen-modal" class="overlay" style="display:none;">
  <div class="modal">
    <button class="modal-close" onclick="document.getElementById('regen-modal').style.display='none'">✕</button>
    <h3>🔄 Page Regenerate Karo</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:14px;">Naya photo upload karo — AI page update karega (₹9)</p>
    <div class="upload-area show" style="margin-bottom:12px;position:relative;">
      <input type="file" id="regen-photo" accept="image/*" onchange="handleRegenPhoto(event)">
      <img id="regen-prev" style="width:100%;max-height:150px;object-fit:cover;border-radius:10px;margin-bottom:8px;display:none;">
      <div id="regen-hint"><div style="font-size:1.8rem;margin-bottom:6px;">📸</div><div style="font-size:.85rem;">Naya photo upload karo</div></div>
    </div>
    <button class="btn-p" id="regen-btn" onclick="sendRegen()" disabled>
      <i class="fas fa-sync"></i> Regenerate (₹9)
    </button>
  </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
let photoB64=null,photoMime=null,regenToken=null,regenB64=null,regenMime=null;
const USER_NAME='<?= addslashes($user['name']) ?>', USER_EMAIL='<?= addslashes($user['email']) ?>';
let bal=<?= $bal ?>;

function toggleUpload(){
  const a=document.getElementById('upload-area'),m=document.getElementById('mob-inp'),g=document.getElementById('gen-btn');
  const show=!a.classList.contains('show');
  a.classList.toggle('show',show);
  m.style.display=show?'block':'none';
  if(!show){g.classList.remove('show');}
  if(show){
    setGenStatus(0,'Photo upload karo aur fir “AI se Page Banao” dabao');
    document.getElementById('gen-status').style.display='block';
    document.getElementById('gen-status-bar').style.width='0%';
  }
  if(show)a.scrollIntoView({behavior:'smooth',block:'center'});
}

function handlePhoto(e){
  const f=e.target.files[0]; if(!f)return;
  const r=new FileReader();
  r.onload=(ev)=>{
    photoB64=ev.target.result.split(',')[1]; photoMime=f.type;
    const p=document.getElementById('up-prev'),h=document.getElementById('up-hint');
    p.src=ev.target.result; p.style.display='block'; h.style.display='none';
    document.getElementById('gen-btn').classList.add('show');
    setGenStatus(20,'Photo taiyar hai — ab AI page bana raha hai');
    toast('Photo ready! ✅','tok');
  };
  r.readAsDataURL(f);
}

let genProgressInterval=null;
function setGenStatus(percent,text){
  const bar=document.getElementById('gen-status-bar');
  const txt=document.getElementById('gen-status-text');
  if(bar){bar.style.width=Math.min(Math.max(percent,0),100)+'%';}
  if(txt){txt.textContent=text;}
}
function resetGenStatus(){
  if(genProgressInterval){clearInterval(genProgressInterval);genProgressInterval=null;}
  setGenStatus(0,'');
  document.getElementById('gen-status').style.display='none';
}

function generate(){
  if(!photoB64){toast('Photo upload karo!','ter');return;}
  if(bal<9){document.getElementById('pay-modal').style.display='flex';return;}
  const mob=document.getElementById('mob-inp').value.replace(/\D/g,'');
  const btn=document.getElementById('gen-btn');
  btn.disabled=true; btn.innerHTML='<div class="spin"></div> AI Page Bana Raha Hai...';
  setGenStatus(30,'AI abhi aapka page bana raha hai — thoda intezaar karo');
  let progress=30;
  genProgressInterval=setInterval(()=>{
    progress = Math.min(progress + Math.random() * 8, 90);
    setGenStatus(progress, 'AI page banta ja raha hai — bas thoda aur');
  }, 900);

  fetch('<?= SITE_URL ?>/api/generate.php',{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({image_base64:photoB64,mime_type:photoMime,mobile:mob})
  }).then(r=>r.json()).then(res=>{
    if(genProgressInterval){clearInterval(genProgressInterval);genProgressInterval=null;}
    if(res.success){
      setGenStatus(100,'Page ban gaya! Ab load kar rahe hain...');
      bal=res.balance;
      setTimeout(()=>window.location.href='<?= SITE_URL ?>/p/'+res.token,500);
      return;
    }
    btn.disabled=false; btn.innerHTML='<i class="fas fa-magic"></i> AI se Page Banao — ₹9';
    setGenStatus(0,'Kuch gadbad ho gayi — dobara try karo');
    if(res.error==='no_balance'){document.getElementById('pay-modal').style.display='flex';}
    else{toast(res.error||'Kuch gadbad!','ter');}
  }).catch(()=>{
    if(genProgressInterval){clearInterval(genProgressInterval);genProgressInterval=null;}
    btn.disabled=false; btn.innerHTML='<i class="fas fa-magic"></i> AI se Page Banao — ₹9';
    setGenStatus(0,'Network ya server problem — thoda baad phir se try karo');
    toast('Network error!','ter');
  });
}

function openShare(token,name,url,qr,mobile,wa){
  document.getElementById('share-title').textContent='📤 '+name;
  document.getElementById('share-url').value=url;
  document.getElementById('share-qr').src=qr;
  document.getElementById('qr-dl').href=qr;
  const txt=encodeURIComponent('Hamara page dekho: '+url);
  document.getElementById('sh-wa').href='https://wa.me/?text='+txt;
  document.getElementById('sh-tg').href='https://t.me/share/url?url='+encodeURIComponent(url);
  document.getElementById('sh-tw').href='https://twitter.com/intent/tweet?url='+encodeURIComponent(url);
  document.getElementById('sh-fb').href='https://www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(url);
  const mr=document.getElementById('mob-url-row');
  if(mobile){mr.style.display='block';document.getElementById('mob-url-link').href='<?= SITE_URL ?>/m/'+mobile;document.getElementById('mob-url-link').textContent='<?= SITE_URL ?>/m/'+mobile;}
  else{mr.style.display='none';}
  document.getElementById('share-modal').style.display='flex';
}

function copyUrl(){
  navigator.clipboard.writeText(document.getElementById('share-url').value).then(()=>toast('Link copied! ✅','tok'));
}

function regenPage(token){
  regenToken=token; regenB64=null; regenMime=null;
  document.getElementById('regen-prev').style.display='none';
  document.getElementById('regen-hint').style.display='block';
  document.getElementById('regen-btn').disabled=true;
  document.getElementById('regen-modal').style.display='flex';
}

function handleRegenPhoto(e){
  const f=e.target.files[0]; if(!f)return;
  const r=new FileReader();
  r.onload=(ev)=>{
    regenB64=ev.target.result.split(',')[1]; regenMime=f.type;
    const p=document.getElementById('regen-prev'),h=document.getElementById('regen-hint');
    p.src=ev.target.result; p.style.display='block'; h.style.display='none';
    document.getElementById('regen-btn').disabled=false;
  };
  r.readAsDataURL(f);
}

function sendRegen(){
  if(!regenB64||!regenToken)return;
  if(bal<9){document.getElementById('regen-modal').style.display='none';document.getElementById('pay-modal').style.display='flex';return;}
  const btn=document.getElementById('regen-btn');
  btn.disabled=true; btn.innerHTML='<div class="spin"></div> Regenerating...';
  fetch('<?= SITE_URL ?>/api/generate.php',{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({image_base64:regenB64,mime_type:regenMime,regen_token:regenToken})
  }).then(r=>r.json()).then(res=>{
    if(res.success){toast('Page updated! ✅','tok');setTimeout(()=>location.reload(),1500);}
    else{toast(res.error||'Error!','ter');}
    btn.disabled=false; btn.innerHTML='<i class="fas fa-sync"></i> Regenerate (₹9)';
  });
}

function pay(amount){
  document.getElementById('pay-modal').style.display='none';
  fetch('<?= SITE_URL ?>/api/payment.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'create_order',amount})})
  .then(r=>r.json()).then(data=>{
    if(!data.order_id){toast('Order fail!','ter');return;}
    new Razorpay({key:'<?= RZP_KEY_ID ?>',amount:amount*100,currency:'INR',name:'PosterWall',
      order_id:data.order_id,
      handler:(r)=>{
        fetch('<?= SITE_URL ?>/api/payment.php',{method:'POST',headers:{'Content-Type':'application/json'},
          body:JSON.stringify({action:'verify',razorpay_payment_id:r.razorpay_payment_id,razorpay_order_id:r.razorpay_order_id,razorpay_signature:r.razorpay_signature})
        }).then(res=>res.json()).then(v=>{if(v.success){bal+=amount;toast('₹'+amount+' add ho gaya! ✅','tok');setTimeout(()=>location.reload(),1500);}});
      },
      prefill:{name:USER_NAME,email:USER_EMAIL},theme:{color:'#FF6B00'}
    }).open();
  });
}

function toast(msg,type='tin'){
  const d=document.createElement('div');d.className='toast '+type;d.textContent=msg;
  document.body.appendChild(d);setTimeout(()=>d.remove(),3000);
}
</script>
</body>
</html>
