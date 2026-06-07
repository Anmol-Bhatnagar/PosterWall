<?php
require_once '../config.php';

// Support search by mobile OR business name
// URL patterns: /m/9876543210  OR  /m/?name=Sharma+Dhaba  OR  /m/?mob=9876543210
$rawPath   = basename($_SERVER['REQUEST_URI']);
$queryName = trim($_GET['name'] ?? '');
$queryMob  = preg_replace('/\D/', '', $_GET['mob'] ?? (ctype_digit(str_replace(['+','-',' '], '', $rawPath)) ? $rawPath : ''));

$mobile    = preg_replace('/\D/', '', $queryMob ?: $rawPath);
$searchName = $queryName;

// Determine mode
$isMobileSearch = strlen($mobile) >= 10;
$isNameSearch   = !$isMobileSearch && strlen($searchName) >= 2;
$isSearch       = !$isMobileSearch && !$isNameSearch;

$pages = []; $userName = '';

$db = db();
if ($isMobileSearch) {
    $mob = $db->real_escape_string($mobile);
    $res = $db->query("SELECT p.*, u.name as owner FROM pages p JOIN users u ON p.user_id=u.id WHERE p.mobile='$mob' AND p.is_active=1 ORDER BY p.created_at DESC");
    while ($r = $res->fetch_assoc()) $pages[] = $r;
    if (!empty($pages)) $userName = $pages[0]['owner'];
} elseif ($isNameSearch) {
    $sn  = $db->real_escape_string($searchName);
    $res = $db->query("SELECT p.*, u.name as owner FROM pages p JOIN users u ON p.user_id=u.id WHERE p.business_name LIKE '%$sn%' AND p.is_active=1 ORDER BY p.views DESC, p.created_at DESC LIMIT 20");
    while ($r = $res->fetch_assoc()) $pages[] = $r;
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $isMobileSearch ? "📱 $mobile — PosterWall" : ($isNameSearch ? "🔍 ".htmlspecialchars($searchName)." — PosterWall" : "Business Dhundo — PosterWall") ?></title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--p:#FF6B00;--bg:#ffffff;--bg2:#f7f9fc;--card:#ffffff;--text:#1a1a2e;--muted:#6c7b94;--border:rgba(0,0,0,0.08);}
[data-theme="dark"]{--bg:#0a0e27;--bg2:#111d3a;--card:#1a2d4f;--text:#f0f2f5;--muted:#8892a4;--border:rgba(255,255,255,0.08);}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);min-height:100svh;transition:background .3s ease,color .3s ease;}
a{text-decoration:none;color:inherit;}
nav{background:rgba(255,255,255,.85);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);border-bottom:1px solid var(--border);padding:0 16px;position:sticky;top:0;z-index:100;}
[data-theme="dark"] nav{background:rgba(10,14,39,.88);}
.nav-in{max-width:700px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:56px;}
.logo{font-family:'Baloo 2',cursive;font-size:1.35rem;font-weight:800;background:linear-gradient(135deg,var(--p),#FFD700);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.logo span{-webkit-text-fill-color:unset;}
.hero{background:radial-gradient(ellipse at center,rgba(255,107,0,.12) 0%,transparent 65%);padding:60px 20px 44px;text-align:center;}
.hero h1{font-family:'Baloo 2',cursive;font-size:2rem;font-weight:800;margin-bottom:8px;}
.hero p{color:var(--muted);font-size:.92rem;margin-bottom:32px;line-height:1.5;}
.search-box{max-width:460px;margin:0 auto;display:flex;gap:10px;}
.search-inp{flex:1;padding:14px 16px;border-radius:12px;background:rgba(255,255,255,.5);backdrop-filter:blur(10px);border:1.5px solid var(--border);color:var(--text);font-size:1rem;font-family:'Poppins',sans-serif;transition:all .3s;}
[data-theme="dark"] .search-inp{background:rgba(26,45,79,.3);}
.search-inp:focus{outline:none;border-color:var(--p);box-shadow:0 4px 12px rgba(255,107,0,.1);}
.search-btn{padding:14px 22px;border-radius:12px;background:linear-gradient(135deg,var(--p),#e05a00);color:#fff;border:none;cursor:pointer;font-size:1rem;font-weight:600;box-shadow:0 6px 20px rgba(255,107,0,.2);transition:all .3s;}
.search-btn:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(255,107,0,.3);}
.con{max-width:700px;margin:0 auto;padding:32px 16px 80px;}

/* Result header */
.result-hd{background:rgba(255,255,255,.5);backdrop-filter:blur(10px);border:1.5px solid var(--border);border-radius:15px;padding:22px;margin-bottom:28px;display:flex;align-items:center;gap:16px;}
[data-theme="dark"] .result-hd{background:rgba(26,45,79,.3);}
.result-av{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--p),#e05a00);display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:800;flex-shrink:0;color:#fff;}
.result-name{font-family:'Baloo 2',cursive;font-size:1.25rem;font-weight:800;}
.result-mob{color:var(--muted);font-size:.83rem;}

/* Page cards */
.pages-grid{display:grid;grid-template-columns:1fr;gap:16px;}
@media(min-width:500px){.pages-grid{grid-template-columns:1fr 1fr;}}
.page-card{background:rgba(255,255,255,.5);backdrop-filter:blur(10px);border:1.5px solid var(--border);border-radius:15px;overflow:hidden;transition:all .3s;display:block;cursor:pointer;}
[data-theme="dark"] .page-card{background:rgba(26,45,79,.3);}
.page-card:hover{border-color:var(--p);transform:translateY(-4px);box-shadow:0 12px 32px rgba(255,107,0,.15);}
.page-thumb{height:140px;background:linear-gradient(135deg,var(--bg2),rgba(255,107,0,.08));display:flex;align-items:center;justify-content:center;font-size:3.8rem;position:relative;}
.page-views{position:absolute;top:10px;right:10px;background:rgba(255,107,0,.9);color:#fff;padding:4px 10px;border-radius:100px;font-size:.7rem;display:flex;align-items:center;gap:4px;font-weight:600;}
.page-info{padding:16px;}
.page-name{font-weight:700;font-size:.98rem;margin-bottom:4px;color:var(--text);}
.page-type{color:var(--muted);font-size:.76rem;margin-bottom:10px;text-transform:capitalize;}
.page-url{font-size:.73rem;color:var(--p);display:flex;align-items:center;gap:4px;font-weight:600;}

/* QR */
.qr-section{background:rgba(255,255,255,.5);backdrop-filter:blur(10px);border:1.5px solid var(--border);border-radius:15px;padding:24px;text-align:center;margin-bottom:16px;}
[data-theme="dark"] .qr-section{background:rgba(26,45,79,.3);}
.qr-section img{width:150px;height:150px;border-radius:12px;border:2.5px solid var(--p);margin:0 auto 12px;display:block;}

/* Empty */
.empty{text-align:center;padding:60px 20px;color:var(--muted);}
.empty-icon{font-size:3.8rem;margin-bottom:16px;}

/* CTA */
.cta-box{background:linear-gradient(135deg,rgba(255,107,0,.12),rgba(255,215,0,.08));backdrop-filter:blur(10px);border:1.5px solid rgba(255,107,0,.25);border-radius:16px;padding:32px 20px;text-align:center;margin-top:28px;}
.cta-box h3{font-family:'Baloo 2',cursive;font-size:1.35rem;font-weight:800;margin-bottom:8px;color:var(--text);}
.cta-box p{color:var(--muted);font-size:.89rem;margin-bottom:20px;line-height:1.6;}
.cta-btn{display:inline-block;padding:14px 32px;background:linear-gradient(135deg,var(--p),#e05a00);color:#fff;border-radius:12px;font-weight:700;font-size:.96rem;box-shadow:0 6px 20px rgba(255,107,0,.3);transition:all .3s;}
.cta-btn:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(255,107,0,.4);}

.theme-toggle{position:fixed;top:72px;right:16px;width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,.5);backdrop-filter:blur(10px);border:1.5px solid var(--border);color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:.95rem;transition:all .3s;z-index:50;}
[data-theme="dark"] .theme-toggle{background:rgba(26,45,79,.5);}
.theme-toggle:hover{border-color:var(--p);background:rgba(255,107,0,.1);}

/* Type icons */
<?php
$typeIcons=['restaurant'=>'🍽️','shop'=>'🛍️','clinic'=>'🏥','professional'=>'💼','salon'=>'💇','gym'=>'🏋️','school'=>'🏫','repair'=>'🔧','event'=>'🎉','general'=>'🦅'];
?>
</style>
</head>
<body>

<nav>
  <div class="nav-in">
    <a href="<?= siteUrl('') ?>" class="logo">🦅 Poster<span>Wall</span></a>
    <div style="display:flex;gap:10px;align-items:center;">
      <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">🌙</button>
      <a href="<?= siteUrl('auth/login.php') ?>" style="padding:9px 16px;border-radius:10px;background:linear-gradient(135deg,var(--p),#e05a00);color:#fff;font-weight:600;font-size:.83rem;box-shadow:0 4px 12px rgba(255,107,0,.2);">Apna Page Banao ₹9</a>
    </div>
  </div>
</nav>

<div class="hero">
  <div style="font-size:2.8rem;margin-bottom:12px;">🔍</div>
  <h1>Business Dhundo</h1>
  <p>Mobile number <strong>ya</strong> business name dalein — digital page dikhega</p>
  <div class="search-box" style="flex-direction:column;gap:8px;">
    <div style="display:flex;gap:10px;width:100%;max-width:460px;margin:0 auto;">
      <input type="tel" class="search-inp" id="mob-inp" placeholder="📱 Mobile number..." value="<?= htmlspecialchars($mobile) ?>" maxlength="15" onkeypress="if(event.key==='Enter')doSearch()">
      <button class="search-btn" onclick="doSearch()"><i class="fas fa-search"></i></button>
    </div>
    <div style="color:var(--muted);font-size:.78rem;text-align:center;">— ya —</div>
    <div style="display:flex;gap:10px;width:100%;max-width:460px;margin:0 auto;">
      <input type="text" class="search-inp" id="name-inp" placeholder="🏪 Business name..." value="<?= htmlspecialchars($searchName) ?>" maxlength="80" onkeypress="if(event.key==='Enter')doSearch()">
    </div>
  </div>
</div>

<div class="con">

<?php if ($isMobileSearch || $isNameSearch): ?>

  <?php if (!empty($pages)): ?>

    <!-- Result Header -->
    <div class="result-hd">
      <?php if ($isMobileSearch): ?>
        <div class="result-av"><?= strtoupper(substr($userName ?: 'B',0,1)) ?></div>
        <div>
          <div class="result-name"><?= htmlspecialchars($userName) ?></div>
          <div class="result-mob">📱 <?= htmlspecialchars($mobile) ?> &nbsp;•&nbsp; <?= count($pages) ?> page<?= count($pages)>1?'s':'' ?></div>
        </div>
      <?php else: ?>
        <div class="result-av" style="font-size:1.4rem;">🔍</div>
        <div>
          <div class="result-name">"<?= htmlspecialchars($searchName) ?>"</div>
          <div class="result-mob"><?= count($pages) ?> business<?= count($pages)>1?'es':'' ?> mila — naam se search</div>
        </div>
      <?php endif; ?>
    </div>

    <!-- Pages Grid -->
    <div class="pages-grid">

      <?php foreach($pages as $pg):
        $icon = $typeIcons[$pg['business_type']] ?? '🦅';
        $url  = SITE_URL . '/p/' . $pg['token'];
        $qr   = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.urlencode($url).'&color=FF6B00&bgcolor=ffffff';
      ?>
      <a href="<?= $url ?>" class="page-card">
        <div class="page-thumb">
          <span><?= $icon ?></span>
          <div class="page-views"><i class="fas fa-eye"></i><?= number_format($pg['views']) ?></div>
        </div>
        <div class="page-info">
          <div class="page-name"><?= htmlspecialchars($pg['business_name'] ?: 'My Business') ?></div>
          <div class="page-type"><?= $pg['business_type'] ?></div>
          <div class="page-url"><i class="fas fa-link"></i><?= SITE_URL . $url ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- QR for first page (mobile search only) -->
    <?php if ($isMobileSearch): ?>
      <?php $firstPage = $pages[0]; $firstUrl = SITE_URL.'/p/'.$firstPage['token']; ?>
      <div class="qr-section" style="margin-top:20px;">
        <div style="font-family:'Baloo 2',cursive;font-size:1rem;font-weight:800;color:var(--p);margin-bottom:6px;">📲 QR Code</div>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($firstUrl) ?>&color=FF6B00&bgcolor=0a0a14" alt="QR">
        <p style="color:var(--muted);font-size:.78rem;margin-bottom:10px;">Scan karo — page directly khul jaayega</p>
        <a href="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=<?= urlencode($firstUrl) ?>&color=FF6B00&bgcolor=ffffff" download="posterwall-qr.png" style="display:inline-block;padding:8px 18px;background:var(--p);color:#fff;border-radius:8px;font-weight:600;font-size:.82rem;"><i class="fas fa-download"></i> QR Download</a>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <div class="empty">
      <div class="empty-icon">😕</div>
      <h3 style="margin-bottom:8px;font-family:'Baloo 2',cursive;">Koi Page Nahi Mila</h3>
      <?php if ($isMobileSearch): ?>
        <p style="margin-bottom:6px;"><strong><?= htmlspecialchars($mobile) ?></strong> pe koi page register nahi hai.</p>
        <p style="font-size:.82rem;">Naam se bhi search try karein.</p>
      <?php else: ?>
        <p style="margin-bottom:6px;">"<strong><?= htmlspecialchars($searchName) ?></strong>" naam ka koi business nahi mila.</p>
        <p style="font-size:.82rem;">Alag spelling ya mobile number se try karein.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>

  <div class="cta-box">
    <div style="font-size:2rem;margin-bottom:10px;">🦅</div>
    <h3>Apna Digital Page Banao!</h3>
    <p>Sirf ₹9 mein apni dukaan ya business ka beautiful page banao — photo lo, AI page banata hai!</p>
    <a href="<?= siteUrl('') ?>" class="cta-btn">Abhi Shuru Karo →</a>
  </div>

</div>

<script>
function doSearch(){
  const mob  = document.getElementById('mob-inp').value.replace(/\D/g,'');
  const name = (document.getElementById('name-inp')?.value || '').trim();
  if(mob.length >= 10){
    window.location.href='<?= SITE_URL ?>/m/'+mob;
  } else if(name.length >= 2){
    window.location.href='<?= SITE_URL ?>/m/?name='+encodeURIComponent(name);
  } else {
    alert('Mobile number (10 digit) ya business name (2+ letters) dalein!');
  }
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
<footer style="text-align:center;padding:16px 20px 20px;font-size:.7rem;color:var(--muted);border-top:1px solid var(--border);margin-top:8px;">
  <strong style="color:var(--p);">A Product of TechEagles</strong> &nbsp;|&nbsp; Under Mahakumbrix Innovation
</footer>
</body>
</html>
