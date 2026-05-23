<?php
$items = getMiniaturas([]);
$autos = [];
foreach ($items as $car) {
    $autos[] = [
        'id'         => $car['id'],
        'year'       => $car['year'],
        'team'       => $car['team'],
        'model'      => $car['model'],
        'driver'     => $car['driver'] ?? '',
        'maker'      => $car['maker'] ?? '',
        'collection' => $car['collection'] ?? '',
        'scale_img'  => $car['scale_img'] ?? '',
        'real_img'   => $car['real_img'] ?? '',
    ];
}
shuffle($autos);
$autosJson = json_encode($autos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);

// Determinar la era de cada año
function getEra(int $year): string {
    if ($year < 1950) return 'PRE F1';
    if ($year <= 1961) return 'ERA PREMODERNA';
    if ($year <= 1972) return 'ERA ALAS';
    if ($year <= 1982) return 'EFECTO SUELO';
    if ($year <= 1988) return 'ERA TURBO';
    if ($year <= 1994) return 'ERA ASPIRADA';
    if ($year <= 2004) return 'ERA SCHUMACHER';
    if ($year <= 2013) return 'ERA V8';
    if ($year <= 2021) return 'ERA HÍBRIDA';
    return 'EFECTO SUELO 2.0';
}

$eras = [];
foreach ($autos as $a) {
    $eras[$a['id']] = getEra((int)$a['year']);
}
$erasJson = json_encode($eras);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Modo Exposición — F1 Collection</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    background: #000;
    color: #fff;
    font-family: 'Formula1', 'Arial Narrow', Arial, sans-serif;
    overflow: hidden;
    height: 100vh;
    width: 100vw;
    cursor: none;
}
body.show-cursor { cursor: default; }

@font-face {
    font-family: 'Formula1';
    src: url('../fonts/FORMULA1-REGULAR_WEB.TTF') format('truetype');
    font-weight: 400;
}
@font-face {
    font-family: 'Formula1';
    src: url('../fonts/FORMULA1-BOLD_WEB.TTF') format('truetype');
    font-weight: 700;
}

/* ── Fondo / imagen ── */
#k-bg {
    position: fixed; inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    transition: opacity 0.8s ease;
    transform: scale(1.04);
    filter: brightness(0.45);
}
#k-bg-next {
    position: fixed; inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0;
    transform: scale(1.04);
    filter: brightness(0.45);
}

/* ── Gradientes ── */
#k-grad-bottom {
    position: fixed; bottom: 0; left: 0; right: 0; height: 70%;
    background: linear-gradient(to top, rgba(0,0,0,0.97) 0%, rgba(0,0,0,0.5) 50%, transparent 100%);
    pointer-events: none;
}
#k-grad-top {
    position: fixed; top: 0; left: 0; right: 0; height: 140px;
    background: linear-gradient(to bottom, rgba(0,0,0,0.8) 0%, transparent 100%);
    pointer-events: none;
}
#k-grad-left {
    position: fixed; top: 0; left: 0; bottom: 0; width: 30%;
    background: linear-gradient(to right, rgba(0,0,0,0.6) 0%, transparent 100%);
    pointer-events: none;
}

/* ── Barra superior ── */
#k-top {
    position: fixed; top: 0; left: 0; right: 0;
    display: flex; align-items: center; justify-content: space-between;
    padding: 22px 36px;
    z-index: 10;
}
#k-logo {
    font-size: 11px; letter-spacing: 4px; color: #cc0000; font-weight: 700;
}
#k-counter {
    font-size: 11px; letter-spacing: 2px; color: rgba(255,255,255,0.35);
}

/* ── Contenido principal ── */
#k-content {
    position: fixed; bottom: 0; left: 0; right: 0;
    padding: 0 56px 48px;
    z-index: 10;
    transition: opacity 0.4s ease;
}
#k-era {
    display: inline-block;
    background: #cc0000;
    color: #fff;
    font-size: 9px; letter-spacing: 3px; font-weight: 700;
    padding: 4px 12px; border-radius: 2px;
    margin-bottom: 14px;
}
#k-year {
    font-size: 13px; letter-spacing: 3px; color: rgba(255,255,255,0.45);
    margin-bottom: 6px;
}
#k-model {
    font-size: clamp(28px, 5vw, 52px);
    font-weight: 700; line-height: 1.05;
    color: #fff;
    margin-bottom: 8px;
    text-shadow: 0 2px 20px rgba(0,0,0,0.5);
}
#k-team {
    font-size: clamp(14px, 2vw, 18px);
    color: rgba(255,255,255,0.55);
    margin-bottom: 24px;
}
#k-meta {
    display: flex; gap: 36px; margin-bottom: 28px;
    flex-wrap: wrap;
}
.k-meta-item { display: flex; flex-direction: column; gap: 3px; }
.k-meta-label {
    font-size: 9px; letter-spacing: 2.5px; color: rgba(255,255,255,0.3);
}
.k-meta-value {
    font-size: 13px; color: rgba(255,255,255,0.8); font-weight: 700; letter-spacing: 1px;
}

/* ── Barra de progreso ── */
#k-progress-wrap {
    height: 2px; background: rgba(255,255,255,0.1); border-radius: 1px; overflow: hidden;
    width: min(480px, 100%);
}
#k-progress-bar {
    height: 100%; background: #cc0000; border-radius: 1px; width: 0%;
    transition: width 0.1s linear;
}

/* ── Navegación ── */
#k-nav-left, #k-nav-right {
    position: fixed; top: 50%; transform: translateY(-50%);
    z-index: 20;
    width: 52px; height: 52px; border-radius: 50%;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 22px; color: rgba(255,255,255,0.5);
    transition: background 0.2s, color 0.2s;
    opacity: 0; transition: opacity 0.3s;
}
#k-nav-left { left: 16px; }
#k-nav-right { right: 16px; }
#k-nav-left:hover, #k-nav-right:hover {
    background: rgba(255,255,255,0.15); color: #fff;
}

/* ── Controles flotantes ── */
#k-controls {
    position: fixed; top: 22px; right: 36px;
    display: flex; gap: 10px; align-items: center;
    z-index: 20; opacity: 0; transition: opacity 0.3s;
}
.k-ctrl-btn {
    width: 34px; height: 34px; border-radius: 50%;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: rgba(255,255,255,0.6);
    font-size: 14px; text-decoration: none;
    transition: background 0.2s, color 0.2s;
}
.k-ctrl-btn:hover { background: rgba(255,255,255,0.18); color: #fff; }
#k-btn-pause.paused { color: #cc0000; border-color: rgba(204,0,0,0.4); }

/* ── Foto escala badge ── */
#k-scale-badge {
    position: fixed; bottom: 48px; right: 56px;
    font-size: 9px; letter-spacing: 2px; color: rgba(255,255,255,0.2);
    z-index: 10;
}

/* ── Sin autos ── */
#k-empty {
    position: fixed; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: 16px; display: none;
}
#k-empty p { font-size: 16px; color: rgba(255,255,255,0.4); letter-spacing: 2px; }

/* ── Hover reveal controls ── */
body:hover #k-nav-left,
body:hover #k-nav-right,
body:hover #k-controls { opacity: 1; }

/* ── Animación entrada texto ── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.k-animate { animation: fadeUp 0.5s ease forwards; }
</style>
</head>
<body>

<div id="k-bg"></div>
<div id="k-bg-next"></div>
<div id="k-grad-bottom"></div>
<div id="k-grad-top"></div>
<div id="k-grad-left"></div>

<!-- Top -->
<div id="k-top">
    <div id="k-logo">🏁 F1 COLLECTION — MODO EXPOSICIÓN</div>
    <div id="k-counter"></div>
</div>

<!-- Flechas nav -->
<div id="k-nav-left" onclick="prev()">&#8249;</div>
<div id="k-nav-right" onclick="next()">&#8250;</div>

<!-- Controles -->
<div id="k-controls">
    <div class="k-ctrl-btn" id="k-btn-pause" onclick="togglePause()" title="Pausar / Reanudar">⏸</div>
    <div class="k-ctrl-btn" id="k-btn-fs" onclick="toggleFullscreen()" title="Pantalla completa">⛶</div>
    <a class="k-ctrl-btn" href="?page=home" title="Salir del kiosco">✕</a>
</div>

<!-- Contenido del auto -->
<div id="k-content">
    <div id="k-era"></div>
    <div id="k-year"></div>
    <div id="k-model"></div>
    <div id="k-team"></div>
    <div id="k-meta">
        <div class="k-meta-item" id="k-driver-wrap">
            <span class="k-meta-label">PILOTO</span>
            <span class="k-meta-value" id="k-driver"></span>
        </div>
        <div class="k-meta-item" id="k-maker-wrap">
            <span class="k-meta-label">FABRICANTE</span>
            <span class="k-meta-value" id="k-maker"></span>
        </div>
        <div class="k-meta-item" id="k-collection-wrap">
            <span class="k-meta-label">COLECCIÓN</span>
            <span class="k-meta-value" id="k-collection"></span>
        </div>
    </div>
    <div id="k-progress-wrap">
        <div id="k-progress-bar"></div>
    </div>
</div>

<div id="k-scale-badge">FOTO DE MINIATURA</div>

<div id="k-empty">
    <p>SIN MINIATURAS CON FOTOS</p>
    <a href="?page=home" style="color:#cc0000;letter-spacing:2px;font-size:11px;">VOLVER AL INICIO</a>
</div>

<script>
var autos = <?= $autosJson ?>;
var eras  = <?= $erasJson ?>;

var idx       = 0;
var paused    = false;
var progress  = 0;
var interval  = null;
var DURATION  = 10000; // ms por slide
var TICK      = 80;    // ms por tick

if (autos.length === 0) {
    document.getElementById('k-empty').style.display = 'flex';
}

function getEra(id) {
    return eras[id] || '';
}

function render(i, animate) {
    var a = autos[i];
    if (!a) return;

    // Fondo: preferir foto de miniatura (scale_img), sino real_img
    var img = a.scale_img || a.real_img || '';

    var bgNext = document.getElementById('k-bg-next');
    var bgCur  = document.getElementById('k-bg');

    if (img) {
        bgNext.style.backgroundImage = "url('" + img + "')";
        bgNext.style.opacity = '0';

        setTimeout(function() {
            bgCur.style.backgroundImage = "url('" + img + "')";
            bgNext.style.opacity = '0';
        }, 800);
    } else {
        bgCur.style.backgroundImage = 'none';
    }

    var content = document.getElementById('k-content');
    content.style.opacity = '0';
    setTimeout(function() {
        document.getElementById('k-era').textContent   = getEra(a.id);
        document.getElementById('k-year').textContent  = a.year;
        document.getElementById('k-model').textContent = a.model;
        document.getElementById('k-team').textContent  = a.team;
        document.getElementById('k-counter').textContent = (i + 1) + ' / ' + autos.length;

        var driverWrap = document.getElementById('k-driver-wrap');
        if (a.driver) {
            document.getElementById('k-driver').textContent = a.driver;
            driverWrap.style.display = 'flex';
        } else {
            driverWrap.style.display = 'none';
        }

        var makerWrap = document.getElementById('k-maker-wrap');
        if (a.maker) {
            document.getElementById('k-maker').textContent = a.maker;
            makerWrap.style.display = 'flex';
        } else {
            makerWrap.style.display = 'none';
        }

        var collWrap = document.getElementById('k-collection-wrap');
        if (a.collection) {
            document.getElementById('k-collection').textContent = a.collection;
            collWrap.style.display = 'flex';
        } else {
            collWrap.style.display = 'none';
        }

        content.style.opacity = '1';
    }, 300);

    // Badge foto
    document.getElementById('k-scale-badge').textContent =
        a.scale_img ? 'FOTO DE MINIATURA' : (a.real_img ? 'FOTO DEL AUTO REAL' : '');
}

function startProgress() {
    progress = 0;
    document.getElementById('k-progress-bar').style.width = '0%';
    clearInterval(interval);
    interval = setInterval(function() {
        if (paused) return;
        progress += (TICK / DURATION) * 100;
        document.getElementById('k-progress-bar').style.width = Math.min(progress, 100) + '%';
        if (progress >= 100) {
            next();
        }
    }, TICK);
}

function next() {
    idx = (idx + 1) % autos.length;
    render(idx, true);
    startProgress();
}

function prev() {
    idx = (idx - 1 + autos.length) % autos.length;
    render(idx, true);
    startProgress();
}

function togglePause() {
    paused = !paused;
    var btn = document.getElementById('k-btn-pause');
    btn.textContent = paused ? '▶' : '⏸';
    btn.classList.toggle('paused', paused);
}

function toggleFullscreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        document.getElementById('k-btn-fs').textContent = '⊠';
    } else {
        document.exitFullscreen();
        document.getElementById('k-btn-fs').textContent = '⛶';
    }
}

// Teclado
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowRight' || e.key === ' ') { e.preventDefault(); next(); }
    if (e.key === 'ArrowLeft')                    { e.preventDefault(); prev(); }
    if (e.key === 'p' || e.key === 'P')           { togglePause(); }
    if (e.key === 'f' || e.key === 'F')           { toggleFullscreen(); }
    if (e.key === 'Escape' && !document.fullscreenElement) {
        window.location.href = '?page=home';
    }
});

// Cursor: ocultar después de 3s de inactividad
var cursorTimer;
document.addEventListener('mousemove', function() {
    document.body.classList.add('show-cursor');
    clearTimeout(cursorTimer);
    cursorTimer = setTimeout(function() {
        document.body.classList.remove('show-cursor');
    }, 3000);
});

// Arrancar
if (autos.length > 0) {
    render(idx, false);
    startProgress();
}
</script>
</body>
</html>
<?php
// Salir antes de que index.php agregue el footer
exit;
?>
