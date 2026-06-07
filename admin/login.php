<?php
require_once '../config.php';
if (loggedIn() && me()['role']==='admin') { header('Location: /admin/'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email = trim($_POST['email']??''); $pass = trim($_POST['password']??'');
    if ($email && $pass) {
        $db=db(); $em=$db->real_escape_string($email);
        $u=$db->query("SELECT * FROM users WHERE email='$em' AND role='admin'")->fetch_assoc();
        $valid = false;
        if ($u) {
            $stored = $u['password'];
            if (password_verify($pass, $stored)) {
                $valid = true;
            } elseif ($pass === $stored) {
                $hash = password_hash($pass, PASSWORD_BCRYPT);
                $hEsc = $db->real_escape_string($hash);
                $db->query("UPDATE users SET password='$hEsc' WHERE id=" . (int)$u['id']);
                $valid = true;
            }
        }
        if ($valid) {
            $_SESSION['uid']=$u['id']; $_SESSION['name']=$u['name']; $_SESSION['email']=$u['email']; $_SESSION['avatar']=$u['avatar']??'';
            header('Location: /admin/'); exit;
        } else { $error='Invalid credentials!'; }
    }
}
?><!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login — PosterWall</title>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>*{margin:0;padding:0;box-sizing:border-box;}body{font-family:'Poppins',sans-serif;background:#0a0a14;color:#f0f0f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background-image:radial-gradient(ellipse at 50% 30%,rgba(255,107,0,.1) 0%,transparent 60%);}
.card{background:#1a2540;border:1px solid rgba(255,255,255,.07);border-radius:18px;padding:36px 28px;width:100%;max-width:380px;text-align:center;}
.logo{font-family:'Baloo 2',cursive;font-size:1.7rem;font-weight:800;margin-bottom:4px;}.logo span{color:#FF6B00;}
.sub{color:#8892a4;font-size:.82rem;margin-bottom:28px;}
.inp{width:100%;padding:12px 14px;border-radius:10px;background:#12192b;border:1px solid rgba(255,255,255,.07);color:#f0f0f0;font-family:'Poppins',sans-serif;font-size:.95rem;margin-bottom:12px;text-align:left;}
.inp:focus{outline:none;border-color:#FF6B00;}
.btn{width:100%;padding:13px;border-radius:10px;background:#FF6B00;color:#fff;font-weight:700;font-size:.95rem;border:none;cursor:pointer;}
.err{background:rgba(255,85,85,.1);border:1px solid rgba(255,85,85,.3);color:#ff5555;padding:10px;border-radius:8px;font-size:.82rem;margin-bottom:12px;}
.back{margin-top:16px;font-size:.78rem;color:#8892a4;}.back a{color:#FF6B00;}
.site-footer{text-align:center;margin-top:24px;padding-top:14px;border-top:1px solid rgba(255,255,255,.07);font-size:.68rem;color:#8892a4;line-height:1.9;}
.site-footer strong{color:#FF6B00;font-weight:600;}
</style></head>
<body><div class="card">
<div class="logo">🦅 Poster<span>Wall</span></div>
<div class="sub">Admin Panel v2.0</div>
<?php if($error): ?><div class="err">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST">
<input type="email" name="email" class="inp" placeholder="Admin Email" value="<?= htmlspecialchars($_POST['email']??'') ?>" required>
<input type="password" name="password" class="inp" placeholder="Password" required>
<button type="submit" class="btn">🔐 Login</button>
</form>
<div class="back"><a href="<?= siteUrl('') ?>">← Back to PosterWall</a></div>
<div class="site-footer">
  <strong>A Product of TechEagles</strong><br>
  Under Mahakumbrix Innovation
</div>
</div></body></html>
