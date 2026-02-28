<?php
declare(strict_types=1);

$panelFile = __DIR__ . '/dj/dj.php';
if (!is_file($panelFile)) {
    http_response_code(404);
    exit('DJ panel not found.');
}

$panelUrl = '/dj/dj.php';
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Habble DJ Panel</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --hud-bg: #061724;
            --hud-panel: #0c2435;
            --hud-panel-soft: rgba(12, 36, 53, 0.9);
            --hud-border: rgba(255, 255, 255, 0.18);
            --hud-text: #f5fbff;
            --hud-subtle: #9ec4df;
            --hud-primary: #2a95de;
            --hud-primary-dark: #1876b4;
            --hud-shadow: rgba(1, 8, 14, 0.5);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: "Exo 2", sans-serif;
            color: var(--hud-text);
            background: var(--hud-bg);
        }

        body {
            background-image:
                linear-gradient(180deg, rgba(6, 23, 36, 0.8) 0%, rgba(6, 23, 36, 0.96) 50%, rgba(2, 11, 18, 1) 100%),
                url('/images/background.webp');
            background-size: cover;
            background-position: center top;
            background-attachment: fixed;
        }

        .hud-clouds {
            position: fixed;
            inset: 0 0 auto 0;
            height: 180px;
            background: url('/images/clouds.png') repeat-x center top / auto 180px;
            opacity: 0.35;
            pointer-events: none;
            z-index: 0;
        }

        .hud-shell {
            position: relative;
            z-index: 1;
            max-width: 1320px;
            margin: 0 auto;
            padding: 16px 14px 24px;
        }

        .hud-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 10px 14px;
            border: 1px solid var(--hud-border);
            border-radius: 14px;
            background: linear-gradient(180deg, rgba(27, 70, 100, 0.9), rgba(10, 31, 47, 0.92));
            box-shadow: 0 14px 30px var(--hud-shadow);
            backdrop-filter: blur(4px);
        }

        .hud-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .hud-logo {
            width: 124px;
            height: auto;
            display: block;
            image-rendering: auto;
        }

        .hud-meta {
            min-width: 0;
        }

        .hud-title {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0.3px;
        }

        .hud-subtitle {
            margin: 6px 0 0;
            color: var(--hud-subtle);
            font-size: 13px;
            font-weight: 500;
            line-height: 1.2;
        }

        .hud-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 8px;
        }

        .hud-btn {
            appearance: none;
            border: 1px solid var(--hud-border);
            border-radius: 10px;
            padding: 9px 12px;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            line-height: 1;
            color: var(--hud-text);
            text-decoration: none;
            background: linear-gradient(180deg, rgba(35, 108, 160, 0.95), rgba(17, 71, 112, 0.95));
            transition: transform 0.14s ease, filter 0.14s ease, border-color 0.14s ease;
            cursor: pointer;
        }

        .hud-btn:hover {
            filter: brightness(1.07);
            transform: translateY(-1px);
            border-color: rgba(255, 255, 255, 0.32);
        }

        .hud-btn.secondary {
            background: linear-gradient(180deg, rgba(18, 59, 87, 0.95), rgba(8, 35, 55, 0.95));
        }

        .hud-btn.ghost {
            background: rgba(8, 37, 58, 0.6);
        }

        .hud-panel {
            margin-top: 14px;
            border: 1px solid var(--hud-border);
            border-radius: 14px;
            background: var(--hud-panel-soft);
            box-shadow: 0 16px 40px var(--hud-shadow);
            overflow: hidden;
        }

        .hud-nav {
            margin-top: 12px;
            display: grid;
            grid-template-columns: repeat(5, minmax(90px, 1fr));
            gap: 8px;
        }

        .hud-nav-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 42px;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            color: #dbf2ff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.2px;
            background: linear-gradient(180deg, rgba(18, 54, 80, 0.86), rgba(10, 33, 51, 0.9));
            transition: transform 0.14s ease, border-color 0.14s ease, filter 0.14s ease;
        }

        .hud-nav-item:hover {
            transform: translateY(-1px);
            filter: brightness(1.08);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .hud-nav-item img {
            width: 18px;
            height: 18px;
            object-fit: contain;
            image-rendering: auto;
            flex: 0 0 auto;
        }

        .hud-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            padding: 10px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            background: linear-gradient(180deg, rgba(14, 53, 79, 0.95), rgba(9, 35, 55, 0.95));
        }

        .hud-panel-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 700;
            color: #d8f1ff;
            background: rgba(16, 135, 210, 0.22);
            border: 1px solid rgba(115, 206, 255, 0.36);
        }

        .hud-panel-tools {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .hud-frame-wrap {
            height: calc(100vh - 190px);
            min-height: 620px;
            background: #0b1f2f;
        }

        .hud-frame {
            border: 0;
            width: 100%;
            height: 100%;
            display: block;
            background: #0b1f2f;
        }

        @media (max-width: 920px) {
            .hud-shell {
                padding: 10px 10px 14px;
            }

            .hud-topbar {
                padding: 10px;
            }

            .hud-logo {
                width: 108px;
            }

            .hud-title {
                font-size: 17px;
            }

            .hud-subtitle {
                font-size: 12px;
            }

            .hud-btn {
                font-size: 12px;
                padding: 8px 10px;
            }

            .hud-frame-wrap {
                height: calc(100vh - 220px);
                min-height: 520px;
            }

            .hud-nav {
                grid-template-columns: repeat(2, minmax(120px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="hud-clouds" aria-hidden="true"></div>
    <div class="hud-shell">
        <header class="hud-topbar">
            <div class="hud-brand">
                <img class="hud-logo" src="/images/logo.png" alt="Habble">
                <div class="hud-meta">
                    <h1 class="hud-title">DJ Control HUD</h1>
                    <p class="hud-subtitle">Panel integrado con el estilo de Habble</p>
                </div>
            </div>
            <div class="hud-actions">
                <a class="hud-btn secondary" href="/">Home</a>
                <a class="hud-btn secondary" href="/pages/radio">Radio</a>
                <a class="hud-btn ghost" href="<?php echo htmlspecialchars($panelUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">Open panel</a>
            </div>
        </header>

        <nav class="hud-nav" aria-label="Habble sections">
            <a class="hud-nav-item" href="/">
                <img src="/images/menu/inicio.png" alt="">
                <span>Inicio</span>
            </a>
            <a class="hud-nav-item" href="/pages/placas">
                <img src="/images/menu/habbo.png" alt="">
                <span>Habbo</span>
            </a>
            <a class="hud-nav-item" href="/pages/noticias">
                <img src="/images/menu/contenidos.png" alt="">
                <span>Contenidos</span>
            </a>
            <a class="hud-nav-item" href="/pages/generador-de-avatar">
                <img src="/images/menu/fancenter.png" alt="">
                <span>Fan Center</span>
            </a>
            <a class="hud-nav-item" href="/pages/radio">
                <img src="/images/menu/radio.png" alt="">
                <span>Radio</span>
            </a>
        </nav>

        <section class="hud-panel">
            <div class="hud-panel-head">
                <span class="hud-panel-badge">ONLINE DJ PANEL</span>
                <div class="hud-panel-tools">
                    <button id="reloadPanel" class="hud-btn secondary" type="button">Reload</button>
                    <button id="fullscreenPanel" class="hud-btn" type="button">Fullscreen</button>
                </div>
            </div>
            <div class="hud-frame-wrap">
                <iframe
                    id="djFrame"
                    class="hud-frame"
                    src="<?php echo htmlspecialchars($panelUrl, ENT_QUOTES, 'UTF-8'); ?>"
                    title="Habble DJ Panel"
                    loading="lazy"
                    referrerpolicy="same-origin"
                ></iframe>
            </div>
        </section>
    </div>

    <script>
        (function () {
            var frame = document.getElementById('djFrame');
            var reloadBtn = document.getElementById('reloadPanel');
            var fullscreenBtn = document.getElementById('fullscreenPanel');

            if (reloadBtn && frame) {
                reloadBtn.addEventListener('click', function () {
                    frame.contentWindow.location.reload();
                });
            }

            if (fullscreenBtn && frame) {
                fullscreenBtn.addEventListener('click', function () {
                    if (frame.requestFullscreen) {
                        frame.requestFullscreen();
                    }
                });
            }
        })();
    </script>
</body>
</html>
