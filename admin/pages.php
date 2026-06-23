<?php
require_once '../config.php';
requireAuth();
$user = me();
if ($user['role'] !== 'admin') { header('Location: /dashboard/'); exit; }
$db = db();

$msg = ''; $msgType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pid    = (int)($_POST['pid'] ?? 0);

    if ($action === 'delete' && $pid) {
        $db->query("DELETE FROM pages WHERE id=$pid");
        $msg = '✅ Page deleted successfully.'; $msgType = 'ok';
    } elseif ($action === 'toggle_active' && $pid) {
        $p = $db->query("SELECT is_active FROM pages WHERE id=$pid")->fetch_assoc();
        if ($p) {
            $newStatus = $p['is_active'] ? 0 : 1;
            $db->query("UPDATE pages SET is_active=$newStatus WHERE id=$pid");
            $msg = '✅ Page ' . ($newStatus ? 'activated' : 'deactivated') . '.'; $msgType = 'ok';
        }
    }
}

// Filters
$search     = trim($_GET['q'] ?? '');
$typeFilter = $_GET['type'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where = '1=1';
if ($search) {
    $s = $db->real_escape_string($search);
    $where .= " AND (p.business_name LIKE '%$s%' OR p.token LIKE '%$s%' OR u.name LIKE '%$s%' OR u.email LIKE '%$s%' OR p.mobile LIKE '%$s%')";
}
if ($typeFilter) {
    $t = $db->real_escape_string($typeFilter);
    $where .= " AND p.business_type='$t'";
}
if ($statusFilter !== '') {
    $st = (int)$statusFilter;
    $where .= " AND p.is_active=$st";
}

$total      = $db->query("SELECT COUNT(*) as c FROM pages p JOIN users u ON p.user_id=u.id WHERE $where")->fetch_assoc()['c'];
$totalPages = ceil($total / $perPage);

$pages = $db->query("
    SELECT p.*, u.name as owner_name, u.email as owner_email
    FROM pages p
    JOIN users u ON p.user_id = u.id
    WHERE $where
    ORDER BY p.created_at DESC
    LIMIT $perPage OFFSET $offset
");

// Stats
$totalPagesCount  = $db->query("SELECT COUNT(*) as c FROM pages")->fetch_assoc()['c'];
$activePagesCount = $db->query("SELECT COUNT(*) as c FROM pages WHERE is_active=1")->fetch_assoc()['c'];
$totalViews       = $db->query("SELECT COALESCE(SUM(views),0) as v FROM pages")->fetch_assoc()['v'];
$newToday         = $db->query("SELECT COUNT(*) as c FROM pages WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];

// Business types for filter
$types = $db->query("SELECT DISTINCT business_type FROM pages WHERE business_type IS NOT NULL ORDER BY business_type");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Pages — PosterWall Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
[data-theme="dark"]{--bg:#0a0a14;--bg2:#12192b;--card:#1a2540;--border:rgba(255,255,255,.07);--text:#f0f0f0;--muted:#8892a4;--p:#FF6B00;--g:#00d464;--r:#ff5555;--b:#4f8ef7;--shadow:0 8px 32px rgba(0,0,0,.4);}
[data-theme="light"]{--bg:#f0f2f8;--bg2:#fff;--card:#fff;--border:rgba(0,0,0,.07);--text:#1a1a2e;--muted:#6b7280;--p:#FF6B00;--g:#16a34a;--r:#dc2626;--b:#2563eb;--shadow:0 4px 20px rgba(0,0,0,.08);}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);transition:background .3s,color .3s;}
a{text-decoration:none;color:inherit;}
.layout{display:flex;min-height:100vh;}
.sb{width:220px;background:var(--bg2);border-right:1px solid var(--border);position:fixed;inset:0 auto 0 0;display:flex;flex-direction:column;z-index:100;}
.sb-logo{padding:18px 16px;font-family:'Baloo 2',cursive;font-size:1.3rem;font-weight:800;border-bottom:1px solid var(--border);}
.sb-logo span{color:var(--p);}
.sb-pill{font-size:.58rem;background:var(--p);color:#fff;padding:2px 6px;border-radius:4px;margin-left:4px;vertical-align:middle;}
.sb-sec{padding:12px 14px 4px;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:1.5px;color:var(--muted);}
.sb-link{display:flex;align-items:center;gap:9px;padding:9px 16px;color:var(--muted);font-size:.84rem;border-left:3px solid transparent;transition:all .2s;}
.sb-link:hover,.sb-link.on{color:var(--text);background:rgba(255,107,0,.07);border-left-color:var(--p);}
.sb-link i{width:14px;text-align:center;font-size:.82rem;}
.sb-foot{margin-top:auto;padding:14px 16px;border-top:1px solid var(--border);font-size:.7rem;color:var(--muted);line-height:1.6;}
.main{margin-left:220px;flex:1;}
.tb{height:56px;background:var(--bg2);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 24px;position:sticky;top:0;z-index:50;}
.tb-title{font-family:'Baloo 2',cursive;font-size:1.1rem;font-weight:800;}
.toggle{width:44px;height:24px;background:var(--p);border-radius:12px;border:none;cursor:pointer;position:relative;display:flex;align-items:center;padding:2px;}
.tdot{width:20px;height:20px;background:#fff;border-radius:50%;transition:transform .3s;font-size:10px;display:flex;align-items:center;justify-content:center;}
[data-theme="dark"] .tdot{transform:translateX(20px);}
.av{width:32px;height:32px;border-radius:50%;border:2px solid var(--p);object-fit:cover;}
.con{padding:24px;}
.msg{padding:12px 14px;border-radius:10px;font-size:.85rem;margin-bottom:18px;}
.msg.ok{background:rgba(0,212,100,.1);border:1px solid rgba(0,212,100,.3);color:var(--g);}
.msg.err{background:rgba(255,85,85,.1);border:1px solid rgba(255,85,85,.3);color:var(--r);}
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
.sc{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px;position:relative;overflow:hidden;box-shadow:var(--shadow);}
.sc::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--ca,var(--p));}
.sc.g::before{--ca:var(--g);} .sc.b::before{--ca:var(--b);} .sc.r::before{--ca:var(--r);}
.sc-lbl{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;}
.sc-val{font-family:'Baloo 2',cursive;font-size:1.8rem;font-weight:800;}
.filters{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center;}
.finp{padding:9px 14px;border-radius:10px;background:var(--card);border:1px solid var(--border);color:var(--text);font-family:'Poppins',sans-serif;font-size:.85rem;}
.finp:focus{outline:none;border-color:var(--p);}
.fbtn{padding:9px 18px;border-radius:10px;background:var(--p);color:#fff;font-weight:600;font-size:.85rem;border:none;cursor:pointer;}
.tc{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:var(--shadow);}
.tc-hd{padding:14px 18px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border);}
.tc-ttl{font-weight:600;font-size:.88rem;}
table{width:100%;border-collapse:collapse;}
th{padding:9px 14px;text-align:left;font-size:.67rem;color:var(--muted);text-transform:uppercase;background:rgba(0,0,0,.04);border-bottom:1px solid var(--border);}
td{padding:9px 14px;font-size:.81rem;border-bottom:1px solid var(--border);vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:rgba(255,107,0,.02);}
.badge{display:inline-flex;padding:3px 8px;border-radius:100px;font-size:.68rem;font-weight:600;}
.bg{background:rgba(0,212,100,.1);color:var(--g);}
.br{background:rgba(255,85,85,.1);color:var(--r);}
.bo{background:rgba(255,107,0,.1);color:var(--p);}
.bb{background:rgba(79,142,247,.1);color:var(--b);}
.act-btn{padding:5px 10px;border-radius:7px;font-size:.72rem;font-weight:600;border:none;cursor:pointer;transition:all .2s;}
.act-btn.danger{background:rgba(255,85,85,.12);color:var(--r);}
.act-btn.danger:hover{background:var(--r);color:#fff;}
.act-btn.warn{background:rgba(255,107,0,.12);color:var(--p);}
.act-btn.warn:hover{background:var(--p);color:#fff;}
.act-btn.info{background:rgba(79,142,247,.12);color:var(--b);}
.act-btn.info:hover{background:var(--b);color:#fff;}
.thumb{width:40px;height:40px;border-radius:8px;object-fit:cover;border:1.5px solid var(--border);background:var(--bg2);}
.pagination{display:flex;gap:6px;justify-content:center;padding:16px;flex-wrap:wrap;}
.pag-btn{padding:6px 12px;border-radius:8px;font-size:.8rem;border:1px solid var(--border);background:var(--card);color:var(--text);cursor:pointer;text-decoration:none;}
.pag-btn.on{background:var(--p);color:#fff;border-color:var(--p);}
.pag-btn:hover:not(.on){background:var(--bg2);}
.no-data{text-align:center;padding:36px;color:var(--muted);font-size:.85rem;}
.site-footer{text-align:center;padding:16px 20px;font-size:.72rem;color:var(--muted);border-top:1px solid var(--border);margin-top:8px;}
.site-footer strong{color:var(--p);}
@media(max-width:900px){.sb{width:52px;}.sb-logo span,.sb-link span,.sb-sec,.sb-foot p{display:none;}.main{margin-left:52px;}.stats{grid-template-columns:1fr 1fr;}}
</style>
</head>
<body>
<div class="layout">
  <nav class="sb">
    <div class="sb-logo">🦅 <span>Poster<span style="color:var(--p)">Wall</span><span class="sb-pill">v2</span></span></div>
    <div class="sb-sec">Analytics</div>
    <a href="index.php" class="sb-link"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
    <div class="sb-sec">Manage</div>
    <a href="users.php" class="sb-link"><i class="fas fa-users"></i><span>Users</span></a>
    <a href="pages.php" class="sb-link on"><i class="fas fa-file-alt"></i><span>Pages</span></a>
    <a href="wallet.php" class="sb-link"><i class="fas fa-wallet"></i><span>Manage Funds</span></a>
    <div class="sb-sec">Site</div>
    <a href="<?= siteUrl('') ?>" target="_blank" class="sb-link"><i class="fas fa-globe"></i><span>View Site</span></a>
    <a href="<?= siteUrl('dashboard/') ?>" class="sb-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
    <a href="<?= siteUrl('auth/logout.php') ?>" class="sb-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    <div class="sb-foot"><p><strong style="color:var(--p);">TechEagles</strong><br>Mahakumbrix Innovation<br>PosterWall v2.0</p></div>
  </nav>

  <div class="main">
    <div class="tb">
      <div class="tb-title">📄 Pages</div>
      <div style="display:flex;align-items:center;gap:12px;">
        <button class="toggle" onclick="toggleTheme()"><div class="tdot">🌙</div></button>
        <img src="<?= htmlspecialchars($user['avatar'] ?? '') ?>" class="av" alt="" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=FF6B00&color=fff'">
      </div>
    </div>

    <div class="con">
      <?php if ($msg): ?><div class="msg <?= $msgType ?>"><?= $msg ?></div><?php endif; ?>

      <div class="stats">
        <div class="sc"><div class="sc-lbl">Total Pages</div><div class="sc-val"><?= number_format($totalPagesCount) ?></div></div>
        <div class="sc g"><div class="sc-lbl">Active</div><div class="sc-val"><?= number_format($activePagesCount) ?></div></div>
        <div class="sc b"><div class="sc-lbl">Total Views</div><div class="sc-val"><?= number_format($totalViews) ?></div></div>
        <div class="sc r"><div class="sc-lbl">New Today</div><div class="sc-val"><?= $newToday ?></div></div>
      </div>

      <!-- Filters -->
      <form method="GET" class="filters">
        <input type="text" name="q" class="finp" placeholder="🔍 Search name, token, owner..." value="<?= htmlspecialchars($search) ?>" style="flex:1;min-width:200px;">
        <select name="type" class="finp">
          <option value="">All Types</option>
          <?php while ($t = $types->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($t['business_type']) ?>" <?= $typeFilter===$t['business_type']?'selected':'' ?>>
            <?= htmlspecialchars(ucfirst($t['business_type'])) ?>
          </option>
          <?php endwhile; ?>
        </select>
        <select name="status" class="finp">
          <option value="">All Status</option>
          <option value="1" <?= $statusFilter==='1'?'selected':'' ?>>Active</option>
          <option value="0" <?= $statusFilter==='0'?'selected':'' ?>>Inactive</option>
        </select>
        <button type="submit" class="fbtn"><i class="fas fa-search"></i> Filter</button>
        <?php if ($search || $typeFilter || $statusFilter !== ''): ?>
        <a href="pages.php" class="fbtn" style="background:var(--bg2);color:var(--muted);">✕ Clear</a>
        <?php endif; ?>
      </form>

      <div class="tc">
        <div class="tc-hd">
          <div class="tc-ttl">All Pages</div>
          <span style="font-size:.75rem;color:var(--muted);"><?= $total ?> result<?= $total!=1?'s':'' ?></span>
        </div>
        <?php if ($pages && $pages->num_rows > 0): ?>
        <div style="overflow-x:auto;">
        <table>
          <thead>
            <tr>
              <th>Preview</th>
              <th>Business</th>
              <th>Token</th>
              <th>Owner</th>
              <th>Type</th>
              <th>Views</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($p = $pages->fetch_assoc()): ?>
            <tr>
              <td>
                <?php if ($p['photo_url']): ?>
                <img src="<?= htmlspecialchars($p['photo_url']) ?>" class="thumb" alt="" onerror="this.style.display='none'">
                <?php else: ?>
                <div class="thumb" style="display:flex;align-items:center;justify-content:center;font-size:1.2rem;">🏪</div>
                <?php endif; ?>
              </td>
              <td style="font-weight:500;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <?= htmlspecialchars($p['business_name'] ?: '—') ?>
              </td>
              <td>
                <a href="<?= siteUrl('p/') ?>?t=<?= $p['token'] ?>" target="_blank" style="color:var(--p);font-family:monospace;font-size:.8rem;">
                  <?= $p['token'] ?> <i class="fas fa-external-link-alt" style="font-size:.65rem;"></i>
                </a>
              </td>
              <td>
                <div style="font-size:.8rem;"><?= htmlspecialchars($p['owner_name']) ?></div>
                <div style="font-size:.72rem;color:var(--muted);"><?= htmlspecialchars($p['owner_email']) ?></div>
              </td>
              <td><span class="badge bb"><?= htmlspecialchars($p['business_type'] ?: 'general') ?></span></td>
              <td style="font-weight:600;"><?= number_format($p['views']) ?></td>
              <td>
                <span class="badge <?= $p['is_active'] ? 'bg' : 'br' ?>">
                  <?= $p['is_active'] ? '● Active' : '○ Inactive' ?>
                </span>
              </td>
              <td style="color:var(--muted);font-size:.75rem;"><?= date('d M Y', strtotime($p['created_at'])) ?></td>
              <td>
                <div style="display:flex;gap:5px;flex-wrap:wrap;">
                  <a href="<?= siteUrl('p/') ?>?t=<?= $p['token'] ?>" target="_blank" class="act-btn info">
                    <i class="fas fa-eye"></i> View
                  </a>
                  <button class="act-btn warn" onclick="submitAction('toggle_active', <?= $p['id'] ?>)">
                    <i class="fas fa-toggle-<?= $p['is_active']?'on':'off' ?>"></i> <?= $p['is_active']?'Disable':'Enable' ?>
                  </button>
                  <button class="act-btn danger" onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['business_name'] ?: $p['token'])) ?>')">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="no-data">😕 No pages found<?= $search ? " for \"".htmlspecialchars($search)."\"" : '' ?>.</div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?q=<?= urlencode($search) ?>&type=<?= urlencode($typeFilter) ?>&status=<?= urlencode($statusFilter) ?>&page=<?= $i ?>" class="pag-btn <?= $i==$page?'on':'' ?>"><?= $i ?></a>
          <?php endfor; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="site-footer">
        <strong>A Product of TechEagles</strong> &nbsp;|&nbsp; Under Mahakumbrix Innovation
      </div>
    </div>
  </div>
</div>

<!-- Hidden form for actions -->
<form id="action-form" method="POST" style="display:none;">
  <input type="hidden" name="action" id="af-action">
  <input type="hidden" name="pid" id="af-pid">
</form>

<script>
function applyTheme(t){document.documentElement.setAttribute('data-theme',t);localStorage.setItem('theme',t);const d=document.querySelector('.tdot');if(d)d.textContent=t==='dark'?'☀️':'🌙';}
function toggleTheme(){applyTheme(document.documentElement.getAttribute('data-theme')==='dark'?'light':'dark');}
(function(){applyTheme(localStorage.getItem('theme')||'dark');})();

function submitAction(action, pid) {
  document.getElementById('af-action').value = action;
  document.getElementById('af-pid').value = pid;
  document.getElementById('action-form').submit();
}
function confirmDelete(pid, name) {
  if (confirm('Delete page "' + name + '"? This cannot be undone.')) submitAction('delete', pid);
}
</script>
</body>
</html>
