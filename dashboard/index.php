<?php
require_once '../config.php';
requireAuth();
$user  = me();
$bal   = wallet();
$power = isPowerUser($user);
$uid   = (int)$_SESSION['uid'];
$db    = db();
$pages = $db->query("SELECT * FROM pages WHERE user_id=$uid AND is_active=1 ORDER BY created_at DESC");
$total = $db->query("SELECT COUNT(*) as c FROM pages WHERE user_id=$uid AND is_active=1")->fetch_assoc()['c'];
$views = $db->query("SELECT COALESCE(SUM(views),0) as v FROM pages WHERE user_id=$uid AND is_active=1")->fetch_assoc()['v'];
$gens  = $db->query("SELECT COUNT(*) as c FROM generations WHERE user_id=$uid")->fetch_assoc()['c'];
$ordersCount = $db->query("SELECT COUNT(*) as c FROM orders o JOIN pages p ON o.page_id=p.id WHERE p.user_id=$uid AND o.payment_status='success'")->fetch_assoc()['c'];
$typeIcons=['restaurant'=>'🍽️','shop'=>'🛍️','clinic'=>'🏥','professional'=>'💼','salon'=>'💇','gym'=>'🏋️','school'=>'🏫','repair'=>'🔧','event'=>'🎉','general'=>'🦅'];
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — PosterWall</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@300;400;500;600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{
  --p:#7C3AED;--p2:#A855F7;--p3:#C084FC;
  --bg:#05050f;--bg2:#0d0d1f;--bg3:#12102a;
  --card:rgba(20,14,50,0.65);--card2:rgba(30,20,65,0.8);
  --text:#ede9ff;--muted:#8B7FAB;
  --border:rgba(124,58,237,0.22);--border2:rgba(168,85,247,0.15);
  --glow:rgba(124,58,237,0.35);--glow2:rgba(124,58,237,0.15);
  --green:#10b981;--red:#ef4444;--gold:#f59e0b;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{
  font-family:'Inter','Poppins',sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100svh;
  padding-bottom:90px;
  overflow-x:hidden;
}
body::before{
  content:'';
  position:fixed;
  top:-20%;
  left:-10%;
  width:60vw;
  height:60vw;
  max-width:700px;
  max-height:700px;
  border-radius:50%;
  background:radial-gradient(circle,rgba(124,58,237,0.18) 0%,transparent 70%);
  pointer-events:none;
  z-index:0;
  animation:orbFloat 18s ease-in-out infinite;
}
body::after{
  content:'';
  position:fixed;
  bottom:-15%;
  right:-10%;
  width:50vw;
  height:50vw;
  max-width:500px;
  max-height:500px;
  border-radius:50%;
  background:radial-gradient(circle,rgba(168,85,247,0.12) 0%,transparent 70%);
  pointer-events:none;
  z-index:0;
}
@keyframes orbFloat{0%,100%{transform:translateY(0) scale(1);}50%{transform:translateY(40px) scale(1.05);}}
a{text-decoration:none;color:inherit;}

/* NAV */
nav{
  background:rgba(5,5,15,0.75);
  backdrop-filter:blur(20px);
  -webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  padding:0 16px;
  position:sticky;
  top:0;
  z-index:100;
}
.nav-in{max-width:960px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:58px;}
.logo{
  font-family:'Baloo 2',cursive;
  font-size:1.4rem;
  font-weight:800;
  background:linear-gradient(135deg,var(--p2),var(--p3),#e879f9);
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  background-clip:text;
}
.logo span{-webkit-text-fill-color:unset;}
.nav-r{display:flex;align-items:center;gap:10px;}
.nav-av{
  width:36px;height:36px;border-radius:50%;
  border:2px solid var(--p2);
  object-fit:cover;
  box-shadow:0 0 14px var(--glow2);
}
.logout-btn{
  padding:8px 14px;border-radius:20px;
  background:rgba(124,58,237,0.15);
  border:1px solid var(--border);
  color:var(--p3);font-weight:600;font-size:.82rem;
  cursor:pointer;transition:all .3s;
}
.logout-btn:hover{background:rgba(124,58,237,0.3);box-shadow:0 0 16px var(--glow2);}

/* LAYOUT */
.con{max-width:960px;margin:0 auto;padding:28px 16px;min-height:calc(100svh - 200px);position:relative;z-index:1;}
.page-title{
  font-family:'Baloo 2',cursive;
  font-size:1.9rem;font-weight:800;
  margin-bottom:6px;
  background:linear-gradient(135deg,#fff 30%,var(--p3));
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  background-clip:text;
}
.page-sub{color:var(--muted);font-size:.93rem;line-height:1.6;margin-bottom:28px;}

/* STATS */
.stats{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:28px;}
@media(min-width:600px){.stats{grid-template-columns:repeat(5,1fr);}}
.stat-c{
  background:var(--card);
  backdrop-filter:blur(16px);
  border:1px solid var(--border);
  border-radius:16px;
  padding:18px 14px;
  position:relative;
  overflow:hidden;
  transition:all .35s;
  cursor:default;
}
.stat-c::before{
  content:'';
  position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,var(--p),var(--p2));
}
.stat-c.g::before{background:linear-gradient(90deg,#10b981,#34d399);}
.stat-c.b::before{background:linear-gradient(90deg,#6366f1,#818cf8);}
.stat-c.y::before{background:linear-gradient(90deg,#f59e0b,#fbbf24);}
.stat-c::after{
  content:'';
  position:absolute;
  inset:0;
  background:radial-gradient(circle at 80% 20%,rgba(124,58,237,0.08),transparent 60%);
  pointer-events:none;
}
.stat-c:hover{
  transform:translateY(-3px);
  border-color:var(--p);
  box-shadow:0 8px 32px var(--glow2);
}
.stat-label{font-size:.67rem;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px;}
.stat-val{
  font-family:'Baloo 2',cursive;
  font-size:2rem;font-weight:800;
  background:linear-gradient(135deg,var(--p2),var(--p3));
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  background-clip:text;
}
.stat-c.g .stat-val{background:linear-gradient(135deg,#10b981,#6ee7b7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.stat-c.b .stat-val{background:linear-gradient(135deg,#818cf8,#c7d2fe);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.stat-c.y .stat-val{background:linear-gradient(135deg,#f59e0b,#fde68a);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}

/* NEW PAGE CTA */
.new-cta{
  background:linear-gradient(135deg,rgba(124,58,237,0.15),rgba(168,85,247,0.08));
  backdrop-filter:blur(14px);
  border:1px solid rgba(124,58,237,0.3);
  border-radius:18px;
  padding:24px;
  margin-bottom:24px;
  display:flex;
  align-items:center;
  gap:16px;
  flex-wrap:wrap;
  position:relative;
  overflow:hidden;
}
.new-cta::before{
  content:'';
  position:absolute;
  top:-50%;right:-20%;
  width:200px;height:200px;
  border-radius:50%;
  background:radial-gradient(circle,rgba(168,85,247,0.15),transparent);
  pointer-events:none;
}
.new-cta-text h3{font-family:'Baloo 2',cursive;font-size:1.15rem;margin-bottom:4px;color:var(--text);}
.new-cta-text p{color:var(--muted);font-size:.83rem;}
.cta-btn{
  padding:12px 22px;border-radius:50px;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;font-weight:600;font-size:.88rem;
  border:none;cursor:pointer;
  display:flex;align-items:center;gap:8px;
  white-space:nowrap;flex-shrink:0;
  box-shadow:0 6px 24px var(--glow);
  transition:all .35s;
}
.cta-btn:hover{transform:translateY(-2px);box-shadow:0 10px 36px var(--glow);}

/* UPLOAD */
.upload-area{
  background:rgba(20,14,50,0.5);
  backdrop-filter:blur(12px);
  border:1.5px dashed rgba(124,58,237,0.4);
  border-radius:16px;
  padding:32px;
  text-align:center;
  cursor:pointer;
  transition:all .3s;
  display:none;
  margin-bottom:16px;
  position:relative;
}
.upload-area.show{display:block;}
.upload-area:hover{border-color:var(--p2);box-shadow:0 8px 32px var(--glow2);}
.upload-area input{position:absolute;inset:0;opacity:0;cursor:pointer;}
.upload-preview{width:100%;max-height:180px;object-fit:cover;border-radius:12px;margin-bottom:12px;display:none;}

.gen-btn{
  width:100%;padding:14px;
  border-radius:50px;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;font-weight:700;
  border:none;cursor:pointer;
  font-size:.95rem;
  display:none;
  align-items:center;justify-content:center;gap:8px;
  margin-bottom:16px;
  box-shadow:0 8px 32px var(--glow);
  transition:all .3s;
}
.gen-btn:hover{transform:translateY(-2px);box-shadow:0 12px 42px var(--glow);}
.gen-btn.show{display:flex;}

/* PAGES GRID */
.sec-title{
  font-family:'Baloo 2',cursive;
  font-size:1.15rem;font-weight:800;
  margin-bottom:16px;
  background:linear-gradient(135deg,var(--text),var(--p3));
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  background-clip:text;
}
.pages-grid{display:grid;grid-template-columns:1fr;gap:16px;}
@media(min-width:500px){.pages-grid{grid-template-columns:repeat(2,1fr);}}
@media(min-width:800px){.pages-grid{grid-template-columns:repeat(3,1fr);}}

.pg-card{
  background:var(--card);
  backdrop-filter:blur(16px);
  border:1px solid var(--border);
  border-radius:18px;
  overflow:hidden;
  transition:all .35s;
  position:relative;
}
.pg-card::before{
  content:'';
  position:absolute;
  inset:0;
  background:linear-gradient(135deg,rgba(124,58,237,0.04),transparent);
  pointer-events:none;
  z-index:0;
}
.pg-card:hover{
  border-color:var(--p2);
  transform:translateY(-5px);
  box-shadow:0 16px 48px var(--glow2);
}
.pg-thumb{
  height:120px;
  background:linear-gradient(135deg,rgba(124,58,237,0.2),rgba(168,85,247,0.1));
  display:flex;align-items:center;justify-content:center;
  font-size:3.5rem;
  position:relative;
}
.pg-badge{
  position:absolute;top:8px;right:8px;
  background:rgba(124,58,237,0.8);
  color:#fff;
  padding:4px 10px;border-radius:20px;
  font-size:.68rem;font-weight:600;
  backdrop-filter:blur(8px);
  border:1px solid rgba(168,85,247,0.3);
}
.pg-info{padding:16px;position:relative;z-index:1;}
.pg-name{font-weight:700;font-size:.95rem;margin-bottom:4px;color:var(--text);}
.pg-type{color:var(--muted);font-size:.72rem;margin-bottom:12px;text-transform:capitalize;}
.pg-actions{display:flex;gap:5px;flex-wrap:wrap;}
.pg-btn{
  flex:1;padding:8px;
  border-radius:10px;
  border:1px solid var(--border);
  background:rgba(124,58,237,0.06);
  color:var(--muted);
  cursor:pointer;
  font-size:.7rem;
  display:flex;align-items:center;justify-content:center;gap:4px;
  transition:all .25s;font-weight:500;
}
.pg-btn:hover{border-color:var(--p2);color:var(--p3);background:rgba(124,58,237,0.18);}
.pg-btn.del-btn:hover{border-color:rgba(239,68,68,.5);color:#f87171;background:rgba(239,68,68,.08);}
.pg-btn.view{background:rgba(124,58,237,0.15);border-color:rgba(124,58,237,0.4);color:var(--p3);}
.pg-btn.ai-edit-btn{background:rgba(99,102,241,0.12);border-color:rgba(99,102,241,0.3);color:#a5b4fc;}
.pg-btn.ai-edit-btn:hover{border-color:#818cf8;background:rgba(99,102,241,0.22);}

/* WALLET BAR */
.wallet-bar{
  background:linear-gradient(135deg,rgba(124,58,237,0.3),rgba(168,85,247,0.2));
  backdrop-filter:blur(16px);
  border:1px solid rgba(124,58,237,0.35);
  border-radius:18px;
  padding:20px 24px;
  margin-bottom:24px;
  display:flex;align-items:center;justify-content:space-between;
  gap:12px;flex-wrap:wrap;
  position:relative;overflow:hidden;
  box-shadow:0 8px 32px rgba(124,58,237,0.15);
}
.wallet-bar::before{
  content:'';
  position:absolute;top:-50%;right:-10%;
  width:250px;height:250px;border-radius:50%;
  background:radial-gradient(circle,rgba(168,85,247,0.2),transparent);
  pointer-events:none;
}
.wal-bal{
  font-family:'Baloo 2',cursive;
  font-size:2rem;font-weight:800;
  background:linear-gradient(135deg,#fff,var(--p3));
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  background-clip:text;
}
.wal-label{font-size:.72rem;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1.2px;margin-bottom:4px;}
.wal-btn{
  padding:10px 20px;border-radius:50px;
  background:rgba(255,255,255,.1);
  border:1px solid rgba(255,255,255,.25);
  color:#fff;font-weight:600;font-size:.84rem;
  cursor:pointer;transition:all .3s;
  backdrop-filter:blur(8px);
}
.wal-btn:hover{background:rgba(255,255,255,.2);box-shadow:0 6px 20px rgba(0,0,0,.2);}

/* BOTTOM NAV */
.bot-nav{
  position:fixed;bottom:0;left:0;right:0;
  background:rgba(5,5,15,0.82);
  backdrop-filter:blur(20px);
  border-top:1px solid var(--border);
  display:flex;z-index:200;
}
.bot-nav a{
  flex:1;display:flex;flex-direction:column;
  align-items:center;justify-content:center;
  gap:3px;padding:10px 4px;
  color:var(--muted);font-size:.58rem;font-weight:500;
  transition:all .25s;
}
.bot-nav a:hover{color:var(--p3);}
.bot-nav a.on{color:var(--p3);}
.bot-nav a.on i{filter:drop-shadow(0 0 6px var(--p2));}
.bot-nav a i{font-size:1.1rem;}

/* MODAL */
.overlay{
  position:fixed;inset:0;
  background:rgba(0,0,0,.75);
  z-index:500;
  display:flex;align-items:flex-end;justify-content:center;
  backdrop-filter:blur(8px);
}
.modal{
  background:rgba(13,13,31,0.95);
  backdrop-filter:blur(20px);
  border:1px solid var(--border);
  border-radius:24px 24px 0 0;
  padding:28px 22px;
  width:100%;max-height:88svh;overflow-y:auto;
}
@media(min-width:600px){.overlay{align-items:center;padding:20px;}.modal{border-radius:24px;max-width:480px;}}
.modal h3{font-family:'Baloo 2',cursive;font-size:1.25rem;margin-bottom:14px;color:var(--text);}
.modal-close{
  float:right;background:rgba(124,58,237,0.15);
  border:1px solid var(--border);
  color:var(--muted);font-size:1rem;
  cursor:pointer;border-radius:8px;
  width:30px;height:30px;
  display:flex;align-items:center;justify-content:center;
}
.menu-category{
  border:1px solid var(--border);
  border-radius:16px;
  background:rgba(20,14,50,0.6);
  padding:14px;margin-bottom:14px;
}
.menu-category .menu-cat-header{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:12px;}
.menu-category .menu-cat-name{flex:1;}
.menu-items-list{display:grid;gap:10px;}
.menu-item-row{display:grid;grid-template-columns:1fr 140px 44px;gap:10px;align-items:center;}
.menu-item-row input{margin-bottom:0;}

.inp{
  width:100%;padding:12px 14px;
  border-radius:12px;
  background:rgba(20,14,50,0.7);
  border:1px solid var(--border);
  color:var(--text);
  font-family:'Poppins',sans-serif;
  font-size:.92rem;margin-bottom:10px;
  transition:border-color .25s;
}
.inp:focus{outline:none;border-color:var(--p2);box-shadow:0 0 0 3px rgba(124,58,237,0.15);}

.btn-p{
  width:100%;padding:13px;
  border-radius:50px;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;font-weight:700;
  border:none;cursor:pointer;
  font-size:.9rem;margin-bottom:8px;
  box-shadow:0 6px 24px var(--glow);
  transition:all .3s;
}
.btn-p:hover{transform:translateY(-1px);box-shadow:0 10px 32px var(--glow);}
.btn-s{
  width:100%;padding:11px;
  border-radius:50px;
  background:rgba(124,58,237,0.1);
  border:1px solid var(--border);
  color:var(--text);font-weight:600;
  cursor:pointer;font-size:.88rem;
  transition:all .3s;
}
.btn-s:hover{background:rgba(124,58,237,0.2);}

/* Share modal */
.soc-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:12px 0;}
.soc-btn{display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:10px;color:#fff;font-weight:600;font-size:.8rem;text-decoration:none;}

/* AI Edit Modal */
.ai-edit-prompt{
  width:100%;padding:13px 15px;border-radius:12px;
  background:rgba(20,14,50,0.8);
  border:1.5px solid var(--border);
  color:var(--text);
  font-family:'Poppins',sans-serif;
  font-size:.9rem;resize:vertical;min-height:100px;line-height:1.5;
}
.ai-edit-prompt:focus{outline:none;border-color:var(--p2);box-shadow:0 0 0 3px rgba(124,58,237,0.12);}
.ai-preview-frame{width:100%;height:420px;border:1px solid var(--border);border-radius:12px;background:#fff;}
.ai-step{display:none;}.ai-step.active{display:block;}
.tag-btn{
  display:inline-block;padding:6px 12px;
  border-radius:8px;
  background:rgba(124,58,237,0.12);
  border:1px solid rgba(124,58,237,0.25);
  color:var(--p3);font-size:.75rem;cursor:pointer;
  margin:4px 4px 4px 0;transition:all .2s;
}
.tag-btn:hover{background:rgba(124,58,237,0.25);}
.ai-step-indicator{display:flex;align-items:center;gap:8px;margin-bottom:20px;}
.ai-dot{
  width:28px;height:28px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:.75rem;font-weight:700;
  border:2px solid var(--border);color:var(--muted);
}
.ai-dot.done{background:var(--p);border-color:var(--p2);color:#fff;}
.ai-dot.active{background:rgba(124,58,237,0.15);border-color:var(--p2);color:var(--p3);}
.ai-dot-line{flex:1;height:2px;background:var(--border);}
.ai-dot-line.done{background:linear-gradient(90deg,var(--p),var(--p2));}
@keyframes sp{to{transform:rotate(360deg);}}

/* TOASTS */
.toast{
  position:fixed;bottom:76px;left:50%;transform:translateX(-50%);
  background:rgba(13,13,31,0.95);
  backdrop-filter:blur(16px);
  border:1px solid var(--border);
  padding:10px 20px;border-radius:50px;
  font-size:.82rem;z-index:999;white-space:nowrap;
  box-shadow:0 8px 24px rgba(0,0,0,.3);
}
.tok{border-color:rgba(16,185,129,.4);color:#34d399;}
.ter{border-color:rgba(239,68,68,.4);color:#f87171;}
.tin{border-color:rgba(124,58,237,.4);color:var(--p3);}

/* Theme Toggle */
.theme-toggle{
  width:38px;height:38px;border-radius:10px;
  background:rgba(124,58,237,0.12);
  border:1px solid var(--border);
  color:var(--p3);cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  font-size:.9rem;transition:all .3s;
}
.theme-toggle:hover{background:rgba(124,58,237,0.3);box-shadow:0 0 16px var(--glow2);}
</style>
</head>
<body>

<!-- ── GLOBAL AI PROGRESS BAR ───────────────────────────────────────────── -->
<div id="pw-ai-progress-wrap" style="display:none;position:fixed;top:0;left:0;right:0;z-index:99999;pointer-events:none;">
  <div style="height:3px;background:rgba(124,58,237,.15);">
    <div id="pw-ai-progress-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#7C3AED,#A855F7,#C084FC);background-size:200% 100%;animation:pw-shimmer 1.4s linear infinite;transition:width .35s ease;"></div>
  </div>
  <div id="pw-ai-progress-label" style="background:rgba(124,58,237,.92);backdrop-filter:blur(8px);color:#fff;font-size:.72rem;font-weight:600;padding:5px 14px 5px 12px;display:inline-flex;align-items:center;gap:7px;border-radius:0 0 10px 0;letter-spacing:.02em;white-space:nowrap;">
    <span style="display:inline-block;width:10px;height:10px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:pw-spin .8s linear infinite;"></span>
    <span id="pw-ai-progress-text">AI kaam kar raha hai...</span>
    <span id="pw-ai-progress-pct" style="margin-left:4px;opacity:.8;font-size:.68rem;">0%</span>
  </div>
</div>
<style>
@keyframes pw-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
@keyframes pw-spin{to{transform:rotate(360deg)}}
</style>
<script>
// ── Global AI progress bar controller ─────────────────────────────────────
window.pwProgress = (function(){
  let _interval=null, _pct=0;
  const wrap=()=>document.getElementById('pw-ai-progress-wrap');
  const bar=()=>document.getElementById('pw-ai-progress-bar');
  const txt=()=>document.getElementById('pw-ai-progress-text');
  const pct=()=>document.getElementById('pw-ai-progress-pct');
  function _set(p,msg){
    _pct=Math.min(Math.max(p,0),100);
    if(bar()){bar().style.width=_pct+'%';}
    if(pct()){pct().textContent=Math.round(_pct)+'%';}
    if(msg&&txt()){txt().textContent=msg;}
  }
  return {
    start:function(msg,startPct){
      if(_interval){clearInterval(_interval);_interval=null;}
      _pct=startPct||5;
      if(wrap()){wrap().style.display='block';}
      _set(_pct,msg||'AI kaam kar raha hai...');
      _interval=setInterval(function(){
        // Crawl slowly toward 90 — never auto-complete
        var step=_pct<50?3:_pct<75?1.5:0.6;
        _set(Math.min(_pct+step*(0.5+Math.random()),90),null);
      },800);
    },
    update:function(p,msg){_set(p,msg);},
    done:function(msg){
      if(_interval){clearInterval(_interval);_interval=null;}
      _set(100,msg||'Ho gaya! ✅');
      setTimeout(function(){if(wrap()){wrap().style.display='none';}},900);
    },
    error:function(msg){
      if(_interval){clearInterval(_interval);_interval=null;}
      if(bar()){bar().style.background='#ef4444';}
      _set(_pct,msg||'Kuch gadbad! ❌');
      setTimeout(function(){if(wrap()){wrap().style.display='none';}if(bar()){bar().style.background='';}},2000);
    }
  };
})();
</script>

<nav>
  <div class="nav-in">
    <a href="<?= siteUrl('') ?>" class="logo">🦅 Poster<span>Wall</span></a>
    <div class="nav-r">
      <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">🌙</button>
      <img src="<?= htmlspecialchars($user['avatar']??'') ?>" class="nav-av" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=FF6B00&color=fff'">
      <a href="<?= siteUrl('auth/logout.php') ?>" class="logout-btn">Logout</a>
    </div>
  </div>
</nav>

<div class="con">
  <div class="page-title">Namaste, <?= htmlspecialchars(explode(' ',$user['name'])[0]) ?>! 👋
  <?php if($power && $user['role']!=='admin'): ?><span style="display:inline-block;background:linear-gradient(135deg,#a855f7,#7c3aed);color:#fff;font-size:.75rem;padding:4px 12px;border-radius:999px;font-family:'Poppins',sans-serif;font-weight:600;vertical-align:middle;margin-left:6px;">⚡ Power User</span><?php endif; ?>
  </div>
  <div class="page-sub">Apne saare digital pages manage karo<?php if($power): ?> — <strong style="color:#a855f7;">Unlimited Access Active</strong><?php endif; ?></div>

  <!-- Wallet -->
  <?php if($power): ?>
  <div class="wallet-bar" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">
    <div>
      <div class="wal-label">Access Status</div>
      <div class="wal-bal" style="font-size:1.4rem;">⚡ Unlimited Access</div>
    </div>
    <div style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:#fff;padding:8px 14px;border-radius:9px;font-size:.8rem;font-weight:600;">No Charges</div>
  </div>
  <?php else: ?>
  <div class="wallet-bar">
    <div>
      <div class="wal-label">Wallet Balance</div>
      <div class="wal-bal">₹<?= number_format($bal,2) ?></div>
    </div>
    <button class="wal-btn" onclick="document.getElementById('pay-modal').style.display='flex'">+ Recharge</button>
  </div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="stats">
    <div class="stat-c"><div class="stat-label">My Pages</div><div class="stat-val"><?= $total ?></div></div>
    <div class="stat-c b"><div class="stat-label">Total Views</div><div class="stat-val"><?= number_format($views) ?></div></div>
    <div class="stat-c g"><div class="stat-label">Generations</div><div class="stat-val"><?= $gens ?></div></div>
    <div class="stat-c b"><div class="stat-label">Orders</div><div class="stat-val"><a href="<?= siteUrl('dashboard/orders.php') ?>" style="color:inherit;"><?= $ordersCount ?></a></div></div>
    <div class="stat-c y"><div class="stat-label">Cost/Page</div><div class="stat-val"><?= $power ? '₹0' : '₹9' ?></div></div>
  </div>

  <!-- New Page -->
  <div class="new-cta" id="new-page-cta">
    <div class="new-cta-text">
      <h3>📸 Naya Digital Page Banao</h3>
      <p>Photo upload karo — AI 30 seconds mein beautiful page banayega!</p>
    </div>
    <a href="<?= siteUrl('dashboard/create.php') ?>" class="cta-btn"><i class="fas fa-plus"></i> <?= $power ? 'New Page (Free)' : 'New Page (₹9)' ?></a>
  </div>

  <!-- Upload Area -->
  <div class="upload-area" id="upload-area">
    <input type="file" id="photo-inp" accept="image/*" onchange="handlePhoto(event)">
    <img id="up-prev" class="upload-preview">
    <div id="up-hint"><div style="font-size:2.2rem;margin-bottom:8px;">📸</div><div style="font-weight:600;margin-bottom:4px;">Dukaan/Dhaba ki photo upload karo</div><div style="color:var(--muted);font-size:.8rem;">Click ya photo khींcho</div></div>
  </div>

  <div id="gen-status" style="display:none;margin:16px 0;">
    <div style="background:rgba(124,58,237,.15);border:1px solid rgba(124,58,237,.2);border-radius:999px;overflow:hidden;height:10px;">
      <div id="gen-status-bar" style="width:0%;height:100%;background:linear-gradient(90deg,#7C3AED,#A855F7,#C084FC);transition:width .3s ease;"></div>
    </div>
    <div id="gen-status-text" style="margin-top:10px;color:var(--p3);font-size:.9rem;line-height:1.4;">AI page ban raha hai...</div>
  </div>


  <button class="gen-btn" id="gen-btn" onclick="generate()"><i class="fas fa-magic"></i> <?= $power ? 'AI se Page Banao — Free!' : 'AI se Page Banao — ₹9' ?></button>

  <!-- My Pages -->
  <div class="sec-title">🖼️ Mere Pages</div>

  <?php if($pages->num_rows > 0):
    $pageData = [];
  ?>
  <div class="pages-grid">
    <?php while($pg=$pages->fetch_assoc()):
      $icon  = $typeIcons[$pg['business_type']] ?? '🦅';
      $url   = SITE_URL . '/p/' . $pg['token'];
      $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data='.urlencode($url).'&color=A855F7&bgcolor=05050f';
      $pageData[$pg['token']] = [
          'business_name' => $pg['business_name'] ?? 'My Page',
          'menu_items' => json_decode($pg['menu_items'] ?? 'null', true) ?: []
      ];
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
          <button class="pg-btn ai-edit-btn" onclick="openAiEdit('<?= $pg['token'] ?>','<?= addslashes($pg['business_name']) ?>')" title="Edit with AI"><i class="fas fa-wand-magic-sparkles"></i> AI Edit</button>
          <button class="pg-btn" onclick="openMenuEditor('<?= $pg['token'] ?>')" title="Edit menu"><i class="fas fa-utensils"></i></button>
          <button class="pg-btn" onclick="regenPage('<?= $pg['token'] ?>')" title="Regenerate"><i class="fas fa-sync"></i></button>
          <button class="pg-btn del-btn" onclick="confirmDelete('<?= $pg['token'] ?>','<?= addslashes($pg['business_name'] ?: 'My Page') ?>')" title="Delete page"><i class="fas fa-trash"></i></button>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>

  <?php else: ?>
  <div style="text-align:center;padding:60px 20px;color:var(--muted);">
    <div style="font-size:3.5rem;margin-bottom:16px;filter:drop-shadow(0 0 16px rgba(168,85,247,.4));">📸</div>
    <div style="font-family:'Baloo 2',cursive;font-size:1.2rem;font-weight:800;margin-bottom:8px;background:linear-gradient(135deg,#fff,var(--p3));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Koi Page Nahi Hai</div>
    <div style="font-size:.875rem;margin-bottom:20px;">Pehla digital page banao — sirf ₹9 mein!</div>
    <a href="<?= siteUrl('dashboard/create.php') ?>" class="cta-btn" style="margin:0 auto;"><i class="fas fa-magic"></i> Abhi Banao</a>
  </div>
  <?php endif; ?>
  <?php if (!empty($pageData)): ?>
    <script>window.pageMenuData = <?= json_encode($pageData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
  <?php endif; ?>
</div>

<!-- Bottom Nav -->
<nav class="bot-nav">
  <a href="<?= siteUrl('dashboard/') ?>" class="on"><i class="fas fa-home"></i>Home</a>
  <a href="<?= siteUrl('dashboard/orders.php') ?>"><i class="fas fa-shopping-bag"></i>Orders</a>
  <a href="<?= siteUrl('dashboard/create.php') ?>"><i class="fas fa-plus-circle"></i>New Page</a>
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

<!-- MENU EDIT MODAL -->
<div id="menu-modal" class="overlay" style="display:none;">
  <div class="modal">
    <button class="modal-close" onclick="closeMenuModal()">✕</button>
    <h3>📝 Edit Menu</h3>
    <p style="color:var(--muted);font-size:.9rem;margin-bottom:18px;">Menu items ko add, update, ya delete karo. Public page par latest menu aapke changes ke saath dikhega.</p>
    <div id="menu-editor"></div>
    <button class="btn-s" type="button" onclick="addCategory()">+ Add Category</button>
    <div style="display:flex;gap:10px;margin-top:18px;flex-wrap:wrap;">
      <button class="btn-p" id="menu-save-btn" type="button" onclick="saveMenu()">Save Menu</button>
      <button class="btn-s" type="button" onclick="closeMenuModal()">Cancel</button>
    </div>
  </div>
</div>

<!-- AI EDIT MODAL -->
<div id="ai-edit-modal" class="overlay" style="display:none;">
  <div class="modal" style="max-width:560px;">
    <button class="modal-close" onclick="closeAiEdit()">✕</button>
    <h3>✨ AI se Page Edit Karo</h3>

    <!-- Step indicator -->
    <div class="ai-step-indicator">
      <div class="ai-dot active" id="ai-dot-1">1</div>
      <div class="ai-dot-line" id="ai-line-1"></div>
      <div class="ai-dot" id="ai-dot-2">2</div>
      <div class="ai-dot-line" id="ai-line-2"></div>
      <div class="ai-dot" id="ai-dot-3">3</div>
    </div>

    <!-- Step 1: Prompt -->
    <div class="ai-step active" id="ai-step-1">
      <p style="color:var(--muted);font-size:.85rem;margin-bottom:14px;">Apni bhasha mein batao kya badalna hai — AI aapka page update kar dega.</p>
      <textarea id="ai-prompt-input" class="ai-edit-prompt" placeholder="Jaise: 'Background colour nila karo', 'Font size bada karo', 'Header mein phone number add karo', 'Contact section hata do'..."></textarea>
      <div style="margin:10px 0 18px;">
        <div style="font-size:.72rem;color:var(--muted);margin-bottom:8px;">Quick suggestions:</div>
        <span class="tag-btn" onclick="appendPrompt('Background colour badlo aur dark theme banao')">🎨 Dark theme</span>
        <span class="tag-btn" onclick="appendPrompt('Font size bada karo aur readability improve karo')">🔤 Bada font</span>
        <span class="tag-btn" onclick="appendPrompt('Contact section mein ek WhatsApp button add karo')">📱 WhatsApp button</span>
        <span class="tag-btn" onclick="appendPrompt('Footer mein social media links add karo')">🔗 Social links</span>
        <span class="tag-btn" onclick="appendPrompt('Page ki colour scheme orange se green karo')">🟢 Green theme</span>
        <span class="tag-btn" onclick="appendPrompt('Header image ko full width banao')">🖼️ Full-width header</span>
      </div>
      <button class="btn-p" id="ai-preview-btn" onclick="requestAiPreview()"><i class="fas fa-wand-magic-sparkles"></i> Preview Generate Karo</button>
    </div>

    <!-- Step 2: Loading -->
    <div class="ai-step" id="ai-step-2">
      <div style="text-align:center;padding:40px 20px;">
        <div style="font-size:3rem;margin-bottom:16px;">✨</div>
        <div style="font-family:'Baloo 2',cursive;font-size:1.1rem;font-weight:800;margin-bottom:8px;" id="ai-loading-text">AI aapka page update kar raha hai...</div>
        <div style="color:var(--muted);font-size:.82rem;margin-bottom:20px;">Thoda intezaar karo — 15-30 seconds lagte hain</div>
        <div style="background:var(--bg2);border-radius:999px;overflow:hidden;height:8px;width:100%;max-width:300px;margin:0 auto;">
          <div id="ai-progress-bar" style="width:5%;height:100%;background:linear-gradient(90deg,#6366f1,#a78bfa);transition:width .4s ease;border-radius:999px;"></div>
        </div>
      </div>
    </div>

    <!-- Step 3: Preview & Accept -->
    <div class="ai-step" id="ai-step-3">
      <p style="color:var(--muted);font-size:.82rem;margin-bottom:10px;">Neeche preview dekho — pasand aaye to <strong>Save Changes</strong> dabao.</p>
      <iframe id="ai-preview-frame" class="ai-preview-frame" sandbox="allow-scripts allow-same-origin"></iframe>
      <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;">
        <button class="btn-p" id="ai-save-btn" onclick="saveAiChanges()" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);flex:1;">
          <i class="fas fa-check"></i> Haan, Save Karo!
        </button>
        <button class="btn-s" onclick="goBackToPrompt()" style="flex:1;">
          <i class="fas fa-arrow-left"></i> Wapas Jao
        </button>
      </div>
    </div>
  </div>
</div>

<!-- DELETE PAGE MODAL -->
<div id="delete-modal" class="overlay" style="display:none;">
  <div class="modal" style="max-width:420px;">
    <h3 style="margin-bottom:6px;color:#ef4444;">🗑️ Page Delete Karo</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:8px;">Kya aap sach mein <strong id="delete-modal-name" style="color:var(--text);"></strong> ko delete karna chahte hain?</p>
    <div style="background:rgba(239,68,68,.08);border:1.5px solid rgba(239,68,68,.25);border-radius:12px;padding:14px 16px;font-size:.82rem;color:var(--muted);margin-bottom:20px;line-height:1.5;">
      ⚠️ <strong style="color:#ef4444;">Yeh action undo nahi hoga.</strong> Page aur uska link hamesha ke liye delete ho jaayega. Views aur data bhi hata diye jaayenge.
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <button id="delete-confirm-btn" onclick="deletePage()" style="flex:1;padding:13px;border-radius:10px;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;font-weight:700;border:none;cursor:pointer;font-size:.9rem;display:flex;align-items:center;justify-content:center;gap:8px;"><i class="fas fa-trash"></i> Haan, Delete Karo</button>
      <button onclick="document.getElementById('delete-modal').style.display='none'" style="flex:1;padding:11px;border-radius:10px;background:rgba(255,255,255,.06);border:1px solid var(--border);color:var(--text);font-weight:600;cursor:pointer;font-size:.88rem;">Cancel</button>
    </div>
  </div>
</div>

<!-- REGEN MODAL -->
<!-- CONFIRM BUSINESS MODAL -->
<div id="confirm-modal" class="overlay" style="display:none;">
  <div class="modal" style="max-width:460px;">
    <h3 style="margin-bottom:6px;">✅ Business Verify Karo</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:18px;">AI ne neeche diya naam detect kiya hai. Kya aapka business isi naam se jaana jaata hai?</p>

    <label style="font-size:.8rem;font-weight:600;color:var(--muted);display:block;margin-bottom:6px;">Business Name <span style="color:#e05a00;">*</span></label>
    <input id="confirm-name-inp" class="inp" type="text" placeholder="Business ka naam" style="margin-bottom:16px;">

    <label style="font-size:.8rem;font-weight:600;color:var(--muted);display:block;margin-bottom:6px;">📱 Mobile Number <span style="color:var(--muted);font-weight:400;">(optional — log mobile se bhi dhundh sakenge)</span></label>
    <input id="confirm-mob-inp" class="inp" type="tel" placeholder="10-digit mobile (optional)" maxlength="10" style="margin-bottom:6px;">
    <p id="confirm-validation-msg" style="color:#e05a00;font-size:.8rem;min-height:18px;margin-bottom:14px;display:none;"></p>

    <div style="background:rgba(255,107,0,.07);border:1px solid rgba(255,107,0,.2);border-radius:12px;padding:12px 14px;font-size:.82rem;color:var(--muted);margin-bottom:18px;line-height:1.5;">
      💡 <strong style="color:var(--text);">Name se dhundhe jaoge:</strong> Customers aapko business name se search kar sakenge.<br>
      💡 <strong style="color:var(--text);">Mobile se dhundhe jaoge:</strong> Customers aapko number dalke bhi page khol sakenge.
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <button class="btn-p" id="confirm-save-btn" onclick="confirmAndSave()" style="flex:1;">
        <i class="fas fa-check"></i> Haan, Save Karo
      </button>
      <button class="btn-s" onclick="cancelConfirm()" style="flex:1;">Cancel</button>
    </div>
  </div>
</div>

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
      <i class="fas fa-sync"></i> Regenerate (<?= $power ? 'Free' : '₹9' ?>)
    </button>
  </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
let photoB64=null,photoMime=null,regenToken=null,regenB64=null,regenMime=null;
const USER_NAME='<?= addslashes($user['name']) ?>', USER_EMAIL='<?= addslashes($user['email']) ?>';
let bal=<?= $bal ?>;
const IS_POWER_USER=<?= $power ? 'true' : 'false' ?>;

function toggleUpload(){
  const a=document.getElementById('upload-area');
  const g=document.getElementById('gen-btn');
  const show=!a.classList.contains('show');
  a.classList.toggle('show',show);
  if(!show && g){g.classList.remove('show');}
  if(show){
    setGenStatus(0,'Photo upload karo aur fir "AI se Page Banao" dabao');
    document.getElementById('gen-status').style.display='block';
    document.getElementById('gen-status-bar').style.width='0%';
    a.scrollIntoView({behavior:'smooth',block:'center'});
  }
}

function openNewPage(){
  const a=document.getElementById('upload-area');
  const newCta=document.getElementById('new-page-cta');
  if(!a.classList.contains('show')){
    a.classList.add('show');
    setGenStatus(0,'Photo upload karo aur fir "AI se Page Banao" dabao');
    document.getElementById('gen-status').style.display='block';
    document.getElementById('gen-status-bar').style.width='0%';
  }
  const target = newCta || a;
  target.scrollIntoView({behavior:'smooth', block:'start'});
  target.style.transition='box-shadow .4s';
  target.style.boxShadow='0 0 0 3px rgba(255,107,0,.55)';
  setTimeout(()=>{ target.style.boxShadow=''; }, 1400);
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

let pendingKey = null;

function generate(){
  if(!photoB64){toast('Photo upload karo!','ter');return;}
  if(!IS_POWER_USER && bal<9){document.getElementById('pay-modal').style.display='flex';return;}
  const btn=document.getElementById('gen-btn');
  btn.disabled=true; btn.innerHTML='<div class="spin"></div> AI Page Bana Raha Hai...';
  setGenStatus(30,'AI abhi aapka page bana raha hai — thoda intezaar karo');
  // Start global top progress bar
  pwProgress.start('📸 Photo se page ban raha hai...', 10);
  let progress=30;
  genProgressInterval=setInterval(()=>{
    progress = Math.min(progress + Math.random() * 8, 90);
    setGenStatus(progress, 'AI page banta ja raha hai — bas thoda aur');
  }, 900);

  fetch('<?= SITE_URL ?>/api/generate.php',{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({image_base64:photoB64,mime_type:photoMime})
  }).then(r=>r.json()).then(res=>{
    if(genProgressInterval){clearInterval(genProgressInterval);genProgressInterval=null;}
    btn.disabled=false; btn.innerHTML='<i class="fas fa-magic"></i> <?= $power ? 'AI se Page Banao — Free!' : 'AI se Page Banao — ₹9' ?>';
    resetGenStatus();
    if(res.pending){
      pwProgress.done('Page taiyar hai! ✅');
      // Show confirmation modal
      pendingKey = res.pending_key;
      document.getElementById('confirm-name-inp').value = res.detected_name || '';
      document.getElementById('confirm-mob-inp').value = '';
      document.getElementById('confirm-validation-msg').style.display = 'none';
      document.getElementById('confirm-modal').style.display = 'flex';
      return;
    }
    pwProgress.error('Kuch gadbad!');
    setGenStatus(0,'Kuch gadbad ho gayi — dobara try karo');
    if(res.error==='no_balance'){document.getElementById('pay-modal').style.display='flex';}
    else{toast(res.error||'Kuch gadbad!','ter');}
  }).catch(()=>{
    if(genProgressInterval){clearInterval(genProgressInterval);genProgressInterval=null;}
    btn.disabled=false; btn.innerHTML='<i class="fas fa-magic"></i> <?= $power ? 'AI se Page Banao — Free!' : 'AI se Page Banao — ₹9' ?>';
    resetGenStatus();
    pwProgress.error('Network error!');
    toast('Network error!','ter');
  });
}

function cancelConfirm(){
  document.getElementById('confirm-modal').style.display='none';
  pendingKey = null;
}

function confirmAndSave(){
  const name = document.getElementById('confirm-name-inp').value.trim();
  const mob  = document.getElementById('confirm-mob-inp').value.replace(/\D/g,'');
  const msg  = document.getElementById('confirm-validation-msg');

  if(!name && !mob){
    msg.textContent = 'Business name ya mobile number mein se ek zaroori hai.';
    msg.style.display = 'block';
    return;
  }
  if(mob && mob.length !== 10){
    msg.textContent = 'Valid 10-digit mobile number dalein.';
    msg.style.display = 'block';
    return;
  }
  msg.style.display = 'none';

  const btn = document.getElementById('confirm-save-btn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spin"></div> Save ho raha hai...';

  fetch('<?= SITE_URL ?>/api/confirm.php',{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({pending_key:pendingKey, business_name:name, mobile:mob})
  }).then(r=>r.json()).then(res=>{
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Haan, Save Karo';
    if(res.success){
      document.getElementById('confirm-modal').style.display='none';
      bal = res.balance;
      toast('Page save ho gaya! 🎉','tok');
      setTimeout(()=>window.location.href='<?= SITE_URL ?>/p/'+res.token, 600);
    } else {
      msg.textContent = res.msg || res.error || 'Save mein dikkat aayi.';
      msg.style.display = 'block';
      if(res.error==='no_balance'){document.getElementById('confirm-modal').style.display='none';document.getElementById('pay-modal').style.display='flex';}
      if(res.error==='expired'){document.getElementById('confirm-modal').style.display='none';toast('Session expire ho gaya — dobara try karo','ter');}
    }
  }).catch(()=>{
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Haan, Save Karo';
    msg.textContent = 'Network error — dobara try karo.';
    msg.style.display = 'block';
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
  if(!IS_POWER_USER && bal<9){document.getElementById('regen-modal').style.display='none';document.getElementById('pay-modal').style.display='flex';return;}
  const btn=document.getElementById('regen-btn');
  btn.disabled=true; btn.innerHTML='<div class="spin"></div> Regenerating...';
  pwProgress.start('🔄 Page regenerate ho raha hai...', 10);
  fetch('<?= SITE_URL ?>/api/generate.php',{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({image_base64:regenB64,mime_type:regenMime,regen_token:regenToken})
  }).then(r=>r.json()).then(res=>{
    if(res.success||res.pending){pwProgress.done('Done! ✅');toast('Page updated! ✅','tok');setTimeout(()=>location.reload(),1500);}
    else{pwProgress.error(res.error||'Error!');toast(res.error||'Error!','ter');}
    btn.disabled=false; btn.innerHTML='<i class="fas fa-sync"></i> Regenerate (₹9)';
  }).catch(()=>{
    pwProgress.error('Network error!');
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

let _deleteToken = null;
function confirmDelete(token, name){
  _deleteToken = token;
  document.getElementById('delete-modal-name').textContent = name;
  document.getElementById('delete-modal').style.display = 'flex';
}
function deletePage(){
  if(!_deleteToken) return;
  const btn = document.getElementById('delete-confirm-btn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spin"></div> Delete ho raha hai...';
  fetch('<?= SITE_URL ?>/api/delete_page.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({token: _deleteToken})
  }).then(r => r.json()).then(res => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-trash"></i> Haan, Delete Karo';
    document.getElementById('delete-modal').style.display = 'none';
    _deleteToken = null;
    if(res.success){
      toast('Page delete ho gaya! 🗑️','ter');
      setTimeout(() => location.reload(), 900);
    } else {
      toast(res.error || 'Delete nahi hua — dobara try karo','ter');
    }
  }).catch(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-trash"></i> Haan, Delete Karo';
    toast('Network error!','ter');
  });
}

function openMenuEditor(token){
  const data = window.pageMenuData?.[token] ?? {business_name:'Page', menu_items:[]};
  document.getElementById('menu-modal').style.display='flex';
  document.getElementById('menu-save-btn').disabled = false;
  document.getElementById('menu-editor').innerHTML = '';
  document.getElementById('menu-editor').appendChild(buildMenuEditor(data.menu_items));
  document.getElementById('menu-modal').dataset.token = token;
}

function closeMenuModal(){
  document.getElementById('menu-modal').style.display='none';
  document.getElementById('menu-editor').innerHTML = '';
  delete document.getElementById('menu-modal').dataset.token;
}

function buildMenuEditor(menuItems){
  const container = document.createElement('div');
  if (!Array.isArray(menuItems) || menuItems.length === 0) {
    const note = document.createElement('div');
    note.style.color = 'var(--muted)';
    note.style.marginBottom = '12px';
    note.textContent = 'Koi menu nahi mila. Naya category add karo aur items bhar do.';
    container.appendChild(note);
  }
  if (Array.isArray(menuItems)) {
    menuItems.forEach(section => container.appendChild(createCategoryCard(section)));
  }
  return container;
}

function createCategoryCard(section = {}){
  const card = document.createElement('div');
  card.className = 'menu-category';
  card.innerHTML = `<div class="menu-cat-header"><input type="text" class="inp menu-cat-name" placeholder="Category name" value="${escapeHtml(section.category || 'Menu')}"><button class="btn-s" type="button" onclick="removeCategory(this)">Delete</button></div><div class="menu-items-list"></div><button class="btn-s" type="button" onclick="addMenuItem(this)">+ Add item</button>`;
  const list = card.querySelector('.menu-items-list');
  if (Array.isArray(section.items)) {
    section.items.forEach(item => list.appendChild(createItemRow(item)));
  }
  return card;
}

function createItemRow(item = {}){
  const row = document.createElement('div');
  row.className = 'menu-item-row';
  row.innerHTML = `<input type="text" class="inp" placeholder="Item name" value="${escapeHtml(item.name || '')}"><input type="text" class="inp" placeholder="Price" value="${escapeHtml(item.price || '')}"><button class="btn-s" type="button" onclick="removeMenuItem(this)">✕</button>`;
  return row;
}

function addCategory(){
  const editor = document.getElementById('menu-editor');
  editor.appendChild(createCategoryCard({category:'Menu', items:[]}));
}

function addMenuItem(button){
  const card = button.closest('.menu-category');
  const list = card.querySelector('.menu-items-list');
  list.appendChild(createItemRow({name:'', price:''}));
}

function removeCategory(button){
  const card = button.closest('.menu-category');
  if (card) { card.remove(); }
}

function removeMenuItem(button){
  const row = button.closest('.menu-item-row');
  if (row) { row.remove(); }
}

function escapeHtml(value){
  return String(value || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function collectMenuData(){
  const categories = [];
  document.querySelectorAll('#menu-editor .menu-category').forEach(card => {
    const categoryName = card.querySelector('.menu-cat-name')?.value.trim() || 'Menu';
    const items = [];
    card.querySelectorAll('.menu-item-row').forEach(row => {
      const inputs = row.querySelectorAll('input');
      const name = inputs[0]?.value.trim() || '';
      const price = inputs[1]?.value.trim() || '';
      if (name !== '') {
        items.push({name, price: price !== '' ? price : null});
      }
    });
    if (items.length > 0) {
      categories.push({category: categoryName, items});
    }
  });
  return categories;
}

function saveMenu(){
  const token = document.getElementById('menu-modal').dataset.token;
  if (!token) { return; }
  const menuItems = collectMenuData();
  const btn = document.getElementById('menu-save-btn');
  btn.disabled = true;
  btn.textContent = 'Saving...';
  fetch('<?= SITE_URL ?>/api/menu.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({action:'save', token, menu_items: menuItems})
  }).then(r => r.json()).then(res => {
    btn.disabled = false;
    btn.textContent = 'Save Menu';
    if (res.success) {
      window.pageMenuData = window.pageMenuData || {};
      window.pageMenuData[token] = {business_name: window.pageMenuData[token]?.business_name || 'Page', menu_items: res.menu_items};
      toast('Menu saved successfully!','tok');
      closeMenuModal();
    } else {
      toast(res.error || 'Could not save menu', 'ter');
    }
  }).catch(() => {
    btn.disabled = false;
    btn.textContent = 'Save Menu';
    toast('Network error while saving menu', 'ter');
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


// ===== AI PAGE EDIT =====
let aiEditToken = null;
let aiEditBizName = null;
let aiModifiedHtml = null;
let aiProgressInterval = null;

function openAiEdit(token, bizName) {
  aiEditToken = token;
  aiEditBizName = bizName;
  aiModifiedHtml = null;
  document.getElementById('ai-prompt-input').value = '';
  document.getElementById('ai-preview-frame').srcdoc = '';
  setAiStep(1);
  document.getElementById('ai-edit-modal').style.display = 'flex';
}

function closeAiEdit() {
  document.getElementById('ai-edit-modal').style.display = 'none';
  if (aiProgressInterval) { clearInterval(aiProgressInterval); aiProgressInterval = null; }
  aiEditToken = null;
  aiModifiedHtml = null;
}

function appendPrompt(text) {
  const inp = document.getElementById('ai-prompt-input');
  inp.value = (inp.value.trim() ? inp.value.trim() + '\n' : '') + text;
  inp.focus();
}

function setAiStep(step) {
  [1,2,3].forEach(n => {
    document.getElementById('ai-step-'+n).classList.toggle('active', n===step);
    const dot = document.getElementById('ai-dot-'+n);
    dot.classList.toggle('active', n===step);
    dot.classList.toggle('done', n<step);
  });
  [1,2].forEach(n => {
    const line = document.getElementById('ai-line-'+n);
    if (line) line.classList.toggle('done', n<step);
  });
}

function requestAiPreview() {
  const prompt = document.getElementById('ai-prompt-input').value.trim();
  if (!prompt) { toast('Kuch toh batao kya badalna hai!','ter'); return; }
  if (!aiEditToken) return;

  setAiStep(2);
  // Start global top progress bar
  pwProgress.start('✨ AI page edit kar raha hai...', 5);

  // Animated progress bar
  let prog = 5;
  document.getElementById('ai-progress-bar').style.width = '5%';
  const loadingMessages = [
    'AI aapka page padh raha hai...',
    'Changes plan kar raha hai...',
    'HTML update ho raha hai...',
    'Final touches lag rahe hain...'
  ];
  let msgIdx = 0;
  aiProgressInterval = setInterval(() => {
    prog = Math.min(prog + (Math.random() * 6), 88);
    document.getElementById('ai-progress-bar').style.width = prog + '%';
    pwProgress.update(prog, loadingMessages[msgIdx]);
    if (prog > 25 * (msgIdx + 1) && msgIdx < loadingMessages.length - 1) {
      msgIdx++;
      document.getElementById('ai-loading-text').textContent = loadingMessages[msgIdx];
    }
  }, 600);

  fetch('<?= SITE_URL ?>/api/ai_edit.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'preview', token: aiEditToken, prompt: prompt})
  })
  .then(r => r.json())
  .then(res => {
    clearInterval(aiProgressInterval); aiProgressInterval = null;
    document.getElementById('ai-progress-bar').style.width = '100%';

    if (res.success && res.modified_html) {
      pwProgress.done('Preview taiyar! ✅');
      aiModifiedHtml = res.modified_html;
      setTimeout(() => {
        setAiStep(3);
        const frame = document.getElementById('ai-preview-frame');
        frame.srcdoc = aiModifiedHtml;
      }, 400);
    } else {
      const msg = res.error === 'page_not_found' ? 'Page nahi mila!'
                : res.error === 'missing_params'  ? 'Prompt likhna zaroori hai!'
                : res.error === 'ai_failed'        ? 'AI service error — thodi der baad try karo'
                : res.error === 'empty_response'   ? 'AI khaali jawab diya — prompt aur clear karo'
                : 'Kuch gadbad — dobara try karo (' + (res.error||'unknown') + ')';
      pwProgress.error(msg);
      toast(msg, 'ter');
      setAiStep(1);
    }
  })
  .catch(err => {
    clearInterval(aiProgressInterval); aiProgressInterval = null;
    pwProgress.error('Network error!');
    toast('Network error — server se connection nahi hua', 'ter');
    console.error('AI Edit error:', err);
    setAiStep(1);
  });
}


function goBackToPrompt() {
  aiModifiedHtml = null;
  document.getElementById('ai-preview-frame').srcdoc = '';
  setAiStep(1);
}

function saveAiChanges() {
  if (!aiModifiedHtml || !aiEditToken) return;
  const btn = document.getElementById('ai-save-btn');
  btn.disabled = true;
  btn.innerHTML = '<div class="spin"></div> Saving...';
  pwProgress.start('💾 Changes save ho rahi hain...', 30);

  fetch('<?= SITE_URL ?>/api/ai_edit.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({action: 'save', token: aiEditToken, modified_html: aiModifiedHtml})
  })
  .then(r => r.json())
  .then(res => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Haan, Save Karo!';
    if (res.success) {
      pwProgress.done('Saved! ✅');
      toast('Page update ho gaya! ✅', 'tok');
      closeAiEdit();
    } else {
      pwProgress.error(res.error || 'Save nahi hua');
      toast(res.error || 'Save nahi hua — try again', 'ter');
    }
  })
  .catch(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-check"></i> Haan, Save Karo!';
    pwProgress.error('Network error!');
    toast('Network error', 'ter');
  });
}

document.addEventListener('DOMContentLoaded', initTheme);
</script>
<footer style="text-align:center;padding:16px 20px 80px;font-size:.7rem;color:var(--muted);border-top:1px solid var(--border);margin-top:8px;">
  <strong style="color:var(--p);">A Product of TechEagles</strong> &nbsp;|&nbsp; Under Mahakumbrix Innovation
</footer>
</body>
</html>
