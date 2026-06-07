<?php
require_once '../config.php';
requireAuth();
$user = me();
if ($user['role'] !== 'admin') { header('Location: /dashboard/'); exit; }
$db = db();

$msg = '';
$msgType = '';

// Handle fund add/remove
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $amount   = (float)($_POST['amount'] ?? 0);
    $note     = trim($_POST['note'] ?? '');

    if ($email && $amount > 0 && in_array($action, ['add', 'remove'])) {
        $em = $db->real_escape_string($email);
        $u  = $db->query("SELECT id, name FROM users WHERE email='$em'")->fetch_assoc();

        if (!$u) {
            $msg = "No user found with email: " . htmlspecialchars($email);
            $msgType = 'err';
        } else {
            $uid = (int)$u['id'];
            $amt = round($amount, 2);

            // Ensure wallet exists
            $db->query("INSERT IGNORE INTO wallets (user_id, balance) VALUES ($uid, 0)");

            if ($action === 'add') {
                $db->query("UPDATE wallets SET balance = balance + $amt WHERE user_id = $uid");
                $noteEsc = $db->real_escape_string($note ?: 'Admin credit');
                $db->query("INSERT INTO transactions (user_id, amount, type, status, note) VALUES ($uid, $amt, 'credit', 'success', '$noteEsc')");
                $msg = "✅ ₹{$amt} added to " . htmlspecialchars($u['name']) . " (" . htmlspecialchars($email) . ")";
                $msgType = 'ok';
            } else {
                // Check current balance
                $bal = (float)$db->query("SELECT balance FROM wallets WHERE user_id=$uid")->fetch_assoc()['balance'];
                if ($amt > $bal) {
                    $msg = "❌ Insufficient balance. Current balance: ₹{$bal}";
                    $msgType = 'err';
                } else {
                    $db->query("UPDATE wallets SET balance = balance - $amt WHERE user_id = $uid");
                    $noteEsc = $db->real_escape_string($note ?: 'Admin debit');
                    $db->query("INSERT INTO transactions (user_id, amount, type, status, note) VALUES ($uid, $amt, 'debit', 'success', '$noteEsc')");
                    $msg = "✅ ₹{$amt} removed from " . htmlspecialchars($u['name']) . " (" . htmlspecialchars($email) . ")";
                    $msgType = 'ok';
                }
            }
        }
    } elseif ($amount <= 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $msg = "❌ Amount must be greater than 0.";
        $msgType = 'err';
    }
}

// Recent admin wallet transactions
$recentTx = $db->query("
    SELECT t.*, u.name, u.email
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE t.note LIKE 'Admin%'
    ORDER BY t.created_at DESC
    LIMIT 20
");

// Search user wallet
$searchUser = null;
if (!empty($_GET['lookup'])) {
    $em = $db->real_escape_string(trim($_GET['lookup']));
    $row = $db->query("SELECT u.id, u.name, u.email, COALESCE(w.balance,0) as balance FROM users u LEFT JOIN wallets w ON w.user_id=u.id WHERE u.email='$em'")->fetch_assoc();
    $searchUser = $row ?: false;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Funds — PosterWall Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
.con{padding:24px;}
.page-hd{margin-bottom:22px;}
.page-hd h2{font-family:'Baloo 2',cursive;font-size:1.5rem;font-weight:800;}
.page-hd p{color:var(--muted);font-size:.85rem;margin-top:3px;}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;}
.card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:22px;box-shadow:var(--shadow);}
.card h3{font-size:.9rem;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.card h3 i{color:var(--p);}
.inp{width:100%;padding:11px 14px;border-radius:10px;background:var(--bg2);border:1px solid var(--border);color:var(--text);font-family:'Poppins',sans-serif;font-size:.9rem;margin-bottom:12px;transition:border .2s;}
.inp:focus{outline:none;border-color:var(--p);}
select.inp{cursor:pointer;}
.btn{width:100%;padding:12px;border-radius:10px;font-weight:700;font-size:.92rem;border:none;cursor:pointer;transition:all .3s;display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-add{background:var(--g);color:#fff;}
.btn-add:hover{opacity:.88;transform:translateY(-2px);}
.btn-remove{background:var(--r);color:#fff;}
.btn-remove:hover{opacity:.88;transform:translateY(-2px);}
.btn-search{background:var(--b);color:#fff;}
.btn-search:hover{opacity:.88;}
.btn-row{display:flex;gap:10px;}
.btn-row .btn{width:auto;flex:1;}
.msg{padding:12px 14px;border-radius:10px;font-size:.85rem;margin-bottom:18px;}
.msg.ok{background:rgba(0,212,100,.1);border:1px solid rgba(0,212,100,.3);color:var(--g);}
.msg.err{background:rgba(255,85,85,.1);border:1px solid rgba(255,85,85,.3);color:var(--r);}
.lookup-result{background:var(--bg2);border:1px solid var(--border);border-radius:10px;padding:14px 16px;margin-top:12px;font-size:.85rem;}
.lookup-result .name{font-weight:600;margin-bottom:3px;}
.lookup-result .bal{font-family:'Baloo 2',cursive;font-size:1.5rem;font-weight:800;color:var(--g);}
.lookup-result .email{color:var(--muted);font-size:.78rem;}
.tc{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:var(--shadow);}
.tc-hd{padding:16px 18px 10px;display:flex;justify-content:space-between;align-items:center;}
.tc-ttl{font-weight:600;font-size:.88rem;}
table{width:100%;border-collapse:collapse;}
th{padding:9px 16px;text-align:left;font-size:.68rem;color:var(--muted);text-transform:uppercase;background:rgba(0,0,0,.04);border-bottom:1px solid var(--border);}
td{padding:10px 16px;font-size:.82rem;border-bottom:1px solid var(--border);}
tr:last-child td{border-bottom:none;}
tr:hover td{background:rgba(255,107,0,.02);}
.badge{display:inline-flex;padding:3px 8px;border-radius:100px;font-size:.68rem;font-weight:600;}
.bg{background:rgba(0,212,100,.1);color:var(--g);}
.br{background:rgba(255,85,85,.1);color:var(--r);}
.no-data{text-align:center;padding:32px;color:var(--muted);font-size:.85rem;}
.site-footer{text-align:center;padding:18px 20px;font-size:.72rem;color:var(--muted);border-top:1px solid var(--border);margin-top:8px;}
.site-footer strong{color:var(--p);}
@media(max-width:900px){.sb{width:52px;}.sb-logo span,.sb-link span,.sb-sec,.sb-foot p{display:none;}.main{margin-left:52px;}.grid{grid-template-columns:1fr;}}
</style>
</head>
<body>
<div class="layout">
  <!-- Sidebar -->
  <nav class="sb">
    <div class="sb-logo">🦅 Poster<span>Wall</span></div>
    <div class="sb-sec">Analytics</div>
    <a href="index.php" class="sb-link"><i class="fas fa-chart-pie"></i><span>Dashboard</span></a>
    <div class="sb-sec">Manage</div>
    <a href="users.php" class="sb-link"><i class="fas fa-users"></i><span>Users</span></a>
    <a href="pages.php" class="sb-link"><i class="fas fa-file-alt"></i><span>Pages</span></a>
    <a href="wallet.php" class="sb-link on"><i class="fas fa-wallet"></i><span>Manage Funds</span></a>
    <div class="sb-sec">Site</div>
    <a href="<?= siteUrl('') ?>" target="_blank" class="sb-link"><i class="fas fa-globe"></i><span>View Site</span></a>
    <a href="<?= siteUrl('dashboard/') ?>" class="sb-link"><i class="fas fa-home"></i><span>Dashboard</span></a>
    <a href="<?= siteUrl('auth/logout.php') ?>" class="sb-link"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    <div class="sb-foot"><p><strong style="color:var(--p);">TechEagles</strong><br>Mahakumbrix Innovation<br>PosterWall v2.0</p></div>
  </nav>

  <div class="main">
    <!-- Topbar -->
    <div class="tb">
      <div class="tb-title">💰 Manage User Funds</div>
      <div style="display:flex;align-items:center;gap:12px;">
        <button class="toggle" onclick="toggleTheme()" title="Toggle theme"><div class="tdot">🌙</div></button>
        <img src="<?= htmlspecialchars($user['avatar'] ?? '') ?>" class="av" style="width:32px;height:32px;border-radius:50%;border:2px solid var(--p);object-fit:cover;" alt="<?= htmlspecialchars($user['name']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=FF6B00&color=fff'">
      </div>
    </div>

    <div class="con">
      <div class="page-hd">
        <h2>💳 Wallet Fund Manager</h2>
        <p>Add or remove funds from any user's wallet by their email address.</p>
      </div>

      <?php if ($msg): ?>
      <div class="msg <?= $msgType ?>"><?= $msg ?></div>
      <?php endif; ?>

      <div class="grid">
        <!-- Add / Remove Funds -->
        <div class="card">
          <h3><i class="fas fa-exchange-alt"></i> Add / Remove Funds</h3>
          <form method="POST">
            <input type="email" name="email" class="inp" placeholder="User Email (e.g. user@gmail.com)" required
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <input type="number" name="amount" class="inp" placeholder="Amount (₹)" min="1" step="0.01" required
              value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>">
            <input type="text" name="note" class="inp" placeholder="Note / Reason (optional)"
              value="<?= htmlspecialchars($_POST['note'] ?? '') ?>">
            <div class="btn-row">
              <button type="submit" name="action" value="add" class="btn btn-add">
                <i class="fas fa-plus-circle"></i> Add Funds
              </button>
              <button type="submit" name="action" value="remove" class="btn btn-remove">
                <i class="fas fa-minus-circle"></i> Remove Funds
              </button>
            </div>
          </form>
        </div>

        <!-- Lookup User Balance -->
        <div class="card">
          <h3><i class="fas fa-search"></i> Check User Balance</h3>
          <form method="GET">
            <input type="email" name="lookup" class="inp" placeholder="User Email to look up"
              value="<?= htmlspecialchars($_GET['lookup'] ?? '') ?>">
            <button type="submit" class="btn btn-search">
              <i class="fas fa-search"></i> Look Up Balance
            </button>
          </form>
          <?php if ($searchUser === false): ?>
          <div class="lookup-result" style="color:var(--r);">❌ No user found with that email.</div>
          <?php elseif ($searchUser): ?>
          <div class="lookup-result">
            <div class="name">👤 <?= htmlspecialchars($searchUser['name']) ?></div>
            <div class="email"><?= htmlspecialchars($searchUser['email']) ?></div>
            <div class="bal" style="margin-top:8px;">₹<?= number_format($searchUser['balance'], 2) ?></div>
            <div style="font-size:.72rem;color:var(--muted);margin-top:2px;">Current Wallet Balance</div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Recent Admin Transactions -->
      <div class="tc">
        <div class="tc-hd">
          <div class="tc-ttl">📋 Recent Admin Transactions</div>
          <span style="font-size:.75rem;color:var(--muted);">Last 20 entries</span>
        </div>
        <?php if ($recentTx && $recentTx->num_rows > 0): ?>
        <table>
          <thead>
            <tr>
              <th>User</th>
              <th>Email</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Note</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($tx = $recentTx->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($tx['name']) ?></td>
              <td style="color:var(--muted);"><?= htmlspecialchars($tx['email']) ?></td>
              <td>
                <span class="badge <?= $tx['type'] === 'credit' ? 'bg' : 'br' ?>">
                  <?= $tx['type'] === 'credit' ? '▲ Credit' : '▼ Debit' ?>
                </span>
              </td>
              <td style="font-weight:600;color:<?= $tx['type'] === 'credit' ? 'var(--g)' : 'var(--r)' ?>;">
                <?= $tx['type'] === 'credit' ? '+' : '-' ?>₹<?= number_format($tx['amount'], 2) ?>
              </td>
              <td style="color:var(--muted);"><?= htmlspecialchars($tx['note'] ?? '—') ?></td>
              <td style="color:var(--muted);font-size:.75rem;"><?= date('d M Y, H:i', strtotime($tx['created_at'])) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <?php else: ?>
        <div class="no-data">No admin transactions yet.</div>
        <?php endif; ?>
      </div>

      <div class="site-footer">
        <strong>A Product of TechEagles</strong> &nbsp;|&nbsp; Under Mahakumbrix Innovation
      </div>
    </div>
  </div>
</div>
<script>
function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  localStorage.setItem('theme', theme);
  const d = document.querySelector('.tdot');
  if (d) d.textContent = theme === 'dark' ? '☀️' : '🌙';
}
function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') || 'dark';
  applyTheme(current === 'dark' ? 'light' : 'dark');
}
(function(){
  const t = localStorage.getItem('theme') || 'dark';
  applyTheme(t);
})();
</script>
</body>
</html>
