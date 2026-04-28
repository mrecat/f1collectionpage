<?php
$admin      = isAdmin();
$aboutText  = getSetting('about_text');
$aboutPhoto = getSetting('about_photo');
$totalCars  = getTotalCars();
?>

<div class="page-title">👤 SOBRE LA <span>COLECCIÓN</span></div>

<div class="about-wrap">

  <!-- Foto + stats -->
  <div class="about-left">
    <div class="about-photo-wrap">
      <?php if ($aboutPhoto): ?>
        <img src="<?= htmlspecialchars($aboutPhoto) ?>" alt="El coleccionista" class="about-photo">
      <?php else: ?>
        <div class="about-photo-placeholder">🏎️</div>
      <?php endif; ?>
    </div>

    <div class="about-stats">
      <div class="about-stat">
        <span class="about-stat-num">13</span>
        <span class="about-stat-label">AÑOS COLECCIONANDO</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-num">240<span class="about-stat-plus">+</span></span>
        <span class="about-stat-label">MINIATURAS EN TOTAL</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-num"><?= $totalCars ?></span>
        <span class="about-stat-label">AUTOS CATALOGADOS</span>
      </div>
      <div class="about-stat">
        <span class="about-stat-num">1936</span>
        <span class="about-stat-label">AÑO MÁS ANTIGUO</span>
      </div>
    </div>
  </div>

  <!-- Texto -->
  <div class="about-right">
    <?php if ($aboutText): ?>
  <div class="about-text">
    <?php
      $paragraphs = preg_split('/\r?\n\r?\n/', trim($aboutText));
      foreach ($paragraphs as $p) {
          if (trim($p)) echo '<p>' . nl2br(htmlspecialchars(trim($p))) . '</p>';
      }
    ?>
  </div>
    <?php else: ?>
      <div class="about-text about-text--empty">
        <?php if ($admin): ?>
          <p>Todavía no escribiste nada. Usá el panel de admin para agregar tu historia. ✏️</p>
        <?php else: ?>
          <p>Contenido próximamente.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Cafecito -->
    <div class="about-cafecito">
      <p class="about-cafecito-text">
        Este sitio lo construí y lo mantengo solo. Si te gustó la colección, podés invitarme un café. ☕
      </p>
      <a href="https://cafecito.app/f1collection" rel="noopener" target="_blank" class="about-cafecito-btn">
        <img src="https://cdn.cafecito.app/imgs/buttons/button_5.svg" alt="Invitame un café en cafecito.app">
      </a>
    </div>

    <?php if ($admin): ?>
    <div class="about-admin-bar">
      <a href="?page=about_edit" class="btn btn-ghost">✏️ EDITAR ESTA PÁGINA</a>
    </div>
    <?php endif; ?>
  </div>

</div>
