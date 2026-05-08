<?php
$d      = getHomeData();
$hero   = $d['hero'];
$stats  = $d['stats'];
$recents = $d['recents'] ?? [];

$recentSlugs = [];
foreach ($recents as $r) {
    $recentSlugs[] = htmlspecialchars(makeCarSlug($r));
}

$mosaicSlugs = [];
foreach ($d['mosaic'] as $m) {
    $mosaicSlugs[] = htmlspecialchars(makeCarSlug($m));
}
?>

<!-- HERO -->
<div class="home-hero">
  <?php if ($hero && $hero['thumb']): ?>
    <div class="home-hero-bg" style="background-image:url('<?= htmlspecialchars($hero['thumb']) ?>')"></div>
  <?php endif; ?>
  <div class="home-hero-overlay"></div>
  <div class="home-hero-content">
    <div class="home-hero-eyebrow">🏁 MUSEO VIRTUAL DE FÓRMULA 1</div>
    <!--<h1 class="home-hero-title">UNA COLECCIÓN<br><span>DE OTRO MUNDO</span></h1> -->
    <h1 class="home-hero-title">Leyendas<br><span>en Miniatura</span></h1>
    <p class="home-hero-sub">
      <?= $stats['total'] ?> autos de Fórmula 1 y algunos otros en escala, desde <?= $stats['years']['mn'] ?> hasta <?= $stats['years']['mx'] ?>.<br>
      Cada pieza tiene su historia. Cada foto, su momento.
    </p>
    <a href="?page=collection" class="btn btn-primary home-hero-cta">EXPLORA LA COLECCIÓN</a>
  </div>
  <div class="home-stats-bar">
    <div class="home-stat"><span class="home-stat-n"><?= $stats['total'] ?></span><span class="home-stat-l">AUTOS</span></div>
    <div class="home-stat-sep"></div>
    <div class="home-stat"><span class="home-stat-n"><?= $stats['teams'] ?></span><span class="home-stat-l">ESCUDERÍAS</span></div>
    <div class="home-stat-sep"></div>
    <div class="home-stat"><span class="home-stat-n"><?= $stats['years']['mn'] ?>–<?= $stats['years']['mx'] ?></span><span class="home-stat-l">AÑOS</span></div>
    <div class="home-stat-sep"></div>
    <div class="home-stat"><span class="home-stat-n"><?= $stats['photos'] ?></span><span class="home-stat-l">FOTOS</span></div>
  </div>
</div>

<!-- SOBRE LA COLECCIÓN -->
<div class="home-about-section">
  <div class="home-about-inner">
    <div class="home-about-icon">🏆</div>
    <div class="home-about-text">
      <div class="home-about-title">SOBRE ESTA COLECCIÓN</div>
      <p class="home-about-desc">
        Una pasión que arrancó hace años y no para de crecer. Cada auto a escala
        representa una era, un piloto, una historia de la Fórmula 1 o algunas otras categorias que conectan con la categoria. Desde los
        clásicos de los <?= $stats['years']['mn'] ?>s hasta los monoplazas más modernos,
        esta colección es un recorrido visual por la historia del deporte motor más
        fascinante del mundo.
      </p>
      <div class="home-about-tags">
        <span class="home-about-tag">🏎️ <?= $stats['total'] ?> modelos</span>
        <span class="home-about-tag">🏁 <?= $stats['teams'] ?> escuderías</span>
        <span class="home-about-tag">📸 <?= $stats['photos'] ?> fotos</span>
        <span class="home-about-tag">📅 <?= $stats['years']['mx'] - $stats['years']['mn'] ?> años de historia</span>
      </div>
    </div>
  </div>
</div>

<!-- MOSAICO -->
<?php if (!empty($d['mosaic'])): ?>
<div class="home-mosaic-section">
  <div class="home-section-header">
    <div class="home-section-title">ALGUNOS AUTOS DE LA COLECCIÓN</div>
    <a href="?page=collection" class="home-section-link">Ver todos →</a>
  </div>
  <div class="home-mosaic">
    <?php foreach ($d['mosaic'] as $i => $m): ?>
    <a href="?page=car&slug=<?= $mosaicSlugs[$i] ?>" class="home-mosaic-item">
      <img src="<?= htmlspecialchars($m['thumb']) ?>" alt="<?= htmlspecialchars($m['model']) ?>">
      <div class="home-mosaic-overlay">
        <span class="home-mosaic-year"><?= $m['year'] ?></span>
        <span class="home-mosaic-team"><?= htmlspecialchars($m['team']) ?></span>
        <?php if ($m['driver']): ?>
          <span class="home-mosaic-driver"><?= htmlspecialchars($m['driver']) ?></span>
        <?php endif; ?>
        <span class="home-mosaic-cta">Ver detalle →</span>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ÚLTIMAS INCORPORACIONES — CARRUSEL -->
<?php if (!empty($recents)): ?>
<div class="home-latest-section">
  <div class="home-section-header">
    <div class="home-section-title">⚡ ÚLTIMAS INCORPORACIONES</div>
    <a href="?page=collection" class="home-section-link">Ver toda la colección →</a>
  </div>

  <div class="carousel-wrap">
    <button class="carousel-arrow carousel-prev" onclick="carouselMove(-1)" aria-label="Anterior">&#8249;</button>

    <div class="carousel-track-wrap">
      <div class="carousel-track" id="carouselTrack">
        <?php foreach ($recents as $i => $r): ?>
        <div class="carousel-slide">
          <div class="home-latest-card">
            <a href="?page=car&slug=<?= $recentSlugs[$i] ?>" class="home-latest-img">
              <?php if ($r['thumb']): ?>
                <img src="<?= htmlspecialchars($r['thumb']) ?>" alt="<?= htmlspecialchars($r['model']) ?>">
              <?php else: ?>
                <span class="home-latest-noimg">🏎️</span>
              <?php endif; ?>
              <span class="home-latest-badge">NUEVO</span>
            </a>
            <div class="home-latest-body">
              <div class="home-latest-year"><?= $r['year'] ?></div>
              <div class="home-latest-team"><?= htmlspecialchars($r['team']) ?></div>
              <div class="home-latest-model"><?= htmlspecialchars($r['model']) ?></div>
              <?php if ($r['driver']): ?>
                <div class="home-latest-driver">🧑‍✈️ <?= htmlspecialchars($r['driver']) ?></div>
              <?php endif; ?>
              <?php if ($r['maker']): ?>
                <div class="home-latest-maker">🏭 <?= htmlspecialchars($r['maker']) ?></div>
              <?php endif; ?>
              <?php if ($r['created_at']): ?>
                <div class="home-latest-date">📅 <?= date('d/m/Y', strtotime($r['created_at'])) ?></div>
              <?php endif; ?>
              <?php if ($r['note']): ?>
                <div class="home-latest-note">"<?= htmlspecialchars($r['note']) ?>"</div>
              <?php endif; ?>
              <a href="?page=car&slug=<?= $recentSlugs[$i] ?>" class="btn btn-primary home-latest-btn">🔍 VER DETALLE</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <button class="carousel-arrow carousel-next" onclick="carouselMove(1)" aria-label="Siguiente">&#8250;</button>
  </div>

  <!-- Dots -->
  <div class="carousel-dots" id="carouselDots">
    <?php foreach ($recents as $i => $r): ?>
      <button class="carousel-dot <?= $i===0?'active':'' ?>" onclick="carouselGoTo(<?= $i ?>)" aria-label="Slide <?= $i+1 ?>"></button>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- CTA FINAL -->
<div class="home-cta-section">
  <div class="home-cta-text">¿Querés explorar la historia completa de la F1?</div>
  <div class="home-cta-btns">
    <a href="?page=collection" class="btn btn-primary">🏎️ VER LA COLECCIÓN</a>
    <a href="?page=timeline"   class="btn btn-ghost">📅 LÍNEA DE TIEMPO</a>
    <a href="?page=stats"      class="btn btn-ghost">📊 ESTADÍSTICAS</a>
  </div>
</div>


