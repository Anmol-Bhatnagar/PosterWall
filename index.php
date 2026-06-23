<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PosterWall — Apna Design. Apna Brand. Apni Pehchaan.</title>
<meta name="description" content="Sirf ₹9 mein apni dukaan, dhaba, clinic ki photo se beautiful digital page banao. QR Code aur mobile number se accessible!">
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--p:#FF6B00;--pd:#e05a00;--a:#FFD700;--bg:#0a0a14;--bg2:#12192b;--card:#1a2540;--text:#f0f0f0;--muted:#8892a4;--border:rgba(255,255,255,0.07);}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);overflow-x:hidden;}
a{text-decoration:none;color:inherit;}

/* NAV */
nav{position:fixed;top:0;left:0;right:0;z-index:100;padding:0 20px;background:rgba(10,10,20,.95);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);}
.nav-in{max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:58px;}
.logo{font-family:'Baloo 2',cursive;font-size:1.5rem;font-weight:800;display:flex;align-items:center;gap:6px;}
.logo span{color:var(--p);}
.nav-right{display:flex;align-items:center;gap:12px;}
.nav-btn{padding:8px 18px;border-radius:9px;font-weight:600;font-size:.85rem;background:var(--p);color:#fff;border:none;cursor:pointer;transition:all .2s;}
.nav-btn.out{background:transparent;border:1px solid var(--border);color:var(--text);}

/* HERO */
.hero{min-height:100svh;padding-top:58px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:80px 20px 60px;position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 50% 30%,rgba(255,107,0,.18) 0%,transparent 65%);pointer-events:none;}

.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(255,107,0,.12);border:1px solid rgba(255,107,0,.3);color:var(--p);padding:6px 16px;border-radius:100px;font-size:.8rem;font-weight:600;margin-bottom:24px;animation:fadeUp .6s ease;}
.hero h1{font-family:'Baloo 2',cursive;font-size:clamp(2rem,7vw,4rem);font-weight:800;line-height:1.15;margin-bottom:16px;animation:fadeUp .7s ease;}
.grad{background:linear-gradient(135deg,var(--p),var(--a));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.hero-sub{color:var(--muted);font-size:clamp(.95rem,2.5vw,1.1rem);max-width:540px;line-height:1.8;margin-bottom:36px;animation:fadeUp .8s ease;}

/* UPLOAD BOX */
.upload-box{background:var(--card);border:2px dashed rgba(255,107,0,.4);border-radius:20px;padding:36px 24px;max-width:500px;width:100%;margin:0 auto 32px;cursor:pointer;transition:all .3s;animation:fadeUp .9s ease;position:relative;overflow:hidden;}
.upload-box:hover{border-color:var(--p);background:rgba(255,107,0,.06);}
.upload-box input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.upload-icon{font-size:3rem;margin-bottom:12px;}
.upload-title{font-weight:700;font-size:1.1rem;margin-bottom:6px;}
.upload-sub{color:var(--muted);font-size:.85rem;}
.upload-preview{display:none;width:100%;border-radius:12px;margin-bottom:12px;max-height:200px;object-fit:cover;}

.gen-btn{width:100%;max-width:500px;padding:15px;border-radius:12px;background:var(--p);color:#fff;font-weight:700;font-size:1rem;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:all .3s;box-shadow:0 6px 24px rgba(255,107,0,.4);animation:fadeUp 1s ease;margin:0 auto;}
.gen-btn:hover{background:var(--pd);transform:translateY(-2px);}
.gen-btn:disabled{opacity:.6;cursor:not-allowed;transform:none;}

.price-tag{display:inline-flex;align-items:center;gap:6px;margin-top:14px;color:var(--muted);font-size:.82rem;}
.price-tag strong{color:var(--p);}

/* MOBILE SEARCH */
.mob-search{max-width:500px;margin:48px auto 0;text-align:center;animation:fadeUp 1.1s ease;}
.mob-search h3{font-family:'Baloo 2',cursive;font-size:1.1rem;margin-bottom:12px;color:var(--muted);}
.mob-input-row{display:flex;gap:8px;}
.mob-input{flex:1;padding:13px 16px;border-radius:11px;background:var(--card);border:1px solid var(--border);color:var(--text);font-size:1rem;font-family:'Poppins',sans-serif;}
.mob-input:focus{outline:none;border-color:var(--p);}
.mob-btn{padding:13px 18px;border-radius:11px;background:var(--card);border:1px solid var(--border);color:var(--p);cursor:pointer;font-size:1rem;}

/* STATS */
.stats{display:flex;justify-content:center;gap:40px;margin-top:56px;flex-wrap:wrap;animation:fadeUp 1.2s ease;}
.stat-item{text-align:center;}
.stat-item strong{font-family:'Baloo 2',cursive;font-size:2rem;font-weight:800;color:var(--p);display:block;}
.stat-item span{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;}

/* HOW IT WORKS */
.section{padding:72px 20px;}
.section-inner{max-width:1000px;margin:0 auto;}
.sec-head{text-align:center;margin-bottom:52px;}
.sec-head h2{font-family:'Baloo 2',cursive;font-size:clamp(1.6rem,4vw,2.4rem);font-weight:800;margin-bottom:10px;}
.sec-head p{color:var(--muted);font-size:.95rem;}

.steps-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;}
.step-card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px 20px;text-align:center;position:relative;overflow:hidden;transition:transform .3s;}
.step-card:hover{transform:translateY(-4px);}
.step-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--p);}
.step-num{width:40px;height:40px;background:rgba(255,107,0,.15);border:1px solid rgba(255,107,0,.3);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-weight:800;color:var(--p);}
.step-icon{font-size:2.4rem;margin-bottom:12px;}
.step-card h3{font-weight:700;margin-bottom:8px;font-size:1rem;}
.step-card p{color:var(--muted);font-size:.84rem;line-height:1.6;}

/* EXAMPLES */
.examples-bg{background:var(--bg2);}
.examples-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;}
.ex-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px 14px;text-align:center;transition:all .3s;cursor:pointer;}
.ex-card:hover{border-color:var(--p);transform:translateY(-3px);}
.ex-icon{font-size:2.2rem;margin-bottom:10px;}
.ex-name{font-weight:600;font-size:.88rem;margin-bottom:4px;}
.ex-desc{color:var(--muted);font-size:.72rem;}

/* PRICING */
.price-box{max-width:480px;margin:0 auto;background:var(--card);border:1px solid var(--border);border-radius:20px;padding:40px 32px;text-align:center;}
.price-main{font-family:'Baloo 2',cursive;font-size:4rem;font-weight:800;color:var(--p);margin:16px 0 4px;}
.price-list{list-style:none;margin:24px 0;text-align:left;}
.price-list li{padding:10px 0;border-bottom:1px solid var(--border);font-size:.9rem;display:flex;align-items:center;gap:10px;}
.price-list li::before{content:'✅';flex-shrink:0;}

/* FOOTER */
footer{background:#060610;border-top:1px solid var(--border);padding:40px 20px 24px;}
.foot-in{max-width:1000px;margin:0 auto;}
.foot-top{display:grid;grid-template-columns:1fr;gap:28px;margin-bottom:32px;}
@media(min-width:600px){.foot-top{grid-template-columns:2fr 1fr 1fr;}}
.foot-logo{font-family:'Baloo 2',cursive;font-size:1.3rem;font-weight:800;margin-bottom:10px;}
.foot-logo span{color:var(--p);}
.foot-desc{color:var(--muted);font-size:.84rem;line-height:1.7;}
.foot-links h4{font-weight:600;margin-bottom:12px;font-size:.9rem;}
.foot-links a{display:block;color:var(--muted);font-size:.84rem;padding:4px 0;transition:color .2s;}
.foot-links a:hover{color:var(--p);}
.foot-bottom{border-top:1px solid var(--border);padding-top:20px;text-align:center;color:var(--muted);font-size:.78rem;}
.social-row{display:flex;gap:10px;margin-top:14px;}
.soc-a{width:34px;height:34px;border-radius:8px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:.9rem;transition:all .2s;}
.soc-a:hover{border-color:var(--p);color:var(--p);}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:500;display:flex;align-items:flex-end;justify-content:center;backdrop-filter:blur(4px);}
.modal{background:var(--card);border:1px solid var(--border);border-radius:20px 20px 0 0;padding:28px 20px;width:100%;max-height:90svh;overflow-y:auto;}
@media(min-width:600px){.modal-overlay{align-items:center;padding:20px;}.modal{border-radius:20px;max-width:480px;}}
.modal h3{font-family:'Baloo 2',cursive;font-size:1.3rem;font-weight:800;margin-bottom:8px;}

/* ANIMATIONS */
@keyframes fadeUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
@keyframes spin{to{transform:rotate(360deg);}}
.spin{width:20px;height:20px;border:3px solid rgba(255,255,255,.2);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;display:inline-block;}

/* TOAST */
.toast{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--card);border:1px solid var(--border);padding:12px 20px;border-radius:12px;font-size:.875rem;z-index:999;box-shadow:0 8px 32px rgba(0,0,0,.4);animation:fadeUp .3s ease;white-space:nowrap;}
.toast.ok{border-color:rgba(0,212,100,.4);color:#00d464;}
.toast.err{border-color:rgba(255,85,85,.4);color:#ff5555;}
.toast.info{border-color:rgba(255,107,0,.4);color:var(--p);}
</style>
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-in">
    <div class="logo">🦅 Poster<span>Wall</span></div>
    <div class="nav-right">
      <?php if(loggedIn()): ?>
      <a href="<?= siteUrl('dashboard/') ?>" class="nav-btn">Dashboard</a>
      <a href="<?= siteUrl('auth/logout.php') ?>" class="nav-btn out">Logout</a>
      <?php else: ?>
      <a href="<?= siteUrl('m/') ?>" class="nav-btn out">📱 Find by Mobile</a>
      <a href="<?= siteUrl('auth/login.php') ?>" class="nav-btn">Login / Sign Up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge">🤖 AI-Powered Digital Identity — Sirf ₹9</div>
  <h1>Photo lo.<br>AI se <span class="grad">Page Banao.</span><br>Duniya ko Dikhao.</h1>
  <p class="hero-sub">Apni dukaan, dhaba, clinic ya business ki ek photo lo — AI ek beautiful digital page banayega. QR code aur mobile number se koi bhi access kar sakta hai!</p>

  <!-- UPLOAD -->
  <div class="upload-box" id="upload-box">
    <input type="file" id="photo-input" accept="image/*" onchange="handlePhoto(event)">
    <img id="upload-preview" class="upload-preview">
    <div id="upload-content">
      <div class="upload-icon">📸</div>
      <div class="upload-title">Apni dukaan/dhaba ki photo upload karo</div>
      <div class="upload-sub">JPG, PNG — mobile camera ya gallery se</div>
    </div>
  </div>

  <button class="gen-btn" id="gen-btn" onclick="startGeneration()" disabled>
    <i class="fas fa-magic"></i> AI se Digital Page Banao — ₹9
  </button>
  <div class="price-tag"><strong>₹9</strong> mein ek baar pay karo — page hamesha live rahega!</div>

  <!-- MOBILE FIND -->
  <div class="mob-search">
    <h3>📱 Kisi ka page mobile number se dhundho</h3>
    <div class="mob-input-row">
      <input type="tel" class="mob-input" id="mob-find" placeholder="Mobile number dalein..." maxlength="10" oninput="this.value = this.value.replace(/\D/g, '')">
      <button class="mob-btn" onclick="findByMobile()"><i class="fas fa-search"></i></button>
    </div>
  </div>

  <div class="stats">
    <div class="stat-item"><strong>₹9</strong><span>Per Page</span></div>
    <div class="stat-item"><strong>30s</strong><span>Generation</span></div>
    <div class="stat-item"><strong>QR</strong><span>Included</span></div>
    <div class="stat-item"><strong>∞</strong><span>Live Forever</span></div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section">
  <div class="section-inner">
    <div class="sec-head">
      <h2>Kaise Kaam Karta Hai?</h2>
      <p>3 simple steps — bas 30 seconds mein!</p>
    </div>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-num">1</div>
        <div class="step-icon">📸</div>
        <h3>Photo Lo</h3>
        <p>Apni dukaan ke bahar ki photo lo — board, menu, signboard kuch bhi</p>
      </div>
      <div class="step-card">
        <div class="step-num">2</div>
        <div class="step-icon">🤖</div>
        <h3>AI Page Banata Hai</h3>
        <p>Gemini AI photo padhta hai — naam, contact, menu sab extract karta hai aur beautiful page banata hai</p>
      </div>
      <div class="step-card">
        <div class="step-num">3</div>
        <div class="step-icon">📲</div>
        <h3>Share Karo</h3>
        <p>QR code print karo ya mobile number se share karo — customer directly page access kar sakta hai</p>
      </div>
      <div class="step-card">
        <div class="step-num">4</div>
        <div class="step-icon">♾️</div>
        <h3>Hamesha Live</h3>
        <p>Ek baar pay karo ₹9 — page posterwall.in pe hamesha live rahega. Kabhi delete nahi hoga!</p>
      </div>
    </div>
  </div>
</section>

<!-- EXAMPLES -->
<section class="section examples-bg">
  <div class="section-inner">
    <div class="sec-head">
      <h2>Kiske Liye Hai?</h2>
      <p>Har type ke business ke liye</p>
    </div>
    <div class="examples-grid">
      <?php
      $types=[
        ['🍽️','Dhaba/Restaurant','Menu aur contact page'],
        ['🛍️','Dukaan','Products aur timing'],
        ['🏥','Clinic/Doctor','Medical info page'],
        ['💼','Professional','Digital visiting card'],
        ['🎉','Event','Invitation page'],
        ['🏫','Coaching/School','Institute page'],
        ['💇','Salon/Parlour','Services aur booking'],
        ['🏋️','Gym/Fitness','Classes aur contact'],
        ['🔧','Repair Shop','Services aur rates'],
        ['🎨','Artist/Creator','Portfolio page'],
        ['🏠','Real Estate','Property page'],
        ['🚗','Auto/Transport','Booking page'],
      ];
      foreach($types as $t): ?>
      <div class="ex-card">
        <div class="ex-icon"><?=$t[0]?></div>
        <div class="ex-name"><?=$t[1]?></div>
        <div class="ex-desc"><?=$t[2]?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PRICING -->
<section class="section">
  <div class="section-inner">
    <div class="sec-head">
      <h2>Simple Pricing</h2>
      <p>Ek baar pay karo — hamesha live</p>
    </div>
    <div class="price-box">
      <div style="color:var(--muted);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;">Per Digital Page</div>
      <div class="price-main">₹9</div>
      <div style="color:var(--muted);font-size:.85rem;margin-bottom:8px;">One time payment</div>
      <ul class="price-list">
        <li>Beautiful AI-generated HTML page</li>
        <li>Mobile number se accessible</li>
        <li>QR Code — scan karo page khulega</li>
        <li>WhatsApp & Call buttons</li>
        <li>Google Maps link</li>
        <li>Share on all social media</li>
        <li>Hamesha live — kabhi expire nahi</li>
        <li>Edit karo anytime (₹9 per re-generate)</li>
      </ul>
      <a href="<?= siteUrl('auth/login.php') ?>" style="display:block;padding:14px;border-radius:12px;background:var(--p);color:#fff;font-weight:700;font-size:1rem;text-align:center;box-shadow:0 6px 24px rgba(255,107,0,.4);">Abhi Shuru Karo →</a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="foot-in">
    <div class="foot-top">
      <div>
        <div class="foot-logo">🦅 Poster<span>Wall</span></div>
        <p class="foot-desc">Apna Design. Apna Brand. Apni Pehchaan.<br>Har Indian business ka digital identity — sirf ₹9 mein.</p>
        <div class="social-row">
          <a href="https://www.instagram.com/posterwall.in/" target="_blank" class="soc-a"><i class="fab fa-instagram"></i></a>
          <a href="https://x.com/posterwall_in" target="_blank" class="soc-a"><i class="fab fa-x-twitter"></i></a>
          <a href="https://www.youtube.com/channel/UCeo8ZL4PR9hXR7ZdcMNqiuw" target="_blank" class="soc-a"><i class="fab fa-youtube"></i></a>
          <a href="https://whatsapp.com/channel/0029VbChgAZ6LwHsRN2vGJ0O" target="_blank" class="soc-a"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>
      <div class="foot-links">
        <h4>Quick Links</h4>
        <a href="<?= siteUrl('m/') ?>">📱 Find by Mobile</a>
        <a href="<?= siteUrl('auth/login.php') ?>">Login / Sign Up</a>
        <a href="<?= siteUrl('dashboard/') ?>">Dashboard</a>
        <a href="<?= siteUrl('admin/login.php') ?>">Admin</a>
      </div>
      <div class="foot-links">
        <h4>Company</h4>
        <a href="#">About Us</a>
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="mailto:posterwall.in@gmail.com">Contact</a>
      </div>
    </div>
    <div class="foot-bottom">
      © <?=YEAR?> <strong style="color:var(--text);">PosterWall</strong> — A Product of <strong style="color:var(--text);">Tech Eagles</strong> under <strong style="color:var(--text);">Mahakumbrix Innovation</strong> &nbsp;|&nbsp; Made with ❤️ in India
    </div>
  </div>
</footer>

<!-- MOBILE MODAL -->
<div id="mobile-modal" class="modal-overlay" style="display:none;">
  <div class="modal">
    <h3>📱 Apna Mobile Number</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:16px;">Mobile number add karo taaki log aapka page number se dhundh sakein!</p>
    <input type="tel" id="user-mobile" style="width:100%;padding:13px 16px;border-radius:11px;background:var(--bg2);border:1px solid var(--border);color:var(--text);font-size:1rem;font-family:'Poppins',sans-serif;margin-bottom:12px;" placeholder="10-digit mobile number" maxlength="10" oninput="this.value = this.value.replace(/\D/g, '')">
    <button onclick="proceedGeneration()" style="width:100%;padding:14px;border-radius:11px;background:var(--p);color:#fff;font-weight:700;font-size:.95rem;border:none;cursor:pointer;">
      <i class="fas fa-magic"></i> Generate Karo — ₹9
    </button>
    <button onclick="proceedGeneration()" style="width:100%;padding:12px;border-radius:11px;background:none;border:none;color:var(--muted);cursor:pointer;margin-top:8px;font-size:.85rem;">Mobile skip karo — Continue</button>
  </div>
</div>

<script>
let photoB64 = null, photoMime = null;

function handlePhoto(e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (ev) => {
    photoB64  = ev.target.result.split(',')[1];
    photoMime = file.type;
    const prev = document.getElementById('upload-preview');
    const cont = document.getElementById('upload-content');
    prev.src = ev.target.result;
    prev.style.display = 'block';
    cont.style.display = 'none';
    document.getElementById('gen-btn').disabled = false;
    toast('Photo ready! Generate karo 🎨', 'ok');
  };
  reader.readAsDataURL(file);
}

function startGeneration() {
  if (!photoB64) { toast('Pehle photo upload karo!', 'err'); return; }
  <?php if(!loggedIn()): ?>
  window.location.href = '<?= siteUrl('auth/login.php?next=/') ?>&generate=1';
  <?php else: ?>
  document.getElementById('mobile-modal').style.display = 'flex';
  <?php endif; ?>
}

function proceedGeneration() {
  document.getElementById('mobile-modal').style.display = 'none';
  const mobile = document.getElementById('user-mobile').value.replace(/\D/g,'');
  const bal = <?= loggedIn() ? wallet() : 0 ?>;
  if (bal < 9) { window.location.href = '<?= SITE_URL ?>/dashboard/wallet.php?recharge=1'; return; }
  const btn = document.getElementById('gen-btn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spin"></div> AI Page Bana Raha Hai...';
  toast('🤖 Gemini AI kaam kar raha hai...', 'info');

  fetch('<?= SITE_URL ?>/api/generate.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ image_base64: photoB64, mime_type: photoMime, mobile: mobile })
  })
  .then(r => r.json())
  .then(res => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-magic"></i> AI se Digital Page Banao — ₹9';
    if (res.success) {
      window.location.href = '<?= SITE_URL ?>/p/' + res.token;
    } else if (res.error === 'no_balance') {
      window.location.href = '<?= SITE_URL ?>/dashboard/wallet.php?recharge=1';
    } else {
      toast(res.error || 'Kuch gadbad hui. Dobara try karo!', 'err');
    }
  })
  .catch(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-magic"></i> AI se Digital Page Banao — ₹9';
    toast('Network error! Internet check karo.', 'err');
  });
}

function findByMobile() {
  const mob = document.getElementById('mob-find').value.replace(/\D/g,'');
  if (mob.length < 10) { toast('Valid mobile number dalein!', 'err'); return; }
  window.location.href = '<?= SITE_URL ?>/m/' + mob;
}
document.getElementById('mob-find').addEventListener('keypress', (e) => {
  if (e.key === 'Enter') findByMobile();
});

function toast(msg, type='info') {
  const d = document.createElement('div');
  d.className = 'toast ' + type;
  d.textContent = msg;
  document.body.appendChild(d);
  setTimeout(() => d.remove(), 3500);
}
</script>
</body>
</html>
