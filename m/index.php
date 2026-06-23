<?php
require_once '../config.php';

// Get mobile from URL
$mobile = preg_replace('/\D/', '', $_GET['mob'] ?? basename($_SERVER['REQUEST_URI']));
$isSearch = empty($mobile) || strlen($mobile) < 10;
$pages = []; $userName = '';

if (!$isSearch && strlen($mobile) >= 10) {
    $db  = db();
    $mob = $db->real_escape_string($mobile);
    $res = $db->query("SELECT p.*, u.name as owner FROM pages p JOIN users u ON p.user_id=u.id WHERE p.mobile='$mob' AND p.is_active=1 ORDER BY p.created_at DESC");
    while ($r = $res->fetch_assoc()) $pages[] = $r;
    if (!empty($pages)) $userName = $pages[0]['owner'];
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $mobile ? "📱 $mobile — PosterWall" : "Mobile se Dhundo — PosterWall" ?></title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{--p:#FF6B00;--bg:#0a0a14;--bg2:#12192b;--card:#1a2540;--text:#f0f0f0;--muted:#8892a4;--border:rgba(255,255,255,0.07);}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);min-height:100svh;}
a{text-decoration:none;color:inherit;}
nav{background:rgba(10,10,20,.97);border-bottom:1px solid var(--border);padding:0 16px;position:sticky;top:0;z-index:100;}
.nav-in{max-width:700px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:54px;}
.logo{font-family:'Baloo 2',cursive;font-size:1.3rem;font-weight:800;}
.logo span{color:var(--p);}
.hero{background:radial-gradient(ellipse at center,rgba(255,107,0,.12) 0%,transparent 65%);padding:56px 20px 40px;text-align:center;}
.hero h1{font-family:'Baloo 2',cursive;font-size:1.9rem;font-weight:800;margin-bottom:8px;}
.hero p{color:var(--muted);font-size:.9rem;margin-bottom:28px;}
.search-box{max-width:440px;margin:0 auto;display:flex;gap:10px;}
.search-inp{flex:1;padding:13px 16px;border-radius:11px;background:var(--card);border:1px solid var(--border);color:var(--text);font-size:1rem;font-family:'Poppins',sans-serif;}
.search-inp:focus{outline:none;border-color:var(--p);}
.search-btn{padding:13px 20px;border-radius:11px;background:var(--p);color:#fff;border:none;cursor:pointer;font-size:1rem;font-weight:600;}
.con{max-width:700px;margin:0 auto;padding:28px 16px 80px;}

/* Result header */
.result-hd{background:var(--card);border:1px solid rgba(255,107,0,.2);border-radius:14px;padding:20px;margin-bottom:24px;display:flex;align-items:center;gap:14px;}
.result-av{width:50px;height:50px;border-radius:50%;background:var(--p);display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;flex-shrink:0;}
.result-name{font-family:'Baloo 2',cursive;font-size:1.2rem;font-weight:800;}
.result-mob{color:var(--muted);font-size:.82rem;}

/* Page cards */
.pages-grid{display:grid;grid-template-columns:1fr;gap:14px;}
@media(min-width:500px){.pages-grid{grid-template-columns:1fr 1fr;}}
.page-card{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:all .25s;display:block;cursor:pointer;}
.page-card:hover{border-color:var(--p);transform:translateY(-3px);box-shadow:0 8px 24px rgba(255,107,0,.2);}
.page-thumb{height:130px;background:linear-gradient(135deg,var(--bg2),var(--card));display:flex;align-items:center;justify-content:center;font-size:3.5rem;position:relative;}
.page-views{position:absolute;top:10px;right:10px;background:rgba(0,0,0,.6);color:#fff;padding:3px 9px;border-radius:100px;font-size:.7rem;display:flex;align-items:center;gap:4px;}
.page-info{padding:14px;}
.page-name{font-weight:700;font-size:.95rem;margin-bottom:4px;}
.page-type{color:var(--muted);font-size:.75rem;margin-bottom:8px;text-transform:capitalize;}
.page-url{font-size:.72rem;color:var(--p);display:flex;align-items:center;gap:4px;}

/* QR */
.qr-section{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;text-align:center;margin-bottom:14px;}
.qr-section img{width:140px;height:140px;border-radius:10px;border:2px solid var(--p);margin:0 auto 10px;display:block;}

/* Empty */
.empty{text-align:center;padding:60px 20px;color:var(--muted);}
.empty-icon{font-size:3.5rem;margin-bottom:16px;}

/* CTA */
.cta-box{background:linear-gradient(135deg,rgba(255,107,0,.15),rgba(255,215,0,.06));border:1px solid rgba(255,107,0,.25);border-radius:16px;padding:28px 20px;text-align:center;margin-top:24px;}
.cta-box h3{font-family:'Baloo 2',cursive;font-size:1.3rem;font-weight:800;margin-bottom:8px;}
.cta-box p{color:var(--muted);font-size:.875rem;margin-bottom:18px;}
.cta-btn{display:inline-block;padding:13px 28px;background:var(--p);color:#fff;border-radius:11px;font-weight:700;font-size:.95rem;box-shadow:0 4px 18px rgba(255,107,0,.35);}

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
    <a href="<?= siteUrl('auth/login.php') ?>" style="padding:8px 16px;border-radius:8px;background:var(--p);color:#fff;font-weight:600;font-size:.82rem;">Apna Page Banao ₹9</a>
  </div>
</nav>

<div class="hero">
  <div style="font-size:2.8rem;margin-bottom:12px;">📱</div>
  <h1>Mobile se Page Dhundo</h1>
  <p>Mobile number dalein — us number ke saare digital pages dikhenge</p>
  <div class="search-box">
    <input type="tel" class="search-inp" id="mob-inp" placeholder="Mobile number dalein..." value="<?= htmlspecialchars($mobile) ?>" maxlength="15" onkeypress="if(event.key==='Enter')findMob()">
    <button class="search-btn" onclick="findMob()"><i class="fas fa-search"></i></button>
  </div>
</div>

<div class="con">

<?php if (!$isSearch && strlen($mobile) >= 10): ?>

  <?php if (!empty($pages)): ?>

    <!-- User Found -->
    <div class="result-hd">
      <div class="result-av"><?= strtoupper(substr($userName,0,1)) ?></div>
      <div>
        <div class="result-name"><?= htmlspecialchars($userName) ?></div>
        <div class="result-mob">📱 <?= htmlspecialchars($mobile) ?> &nbsp;•&nbsp; <?= count($pages) ?> page<?= count($pages)>1?'s':'' ?></div>
      </div>
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

    <!-- QR for first page -->
    <?php $firstPage = $pages[0]; $firstUrl = SITE_URL.'/p/'.$firstPage['token']; ?>
    <div class="qr-section" style="margin-top:20px;">
      <div style="font-family:'Baloo 2',cursive;font-size:1rem;font-weight:800;color:var(--p);margin-bottom:6px;">📲 QR Code</div>
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($firstUrl) ?>&color=FF6B00&bgcolor=0a0a14" alt="QR">
      <p style="color:var(--muted);font-size:.78rem;margin-bottom:10px;">Scan karo — page directly khul jaayega</p>
      <a href="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=<?= urlencode($firstUrl) ?>&color=FF6B00&bgcolor=ffffff" download="posterwall-qr.png" style="display:inline-block;padding:8px 18px;background:var(--p);color:#fff;border-radius:8px;font-weight:600;font-size:.82rem;"><i class="fas fa-download"></i> QR Download</a>
    </div>

  <?php else: ?>
    <div class="empty">
      <div class="empty-icon">😕</div>
      <h3 style="margin-bottom:8px;font-family:'Baloo 2',cursive;">Koi Page Nahi Mila</h3>
      <p style="margin-bottom:6px;"><strong><?= htmlspecialchars($mobile) ?></strong> pe koi page register nahi hai.</p>
      <p style="font-size:.82rem;">Kya aapne PosterWall pe apna page banaya hai?</p>
    </div>
  <?php endif; ?>

<?php elseif (!$isSearch): ?>
  <div class="empty">
    <div class="empty-icon">📱</div>
    <p>Valid 10-digit mobile number dalein.</p>
  </div>
<?php endif; ?>

  <div class="cta-box">
    <div style="font-size:2rem;margin-bottom:10px;">🦅</div>
    <h3>Apna Digital Page Banao!</h3>
    <p>Sirf ₹9 mein apni dukaan ya business ka beautiful page banao — photo lo, AI page banata hai!</p>
    <a href="<?= siteUrl('') ?>" class="cta-btn">Abhi Shuru Karo →</a>
  </div>

</div>

<script>
function findMob(){
  const m=document.getElementById('mob-inp').value.replace(/\D/g,'');
  if(m.length<10){alert('Valid 10-digit mobile dalein!');return;}
  window.location.href='<?= SITE_URL ?>/m/'+m;
}
</script>
</body>
</html>
