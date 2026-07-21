<?php
require_once '../config.php';
requireAuth();

$user = me();
$uid  = (int)$_SESSION['uid'];
$db   = db();

$successMsg = '';
// Handle status update
if (isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $status  = $db->real_escape_string($_POST['status']);
    
    // Verify that the order belongs to one of this merchant's pages
    $verifyRes = $db->query("SELECT o.id FROM orders o JOIN pages p ON o.page_id=p.id WHERE o.id=$orderId AND p.user_id=$uid");
    if ($verifyRes && $verifyRes->num_rows > 0) {
        $db->query("UPDATE orders SET order_status='$status' WHERE id=$orderId");
        $successMsg = "Order status updated to " . ucfirst($status) . "! ✅";
    }
}

// Fetch orders associated with merchant's pages
$ordersRes = $db->query("
    SELECT o.*, p.business_name 
    FROM orders o 
    JOIN pages p ON o.page_id=p.id 
    WHERE p.user_id=$uid AND o.payment_status='success'
    ORDER BY o.created_at DESC
");

$orders = [];
if ($ordersRes) {
    while ($r = $ordersRes->fetch_assoc()) {
        $orders[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Orders — PosterWall</title>
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
.nav-r{display:flex;align-items:center;gap:12px;}
.nav-av{width:36px;height:36px;border-radius:50%;border:2px solid var(--p);object-fit:cover;}
.theme-toggle{width:36px;height:36px;border-radius:10px;background:var(--bg2);border:1px solid var(--border);color:var(--text);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:all .3s;}
.theme-toggle:hover{background:var(--p);color:white;}
.con{max-width:900px;margin:0 auto;padding:28px 16px;min-height:calc(100svh - 200px);}
.page-title{font-family:'Baloo 2',cursive;font-size:1.85rem;font-weight:800;margin-bottom:8px;}
.page-sub{color:var(--muted);font-size:.95rem;line-height:1.6;margin-bottom:24px;}

/* Toast notification */
.toast-msg {
    background: #10b981;
    color: white;
    padding: 12px 18px;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 600;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Tabs */
.tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    overflow-x: auto;
    padding-bottom: 4px;
}
.tab-btn {
    padding: 8px 16px;
    border-radius: 20px;
    border: 1px solid var(--border);
    background: var(--bg2);
    color: var(--muted);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
}
.tab-btn.active {
    background: var(--p);
    color: white;
    border-color: var(--p);
}

/* Orders List */
.orders-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}
@media(min-width: 768px) {
    .orders-list {
        grid-template-columns: repeat(2, 1fr);
    }
}

.order-card {
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(10px);
    border: 1.5px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s;
}
[data-theme="dark"] .order-card {
    background: rgba(26, 45, 79, 0.3);
}
.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    border-color: var(--p);
}
.order-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--p);
}
.order-card.completed::before { background: #10b981; }
.order-card.preparing::before { background: #6366f1; }
.order-card.cancelled::before { background: #ef4444; }

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.order-biz {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.order-date {
    font-size: 0.7rem;
    color: var(--muted);
}
.cust-info {
    margin-bottom: 16px;
}
.cust-name {
    font-weight: 700;
    font-size: 1.05rem;
    margin-bottom: 2px;
}
.cust-phone {
    font-size: 0.85rem;
    color: var(--p);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.cust-addr {
    font-size: 0.8rem;
    color: var(--muted);
    margin-top: 4px;
    background: rgba(0,0,0,0.02);
    [data-theme="dark"] & { background: rgba(255,255,255,0.02); }
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px dashed var(--border);
}

.order-items {
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    padding: 12px 0;
    margin-bottom: 16px;
}
.item-line {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    margin-bottom: 4px;
}

.order-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.order-total {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--p);
}

.status-badge {
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}
.status-badge.new { background: rgba(255, 107, 0, 0.15); color: var(--p); }
.status-badge.preparing { background: rgba(99, 102, 241, 0.15); color: #6366f1; }
.status-badge.completed { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.status-badge.cancelled { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

.action-form {
    margin-top: 14px;
    display: flex;
    gap: 6px;
}
.action-select {
    flex: 1;
    padding: 8px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--bg2);
    color: var(--text);
    font-family: inherit;
    font-size: 0.78rem;
    outline: none;
    cursor: pointer;
}
.action-submit {
    padding: 8px 12px;
    border-radius: 8px;
    border: none;
    background: var(--p);
    color: white;
    font-weight: 600;
    font-size: 0.78rem;
    cursor: pointer;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 48px 24px;
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
    border: 1.5px solid var(--border);
    border-radius: 16px;
}
[data-theme="dark"] .empty-state {
    background: rgba(26, 45, 79, 0.2);
}

/* Bottom Nav */
.bot-nav{position:fixed;top:auto;bottom:0;left:0;right:0;background:var(--bg2);border-top:1px solid var(--border);display:flex;z-index:200;}
.bot-nav a{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;padding:10px 4px;color:var(--muted);font-size:.6rem;font-weight:500;transition:all .2s;}
.bot-nav a:hover{color:var(--p);}
.bot-nav a.on{color:var(--p);}
.bot-nav a i{font-size:1.05rem;}
</style>
</head>
<body>

<nav>
  <div class="nav-in">
    <a href="<?= siteUrl('dashboard/') ?>" class="logo">Poster<span>Wall</span></a>
    <div class="nav-r">
      <button id="theme-toggle" class="theme-toggle" onclick="toggleTheme()" style="margin-right:8px;">🌙</button>
      <?php if (!empty($user['avatar'])): ?>
        <img src="<?= htmlspecialchars($user['avatar']) ?>" class="nav-av" alt="Avatar">
      <?php else: ?>
        <div class="nav-av" style="background:var(--p);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.1rem;"><?= strtoupper(substr($user['name'],0,1)) ?></div>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="con">
    <h2 class="page-title">📦 Customer Orders</h2>
    <p class="page-sub">Apni shops aur restaurants ke public orders manage karein aur live updates dekhein.</p>
    
    <?php if ($successMsg): ?>
        <div class="toast-msg">
            <i class="fas fa-check-circle"></i>
            <span><?= $successMsg ?></span>
        </div>
    <?php endif; ?>
    
    <!-- Filter Tabs -->
    <div class="tabs">
        <button class="tab-btn active" onclick="filterOrders('all')">All Orders (<?= count($orders) ?>)</button>
        <button class="tab-btn" onclick="filterOrders('new')">New</button>
        <button class="tab-btn" onclick="filterOrders('preparing')">Preparing</button>
        <button class="tab-btn" onclick="filterOrders('completed')">Completed</button>
        <button class="tab-btn" onclick="filterOrders('cancelled')">Cancelled</button>
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open" style="font-size:3rem;color:var(--muted);margin-bottom:12px;display:block;"></i>
            <h3 style="font-weight:700;margin-bottom:4px;">No Orders Yet</h3>
            <p style="color:var(--muted);font-size:0.85rem;">Aapke business page se koi orders nahi aaye hain. Pages ko share karein taaki customers order kar sakein.</p>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): 
                $items = json_decode($order['items'], true) ?: [];
                $status = strtolower($order['order_status']);
                $dateStr = date('d M Y, h:i A', strtotime($order['created_at']));
            ?>
                <div class="order-card <?= $status ?>" data-status="<?= $status ?>">
                    <div class="order-header">
                        <span class="order-biz"><?= htmlspecialchars($order['business_name']) ?></span>
                        <span class="order-date"><?= $dateStr ?></span>
                    </div>
                    
                    <div class="cust-info">
                        <div class="cust-name">Order #<?= $order['id'] ?>: <?= htmlspecialchars($order['customer_name']) ?></div>
                        <a href="tel:<?= htmlspecialchars($order['customer_phone']) ?>" class="cust-phone">
                            <i class="fas fa-phone"></i> <?= htmlspecialchars($order['customer_phone']) ?>
                        </a>
                        <div class="cust-addr">
                            <strong>Location/Table:</strong><br>
                            <?= nl2br(htmlspecialchars($order['delivery_address'] ?: 'Not Specified')) ?>
                        </div>
                    </div>
                    
                    <div class="order-items">
                        <?php foreach ($items as $item): ?>
                            <div class="item-line">
                                <span><?= htmlspecialchars($item['name']) ?> &times; <?= (int)$item['qty'] ?></span>
                                <span>₹<?= (int)$item['qty'] * (float)$item['price'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-footer">
                        <div class="order-total">₹<?= $order['total_amount'] ?></div>
                        <span class="status-badge <?= $status ?>"><?= $status ?></span>
                    </div>
                    
                    <form method="POST" class="action-form">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status" class="action-select">
                            <option value="new" <?= $status==='new'?'selected':'' ?>>New</option>
                            <option value="preparing" <?= $status==='preparing'?'selected':'' ?>>Preparing</option>
                            <option value="completed" <?= $status==='completed'?'selected':'' ?>>Completed</option>
                            <option value="cancelled" <?= $status==='cancelled'?'selected':'' ?>>Cancelled</option>
                        </select>
                        <button type="submit" name="update_status" class="action-submit">Update</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Bottom Nav -->
<nav class="bot-nav">
  <a href="<?= siteUrl('dashboard/') ?>"><i class="fas fa-home"></i>Home</a>
  <a href="<?= siteUrl('dashboard/orders.php') ?>" class="on"><i class="fas fa-shopping-bag"></i>Orders</a>
  <a href="<?= siteUrl('dashboard/create.php') ?>"><i class="fas fa-plus-circle"></i>New Page</a>
  <a href="<?= siteUrl('m/') ?>"><i class="fas fa-search"></i>Find</a>
  <a href="<?= siteUrl('dashboard/wallet.php') ?>"><i class="fas fa-wallet"></i>Wallet</a>
</nav>

<script>
function filterOrders(status) {
    // Update active tab styling
    const btns = document.querySelectorAll('.tab-btn');
    btns.forEach(btn => btn.classList.remove('active'));
    
    // Find active button
    event.target.classList.add('active');
    
    const cards = document.querySelectorAll('.order-card');
    cards.forEach(card => {
        if (status === 'all' || card.getAttribute('data-status') === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
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

<footer style="text-align:center;padding:16px 20px 80px;font-size:.7rem;color:var(--muted);border-top:1px solid var(--border);margin-top:24px;">
  <strong style="color:var(--p);">A Product of TechEagles</strong> &nbsp;|&nbsp; Under Mahakumbrix Innovation
</footer>
</body>
</html>
