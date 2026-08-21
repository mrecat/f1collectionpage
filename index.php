<?php
require_once 'db.php';
require_once 'auth.php';

$page = $_GET['page'] ?? 'home';

// Páginas que requieren ser admin
$protectedPages = ['add', 'edit', 'about_edit'];
if (in_array($page, $protectedPages)) {
    requireAdmin();
}

// ── Museo Fangio: detectar si esta vista pertenece a esa colección ────────
// (se usa para aplicar el tema visual distinto sin tocar el resto del sitio)
$isFangio = false;
if ($page === 'museo_fangio') {
    $isFangio = true;
} elseif ($page === 'add' && ($_GET['cat'] ?? '') === 'fangio') {
    $isFangio = true;
}

// ── SEO dinámico por página ───────────────────────────────────────────────
$siteUrl  = 'https://f1collection.onrender.com';
$siteImg  = $siteUrl . '/img/og-default.jpg'; // imagen por defecto para compartir

$totalCarsSite = getTotalCars('f1') + getTotalCars('fangio');

$seoTitle = 'F1 Collection — Museo privado de miniaturas de Fórmula 1';
$seoDesc  = 'Colección personal de más de 200 miniaturas de Fórmula 1, desde 1936 hasta hoy. Autos reales y a escala de todas las épocas y escuderías.';
$seoImg   = $siteImg;
$seoUrl   = $siteUrl . '/?page=' . $page;

if ($page === 'car' && isset($_GET['slug'])) {
    $carSeo = getCarBySlug($_GET['slug']);
    if ($carSeo) {
        $isFangio = ($carSeo['category'] ?? 'f1') === 'fangio';
        $thumb = getFirstImage((int)$carSeo['id']);
        $seoTitle = $carSeo['year'] . ' ' . $carSeo['team'] . ' ' . $carSeo['model'] . ' — F1 Collection';
        $seoDesc  = $carSeo['driver']
            ? 'Miniatura del ' . $carSeo['model'] . ' (' . $carSeo['year'] . ') pilotado por ' . $carSeo['driver'] . '. Colección F1 a escala.'
            : 'Miniatura del ' . $carSeo['model'] . ' (' . $carSeo['year'] . '). Colección F1 a escala.';
        if ($carSeo['note']) $seoDesc = $carSeo['note'];
        if ($thumb) $seoImg = $siteUrl . '/' . ltrim($thumb, '/');
        $seoUrl = $siteUrl . '/?page=car&slug=' . urlencode($_GET['slug']);
    }
} elseif ($page === 'edit' && isset($_GET['id'])) {
    $carEditSeo = getCarById((int)$_GET['id']);
    if ($carEditSeo) $isFangio = ($carEditSeo['category'] ?? 'f1') === 'fangio';
} elseif ($page === 'collection') {
    $seoTitle = 'Colección completa — F1 Collection';
    $seoDesc  = 'Explorá los ' . getTotalCars('f1') . ' autos de Fórmula 1 en escala de la colección: Ferrari, McLaren, Red Bull, Mercedes y más.';
} elseif ($page === 'museo_fangio') {
    $seoTitle = 'Museo Fangio — F1 Collection';
    $seoDesc  = 'La colección de autos de Juan Manuel Fangio, el quíntuple campeón mundial argentino.';
} elseif ($page === 'stats') {
    $seoTitle = 'Estadísticas — F1 Collection';
    $seoDesc  = 'Estadísticas de la colección de miniaturas F1: por escudería, año, piloto y fabricante.';
} elseif ($page === 'timeline') {
    $seoTitle = 'Historia de la F1 — F1 Collection';
    $seoDesc  = 'Recorré la historia de la Fórmula 1 a través de los autos de la colección, desde 1936 hasta hoy.';
} elseif ($page === 'miniaturas') {
    $seoTitle = 'Miniaturas — F1 Collection';
    $seoDesc  = 'Las miniaturas a escala de la colección: IXO, BBurago, Spark, Salvat y más fabricantes.';
} elseif ($page === 'about') {
    $seoTitle = 'Sobre la colección — F1 Collection';
    $seoDesc  = '13 años coleccionando miniaturas de Fórmula 1.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<!-- SEO básico -->
<title><?= htmlspecialchars($seoTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($seoDesc) ?>">
<link rel="canonical" href="<?= htmlspecialchars($seoUrl) ?>">

<!-- Open Graph (WhatsApp, Facebook, etc.) -->
<meta property="og:type"        content="website">
<meta property="og:url"         content="<?= htmlspecialchars($seoUrl) ?>">
<meta property="og:title"       content="<?= htmlspecialchars($seoTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($seoDesc) ?>">
<meta property="og:image"       content="<?= htmlspecialchars($seoImg) ?>">
<meta property="og:locale"      content="es_AR">
<meta property="og:site_name"   content="F1 Collection">

<!-- Twitter Card -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= htmlspecialchars($seoTitle) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($seoDesc) ?>">
<meta name="twitter:image"       content="<?= htmlspecialchars($seoImg) ?>">

<link rel="icon" type="image/x-icon" href="favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="favicon-32.png">
<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="enhancements.css">
</head>
<body class="<?= $isFangio ? 'theme-fangio' : '' ?>">

<div class="track-line"></div>

<header>
  <div class="header-inner">
    <a href="?page=home" class="logo">
      <!-- <span class="logo-flag">🏁</span> -->
      <!-- <img src="img/flag.png" class="logo-flag" alt="flag"> -->
      <div>
        <span class="logo-title"><?= SITE_NAME ?></span>
        <span class="logo-sub"><?= SITE_SUB ?></span>
      </div>
    </a>
    <nav>
      <a href="?page=home"       class="<?= $page==='home'?'active':'' ?>">🏠 Inicio</a>
      <a href="?page=collection" class="<?= $page==='collection'?'active':'' ?>">🏎️ Autos</a>
      <a href="?page=miniaturas" class="<?= $page==='miniaturas'?'active':'' ?>">🔬 Miniaturas</a>
      <a href="?page=stats"      class="<?= $page==='stats'?'active':'' ?>">📊 Stats</a>
      <a href="?page=timeline"   class="<?= $page==='timeline'?'active':'' ?>">📅 Historia</a>
      <a href="?page=museo_fangio" class="nav-fangio <?= $page==='museo_fangio'?'active':'' ?>"><svg width="34" height="22" viewBox="0 0 20 13" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;"><rect width="20" height="13" fill="#75AADB"/><rect y="4.33" width="20" height="4.33" fill="#FFFFFF"/><circle cx="10" cy="6.5" r="1.6" fill="#F6B40E" stroke="#85340A" stroke-width="0.2"/></svg>Museo Fangio</a>
      <a href="?page=about"      class="<?= $page==='about'?'active':'' ?>">👤 Sobre mí</a>
      <?php if (isAdmin()): ?>
        <a href="?page=add" class="<?= ($page==='add' && ($_GET['cat'] ?? '')!=='fangio')?'active':'' ?>">➕ Agregar</a>
        <a href="?logout=1" class="nav-logout" title="Cerrar sesión admin">🔓 SALIR</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main>
<?php
switch($page) {
  case 'home':       include 'pages/home.php';       break;
  case 'stats':      include 'pages/stats.php';      break;
  case 'timeline':   include 'pages/timeline.php';   break;
  case 'miniaturas': include 'pages/miniaturas.php';  break;
  case 'about':      include 'pages/about.php';      break;
  case 'museo_fangio': include 'pages/museo_fangio.php'; break;
  case 'about_edit': include 'pages/about_edit.php'; break;
  case 'add':        include 'pages/add.php';        break;
  case 'edit':       include 'pages/edit.php';       break;
  case 'car':        include 'pages/car.php';        break;
  case 'login':      include 'pages/login.php';      break;
  case 'kiosco':     include 'pages/kiosco.php';     break;
  default:           include 'pages/collection.php'; break;
}
?>
</main>

<div class="track-line bottom"></div>

<!-- ── Command palette (Ctrl+K) ── -->
<div class="cmdk-overlay" id="cmdkOverlay">
  <div class="cmdk-box">
    <div class="cmdk-input-row">
      <span class="cmdk-icon">🔍</span>
      <input type="text" id="cmdkInput" class="cmdk-input" placeholder="Buscar piloto, escudería, auto, año…" autocomplete="off">
      <span class="cmdk-esc">ESC</span>
    </div>
    <div class="cmdk-results" id="cmdkResults"></div>
  </div>
</div>

<!-- ── Volver arriba ── -->
<button class="back-to-top" id="backToTop" aria-label="Volver arriba">
  <svg class="back-to-top-ring" viewBox="0 0 40 40">
    <circle class="back-to-top-ring-bg" cx="20" cy="20" r="17"></circle>
    <circle class="back-to-top-ring-fg" id="backToTopRing" cx="20" cy="20" r="17"></circle>
  </svg>
  <span>▲</span>
</button>

<footer>
  <span><?= SITE_NAME ?> &copy; <?= date('Y') ?> &mdash; <?= $totalCarsSite ?> autos en la parrilla</span>
  <?php if (!isAdmin()): ?>
    <a href="?page=login" class="footer-admin-link">🔒</a>
  <?php endif; ?>
</footer>

<script src="app.js"></script>
<script>
// ── Datos para el buscador rápido (Ctrl+K) ────────────────────
// Liviano a propósito: solo lo esencial para buscar y armar el link.
window.__F1_SEARCH_DATA__ = <?php
    $allCarsSearch = array_merge(
        getCars([], 'year', 'asc', 'f1'),
        getCars([], 'year', 'asc', 'fangio')
    );
    echo json_encode(array_map(function ($c) {
        return [
            'y' => (int)$c['year'],
            't' => $c['team'],
            'm' => $c['model'],
            'd' => $c['driver'],
            's' => makeCarSlug($c),
        ];
    }, $allCarsSearch), JSON_UNESCAPED_UNICODE);
?>;
</script>
<script src="enhancements.js"></script>
</body>
</html>
