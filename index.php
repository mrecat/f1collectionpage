<?php
require_once 'db.php';
require_once 'auth.php';

$page = $_GET['page'] ?? 'home';

// Páginas que requieren ser admin
$protectedPages = ['add', 'edit'];
if (in_array($page, $protectedPages)) {
    requireAdmin();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- <title>🏎️ <?= SITE_NAME ?></title> -->
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
      <?php else: ?>
        <a href="?page=login" class="<?= $page==='login'?'active':'' ?> nav-login">🔒 ADMIN</a>
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
</footer>

<script src="app.js"></script>
</body>
</html>
