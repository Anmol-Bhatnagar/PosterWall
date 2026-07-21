<?php
require_once 'config.php';

// Auto-redirect logged-in users to dashboard
if (loggedIn()) {
    header('Location: ' . siteUrl('dashboard/'));
    exit;
}

$tab = $_GET['tab'] ?? 'home';
$validTabs = ['home','create','search','pricing','about'];
if (!in_array($tab, $validTabs)) $tab = 'home';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if($tab==='home'): ?>
<title>PosterWall — Apna Design. Apna Brand. Apni Pehchaan.</title>
<meta name="description" content="Sirf ₹9 mein apni dukaan, dhaba, clinic ki photo se beautiful digital page banao. QR Code aur mobile number se accessible!">
<?php elseif($tab==='create'): ?>
<title>Page Banao — PosterWall</title>
<?php elseif($tab==='search'): ?>
<title>Business Dhundo — PosterWall</title>
<?php elseif($tab==='pricing'): ?>
<title>Pricing — PosterWall</title>
<?php else: ?>
<title>About Us — PosterWall</title>
<?php endif; ?>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{
  --p:#FF6B00;--pd:#e05a00;--p2:#FFD700;--p3:#FF6B00;
  --bg:#ffffff;--bg2:#f7f9fc;
  --card:#ffffff;
  --text:#1a1a2e;--muted:#6c7b94;
  --border:rgba(0,0,0,0.08);
  --glow:rgba(255,107,0,0.25);--glow2:rgba(255,107,0,0.04);
  --nav-bg:rgba(255,255,255,0.85);
  --toggle-bg:rgba(255,107,0,0.08);
  --logo-g1:#FF6B00;--logo-g2:#FFD700;--logo-g3:#FFD700;
  --orb-color1:rgba(255,107,0,0.04);
  --orb-color2:rgba(255,107,0,0.02);
  --btn-bg-hover:rgba(255,107,0,0.08);
  --tabs-bg:rgba(255,107,0,0.06);
  --title-g1:var(--p);--title-g2:#FF8C00;
  --hero-glow:rgba(255,107,0,0.06);
  --upload-bg:rgba(255,107,0,0.03);
  --upload-border:rgba(255,107,0,0.35);
  --mob-btn-bg:#1a1a2e;--mob-btn-color:#fff;--mob-btn-hover:#121222;
  --sec-bg:#fcfaff;
  --stat-glow:none;
  --h1-grad:linear-gradient(135deg,var(--text),var(--text));
  --step-active-bg:rgba(255,107,0,0.1);
  --input-bg:#f7f9fc;
}
[data-theme="dark"]{
  --p:#7C3AED;--pd:#6D28D9;--p2:#A855F7;--p3:#C084FC;
  --bg:#05050f;--bg2:#0d0d1f;
  --card:rgba(20,14,50,0.65);
  --text:#ede9ff;--muted:#8B7FAB;
  --border:rgba(124,58,237,0.22);
  --glow:rgba(124,58,237,0.35);--glow2:rgba(124,58,237,0.15);
  --nav-bg:rgba(5,5,15,0.78);
  --toggle-bg:rgba(124,58,237,0.12);
  --logo-g1:#A855F7;--logo-g2:#C084FC;--logo-g3:#e879f9;
  --orb-color1:rgba(124,58,237,0.15);
  --orb-color2:rgba(168,85,247,0.1);
  --btn-bg-hover:rgba(124,58,237,0.18);
  --tabs-bg:rgba(124,58,237,0.08);
  --title-g1:var(--p3);--title-g2:#e879f9;
  --hero-glow:rgba(124,58,237,0.18);
  --upload-bg:rgba(20,14,50,0.5);
  --upload-border:rgba(124,58,237,0.4);
  --mob-btn-bg:rgba(124,58,237,0.22);--mob-btn-color:#ede9ff;--mob-btn-hover:rgba(124,58,237,0.35);
  --sec-bg:rgba(20,14,50,0.3);
  --stat-glow:drop-shadow(0 0 12px rgba(168,85,247,0.3));
  --h1-grad:linear-gradient(135deg,#fff 30%,var(--p3));
  --step-active-bg:rgba(124,58,237,0.15);
  --input-bg:rgba(20,14,50,0.7);
}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{
  font-family:'Poppins',sans-serif;
  background:var(--bg);color:var(--text);
  overflow-x:hidden;
}
body::before{
  content:'';position:fixed;
  top:-20%;left:-10%;
  width:60vw;height:60vw;max-width:800px;max-height:800px;
  border-radius:50%;
  background:radial-gradient(circle,var(--orb-color1) 0%,transparent 70%);
  pointer-events:none;z-index:0;
}
body::after{
  content:'';position:fixed;
  bottom:-15%;right:-10%;
  width:50vw;height:50vw;max-width:600px;max-height:600px;
  border-radius:50%;
  background:radial-gradient(circle,var(--orb-color2) 0%,transparent 70%);
  pointer-events:none;z-index:0;
}
a{text-decoration:none;color:inherit;}

/* ── NAV ── */
nav{
  position:fixed;top:0;left:0;right:0;z-index:100;padding:0 20px;
  background:var(--nav-bg);
  -webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
}
.nav-in{max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:60px;}
.logo{
  font-family:'Baloo 2',cursive;font-size:1.5rem;font-weight:800;
  display:flex;align-items:center;gap:6px;
  background:linear-gradient(135deg,var(--logo-g1),var(--logo-g2),var(--logo-g3));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  flex-shrink:0;
}
.logo span{-webkit-text-fill-color:unset;}

/* Tab bar */
.nav-tabs{
  display:flex;align-items:center;gap:2px;
  background:var(--tabs-bg);border-radius:50px;
  padding:4px;margin:0 16px;border:1px solid var(--border);
}
.nav-tab{
  padding:7px 14px;border-radius:50px;font-size:.82rem;font-weight:600;
  color:var(--muted);cursor:pointer;transition:all .25s;
  white-space:nowrap;border:none;background:none;
}
.nav-tab:hover{color:var(--text);}
.nav-tab.active{
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;box-shadow:0 4px 14px var(--glow);
}

.nav-right{display:flex;align-items:center;gap:8px;flex-shrink:0;}
.theme-toggle{
  width:40px;height:40px;border-radius:10px;
  background:var(--toggle-bg);border:1px solid var(--border);
  color:var(--p3);cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  font-size:.9rem;transition:all .3s;
}
.theme-toggle:hover{background:var(--btn-bg-hover);}
.nav-btn{
  padding:9px 18px;border-radius:50px;font-weight:600;font-size:.82rem;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;border:none;cursor:pointer;transition:all .3s;
  box-shadow:0 4px 16px var(--glow);white-space:nowrap;
}
.nav-btn:hover{transform:translateY(-1px);box-shadow:0 6px 24px var(--glow);}
.nav-btn.out{background:rgba(124,58,237,0.12);border:1px solid var(--border);color:var(--text);}
.nav-btn.out:hover{background:rgba(124,58,237,0.22);border-color:var(--p2);color:var(--p3);}

/* Mobile nav */
@media(max-width:540px){
  .nav-tab{padding:7px 10px;font-size:.75rem;}
  .nav-tabs{margin:0 8px;}
  .nav-btn{padding:8px 12px;font-size:.78rem;}
}

/* ── PAGE WRAPPER ── */
.page{display:none;min-height:100svh;position:relative;z-index:1;}
.page.active{display:block;}

/* ═══════════════════ HOME PAGE ═══════════════════ */
.hero{
  padding-top:60px;min-height:100svh;display:flex;
  flex-direction:column;align-items:center;justify-content:center;
  text-align:center;padding:100px 20px 60px;position:relative;overflow:hidden;
}
.hero::before{
  content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse at 50% 30%,var(--hero-glow) 0%,transparent 70%);
  pointer-events:none;
}
.hero::after{
  content:'';position:absolute;top:-50%;right:-10%;
  width:500px;height:500px;border-radius:50%;
  background:radial-gradient(circle,rgba(168,85,247,0.08),transparent);
  animation:float 20s ease-in-out infinite;pointer-events:none;
}
@keyframes float{0%,100%{transform:translateY(0);}50%{transform:translateY(30px);}}

.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--toggle-bg);
  border:1px solid var(--border);
  color:var(--p3);padding:8px 18px;border-radius:100px;
  font-size:.8rem;font-weight:600;margin-bottom:24px;
  animation:slideDown .6s ease;
}
.hero h1{
  font-family:'Baloo 2',cursive;font-size:clamp(2rem,7vw,4.2rem);font-weight:800;
  line-height:1.1;margin-bottom:18px;animation:slideDown .7s ease;
  letter-spacing:-1px;color:var(--text);
}
.grad{background:linear-gradient(135deg,var(--title-g1),var(--title-g2));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.hero-sub{color:var(--muted);font-size:clamp(.95rem,2.5vw,1.15rem);max-width:560px;line-height:1.8;margin-bottom:40px;animation:slideDown .8s ease;}

.upload-box{
  background:var(--upload-bg);backdrop-filter:blur(12px);
  border:2px dashed var(--upload-border);border-radius:24px;
  padding:40px 28px;max-width:500px;width:100%;margin:0 auto 28px;
  cursor:pointer;transition:all .4s;animation:slideDown .9s ease;
  position:relative;overflow:hidden;box-shadow:0 8px 32px var(--glow2);
}
.upload-box:hover{
  border-color:var(--p2);background:var(--btn-bg-hover);
  box-shadow:0 12px 40px var(--glow);transform:translateY(-4px);
}
.upload-box input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.upload-icon{font-size:3.5rem;margin-bottom:14px;animation:bounce 2s ease-in-out infinite;filter:var(--stat-glow);}
.upload-title{
  font-weight:700;font-size:1.15rem;margin-bottom:6px;
  background:var(--h1-grad);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.upload-sub{color:var(--muted);font-size:.88rem;}
.upload-preview{display:none;width:100%;border-radius:14px;margin-bottom:16px;max-height:220px;object-fit:cover;box-shadow:0 6px 24px var(--glow2);}

.gen-btn{
  width:100%;max-width:500px;padding:16px;border-radius:50px;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;font-weight:700;font-size:1rem;border:none;cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:10px;
  transition:all .3s;box-shadow:0 8px 24px var(--glow);animation:slideDown 1s ease;margin:0 auto;
}
.gen-btn:hover{transform:translateY(-3px);box-shadow:0 12px 32px var(--glow);}
.gen-btn:disabled{opacity:.6;cursor:not-allowed;transform:none;}
.price-tag{display:inline-flex;align-items:center;gap:6px;margin-top:14px;color:var(--muted);font-size:.82rem;}
.price-tag strong{color:var(--p3);font-weight:700;}

.mob-search{max-width:500px;margin:48px auto 0;text-align:center;animation:slideDown 1.1s ease;}
.mob-search h3{font-family:'Baloo 2',cursive;font-size:1.1rem;margin-bottom:12px;color:var(--muted);}
.mob-input-row{display:flex;gap:8px;}
.mob-input{
  flex:1;padding:13px 16px;border-radius:12px;
  background:var(--input-bg);backdrop-filter:blur(10px);
  border:1px solid var(--border);color:var(--text);
  font-size:1rem;font-family:'Poppins',sans-serif;transition:all .3s;
}
.mob-input:focus{outline:none;border-color:var(--p2);box-shadow:0 0 16px var(--glow2);}
.mob-btn{
  padding:13px 18px;border-radius:12px;background:var(--mob-btn-bg);
  border:1px solid var(--border);color:var(--mob-btn-color);cursor:pointer;
  font-size:1rem;font-weight:600;transition:all .3s;
}
.mob-btn:hover{background:var(--mob-btn-hover);border-color:var(--p2);box-shadow:0 0 16px var(--glow2);}

.stats{display:flex;justify-content:center;gap:40px;margin-top:56px;flex-wrap:wrap;animation:slideDown 1.2s ease;}
.stat-item{text-align:center;}
.stat-item strong{
  font-family:'Baloo 2',cursive;font-size:2.2rem;font-weight:800;
  background:linear-gradient(135deg,var(--logo-g1),var(--logo-g2),var(--logo-g3));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  display:block;filter:var(--stat-glow);
}
.stat-item span{font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px;}

/* Sections */
.section{padding:72px 20px;}
.section-inner{max-width:1000px;margin:0 auto;}
.sec-head{text-align:center;margin-bottom:52px;}
.sec-head h2{
  font-family:'Baloo 2',cursive;font-size:clamp(1.6rem,4vw,2.4rem);font-weight:800;margin-bottom:12px;
  background:var(--h1-grad);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.sec-head p{color:var(--muted);font-size:.95rem;max-width:500px;margin:0 auto;}

.steps-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;}
.step-card{
  background:var(--card);backdrop-filter:blur(10px);
  border:1px solid var(--border);border-radius:20px;
  padding:32px 24px;text-align:center;position:relative;
  overflow:hidden;transition:all .35s;
}
.step-card:hover{border-color:var(--p2);transform:translateY(-6px);box-shadow:0 12px 40px var(--glow2);}
.step-num{
  width:48px;height:48px;background:var(--step-active-bg);
  border:1px solid var(--border);border-radius:50%;
  display:flex;align-items:center;justify-content:center;margin:0 auto 14px;
  font-weight:800;color:var(--p3);font-size:1.2rem;
  box-shadow:0 0 12px var(--glow2);
}
.step-icon{font-size:2.6rem;margin-bottom:12px;filter:var(--stat-glow);}
.step-card h3{font-weight:700;margin-bottom:8px;font-size:1.05rem;}
.step-card p{color:var(--muted);font-size:.84rem;line-height:1.6;}

.examples-bg{background:var(--sec-bg);border-top:1px solid var(--border);border-bottom:1px solid var(--border);}
.examples-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;}
.ex-card{
  background:var(--card);backdrop-filter:blur(10px);
  border:1px solid var(--border);border-radius:18px;
  padding:22px 16px;text-align:center;transition:all .35s;
}
.ex-card:hover{border-color:var(--p2);transform:translateY(-4px);box-shadow:0 8px 24px var(--glow2);}
.ex-icon{font-size:2.4rem;margin-bottom:12px;}
.ex-name{font-weight:600;font-size:.9rem;margin-bottom:4px;color:var(--text);}
.ex-desc{color:var(--muted);font-size:.73rem;}

/* ═══════════════════ CREATE PAGE ═══════════════════ */
.create-hero{padding:100px 20px 60px;min-height:100svh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;position:relative;overflow:hidden;}
.create-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 50% 30%,var(--hero-glow) 0%,transparent 65%);pointer-events:none;}
.create-steps-row{display:flex;gap:8px;justify-content:center;margin-bottom:32px;flex-wrap:wrap;}
.create-step-pill{
  display:flex;align-items:center;gap:6px;
  background:var(--toggle-bg);border:1px solid var(--border);
  border-radius:100px;padding:6px 14px;font-size:.78rem;font-weight:600;color:var(--p3);
}

/* ═══════════════════ SEARCH PAGE ═══════════════════ */
.search-hero{padding:100px 20px 60px;min-height:100svh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;}
.search-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 50% 30%,rgba(124,58,237,0.12) 0%,transparent 65%);pointer-events:none;}
.search-box-wrap{max-width:520px;width:100%;margin:0 auto;}
.search-inp-styled{
  width:100%;padding:16px 20px;border-radius:14px;
  background:rgba(20,14,50,0.7);backdrop-filter:blur(10px);
  border:1px solid var(--border);color:var(--text);
  font-size:1rem;font-family:'Poppins',sans-serif;transition:all .3s;margin-bottom:10px;
}
.search-inp-styled:focus{outline:none;border-color:var(--p2);box-shadow:0 0 16px var(--glow2);}
.search-or{color:var(--muted);font-size:.8rem;margin:6px 0;text-align:center;}
.search-btn-styled{
  width:100%;padding:15px;border-radius:50px;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;border:none;font-weight:700;font-size:.96rem;
  cursor:pointer;transition:all .3s;box-shadow:0 6px 20px var(--glow);
}
.search-btn-styled:hover{transform:translateY(-2px);box-shadow:0 8px 28px var(--glow);}

/* ═══════════════════ PRICING PAGE ═══════════════════ */
.pricing-page{padding:100px 20px 80px;min-height:100svh;}
.pricing-inner{max-width:600px;margin:0 auto;text-align:center;}
.price-box{
  background:var(--card);backdrop-filter:blur(10px);
  border:1px solid var(--border);border-radius:24px;
  padding:44px 36px;text-align:center;box-shadow:0 12px 40px var(--glow2);
  max-width:480px;margin:0 auto 32px;
}
.price-main{
  font-family:'Baloo 2',cursive;font-size:4rem;font-weight:800;
  background:linear-gradient(135deg,#fff,var(--p3));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;
  background-clip:text;margin:16px 0 4px;filter:drop-shadow(0 0 16px rgba(168,85,247,0.3));
}
.price-list{list-style:none;margin:28px 0;text-align:left;}
.price-list li{padding:12px 0;border-bottom:1px solid var(--border);font-size:.9rem;display:flex;align-items:center;gap:10px;color:var(--text);}
.price-list li::before{content:'✓';flex-shrink:0;color:#34d399;font-weight:700;font-size:1.1rem;}
.price-compare{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:32px;max-width:480px;margin:32px auto 0;}
.compare-card{
  background:var(--card);backdrop-filter:blur(10px);
  border:1px solid var(--border);border-radius:18px;padding:24px 20px;text-align:center;
}
.compare-card.highlighted{border-color:var(--p2);background:rgba(124,58,237,0.08);box-shadow:0 0 20px var(--glow2);}
.compare-price{font-family:'Baloo 2',cursive;font-size:2rem;font-weight:800;color:var(--p3);display:block;margin:8px 0;}

/* ═══════════════════ ABOUT PAGE ═══════════════════ */
.about-page{padding:100px 20px 80px;min-height:100svh;}
.about-inner{max-width:760px;margin:0 auto;}
.about-hero-box{text-align:center;margin-bottom:56px;}
.about-hero-box h1{
  font-family:'Baloo 2',cursive;font-size:clamp(1.8rem,5vw,3rem);font-weight:800;margin-bottom:14px;
  background:linear-gradient(135deg,#fff 30%,var(--p3));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.about-hero-box p{color:var(--muted);font-size:.97rem;line-height:1.8;max-width:560px;margin:0 auto;}
.about-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:48px;}
.about-card{
  background:var(--card);backdrop-filter:blur(10px);
  border:1px solid var(--border);border-radius:20px;
  padding:28px 22px;transition:all .35s;
}
.about-card:hover{border-color:var(--p2);transform:translateY(-4px);box-shadow:0 10px 32px var(--glow2);}
.about-card-icon{font-size:2.2rem;margin-bottom:12px;filter:drop-shadow(0 0 8px rgba(124,58,237,0.3));}
.about-card h3{font-weight:700;font-size:1rem;margin-bottom:8px;}
.about-card p{color:var(--muted);font-size:.83rem;line-height:1.6;}
.about-team{
  text-align:center;background:linear-gradient(135deg,rgba(124,58,237,0.12),rgba(168,85,247,0.06));
  border:1px solid var(--border);border-radius:24px;
  padding:40px 28px;margin-bottom:32px;
}
.about-team h2{font-family:'Baloo 2',cursive;font-size:1.6rem;font-weight:800;margin-bottom:10px;color:#fff;}
.about-team p{color:var(--muted);font-size:.9rem;line-height:1.7;}

/* ═══════════════════ SHARED ═══════════════════ */
.price-box-alt{
  background:var(--card);backdrop-filter:blur(10px);
  border:1px solid var(--border);border-radius:24px;
  padding:44px 36px;text-align:center;box-shadow:0 12px 40px var(--glow2);
}

/* PRICING & HOW IT WORKS in Pricing page */
.pricing-how-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:32px;}
.pricing-how-card{
  background:var(--card);backdrop-filter:blur(10px);
  border:1px solid var(--border);border-radius:18px;padding:24px 18px;text-align:center;
}
.pricing-how-card .icon{font-size:2rem;margin-bottom:10px;}
.pricing-how-card h4{font-weight:700;font-size:.92rem;margin-bottom:6px;color:#fff;}
.pricing-how-card p{color:var(--muted);font-size:.8rem;line-height:1.5;}

/* FOOTER */
footer{background:rgba(20,14,50,0.3);border-top:1px solid var(--border);padding:48px 20px 28px;}
.foot-in{max-width:1000px;margin:0 auto;}
.foot-top{display:grid;grid-template-columns:1fr;gap:32px;margin-bottom:36px;}
@media(min-width:600px){.foot-top{grid-template-columns:2fr 1fr 1fr;}}
.foot-logo{
  font-family:'Baloo 2',cursive;font-size:1.4rem;font-weight:800;margin-bottom:10px;
  background:linear-gradient(135deg,var(--p2),var(--p3),#e879f9);
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.foot-logo span{-webkit-text-fill-color:unset;}
.foot-desc{color:var(--muted);font-size:.84rem;line-height:1.7;}
.foot-links h4{font-weight:600;margin-bottom:12px;font-size:.9rem;color:#fff;}
.foot-links a{display:block;color:var(--muted);font-size:.84rem;padding:5px 0;transition:color .2s;}
.foot-links a:hover{color:var(--p3);}
.foot-bottom{border-top:1px solid var(--border);padding-top:24px;text-align:center;color:var(--muted);font-size:.78rem;}
.social-row{display:flex;gap:10px;margin-top:16px;}
.soc-a{
  width:38px;height:38px;border-radius:10px;background:rgba(124,58,237,0.12);
  border:1px solid rgba(124,58,237,0.25);display:flex;align-items:center;
  justify-content:center;color:var(--p3);font-size:.95rem;transition:all .2s;cursor:pointer;
}
.soc-a:hover{background:linear-gradient(135deg,var(--p),var(--p2));color:white;border-color:transparent;transform:translateY(-2px);}

/* MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:500;display:flex;align-items:flex-end;justify-content:center;backdrop-filter:blur(8px);}
.modal{
  background:rgba(13,13,31,0.95);backdrop-filter:blur(20px);
  border:1px solid var(--border);border-radius:24px 24px 0 0;
  padding:32px 24px;width:100%;max-height:90svh;overflow-y:auto;box-shadow:0 20px 60px rgba(124,58,237,0.2);
}
@media(min-width:600px){.modal-overlay{align-items:center;padding:20px;}.modal{border-radius:24px;max-width:500px;}}
.modal h3{
  font-family:'Baloo 2',cursive;font-size:1.4rem;font-weight:800;margin-bottom:12px;
  background:linear-gradient(135deg,#fff 30%,var(--p3));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.inp-field{
  width:100%;padding:13px 16px;border-radius:12px;
  background:rgba(20,14,50,0.7);border:1px solid var(--border);
  color:var(--text);font-size:1rem;font-family:'Poppins',sans-serif;margin-bottom:12px;transition:border-color .2s;
}
.inp-field:focus{outline:none;border-color:var(--p2);box-shadow:0 0 0 3px rgba(124,58,237,0.15);}
.btn-full{
  width:100%;padding:14px;border-radius:50px;
  background:linear-gradient(135deg,var(--p),var(--p2));
  color:#fff;font-weight:700;font-size:.95rem;border:none;cursor:pointer;
  transition:all .3s;box-shadow:0 6px 20px var(--glow);
}
.btn-full:hover{transform:translateY(-1px);box-shadow:0 8px 28px var(--glow);}

/* ANIMATIONS */
@keyframes slideDown{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
@keyframes bounce{0%,100%{transform:translateY(0);}50%{transform:translateY(-10px);}}
@keyframes spin{to{transform:rotate(360deg);}}
.spin{width:20px;height:20px;border:3px solid rgba(124,58,237,0.2);border-top-color:var(--p2);border-radius:50%;animation:spin .7s linear infinite;display:inline-block;}

/* TOAST */
.toast{
  position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
  background:rgba(13,13,31,0.95);backdrop-filter:blur(16px);
  border:1px solid var(--border);padding:14px 24px;border-radius:50px;
  font-size:.875rem;z-index:999;box-shadow:0 12px 40px rgba(0,0,0,.3);
  animation:slideDown .3s ease;white-space:nowrap;
}
.toast.ok{border-color:rgba(16,185,129,.4);color:#34d399;}
.toast.err{border-color:rgba(239,68,68,.4);color:#f87171;}
.toast.info{border-color:rgba(124,58,237,.4);color:var(--p3);}
</style>
</head>
<body>

<!-- ══════════════════ NAV ══════════════════ -->
<nav>
  <div class="nav-in">
    <a href="<?= siteUrl('') ?>" class="logo">🦅 Poster<span>Wall</span></a>

    <div class="nav-tabs" role="tablist">
      <button class="nav-tab <?= $tab==='home'?'active':'' ?>" onclick="goTab('home')" role="tab">🏠 Home</button>
      <button class="nav-tab <?= $tab==='create'?'active':'' ?>" onclick="goTab('create')" role="tab">✨ Create</button>
      <button class="nav-tab <?= $tab==='search'?'active':'' ?>" onclick="goTab('search')" role="tab">🔍 Search</button>
      <button class="nav-tab <?= $tab==='pricing'?'active':'' ?>" onclick="goTab('pricing')" role="tab">💰 Pricing</button>
      <button class="nav-tab <?= $tab==='about'?'active':'' ?>" onclick="goTab('about')" role="tab">ℹ️ About</button>
    </div>

    <div class="nav-right">
      <button class="theme-toggle" id="theme-toggle" onclick="toggleTheme()" title="Toggle dark mode">🌙</button>
      <a href="<?= siteUrl('auth/login.php') ?>" class="nav-btn">Login / Sign Up</a>
    </div>
  </div>
</nav>


<!-- ══════════════════ HOME TAB ══════════════════ -->
<div id="tab-home" class="page <?= $tab==='home'?'active':'' ?>">

  <section class="hero">
    <div class="hero-badge">🤖 AI-Powered Digital Identity — Sirf ₹9</div>
    <h1>Photo lo.<br>AI se <span class="grad">Page Banao.</span><br>Duniya ko Dikhao.</h1>
    <p class="hero-sub">Apni dukaan, dhaba, clinic ya business ki ek photo lo — AI ek beautiful digital page banayega. QR code aur mobile number se koi bhi access kar sakta hai!</p>

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

    <div class="mob-search">
      <h3>📱 Kisi ka page mobile number se dhundho</h3>
      <div class="mob-input-row">
        <input type="tel" class="mob-input" id="mob-find" placeholder="Mobile number dalein..." maxlength="15">
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
        <div class="step-card"><div class="step-num">1</div><div class="step-icon">📸</div><h3>Photo Lo</h3><p>Apni dukaan ke bahar ki photo lo — board, menu, signboard kuch bhi</p></div>
        <div class="step-card"><div class="step-num">2</div><div class="step-icon">🤖</div><h3>AI Page Banata Hai</h3><p>Gemini AI photo padhta hai — naam, contact, menu sab extract karta hai aur beautiful page banata hai</p></div>
        <div class="step-card"><div class="step-num">3</div><div class="step-icon">📲</div><h3>Share Karo</h3><p>QR code print karo ya mobile number se share karo — customer directly page access kar sakta hai</p></div>
        <div class="step-card"><div class="step-num">4</div><div class="step-icon">♾️</div><h3>Hamesha Live</h3><p>Ek baar pay karo ₹9 — page posterwall.in pe hamesha live rahega. Kabhi delete nahi hoga!</p></div>
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
        <div class="ex-card"><div class="ex-icon"><?=$t[0]?></div><div class="ex-name"><?=$t[1]?></div><div class="ex-desc"><?=$t[2]?></div></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- PRICING SNIPPET -->
  <section class="section">
    <div class="section-inner">
      <div class="sec-head"><h2>Simple Pricing</h2><p>Ek baar pay karo — hamesha live</p></div>
      <div class="price-box">
        <div style="color:var(--muted);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;">Per Digital Page</div>
        <div class="price-main">₹9</div>
        <div style="color:var(--muted);font-size:.85rem;margin-bottom:8px;">One time payment</div>
        <ul class="price-list">
          <li>Beautiful AI-generated HTML page</li>
          <li>Mobile number se accessible</li>
          <li>QR Code — scan karo page khulega</li>
          <li>WhatsApp &amp; Call buttons</li>
          <li>Google Maps link</li>
          <li>Share on all social media</li>
          <li>Hamesha live — kabhi expire nahi</li>
          <li>Edit karo anytime (₹9 per re-generate)</li>
        </ul>
        <button onclick="goTab('create')" style="display:block;width:100%;padding:14px;border-radius:12px;background:var(--p);color:#fff;font-weight:700;font-size:1rem;border:none;cursor:pointer;box-shadow:0 6px 24px rgba(255,107,0,.4);">Abhi Shuru Karo →</button>
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
          <a href="javascript:goTab('search')">🔍 Find Business</a>
          <a href="javascript:goTab('create')">✨ Create Page</a>
          <a href="<?= siteUrl('auth/login.php') ?>">Login / Sign Up</a>
          <a href="<?= siteUrl('admin/login.php') ?>">Admin</a>
        </div>
        <div class="foot-links">
          <h4>Company</h4>
          <a href="javascript:goTab('about')">About Us</a>
          <a href="javascript:goTab('pricing')">Pricing</a>
          <a href="#">Privacy Policy</a>
          <a href="mailto:posterwall.in@gmail.com">Contact</a>
        </div>
      </div>
      <div class="foot-bottom">
        © <?=YEAR?> <strong style="color:var(--text);">PosterWall</strong> — A Product of <strong style="color:var(--text);">TechEagles</strong> &nbsp;|&nbsp; Under <strong style="color:var(--text);">Mahakumbrix Innovation</strong> &nbsp;|&nbsp; Made with ❤️ in India
      </div>
    </div>
  </footer>
</div>


<!-- ══════════════════ CREATE TAB ══════════════════ -->
<div id="tab-create" class="page <?= $tab==='create'?'active':'' ?>">
  <section class="create-hero">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 50% 30%,rgba(255,107,0,.12) 0%,transparent 65%);pointer-events:none;"></div>
    <div style="position:relative;width:100%;max-width:600px;margin:0 auto;">
      <div class="hero-badge" style="margin-bottom:20px;">✨ AI Page Generator — Sirf ₹9</div>
      <h1 style="font-family:'Baloo 2',cursive;font-size:clamp(1.8rem,5vw,3rem);font-weight:800;margin-bottom:12px;line-height:1.15;">Photo lo, <span class="grad">AI Page Banao</span></h1>
      <p style="color:var(--muted);font-size:.95rem;line-height:1.7;margin-bottom:28px;max-width:480px;margin-left:auto;margin-right:auto;">Dukaan/dhaba/clinic ki photo upload karo — AI 30 seconds mein ek beautiful digital page banayega.</p>

      <div class="create-steps-row">
        <div class="create-step-pill">📸 Photo Upload</div>
        <div style="color:var(--muted);font-size:.9rem;">→</div>
        <div class="create-step-pill">🤖 AI Extract</div>
        <div style="color:var(--muted);font-size:.9rem;">→</div>
        <div class="create-step-pill">✅ Live Page</div>
      </div>

      <div class="upload-box" id="upload-box-create" style="margin-bottom:20px;">
        <input type="file" id="photo-input-create" accept="image/*" onchange="handlePhotoCreate(event)">
        <img id="upload-preview-create" class="upload-preview">
        <div id="upload-content-create">
          <div class="upload-icon">📸</div>
          <div class="upload-title">Apni dukaan/dhaba ki photo upload karo</div>
          <div class="upload-sub">JPG, PNG — mobile camera ya gallery se</div>
        </div>
      </div>

      <button class="gen-btn" id="gen-btn-create" onclick="startGenerationCreate()" disabled>
        <i class="fas fa-magic"></i> AI se Digital Page Banao — ₹9
      </button>
      <div class="price-tag" style="justify-content:center;"><strong>₹9</strong> — one-time payment, page hamesha live</div>

      <div style="margin-top:32px;background:rgba(255,107,0,.06);border:1px solid rgba(255,107,0,.18);border-radius:14px;padding:18px 20px;font-size:.83rem;color:var(--muted);line-height:1.6;text-align:left;">
        <strong style="color:var(--text);">🔐 Login required:</strong> Generate karne ke liye login/signup zaroori hai — free mein account bana sakte ho. Ek baar photo upload karo, login karo, aur AI page ban jayega!
      </div>
    </div>
  </section>

  <footer style="background:var(--bg2);border-top:1px solid var(--border);padding:24px 20px;text-align:center;">
    <div style="color:var(--muted);font-size:.78rem;">© <?=YEAR?> <strong style="color:var(--text);">PosterWall</strong> — A Product of TechEagles &nbsp;|&nbsp; Made with ❤️ in India</div>
  </footer>
</div>


<!-- ══════════════════ SEARCH TAB ══════════════════ -->
<div id="tab-search" class="page <?= $tab==='search'?'active':'' ?>">
  <section class="search-hero" style="position:relative;">
    <div style="position:absolute;inset:0;background:radial-gradient(ellipse at 50% 30%,rgba(99,102,241,.1) 0%,transparent 65%);pointer-events:none;"></div>
    <div style="position:relative;width:100%;max-width:540px;margin:0 auto;text-align:center;">
      <div class="hero-badge" style="background:rgba(99,102,241,.1);border-color:rgba(99,102,241,.25);color:#6366f1;margin-bottom:20px;">🔍 Business Search</div>
      <h1 style="font-family:'Baloo 2',cursive;font-size:clamp(1.8rem,5vw,3rem);font-weight:800;margin-bottom:12px;">Business <span style="background:linear-gradient(135deg,#6366f1,#8b5cf6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Dhundo</span></h1>
      <p style="color:var(--muted);font-size:.95rem;line-height:1.7;margin-bottom:32px;">Mobile number <strong>ya</strong> business name dalein — digital page seedha khul jayega</p>

      <div class="search-box-wrap">
        <input type="tel" class="search-inp-styled" id="search-mobile-inp" placeholder="📱 Mobile number (10 digits)..." maxlength="15" onkeypress="if(event.key==='Enter')doSearch()">
        <div class="search-or">— ya —</div>
        <input type="text" class="search-inp-styled" id="search-name-inp" placeholder="🏪 Business ka naam..." maxlength="80" onkeypress="if(event.key==='Enter')doSearch()">
        <button class="search-btn-styled" onclick="doSearch()" style="margin-top:8px;">
          <i class="fas fa-search"></i> Dhundo
        </button>
      </div>

      <div style="margin-top:40px;display:flex;justify-content:center;gap:24px;flex-wrap:wrap;">
        <div style="text-align:center;">
          <div style="font-size:2rem;margin-bottom:6px;">📱</div>
          <div style="font-weight:600;font-size:.88rem;">Mobile se</div>
          <div style="color:var(--muted);font-size:.77rem;">/m/9876543210</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:2rem;margin-bottom:6px;">🏪</div>
          <div style="font-weight:600;font-size:.88rem;">Naam se</div>
          <div style="color:var(--muted);font-size:.77rem;">/m/?name=Sharma+Dhaba</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:2rem;margin-bottom:6px;">📲</div>
          <div style="font-weight:600;font-size:.88rem;">QR Scan karo</div>
          <div style="color:var(--muted);font-size:.77rem;">Direct page khulega</div>
        </div>
      </div>
    </div>
  </section>

  <footer style="background:var(--bg2);border-top:1px solid var(--border);padding:24px 20px;text-align:center;">
    <div style="color:var(--muted);font-size:.78rem;">© <?=YEAR?> <strong style="color:var(--text);">PosterWall</strong> — A Product of TechEagles &nbsp;|&nbsp; Made with ❤️ in India</div>
  </footer>
</div>


<!-- ══════════════════ PRICING TAB ══════════════════ -->
<div id="tab-pricing" class="page <?= $tab==='pricing'?'active':'' ?>">
  <div class="pricing-page">
    <div class="pricing-inner">
      <div style="text-align:center;margin-bottom:48px;">
        <div class="hero-badge" style="margin-bottom:16px;">💰 Transparent Pricing</div>
        <h1 style="font-family:'Baloo 2',cursive;font-size:clamp(1.8rem,5vw,2.8rem);font-weight:800;margin-bottom:12px;">Simple, Honest Pricing</h1>
        <p style="color:var(--muted);font-size:.95rem;line-height:1.7;max-width:480px;margin:0 auto;">Koi hidden fees nahi. Ek baar pay karo — page hamesha live.</p>
      </div>

      <div class="price-box">
        <div style="color:var(--muted);font-size:.85rem;text-transform:uppercase;letter-spacing:1px;">Per Digital Page</div>
        <div class="price-main">₹9</div>
        <div style="color:var(--muted);font-size:.85rem;margin-bottom:4px;">One-time payment</div>
        <div style="display:inline-block;background:rgba(16,185,129,.1);color:#10b981;border-radius:100px;padding:4px 12px;font-size:.75rem;font-weight:600;margin-bottom:20px;">No monthly fees ✓</div>
        <ul class="price-list">
          <li>Beautiful AI-generated HTML page</li>
          <li>Mobile number se accessible</li>
          <li>QR Code — scan karo page khulega</li>
          <li>WhatsApp &amp; Call buttons</li>
          <li>Google Maps link</li>
          <li>Share on all social media</li>
          <li>Hamesha live — kabhi expire nahi hoga</li>
          <li>Edit karo anytime (₹9 per re-generate)</li>
          <li>Menu editor — free updates</li>
          <li>AI Edit feature included</li>
        </ul>
        <button onclick="goTab('create')" style="display:block;width:100%;padding:14px;border-radius:12px;background:var(--p);color:#fff;font-weight:700;font-size:1rem;border:none;cursor:pointer;box-shadow:0 6px 24px rgba(255,107,0,.4);">Abhi Start Karo — ₹9 →</button>
      </div>

      <!-- Comparison -->
      <div style="margin-top:40px;margin-bottom:8px;text-align:center;">
        <h2 style="font-family:'Baloo 2',cursive;font-size:1.4rem;font-weight:800;margin-bottom:8px;">PosterWall vs Traditional</h2>
        <p style="color:var(--muted);font-size:.88rem;">Aap kitna bachate ho?</p>
      </div>
      <div class="price-compare">
        <div class="compare-card">
          <div style="font-size:1.6rem;margin-bottom:8px;">🏗️</div>
          <div style="font-weight:700;font-size:.9rem;margin-bottom:4px;">Website Developer</div>
          <span class="compare-price" style="color:#ef4444;">₹10,000+</span>
          <div style="color:var(--muted);font-size:.75rem;">Ek baar + maintenance</div>
        </div>
        <div class="compare-card highlighted">
          <div style="font-size:1.6rem;margin-bottom:8px;">🦅</div>
          <div style="font-weight:700;font-size:.9rem;margin-bottom:4px;">PosterWall</div>
          <span class="compare-price">₹9</span>
          <div style="color:var(--muted);font-size:.75rem;">Bas itna — hamesha</div>
        </div>
      </div>

      <!-- Wallet info -->
      <div style="margin-top:32px;background:rgba(255,107,0,.06);border:1.5px solid rgba(255,107,0,.2);border-radius:16px;padding:28px 24px;text-align:left;">
        <h3 style="font-family:'Baloo 2',cursive;font-size:1.2rem;font-weight:800;margin-bottom:14px;">💳 Wallet Recharge Options</h3>
        <div style="display:grid;gap:10px;">
          <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:rgba(255,255,255,.5);border-radius:10px;border:1px solid var(--border);">
            <span style="font-weight:600;">Single Page</span>
            <span style="font-weight:800;color:var(--p);">₹9</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:rgba(255,107,0,.08);border-radius:10px;border:1px solid rgba(255,107,0,.2);">
            <span style="font-weight:600;">10 Pages Pack <span style="background:var(--p);color:#fff;font-size:.7rem;padding:2px 8px;border-radius:100px;margin-left:6px;">Save ₹11</span></span>
            <span style="font-weight:800;color:var(--p);">₹79</span>
          </div>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:rgba(255,107,0,.08);border-radius:10px;border:1px solid rgba(255,107,0,.2);">
            <span style="font-weight:600;">25 Pages Pack <span style="background:var(--p);color:#fff;font-size:.7rem;padding:2px 8px;border-radius:100px;margin-left:6px;">Save ₹46</span></span>
            <span style="font-weight:800;color:var(--p);">₹179</span>
          </div>
        </div>
      </div>

      <!-- FAQ -->
      <div style="margin-top:40px;text-align:left;">
        <h2 style="font-family:'Baloo 2',cursive;font-size:1.4rem;font-weight:800;margin-bottom:20px;text-align:center;">❓ Frequently Asked Questions</h2>
        <div style="display:grid;gap:12px;">
          <?php
          $faqs=[
            ['Kya page permanently live rahega?','Haan! Ek baar pay karo aur page hamesha live rahega. Koi annual fee nahi, koi renewal nahi.'],
            ['Kya main page edit kar sakta hoon?','Haan — menu editor free hai. Full page re-generate ₹9 mein hota hai. AI Edit feature bhi available hai.'],
            ['Payment ke baad page kab milega?','Generate karte hi — 30 seconds mein page live ho jata hai.'],
            ['Kya QR code milega?','Haan, har page ke saath ek unique QR code milta hai jo directly page open karta hai.'],
            ['Mobile number compulsory hai?','Nahi — business name se bhi page register kar sakte ho. Mobile optional hai.'],
          ];
          foreach($faqs as $faq): ?>
          <div style="background:rgba(255,255,255,.5);backdrop-filter:blur(10px);border:1.5px solid var(--border);border-radius:12px;padding:18px 20px;">
            <div style="font-weight:700;font-size:.9rem;margin-bottom:6px;"><?= $faq[0] ?></div>
            <div style="color:var(--muted);font-size:.83rem;line-height:1.6;"><?= $faq[1] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <footer style="background:var(--bg2);border-top:1px solid var(--border);padding:24px 20px;text-align:center;">
    <div style="color:var(--muted);font-size:.78rem;">© <?=YEAR?> <strong style="color:var(--text);">PosterWall</strong> — A Product of TechEagles &nbsp;|&nbsp; Made with ❤️ in India</div>
  </footer>
</div>


<!-- ══════════════════ ABOUT TAB ══════════════════ -->
<div id="tab-about" class="page <?= $tab==='about'?'active':'' ?>">
  <div class="about-page">
    <div class="about-inner">
      <div class="about-hero-box">
        <div class="hero-badge" style="margin-bottom:16px;">🦅 Our Story</div>
        <h1>Hum Kaun Hain?</h1>
        <p>PosterWall ek Indian startup hai jo chota business owners ke liye digital presence aasaan aur affordable banata hai — sirf ₹9 mein.</p>
      </div>

      <div class="about-grid">
        <div class="about-card">
          <div class="about-card-icon">🎯</div>
          <h3>Hamara Mission</h3>
          <p>Har Indian business owner — chahe dhaba ho, dukaan ho ya clinic — ko ek beautiful digital identity dena. Sirf ₹9 mein.</p>
        </div>
        <div class="about-card">
          <div class="about-card-icon">🤖</div>
          <h3>AI-Powered</h3>
          <p>Gemini AI se photo se automatically business info extract karta hai aur ek stunning HTML page generate karta hai.</p>
        </div>
        <div class="about-card">
          <div class="about-card-icon">🇮🇳</div>
          <h3>Made in India</h3>
          <p>TechEagles ka product — Mahakumbrix Innovation ke under. Bharat ke chote business ke liye, Bharat mein banaya.</p>
        </div>
        <div class="about-card">
          <div class="about-card-icon">📲</div>
          <h3>Mobile First</h3>
          <p>QR code ya mobile number — koi bhi customer aapka page easily dhundh sakta hai bina app download kiye.</p>
        </div>
      </div>

      <div class="about-team">
        <div style="font-size:3rem;margin-bottom:14px;">🦅</div>
        <h2>TechEagles</h2>
        <p style="margin-bottom:12px;">We are a team of passionate engineers and designers building affordable digital tools for Indian businesses. PosterWall is our flagship product under <strong>Mahakumbrix Innovation</strong>.</p>
        <p>📧 <a href="mailto:posterwall.in@gmail.com" style="color:var(--p);font-weight:600;">posterwall.in@gmail.com</a></p>
      </div>

      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:40px;text-align:center;">
        <div style="background:rgba(255,255,255,.5);backdrop-filter:blur(10px);border:1.5px solid var(--border);border-radius:14px;padding:28px 16px;">
          <div style="font-family:'Baloo 2',cursive;font-size:2.4rem;font-weight:800;color:var(--p);">₹9</div>
          <div style="color:var(--muted);font-size:.8rem;margin-top:4px;">Per Page — Forever</div>
        </div>
        <div style="background:rgba(255,255,255,.5);backdrop-filter:blur(10px);border:1.5px solid var(--border);border-radius:14px;padding:28px 16px;">
          <div style="font-family:'Baloo 2',cursive;font-size:2.4rem;font-weight:800;color:var(--p);">30s</div>
          <div style="color:var(--muted);font-size:.8rem;margin-top:4px;">Average Generation</div>
        </div>
        <div style="background:rgba(255,255,255,.5);backdrop-filter:blur(10px);border:1.5px solid var(--border);border-radius:14px;padding:28px 16px;">
          <div style="font-family:'Baloo 2',cursive;font-size:2.4rem;font-weight:800;color:var(--p);">AI</div>
          <div style="color:var(--muted);font-size:.8rem;margin-top:4px;">Powered by Gemini</div>
        </div>
      </div>

      <div style="text-align:center;margin-bottom:32px;">
        <h2 style="font-family:'Baloo 2',cursive;font-size:1.4rem;font-weight:800;margin-bottom:8px;">Hamare Saath Judo</h2>
        <p style="color:var(--muted);font-size:.88rem;margin-bottom:20px;">Social media pe follow karo updates ke liye</p>
        <div class="social-row" style="justify-content:center;">
          <a href="https://www.instagram.com/posterwall.in/" target="_blank" class="soc-a"><i class="fab fa-instagram"></i></a>
          <a href="https://x.com/posterwall_in" target="_blank" class="soc-a"><i class="fab fa-x-twitter"></i></a>
          <a href="https://www.youtube.com/channel/UCeo8ZL4PR9hXR7ZdcMNqiuw" target="_blank" class="soc-a"><i class="fab fa-youtube"></i></a>
          <a href="https://whatsapp.com/channel/0029VbChgAZ6LwHsRN2vGJ0O" target="_blank" class="soc-a"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>

      <div style="text-align:center;">
        <button onclick="goTab('create')" style="padding:14px 36px;border-radius:12px;background:linear-gradient(135deg,var(--p),var(--pd));color:#fff;font-weight:700;font-size:1rem;border:none;cursor:pointer;box-shadow:0 6px 24px rgba(255,107,0,.3);">Apna Page Banao — ₹9 →</button>
      </div>
    </div>
  </div>

  <footer style="background:var(--bg2);border-top:1px solid var(--border);padding:24px 20px;text-align:center;">
    <div style="color:var(--muted);font-size:.78rem;">© <?=YEAR?> <strong style="color:var(--text);">PosterWall</strong> — A Product of TechEagles &nbsp;|&nbsp; Made with ❤️ in India</div>
  </footer>
</div>


<!-- ══════════════════ MODALS ══════════════════ -->
<!-- Login prompt modal -->
<div id="login-modal" class="modal-overlay" style="display:none;">
  <div class="modal">
    <h3>🔐 Login Zaroori Hai</h3>
    <p style="color:var(--muted);font-size:.875rem;margin-bottom:20px;">Page generate karne ke liye login ya sign up karo — bilkul free!</p>
    <a href="<?= siteUrl('auth/login.php') ?>" style="display:block;padding:14px;border-radius:11px;background:var(--p);color:#fff;font-weight:700;font-size:.95rem;text-align:center;margin-bottom:10px;">Login / Sign Up (Free)</a>
    <button onclick="document.getElementById('login-modal').style.display='none'" style="width:100%;padding:12px;border-radius:11px;background:none;border:1.5px solid var(--border);color:var(--muted);cursor:pointer;font-size:.85rem;">Cancel</button>
  </div>
</div>

<script>
let photoB64 = null, photoMime = null;
let photoB64Create = null, photoMimeCreate = null;

// ── Tab routing ──
function goTab(name) {
  // Update URL without reload
  const url = name === 'home' ? '/' : '/?tab=' + name;
  history.pushState({tab: name}, '', url);
  activateTab(name);
}

function activateTab(name) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('active'));
  const page = document.getElementById('tab-' + name);
  if (page) page.classList.add('active');
  // find matching tab button
  document.querySelectorAll('.nav-tab').forEach(t => {
    if (t.getAttribute('onclick') === "goTab('" + name + "')") t.classList.add('active');
  });
  window.scrollTo(0,0);
}

// Handle browser back/forward
window.addEventListener('popstate', (e) => {
  const tab = (e.state && e.state.tab) ? e.state.tab : 'home';
  activateTab(tab);
});

// ── Home tab photo ──
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
  document.getElementById('login-modal').style.display = 'flex';
}

// ── Create tab photo ──
function handlePhotoCreate(e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (ev) => {
    photoB64Create  = ev.target.result.split(',')[1];
    photoMimeCreate = file.type;
    const prev = document.getElementById('upload-preview-create');
    const cont = document.getElementById('upload-content-create');
    prev.src = ev.target.result;
    prev.style.display = 'block';
    cont.style.display = 'none';
    document.getElementById('gen-btn-create').disabled = false;
    toast('Photo ready! Generate karo 🎨', 'ok');
  };
  reader.readAsDataURL(file);
}

function startGenerationCreate() {
  if (!photoB64Create) { toast('Pehle photo upload karo!', 'err'); return; }
  // Not logged in — show login modal
  document.getElementById('login-modal').style.display = 'flex';
}

// ── Search ──
function findByMobile() {
  const mob = document.getElementById('mob-find').value.replace(/\D/g,'');
  if (mob.length < 10) { toast('Valid mobile number dalein!', 'err'); return; }
  window.location.href = '<?= SITE_URL ?>/m/' + mob;
}

function doSearch() {
  const mob  = document.getElementById('search-mobile-inp').value.replace(/\D/g,'');
  const name = (document.getElementById('search-name-inp').value || '').trim();
  if (mob.length >= 10) {
    window.location.href = '<?= SITE_URL ?>/m/' + mob;
  } else if (name.length >= 2) {
    window.location.href = '<?= SITE_URL ?>/m/?name=' + encodeURIComponent(name);
  } else {
    toast('Mobile number (10 digits) ya business naam (2+ letters) dalein!', 'err');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const mobFind = document.getElementById('mob-find');
  if (mobFind) mobFind.addEventListener('keypress', (e) => { if(e.key==='Enter') findByMobile(); });
});

function toast(msg, type='info') {
  const d = document.createElement('div');
  d.className = 'toast ' + type;
  d.textContent = msg;
  document.body.appendChild(d);
  setTimeout(() => d.remove(), 3500);
}

// ── Theme ──
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
  applyTheme(current === 'dark' ? 'light' : 'dark');
}

document.addEventListener('DOMContentLoaded', initTheme);
</script>
</body>
</html>
