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
    $uid    = (int)($_POST['uid'] ?? 0);

    if ($action === 'delete' && $uid) {
        $db->query("DELETE FROM users WHERE id=$uid AND role!='admin'");
        $msg = '✅ User deleted successfully.'; $msgType = 'ok';
    } elseif ($action === 'toggle_role' && $uid) {
        $u = $db->query("SELECT role FROM users WHERE id=$uid")->fetch_assoc();
        if ($u) {
            $newRole = $u['role'] === 'admin' ? 'user' : 'admin';
            $db->query("UPDATE users SET role='$newRole' WHERE id=$uid");
            $msg = "✅ Role changed to $newRole."; $msgType = 'ok';
        }
    } elseif ($action === 'toggle_power' && $uid) {
        $u = $db->query("SELECT role, is_power_user FROM users WHERE id=$uid")->fetch_assoc();
        if ($u && $u['role'] !== 'admin') {
            $newPower = $u['is_power_user'] ? 0 : 1;
            $db->query("UPDATE users SET is_power_user=$newPower WHERE id=$uid");
            $label = $newPower ? 'granted' : 'revoked';
            $msg = "⚡ Power User access $label successfully."; $msgType = 'ok';
        }
    } elseif ($action === 'reset_password' && $uid) {
        $newPass = trim($_POST['new_password'] ?? '');
        if (strlen($newPass) >= 6) {
            $hash = $db->real_escape_string(password_hash($newPass, PASSWORD_BCRYPT));
            $db->query("UPDATE users SET password='$hash' WHERE id=$uid");
            $msg = '✅ Password reset successfully.'; $msgType = 'ok';
        } else {
            $msg = '❌ Password must be at least 6 characters.'; $msgType = 'err';
        }
    }
}

// Search / filter
$search = trim($_GET['q'] ?? '');
$roleFilter = $_GET['role'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = '1=1';
if ($search) {
    $s = $db->real_escape_string($search);
    $where .= " AND (u.name LIKE '%$s%' OR u.email LIKE '%$s%')";
}
if ($roleFilter) {
    if ($roleFilter === 'power') {
        $where .= " AND u.is_power_user=1 AND u.role!='admin'";
    } else {
        $r = $db->real_escape_string($roleFilter);
        $where .= " AND u.role='$r'";
    }
}

$total = $db->query("SELECT COUNT(*) as c FROM users u WHERE $where")->fetch_assoc()['c'];
$totalPages = ceil($total / $perPage);

$users = $db->query("
    SELECT u.*, COALESCE(w.balance,0) as balance,
           (SELECT COUNT(*) FROM pages p WHERE p.user_id=u.id) as page_count,
           (SELECT COUNT(*) FROM generations g WHERE g.user_id=u.id) as gen_count
    FROM users u
    LEFT JOIN wallets w ON w.user_id=u.id
    WHERE $where
    ORDER BY u.created_at DESC
    LIMIT $perPage OFFSET $offset
");

$totalUsers  = $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalAdmins = $db->query("SELECT COUNT(*) as c FROM users WHERE role='admin'")->fetch_assoc()['c'];
$r = $db->query("SELECT COUNT(*) as c FROM users WHERE is_power_user=1 AND role!='admin'");
$totalPower  = ($r && $r->num_rows > 0) ? $r->fetch_assoc()['c'] : 0;
$newToday    = $db->query("SELECT COUNT(*) as c FROM users WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Users — PosterWall Admin</title>
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
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px;}
.sc{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:18px;position:relative;overflow:hidden;box-shadow:var(--shadow);}
.sc::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--ca,var(--p));}
.sc.g::before{--ca:var(--g);} .sc.b::before{--ca:var(--b);}
.sc-lbl{font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;}
.sc-val{font-family:'Baloo 2',cursive;font-size:2rem;font-weight:800;}
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
.bo{background:rgba(255,107,0,.1);color:var(--p);}
.bb{background:rgba(79,142,247,.1);color:var(--b);}
.bg{background:rgba(0,212,100,.1);color:var(--g);}
.user-av{width:30px;height:30px;border-radius:50%;object-fit:cover;border:1.5px solid var(--border);}
.act-btn{padding:5px 10px;border-radius:7px;font-size:.72rem;font-weight:600;border:none;cursor:pointer;transition:all .2s;}
.act-btn.danger{background:rgba(255,85,85,.12);color:var(--r);}
.act-btn.danger:hover{background:var(--r);color:#fff;}
.act-btn.warn{background:rgba(255,107,0,.12);color:var(--p);}
.act-btn.warn:hover{background:var(--p);color:#fff;}
.act-btn.info{background:rgba(79,142,247,.12);color:var(--b);}
.act-btn.info:hover{background:var(--b);color:#fff;}
.pagination{display:flex;gap:6px;justify-content:center;padding:16px;}
.pag-btn{padding:6px 12px;border-radius:8px;font-size:.8rem;border:1px solid var(--border);background:var(--card);color:var(--text);cursor:pointer;text-decoration:none;}
.pag-btn.on{background:var(--p);color:#fff;border-color:var(--p);}
.pag-btn:hover:not(.on){background:var(--bg2);}
/* Modal */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);z-index:500;display:flex;align-items:center;justify-content:center;}
.modal{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:380px;}
.modal h3{font-size:1rem;font-weight:700;margin-bottom:16px;}
.modal .inp{width:100%;padding:11px 14px;border-radius:10px;background:var(--bg2);border:1px solid var(--border);color:var(--text);font-family:'Poppins',sans-serif;font-size:.9rem;margin-bottom:12px;}
.modal .inp:focus{outline:none;border-color:var(--p);}
.modal-btns{display:flex;gap:10px;margin-top:4px;}
.modal-btns button{flex:1;padding:10px;border-radius:9px;font-weight:600;font-size:.85rem;border:none;cursor:pointer;}
.modal-btns .confirm{background:var(--p);color:#fff;}
.modal-btns .cancel{background:var(--bg2);color:var(--text);}
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
    <a href="users.php" class="sb-link on"><i class="fas fa-users"></i><span>Users</span></a>
    <a href="pages.php" class="sb-link"><i class="fas fa-file-alt"></i><span>Pages</span></a>
    <a href="wallet.php" class="sb-link"><i class="fas fa-wallet"></i><span>Manage Funds</span></a>
    <div class="sb-sec">Site</div>
    <a href="<?= siteUrl('') ?>" target="_blank" class="sb-link"><i class="fas fa-globe"></i><span>View Site</span></a>
    <a href="<?= siteUrl('dashboard/') ?>" class="sb-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
    <a href="<?= siteUrl('auth/logout.php') ?>" class="sb-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    <div class="sb-foot"><p><strong style="color:var(--p);">TechEagles</strong><br>Mahakumbrix Innovation<br>PosterWall v2.0</p></div>
  </nav>

  <div class="main">
    <div class="tb">
      <div class="tb-title">👥 Users</div>
      <div style="display:flex;align-items:center;gap:12px;">
        <button class="toggle" onclick="toggleTheme()"><div class="tdot">🌙</div></button>
        <img src="<?= htmlspecialchars($user['avatar'] ?? '') ?>" class="av" alt="" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=FF6B00&color=fff'">
      </div>
    </div>

    <div class="con">
      <?php if ($msg): ?><div class="msg <?= $msgType ?>"><?= $msg ?></div><?php endif; ?>

      <div class="stats" style="grid-template-columns:repeat(4,1fr);">
        <div class="sc"><div class="sc-lbl">Total Users</div><div class="sc-val"><?= number_format($totalUsers) ?></div></div>
        <div class="sc b"><div class="sc-lbl">Admins</div><div class="sc-val"><?= $totalAdmins ?></div></div>
        <div class="sc" style="--ca:#a855f7;"><div class="sc-lbl">⚡ Power Users</div><div class="sc-val" style="background:linear-gradient(135deg,#a855f7,#7c3aed);-webkit-background-clip:text;-webkit-text-fill-color:transparent;"><?= $totalPower ?></div></div>
        <div class="sc g"><div class="sc-lbl">New Today</div><div class="sc-val"><?= $newToday ?></div></div>
      </div>

      <!-- Filters -->
      <form method="GET" class="filters">
        <input type="text" name="q" class="finp" placeholder="🔍 Search name or email..." value="<?= htmlspecialchars($search) ?>" style="flex:1;min-width:200px;">
        <select name="role" class="finp">
          <option value="">All Roles</option>
          <option value="user" <?= $roleFilter==='user'?'selected':'' ?>>Users</option>
          <option value="admin" <?= $roleFilter==='admin'?'selected':'' ?>>Admins</option>
          <option value="power" <?= $roleFilter==='power'?'selected':'' ?>>⚡ Power Users</option>
        </select>
        <button type="submit" class="fbtn"><i class="fas fa-search"></i> Filter</button>
        <?php if ($search || $roleFilter): ?>
        <a href="users.php" class="fbtn" style="background:var(--bg2);color:var(--muted);">✕ Clear</a>
        <?php endif; ?>
      </form>

      <div class="tc">
        <div class="tc-hd">
          <div class="tc-ttl">All Users</div>
          <span style="font-size:.75rem;color:var(--muted);"><?= $total ?> result<?= $total!=1?'s':'' ?></span>
        </div>
        <?php if ($users && $users->num_rows > 0): ?>
        <div style="overflow-x:auto;">
        <table>
          <thead>
            <tr>
              <th>User</th>
              <th>Email</th>
              <th>Role</th>
              <th>Power</th>
              <th>Balance</th>
              <th>Pages</th>
              <th>Gens</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
              <td style="display:flex;align-items:center;gap:8px;">
                <img src="<?= htmlspecialchars($u['avatar']??'') ?>" class="user-av" alt="" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($u['name']) ?>&background=1a2540&color=FF6B00&size=60'">
                <span style="font-weight:500;"><?= htmlspecialchars($u['name']) ?></span>
              </td>
              <td style="color:var(--muted);"><?= htmlspecialchars($u['email']) ?></td>
              <td><span class="badge <?= $u['role']==='admin'?'bo':'bb' ?>"><?= $u['role'] ?></span></td>
              <td><?php if($u['role']!=='admin'): ?><?php if($u['is_power_user']): ?><span class="badge" style="background:rgba(168,85,247,.15);color:#a855f7;">⚡ Power</span><?php else: ?><span style="color:var(--muted);font-size:.72rem;">—</span><?php endif; ?><?php else: ?><span style="color:var(--muted);font-size:.72rem;">∞</span><?php endif; ?></td>
              <td style="font-weight:600;color:var(--g);">₹<?= number_format($u['balance'],2) ?></td>
              <td><?= $u['page_count'] ?></td>
              <td><?= $u['gen_count'] ?></td>
              <td style="color:var(--muted);font-size:.75rem;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
              <td>
                <div style="display:flex;gap:5px;flex-wrap:wrap;">
                  <button class="act-btn info" onclick="openResetModal(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>')">
                    <i class="fas fa-key"></i> Reset
                  </button>
                  <?php if ($u['id'] != $user['id']): ?>
                  <button class="act-btn warn" onclick="submitAction('toggle_role', <?= $u['id'] ?>)">
                    <i class="fas fa-user-shield"></i> <?= $u['role']==='admin'?'Demote':'Promote' ?>
                  </button>
                  <?php if ($u['role'] === 'user'): ?>
                  <button class="act-btn" style="background:rgba(168,85,247,.12);color:#a855f7;" onclick="confirmPowerToggle(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>', <?= $u['is_power_user'] ? 'true' : 'false' ?>)">
                    <i class="fas fa-bolt"></i> <?= $u['is_power_user'] ? 'Revoke Power' : 'Make Power' ?>
                  </button>
                  <?php endif; ?>
                  <?php if ($u['role'] !== 'admin'): ?>
                  <button class="act-btn danger" onclick="confirmDelete(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>')">
                    <i class="fas fa-trash"></i> Delete
                  </button>
                  <?php endif; ?>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        </div>
        <?php else: ?>
        <div class="no-data">😕 No users found<?= $search ? " for \"".htmlspecialchars($search)."\"" : '' ?>.</div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?q=<?= urlencode($search) ?>&role=<?= urlencode($roleFilter) ?>&page=<?= $i ?>" class="pag-btn <?= $i==$page?'on':'' ?>"><?= $i ?></a>
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
  <input type="hidden" name="uid" id="af-uid">
</form>

<!-- Power User Confirmation Modal -->
<div id="power-modal" class="overlay" style="display:none;">
  <div class="modal" style="max-width:420px;">
    <h3 id="power-modal-title">⚡ Grant Power User Access</h3>
    <div style="margin-bottom:18px;">
      <div style="background:rgba(168,85,247,.08);border:1px solid rgba(168,85,247,.25);border-radius:12px;padding:16px;margin-bottom:14px;">
        <div style="font-weight:700;color:#a855f7;font-size:.9rem;margin-bottom:6px;" id="power-modal-sub"></div>
        <ul style="color:var(--muted);font-size:.8rem;line-height:1.8;padding-left:16px;" id="power-modal-list"></ul>
      </div>
      <p style="font-size:.82rem;color:var(--muted);" id="power-modal-note"></p>
    </div>
    <div class="modal-btns">
      <button class="confirm" id="power-confirm-btn" onclick="doPowerToggle()" style="background:linear-gradient(135deg,#a855f7,#7c3aed);">
        <i class="fas fa-bolt"></i> <span id="power-btn-label">Confirm</span>
      </button>
      <button class="cancel" onclick="document.getElementById('power-modal').style.display='none'">Cancel</button>
    </div>
  </div>
</div>

<!-- Reset Password Modal -->
<div id="reset-modal" class="overlay" style="display:none;">
  <div class="modal">
    <h3>🔑 Reset Password</h3>
    <p style="font-size:.82rem;color:var(--muted);margin-bottom:14px;">Set new password for <strong id="reset-name"></strong></p>
    <form method="POST">
      <input type="hidden" name="action" value="reset_password">
      <input type="hidden" name="uid" id="reset-uid">
      <input type="password" name="new_password" class="inp" placeholder="New Password (min 6 chars)" required minlength="6">
      <div class="modal-btns">
        <button type="submit" class="confirm"><i class="fas fa-save"></i> Save</button>
        <button type="button" class="cancel" onclick="document.getElementById('reset-modal').style.display='none'">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function applyTheme(t){document.documentElement.setAttribute('data-theme',t);localStorage.setItem('theme',t);const d=document.querySelector('.tdot');if(d)d.textContent=t==='dark'?'☀️':'🌙';}
function toggleTheme(){applyTheme(document.documentElement.getAttribute('data-theme')==='dark'?'light':'dark');}
(function(){applyTheme(localStorage.getItem('theme')||'dark');})();

function submitAction(action, uid) {
  document.getElementById('af-action').value = action;
  document.getElementById('af-uid').value = uid;
  document.getElementById('action-form').submit();
}
function confirmDelete(uid, name) {
  if (confirm('Delete user "' + name + '"? This cannot be undone.')) submitAction('delete', uid);
}
function openResetModal(uid, name) {
  document.getElementById('reset-uid').value = uid;
  document.getElementById('reset-name').textContent = name;
  document.getElementById('reset-modal').style.display = 'flex';
}

let _powerUid = null, _powerIsActive = false;
function confirmPowerToggle(uid, name, isActive) {
  _powerUid = uid; _powerIsActive = isActive;
  const granting = !isActive;
  document.getElementById('power-modal-title').textContent = granting ? '⚡ Grant Power User Access' : '🚫 Revoke Power User Access';
  document.getElementById('power-modal-sub').textContent = granting
    ? 'You are about to grant Power User status to: ' + name
    : 'You are about to revoke Power User status from: ' + name;
  document.getElementById('power-modal-list').innerHTML = granting
    ? '<li>Unlimited page creation (no ₹9 charge)</li><li>Unlimited AI page edits</li><li>Access remains until manually revoked</li>'
    : '<li>User will return to standard paid access</li><li>Existing pages remain intact</li><li>Normal ₹9/page charges resume</li>';
  document.getElementById('power-modal-note').textContent = granting
    ? 'This is a complimentary upgrade. The user will NOT be charged for any pages while this is active.'
    : 'The user will need wallet balance to create new pages going forward.';
  document.getElementById('power-btn-label').textContent = granting ? 'Yes, Grant Access' : 'Yes, Revoke Access';
  document.getElementById('power-modal').style.display = 'flex';
}
function doPowerToggle() {
  document.getElementById('power-modal').style.display = 'none';
  submitAction('toggle_power', _powerUid);
}
</script>
</body>
</html>
