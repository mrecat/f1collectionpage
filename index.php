<?php
require_once 'db.php';
require_once 'auth.php';

$page = $_GET['page'] ?? 'home';

// Páginas que requieren ser admin
$protectedPages = ['add', 'edit'];
if (in_array($page, $protectedPages)) {
    requireAdmin();
}

// ── SEO dinámico por página ───────────────────────────────────────────────
$siteUrl  = 'https://f1collection.onrender.com';
$siteImg  = $siteUrl . '/img/og-default.jpg'; // imagen por defecto para compartir

$seoTitle = 'F1 Collection — Museo privado de miniaturas de Fórmula 1';
$seoDesc  = 'Colección personal de más de 240 miniaturas de Fórmula 1, desde 1936 hasta hoy. Autos reales y a escala de todas las épocas y escuderías.';
$seoImg   = $siteImg;
$seoUrl   = $siteUrl . '/?page=' . $page;

if ($page === 'car' && isset($_GET['slug'])) {
    $carSeo = getCarBySlug($_GET['slug']);
    if ($carSeo) {
        $thumb = getFirstImage((int)$carSeo['id']);
        $seoTitle = $carSeo['year'] . ' ' . $carSeo['team'] . ' ' . $carSeo['model'] . ' — F1 Collection';
        $seoDesc  = $carSeo['driver']
            ? 'Miniatura del ' . $carSeo['model'] . ' (' . $carSeo['year'] . ') pilotado por ' . $carSeo['driver'] . '. Colección F1 a escala.'
            : 'Miniatura del ' . $carSeo['model'] . ' (' . $carSeo['year'] . '). Colección F1 a escala.';
        if ($carSeo['note']) $seoDesc = $carSeo['note'];
        if ($thumb) $seoImg = $siteUrl . '/' . ltrim($thumb, '/');
        $seoUrl = $siteUrl . '/?page=car&slug=' . urlencode($_GET['slug']);
    }
} elseif ($page === 'collection') {
    $seoTitle = 'Colección completa — F1 Collection';
    $seoDesc  = 'Explorá los ' . getTotalCars() . ' autos de Fórmula 1 en escala de la colección: Ferrari, McLaren, Red Bull, Mercedes y más.';
} elseif ($page === 'stats') {
    $seoTitle = 'Estadísticas — F1 Collection';
    $seoDesc  = 'Estadísticas de la colección de miniaturas F1: por escudería, año, piloto y fabricante.';
} elseif ($page === 'timeline') {
    $seoTitle = 'Historia de la F1 — F1 Collection';
    $seoDesc  = 'Recorré la historia de la Fórmula 1 a través de los autos de la colección, desde 1936 hasta hoy.';
} elseif ($page === 'miniaturas') {
    $seoTitle = 'Miniaturas — F1 Collection';
    $seoDesc  = 'Las miniaturas a escala de la colección: IXO, BBurago, Spark, Salvat y más fabricantes.';
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
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

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
      <?php if (isAdmin()): ?>
        <a href="?page=add" class="<?= $page==='add'?'active':'' ?>">➕ Agregar</a>
        <a href="?logout=1" class="nav-logout" title="Cerrar sesión admin">🔓 SALIR</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main>
<?php
switch($page) {
  case 'home':      include 'pages/home.php';       break;
  case 'stats':     include 'pages/stats.php';      break;
  case 'timeline':    include 'pages/timeline.php';    break;
  case 'miniaturas':  include 'pages/miniaturas.php';  break;
  case 'add':       include 'pages/add.php';        break;
  case 'edit':      include 'pages/edit.php';       break;
  case 'car':       include 'pages/car.php';        break;
  case 'login':     include 'pages/login.php';      break;
  default:          include 'pages/collection.php'; break;
}
?>
</main>

<div class="track-line bottom"></div>

<footer>
  <span><?= SITE_NAME ?> &copy; <?= date('Y') ?> &mdash; <?= getTotalCars() ?> autos en la parrilla</span>
  <div class="footer-cafecito">
    <span class="footer-cafecito-text">¿Te gustó el sitio? Invitame un café ☕</span>
    <a href="https://cafecito.app/f1collection" rel="noopener" target="_blank" class="footer-cafecito-btn">
      <img src="https://cdn.cafecito.app/imgs/buttons/button_5.svg" alt="Invitame un café en cafecito.app" />
    </a>
  </div>
  <?php if (!isAdmin()): ?>
    <a href="?page=login" class="footer-admin-link">🔒</a>
  <?php endif; ?>
</footer>

<script src="app.js"></script>
</body>
</html>
