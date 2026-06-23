<?php
require_once '../config.php';

// Get token from URL or query string
$token = preg_replace('/[^a-f0-9A-F]/', '', $_GET['t'] ?? basename($_SERVER['REQUEST_URI']));
if (!$token || strlen($token) < 6) { header('Location: ' . siteUrl('')); exit; }

$db  = db();
$tok = $db->real_escape_string($token);
$res = $db->query("SELECT * FROM pages WHERE token='$tok' AND is_active=1");

if ($res->num_rows === 0) {
    http_response_code(404);
    ?><!DOCTYPE html>
    <html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Page Not Found — PosterWall</title>
    <style>body{font-family:sans-serif;background:#0a0a14;color:#f0f0f0;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;}
    .b{padding:40px 20px;}.e{font-size:3rem;margin-bottom:16px;}h2{color:#FF6B00;margin-bottom:10px;}p{color:#8892a4;margin-bottom:24px;}
    a{display:inline-block;background:#FF6B00;color:#fff;padding:12px 28px;border-radius:10px;font-weight:600;text-decoration:none;}</style>
    </head><body><div class="b"><div class="e">😕</div><h2>Page Nahi Mila</h2>
    <p>Yeh link expire ho gaya ya galat hai.</p>
    <a href="<?= siteUrl('') ?>">PosterWall pe Jao →</a></div></body></html>
    <?php exit;
}

$page = $res->fetch_assoc();

// Increment views
$db->query("UPDATE pages SET views=views+1 WHERE token='$tok'");

// Inject QR + share bar into the HTML before </body>
$shareUrl  = SITE_URL . '/p/' . $token;
$qrUrl     = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.urlencode($shareUrl).'&color=FF6B00&bgcolor=ffffff';
$waText    = urlencode('Hamara digital page dekho: ' . $shareUrl);
$mobUrl    = $page['mobile'] ? SITE_URL . '/m/' . $page['mobile'] : '';
$homeUrl   = siteUrl('');
$shareUrlEsc = htmlspecialchars($shareUrl, ENT_QUOTES);
$qrUrlEsc    = htmlspecialchars($qrUrl, ENT_QUOTES);
$mobUrlEsc   = htmlspecialchars($mobUrl, ENT_QUOTES);
$mobileEsc   = htmlspecialchars($page['mobile'] ?? '', ENT_QUOTES);
$clipboardShareUrl = addslashes($shareUrl);
$mobileHtml = $mobUrl ? '&nbsp;|&nbsp; Mobile URL: <a href="' . $mobUrlEsc . '" style="color:#FF6B00;">' . $mobileEsc . '</a>' : '';

$shareBar = <<<HTML
<!-- PosterWall Share Bar -->
<div id="pw-bar" style="position:fixed;bottom:0;left:0;right:0;z-index:9999;background:rgba(10,10,20,.97);border-top:1px solid rgba(255,107,0,.3);padding:10px 16px;display:flex;align-items:center;gap:8px;overflow-x:auto;backdrop-filter:blur(10px);">
  <a href="https://wa.me/?text={$waText}" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:#25D366;color:#fff;font-weight:600;font-size:.78rem;text-decoration:none;white-space:nowrap;flex-shrink:0;"><i class="fab fa-whatsapp"></i> Share</a>
  <button onclick="copyLink()" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:rgba(255,107,0,.15);border:1px solid rgba(255,107,0,.4);color:#FF6B00;font-weight:600;font-size:.78rem;cursor:pointer;white-space:nowrap;flex-shrink:0;"><i class="fas fa-copy"></i> Copy Link</button>
  <button onclick="document.getElementById('pw-qr-modal').style.display='flex'" style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#f0f0f0;font-weight:600;font-size:.78rem;cursor:pointer;white-space:nowrap;flex-shrink:0;"><i class="fas fa-qrcode"></i> QR Code</button>
  <div style="flex:1;"></div>
  <a href="{$homeUrl}" style="display:inline-flex;align-items:center;gap:4px;padding:8px 12px;border-radius:8px;background:#FF6B00;color:#fff;font-weight:700;font-size:.72rem;text-decoration:none;white-space:nowrap;flex-shrink:0;">🦅 PosterWall — ₹9</a>
</div>

<!-- QR Modal -->
<div id="pw-qr-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:99999;align-items:center;justify-content:center;padding:20px;" onclick="this.style.display='none'">
  <div style="background:#1a2540;border:1px solid rgba(255,107,0,.3);border-radius:20px;padding:32px 24px;text-align:center;max-width:320px;width:100%;" onclick="event.stopPropagation()">
    <div style="font-family:'Baloo 2',cursive;font-size:1.2rem;font-weight:800;color:#FF6B00;margin-bottom:6px;">📲 QR Code</div>
    <p style="color:#8892a4;font-size:.8rem;margin-bottom:16px;">Scan karo — page directly khul jaayega!</p>
    <img src="{$qrUrlEsc}" style="width:180px;height:180px;border-radius:12px;border:3px solid #FF6B00;margin:0 auto 16px;display:block;">
    <div style="font-size:.78rem;color:#8892a4;margin-bottom:12px;word-break:break-all;">{$shareUrlEsc}</div>
    <a href="{$qrUrlEsc}" download="posterwall-qr.png" style="display:inline-block;padding:10px 20px;background:#FF6B00;color:#fff;border-radius:9px;font-weight:600;font-size:.85rem;text-decoration:none;margin-right:8px;"><i class="fas fa-download"></i> Download QR</a>
    <button onclick="document.getElementById('pw-qr-modal').style.display='none'" style="padding:10px 16px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:#f0f0f0;border-radius:9px;cursor:pointer;font-size:.85rem;">Close</button>
    <div style="margin-top:16px;font-size:.72rem;color:#8892a4;">
      Views: <strong style="color:#FF6B00;">{$page['views']}</strong>{$mobileHtml}
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script>
function copyLink(){
  navigator.clipboard.writeText('{$clipboardShareUrl}').then(()=>{
    const b=document.querySelector('#pw-bar button');
    const orig=b.innerHTML; b.innerHTML='✅ Copied!'; b.style.background='rgba(0,212,100,.15)'; b.style.borderColor='rgba(0,212,100,.4)'; b.style.color='#00d464';
    setTimeout(()=>{b.innerHTML=orig;b.style.background='';b.style.borderColor='';b.style.color='';},2000);
  });
}
// Add padding to page body so bar doesn't cover content
document.addEventListener('DOMContentLoaded',()=>{
  const bar=document.getElementById('pw-bar');
  if(bar){document.body.style.paddingBottom=(bar.offsetHeight+8)+'px';}
});
</script>
HTML;

// Inject share bar before </body>
$html = $page['html_content'];
$html = str_replace('</body>', $shareBar . '</body>', $html);

// Add meta tags if missing
if (strpos($html, '<meta name="viewport"') === false) {
    $html = str_replace('<head>', '<head><meta name="viewport" content="width=device-width,initial-scale=1">', $html);
}
if (strpos($html, '<title>') === false) {
    $html = str_replace('<head>', '<head><title>'.htmlspecialchars($page['business_name']).' — PosterWall</title>', $html);
}

// Output the page
header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: SAMEORIGIN');
echo $html;
?>
