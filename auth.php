<?php
require_once 'config.php';
if (isset($_SESSION['user'])) { header('Location: index.php'); exit; }
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $res = login_user($_POST['username'], $_POST['password']);
        if ($res === true) { header('Location: index.php'); exit; } else { $error = $res; }
    } elseif ($_POST['action'] === 'register') {
        $res = register_user($_POST['username'], $_POST['password']);
        if ($res === true) { $success = "Kayıt başarılı! Giriş yapabilirsiniz."; } else { $error = $res; }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Venom Hub - Auth Gate</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root { --neon-color: #ff0055; --bg-color: #040406; --card-bg: rgba(13, 13, 17, 0.95); --border-color: #3a1520; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg-color); color: white; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background-image: radial-gradient(circle, rgba(255,0,85,0.05) 0%, transparent 70%); }
        .auth-container { background: var(--card-bg); padding: 40px; border-radius: 24px; border: 1px solid var(--border-color); box-shadow: 0 0 30px rgba(255, 0, 85, 0.15); width: 100%; max-width: 400px; text-align: center; box-sizing: border-box; }
        h1 { font-size: 2rem; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 3px; text-shadow: 0 0 10px var(--neon-color); }
        .tabs { display: flex; gap: 10px; margin-bottom: 25px; }
        .tab-btn { flex: 1; background: #12121a; border: 1px solid #23232e; padding: 12px; color: #8c8c9e; cursor: pointer; border-radius: 10px; font-weight: bold; text-transform: uppercase; font-size: 12px; transition: 0.3s; }
        .tab-btn.active, .tab-btn:hover { border-color: var(--neon-color); color: white; background: rgba(255,0,85,0.05); }
        .form-panel { display: none; text-align: left; }
        .form-panel.active { display: block; }
        .input-group { margin-bottom: 18px; }
        label { display: block; font-size: 11px; color: #8c8c9e; text-transform: uppercase; margin-bottom: 6px; font-weight: bold; }
        input { width: 100%; padding: 14px; background: #0f0f14; border: 1px solid #23232e; border-radius: 10px; color: white; font-size: 15px; box-sizing: border-box; }
        input:focus { outline: none; border-color: var(--neon-color); }
        button.submit-btn { width: 100%; padding: 15px; background: linear-gradient(135deg, #ff0055, #a30030); border: none; border-radius: 10px; color: white; font-weight: bold; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; }
        .alert { padding: 12px; border-radius: 10px; font-size: 13px; margin-bottom: 15px; text-align: center; }
        .alert-danger { background: rgba(255,0,0,0.1); border: 1px solid red; color: #ff5c5c; }
        .alert-success { background: rgba(0,255,0,0.1); border: 1px solid green; color: #5cff5c; }
    </style>
</head>
<body>
<div class="auth-container">
    <h1>Venom Gate</h1>
    <?php if($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>
    <?php if($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>
    <div class="tabs">
        <button class="tab-btn active" onclick="switchForm('login', this)">Giriş Yap</button>
        <button class="tab-btn" onclick="switchForm('register', this)">Kayıt Ol</button>
    </div>
    <form id="loginForm" class="form-panel active" method="POST">
        <input type="hidden" name="action" value="login">
        <div class="input-group"><label>Kullanıcı Adı</label><input type="text" name="username" required autocomplete="off"></div>
        <div class="input-group"><label>Şifre</label><input type="password" name="password" required></div>
        <button type="submit" class="submit-btn">Sisteme Sız</button>
    </form>
    <form id="registerForm" class="form-panel" method="POST">
        <input type="hidden" name="action" value="register">
        <div class="input-group"><label>Kullanıcı Adı</label><input type="text" name="username" required autocomplete="off"></div>
        <div class="input-group"><label>Şifre</label><input type="password" name="password" required></div>
        <button type="submit" class="submit-btn">Hesap Oluştur</button>
    </form>
</div>
<script>
function switchForm(type, btn) {
    document.querySelectorAll('.form-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    if(type === 'login') document.getElementById('loginForm').classList.add('active');
    else document.getElementById('registerForm').classList.add('active');
    btn.classList.add('active');
}
</script>
</body>
</html>
