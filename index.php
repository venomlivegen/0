<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melisa'ma ❤️</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #ffe5ec, #ffc2d1);
            color: #4a0e17;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            text-align: center;
            padding: 20px;
        }

        /* Giriş Perdesi */
        .intro-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #ffc2d1, #ffe5ec);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 1s ease, transform 1s ease;
        }
        .intro-overlay.hidden {
            opacity: 0;
            pointer-events: none;
            transform: translateY(-100%);
        }
        .btn-start {
            background: #ff477e;
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.3rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(255, 71, 126, 0.4);
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .btn-start:hover {
            transform: scale(1.05);
            background: #ff0a54;
        }

        .container {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(10px);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 1s ease 0.5s, transform 1s ease 0.5s;
        }
        body.loaded .container {
            opacity: 1;
            transform: translateY(0);
        }

        h1 {
            font-size: 2.5rem;
            color: #ff477e;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .heart {
            font-size: 5rem;
            color: #ff0a54;
            animation: beat 1s infinite alternate;
            margin: 20px 0;
        }

        p {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 20px;
            font-weight: 500;
        }

        /* Şık ve Modern Oyantıcı Tasarımı */
        .music-box {
            margin-top: 25px;
            background: #ff758f;
            padding: 12px 25px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(255, 117, 143, 0.4);
        }
        
        audio {
            outline: none;
            height: 40px;
        }

        @keyframes beat {
            to { transform: scale(1.15); }
        }

        .falling-hearts {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="intro-overlay" id="introOverlay">
        <h2 style="color: #ff477e; margin-bottom: 25px; font-size: 1.8rem;">Melisa'm İçin Hazırlandı... ❤️</h2>
        <button class="btn-start" onclick="startEverything()">Aşkımızı Başlat ❤️</button>
    </div>

    <div class="falling-hearts" id="heartsContainer"></div>

    <div class="container">
        <h1>Melisam Ömrüm, Seni Çok Seviyorum! ❤️</h1>
        
        <div class="heart">❤️</div>
        
        <p>
            Hayatımın en güzel detayı, yüzümdeki gülümsemenin sebebi... <br>
            İyi ki varsın, iyi ki benimlesin. Sana olan aşkım her geçen gün daha da büyüyor. 
            Bu küçük köşe tamamen sana ve aşkımıza ait.
        </p>

        <p><i>"Sen benim gökyüzündeki en parlak yıldızımsın."</i></p>

        <div class="music-box">
            <audio id="myAudio" controls loop>
                <source src="sarki.mp3" type="audio/mpeg">
                Tarayıcın müzik çaları desteklemiyor.
            </audio>
        </div>
    </div>

    <script>
        const audio = document.getElementById('myAudio');

        function startEverything() {
            // Perdeyi kaldır ve sayfayı göster
            document.getElementById('introOverlay').classList.add('hidden');
            document.body.classList.add('loaded');
            document.body.style.overflow = 'auto';

            // Kendi sunucumuzdaki şarkıyı başlat
            if (audio) {
                audio.play().catch(error => {
                    console.log("Otomatik oynatma engeline takıldı, kullanıcı el ile başlatmalı:", error);
                });
            }

            // Kalp uçurma efektini başlat
            setInterval(createHeart, 300);
        }

        // Arka plandaki kalpler
        function createHeart() {
            const heart = document.createElement('div');
            heart.innerText = '❤️';
            heart.style.position = 'absolute';
            heart.style.left = Math.random() * 100 + 'vw';
            heart.style.top = '100vh';
            heart.style.fontSize = Math.random() * 20 + 15 + 'px';
            heart.style.opacity = Math.random() * 0.7 + 0.3;
            heart.style.transform = 'translateY(0)';
            heart.style.transition = 'transform 5s linear, opacity 5s linear';
            
            document.getElementById('heartsContainer').appendChild(heart);

            setTimeout(() => {
                heart.style.transform = 'translateY(-105vh)';
                heart.style.opacity = '0';
            }, 100);

            setTimeout(() => {
                heart.remove();
            }, 5100);
        }
    </script>
</body>
</html>
