<?php
require_once '../config.php';
requireAuth();
$user = me();
if ($user['role'] !== 'admin') { header('Location: /dashboard/'); exit; }
$db = db();

$totalUsers   = $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalPages   = $db->query("SELECT COUNT(*) as c FROM pages")->fetch_assoc()['c'];
$totalGens    = $db->query("SELECT COUNT(*) as c FROM generations")->fetch_assoc()['c'];
$totalRev     = $totalGens * 9;
$totalCost    = round($totalGens * 0.10, 2);
$totalProfit  = round($totalRev - $totalCost, 2);
$todayGens    = $db->query("SELECT COUNT(*) as c FROM generations WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
$totalViews   = $db->query("SELECT COALESCE(SUM(views),0) as v FROM pages")->fetch_assoc()['v'];

$dailyR = $db->query("SELECT DATE(created_at) as d, COUNT(*) as c FROM generations WHERE created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY d ORDER BY d");
$days=[]; $counts=[]; $revs=[]; $profits=[];
while($r=$dailyR->fetch_assoc()){ $days[]=date('d M',strtotime($r['d'])); $counts[]=(int)$r['c']; $revs[]=$r['c']*9; $profits[]=round($r['c']*9-$r['c']*0.10,2); }

$usersR = $db->query("SELECT DATE(created_at) as d, COUNT(*) as c FROM users WHERE created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY) GROUP BY d ORDER BY d");
$uDays=[]; $uCounts=[];
while($r=$usersR->fetch_assoc()){ $uDays[]=date('d M',strtotime($r['d'])); $uCounts[]=(int)$r['c']; }

$typeR = $db->query("SELECT business_type,COUNT(*) as c FROM pages GROUP BY business_type ORDER BY c DESC");
$tLabels=[]; $tCounts=[];
while($r=$typeR->fetch_assoc()){ $tLabels[]=$r['business_type']; $tCounts[]=(int)$r['c']; }

$recentPages = $db->query("SELECT p.*,u.name as owner FROM pages p JOIN users u ON p.user_id=u.id ORDER BY p.created_at DESC LIMIT 8");
$topUsers    = $db->query("SELECT u.name,u.email,COUNT(p.id) as pages,SUM(p.views) as views FROM pages p JOIN users u ON p.user_id=u.id GROUP BY p.user_id ORDER BY pages DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin — PosterWall v2</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
[data-theme="dark"]{--bg:#0a0a14;--bg2:#12192b;--card:#1a2540;--border:rgba(255,255,255,.07);--text:#f0f0f0;--muted:#8892a4;--p:#FF6B00;--g:#00d464;--r:#ff5555;--b:#4f8ef7;--shadow:0 8px 32px rgba(0,0,0,.4);}
[data-theme="light"]{--bg:#f0f2f8;--bg2:#fff;--card:#fff;--border:rgba(0,0,0,.07);--text:#1a1a2e;--muted:#6b7280;--p:#FF6B00;--g:#16a34a;--r:#dc2626;--b:#2563eb;--shadow:0 4px 20px rgba(0,0,0,.08);}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);transition:background .3s,color .3s;}
a{text-decoration:none;color:inherit;}
.layout{display:flex;min-height:100vh;}
.sb{width:220px;background:var(--bg2);border-right:1px solid var(--border);position:fixed;inset:0 auto 0 0;display:flex;flex-direction:column;z-index:100;transition:background .3s;}
.sb-logo{padding:18px 16px;font-family:'Baloo 2',cursive;font-size:1.3rem;font-weight:800;border-bottom:1px solid var(--border);}
.sb-logo span{color:var(--p);}
.sb-pill{font-size:.58rem;background:var(--p);color:#fff;padding:2px 6px;border-radius:4px;margin-left:4px;vertical-align:middle;}
.sb-sec{padding:12px 14px 4px;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);}
.sb-link{display:flex;align-items:center;gap:9px;padding:9px 16px;color:var(--muted);font-size:.84rem;border-left:3px solid transparent;transition:all .2s;}
.sb-link:hover,.sb-link.on{color:var(--text);background:rgba(255,107,0,.07);border-left-color:var(--p);}
.sb-link i{width:14px;text-align:center;font-size:.82rem;}
.sb-foot{margin-top:auto;padding:14px 16px;border-top:1px solid var(--border);font-size:.7rem;color:var(--muted);line-height:1.6;}
.main{margin-left:220px;flex:1;}
.tb{height:56px;background:var(--bg2);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 24px;position:sticky;top:0;z-index:50;transition:background .3s;}
.tb-title{font-family:'Baloo 2',cursive;font-size:1.1rem;font-weight:800;}
.tb-r{display:flex;align-items:center;gap:12px;}
.toggle{width:44px;height:24px;background:var(--p);border-radius:12px;border:none;cursor:pointer;position:relative;display:flex;align-items:center;padding:2px;}
.tdot{width:20px;height:20px;background:#fff;border-radius:50%;transition:transform .3s;font-size:10px;display:flex;align-items:center;justify-content:center;}
[data-theme="dark"] .tdot{transform:translateX(20px);}
.av{width:32px;height:32px;border-radius:50%;border:2px solid var(--p);object-fit:cover;}
.con{padding:24px;}
.pb{background:linear-gradient(120deg,#FF6B00,#c94d00);border-radius:16px;padding:24px 28px;margin-bottom:22px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;box-shadow:0 8px 32px rgba(255,107,0,.3);}
.pb h2{font-family:'Baloo 2',cursive;font-size:1.3rem;color:#fff;margin-bottom:3px;}
.pb p{color:rgba(255,255,255,.75);font-size:.8rem;}
.pb-ns{display:flex;gap:24px;flex-wrap:wrap;}
.pb-n .l{font-size:.68rem;color:rgba(255,255,255,.65);text-transform:uppercase;letter-spacing:1px;}
.pb-n .v{font-family:'Baloo 2',cursive;font-size:1.8rem;font-weight:800;color:#fff;}
.sg{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px;margin-bottom:22px;}
.sc{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px;position:relative;overflow:hidden;box-shadow:var(--shadow);}
.sc::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--ca,var(--p));}
.sc.g::before{--ca:var(--g);} .sc.b::before{--ca:var(--b);} .sc.r::before{--ca:var(--r);}
.sc-icon{font-size:1.6rem;margin-bottom:6px;}
.sc-lbl{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;}
.sc-val{font-family:'Baloo 2',cursive;font-size:1.8rem;font-weight:800;}
.sc-sub{font-size:.7rem;color:var(--muted);margin-top:4px;}
.cg{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px;}
.cc{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;box-shadow:var(--shadow);}
.cc.full{grid-column:1/-1;}
.cc-title{font-weight:600;font-size:.88rem;margin-bottom:2px;}
.cc-sub{font-size:.72rem;color:var(--muted);margin-bottom:14px;}
.cw{position:relative;height:220px;}
.tg{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px;}
.tc{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:var(--shadow);}
.tc.full{grid-column:1/-1;}
.tc-hd{padding:16px 18px 10px;display:flex;justify-content:space-between;align-items:center;}
.tc-ttl{font-weight:600;font-size:.88rem;}
.badge{display:inline-flex;padding:3px 8px;border-radius:100px;font-size:.68rem;font-weight:600;}
.bg{background:rgba(0,212,100,.1);color:var(--g);} .br{background:rgba(255,85,85,.1);color:var(--r);} .bb{background:rgba(79,142,247,.1);color:var(--b);} .bo{background:rgba(255,107,0,.1);color:var(--p);}
table{width:100%;border-collapse:collapse;}
th{padding:9px 16px;text-align:left;font-size:.68rem;color:var(--muted);text-transform:uppercase;background:rgba(0,0,0,.04);border-bottom:1px solid var(--border);}
td{padding:10px 16px;font-size:.82rem;border-bottom:1px solid var(--border);}
tr:last-child td{border-bottom:none;}
tr:hover td{background:rgba(255,107,0,.02);}
@media(max-width:900px){.sb{width:52px;}.sb-logo span,.sb-link span,.sb-sec,.sb-foot p{display:none;}.main{margin-left:52px;}.cg,.tg{grid-template-columns:1fr;}.cc.full,.tc.full{grid-column:1;}}
</style>
</head>
<body>
<div class="layout">
<aside class="sb">
  <div class="sb-logo">🦅 <span>Poster<span style="color:var(--p)">Wall</span><span class="sb-pill">v2</span></span></div>
  <div class="sb-sec">Analytics</div>
  <a href="index.php" class="sb-link on"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
  <div class="sb-sec">Manage</div>
  <a href="users.php" class="sb-link"><i class="fas fa-users"></i><span>Users</span></a>
  <a href="pages.php" class="sb-link"><i class="fas fa-file-alt"></i><span>Pages</span></a>
  <a href="wallet.php" class="sb-link"><i class="fas fa-wallet"></i><span>Manage Funds</span></a>
  <div class="sb-sec">Site</div>
  <a href="<?= siteUrl('') ?>" target="_blank" class="sb-link"><i class="fas fa-globe"></i><span>View Site</span></a>
  <a href="<?= siteUrl('dashboard/') ?>" class="sb-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
  <a href="<?= siteUrl('auth/logout.php') ?>" class="sb-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
  <div class="sb-foot"><p><strong style="color:var(--p);">TechEagles</strong><br>Mahakumbrix Innovation<br>PosterWall v2.0</p></div>
</aside>
<div class="main">
  <div class="tb">
    <div class="tb-title">📊 PosterWall v2 Admin</div>
    <div class="tb-r">
      <span style="font-size:.75rem;color:var(--muted);"><?= date('d M Y, h:i A') ?></span>
      <button class="toggle" onclick="toggleTheme()"><div class="tdot" id="tdot">🌙</div></button>
      <img src="<?= htmlspecialchars($user['avatar']??'') ?>" class="av" onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=FF6B00&color=fff'">
    </div>
  </div>
  <div class="con">
    <div style="font-family:'Baloo 2',cursive;font-size:1.4rem;font-weight:800;margin-bottom:2px;">Namaste, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?>! 👋</div>
    <div style="color:var(--muted);font-size:.82rem;margin-bottom:20px;">PosterWall v2.0 — Photo to Digital Page Platform</div>

    <!-- Profit Banner -->
    <div class="pb">
      <div><h2>💰 Total Profit</h2><p>Revenue − Gemini API Cost (₹0.10/gen)</p></div>
      <div class="pb-ns">
        <div class="pb-n"><div class="l">Revenue</div><div class="v">₹<?= number_format($totalRev) ?></div></div>
        <div class="pb-n"><div class="l">API Cost</div><div class="v">₹<?= number_format($totalCost) ?></div></div>
        <div class="pb-n"><div class="l">Net Profit</div><div class="v">₹<?= number_format($totalProfit) ?></div></div>
        <div class="pb-n"><div class="l">Margin</div><div class="v"><?= $totalGens>0?round($totalProfit/max($totalRev,1)*100,1):98.9 ?>%</div></div>
      </div>
    </div>

    <!-- Stat Cards -->
    <div class="sg">
      <div class="sc b"><div class="sc-icon">👥</div><div class="sc-lbl">Users</div><div class="sc-val"><?= number_format($totalUsers) ?></div><div class="sc-sub">Registered</div></div>
      <div class="sc"><div class="sc-icon">📄</div><div class="sc-lbl">Pages Generated</div><div class="sc-val"><?= number_format($totalPages) ?></div><div class="sc-sub">Today: <?= $todayGens ?></div></div>
      <div class="sc g"><div class="sc-icon">💰</div><div class="sc-lbl">Revenue</div><div class="sc-val">₹<?= number_format($totalRev) ?></div><div class="sc-sub">₹9 × <?= $totalGens ?> gens</div></div>
      <div class="sc r"><div class="sc-icon">⚡</div><div class="sc-lbl">API Cost</div><div class="sc-val">₹<?= number_format($totalCost) ?></div><div class="sc-sub">Gemini Vision</div></div>
      <div class="sc"><div class="sc-icon">👁️</div><div class="sc-lbl">Total Page Views</div><div class="sc-val"><?= number_format($totalViews) ?></div><div class="sc-sub">All pages combined</div></div>
    </div>

    <!-- Charts -->
    <div class="cg">
      <div class="cc"><div class="cc-title">Daily Page Generations</div><div class="cc-sub">Last 30 days</div><div class="cw"><canvas id="cGens"></canvas></div></div>
      <div class="cc"><div class="cc-title">Daily Revenue (₹)</div><div class="cc-sub">Last 30 days</div><div class="cw"><canvas id="cRev"></canvas></div></div>
      <div class="cc"><div class="cc-title">Business Type Breakdown</div><div class="cc-sub">All pages by type</div><div class="cw"><canvas id="cTypes"></canvas></div></div>
      <div class="cc"><div class="cc-title">New User Signups</div><div class="cc-sub">Last 30 days</div><div class="cw"><canvas id="cUsers"></canvas></div></div>
      <div class="cc full"><div class="cc-title">📈 Profit Analysis</div><div class="cc-sub">Revenue vs API Cost vs Net Profit</div><div class="cw" style="height:260px;"><canvas id="cProfit"></canvas></div></div>
    </div>

    <!-- Tables -->
    <div class="tg">
      <div class="tc">
        <div class="tc-hd"><div class="tc-ttl">🏆 Top Creators</div><span class="badge bb">Top 5</span></div>
        <table>
          <thead><tr><th>User</th><th>Pages</th><th>Views</th></tr></thead>
          <tbody>
          <?php while($u=$topUsers->fetch_assoc()): ?>
          <tr><td><div style="font-weight:500;"><?= htmlspecialchars($u['name']) ?></div><div style="font-size:.7rem;color:var(--muted);"><?= htmlspecialchars($u['email']) ?></div></td>
          <td><span class="badge bb"><?= $u['pages'] ?></span></td><td style="color:var(--g);font-weight:600;"><?= number_format($u['views']) ?></td></tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <div class="tc">
        <div class="tc-hd"><div class="tc-ttl">📄 Recent Pages</div><span class="badge bo">Last 8</span></div>
        <table>
          <thead><tr><th>Business</th><th>Type</th><th>Views</th></tr></thead>
          <tbody>
          <?php while($pg=$recentPages->fetch_assoc()): ?>
          <tr><td><div style="font-weight:500;font-size:.8rem;"><?= htmlspecialchars($pg['business_name']?:'Unnamed') ?></div><div style="font-size:.68rem;color:var(--muted);"><?= htmlspecialchars($pg['owner']) ?></div></td>
          <td style="color:var(--muted);font-size:.75rem;"><?= $pg['business_type'] ?></td>
          <td style="font-weight:600;color:var(--p);"><?= number_format($pg['views']) ?></td></tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div style="text-align:center;padding:16px 0;color:var(--muted);font-size:.74rem;border-top:1px solid var(--border);">
      © <?= YEAR ?> PosterWall v2.0 — Tech Eagles | Mahakumbrix Innovation | Powered by Gemini AI
    </div>
  </div>
</div>
</div>

<script>
const html=document.documentElement,dot=document.getElementById('tdot');
let theme=localStorage.getItem('pw-admin-theme')||'dark';
function applyTheme(t){html.setAttribute('data-theme',t);dot.textContent=t==='dark'?'🌙':'☀️';localStorage.setItem('pw-admin-theme',t);rebuildCharts();}
function toggleTheme(){theme=theme==='dark'?'light':'dark';applyTheme(theme);}
applyTheme(theme);

const D={
  days:   <?= json_encode($days) ?>,
  counts: <?= json_encode($counts) ?>,
  revs:   <?= json_encode($revs) ?>,
  profits:<?= json_encode($profits) ?>,
  uDays:  <?= json_encode($uDays) ?>,
  uCounts:<?= json_encode($uCounts) ?>,
  tLabels:<?= json_encode($tLabels) ?>,
  tCounts:<?= json_encode($tCounts) ?>,
};

const charts={};
function gc(){const d=html.getAttribute('data-theme')==='dark';return{grid:d?'rgba(255,255,255,.05)':'rgba(0,0,0,.05)',text:d?'#8892a4':'#6b7280',bg:d?'#1a2540':'#fff'};}
function mk(id,cfg){if(charts[id])charts[id].destroy();charts[id]=new Chart(document.getElementById(id).getContext('2d'),cfg);}
function sc(){ const c=gc();return{x:{grid:{color:c.grid},ticks:{color:c.text,font:{size:9},maxRotation:45}},y:{grid:{color:c.grid},ticks:{color:c.text,font:{size:9}},beginAtZero:true}}; }

function rebuildCharts(){
  const c=gc(),s=sc(),no=D.days.length===0;
  mk('cGens',{type:'bar',data:{labels:no?['No data']:D.days,datasets:[{label:'Generations',data:no?[0]:D.counts,backgroundColor:'rgba(255,107,0,.75)',borderColor:'#FF6B00',borderWidth:1,borderRadius:5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:s}});
  mk('cRev',{type:'line',data:{labels:no?['No data']:D.days,datasets:[{label:'₹',data:no?[0]:D.revs,borderColor:'#00d464',backgroundColor:'rgba(0,212,100,.1)',borderWidth:2,fill:true,tension:.4,pointRadius:3}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:s}});
  mk('cTypes',{type:'doughnut',data:{labels:D.tLabels.length?D.tLabels:['No data'],datasets:[{data:D.tCounts.length?D.tCounts:[1],backgroundColor:['#FF6B00','#FFD700','#00d464','#4f8ef7','#7c3aed','#ff5555','#00d4aa','#f97316'],borderWidth:2,borderColor:c.bg}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'right',labels:{color:c.text,font:{size:10},boxWidth:12,padding:8}}}}});
  mk('cUsers',{type:'bar',data:{labels:D.uDays.length?D.uDays:['No data'],datasets:[{label:'Users',data:D.uCounts.length?D.uCounts:[0],backgroundColor:'rgba(124,58,237,.75)',borderColor:'#7c3aed',borderWidth:1,borderRadius:5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:s}});
  mk('cProfit',{type:'line',data:{labels:no?['No data']:D.days,datasets:[
    {label:'Revenue ₹',data:no?[0]:D.revs,borderColor:'#4f8ef7',backgroundColor:'rgba(79,142,247,.08)',borderWidth:2,fill:true,tension:.4,pointRadius:3},
    {label:'Net Profit ₹',data:no?[0]:D.profits,borderColor:'#00d464',backgroundColor:'rgba(0,212,100,.08)',borderWidth:3,fill:true,tension:.4,pointRadius:4},
  ]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:c.text,font:{size:10}}}},scales:s}});
}
Chart.defaults.font.family='Poppins';
window.addEventListener('load',rebuildCharts);
</script>
</body>
</html>
