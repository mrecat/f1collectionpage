<?php
$d      = getHomeData();
$hero   = $d['hero'];
$stats  = $d['stats'];
$latest = $d['latest'] ?? null;

// JSON del último auto para el modal
$latestJson = '';
if ($latest) {
    $latestJson = htmlspecialchars(json_encode([
        'id'     => $latest['id'],
        'year'   => $latest['year'],
        'team'   => $latest['team'],
        'model'  => $latest['model'],
        'driver' => $latest['driver'],
        'maker'  => $latest['maker'],
        'note'   => $latest['note'],
        'img'    => $latest['thumb'] ?? '',
        'imgs'   => [$latest['thumb'] ?? ''],
        'admin'  => false,
    ]), ENT_QUOTES);
}

// JSON de cada auto del mosaico para el modal
$mosaicJsons = [];
foreach ($d['mosaic'] as $m) {
    $mosaicJsons[] = htmlspecialchars(json_encode([
        'id'     => $m['id'],
        'year'   => $m['year'],
        'team'   => $m['team'],
        'model'  => $m['model'],
        'driver' => $m['driver'],
        'maker'  => '',
        'note'   => '',
        'img'    => $m['thumb'] ?? '',
        'imgs'   => [$m['thumb'] ?? ''],
        'admin'  => false,
    ]), ENT_QUOTES);
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
    <h1 class="home-hero-title">UNA COLECCIÓN<br><span>DE OTRO MUNDO</span></h1>
    <p class="home-hero-sub">
      <?= $stats['total'] ?> autos de Fórmula 1 y algunos otros en escala, desde <?= $stats['years']['mn'] ?> hasta <?= $stats['years']['mx'] ?>.<br>
      Cada pieza tiene su historia. Cada foto, su momento.
    </p>
    <a href="?page=collection" class="btn btn-primary home-hero-cta">🏎️ EXPLORAR LA COLECCIÓN</a>
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
    <div class="home-mosaic-item" onclick="openModal(<?= $mosaicJsons[$i] ?>)">
      <img src="<?= htmlspecialchars($m['thumb']) ?>" alt="<?= htmlspecialchars($m['model']) ?>">
      <div class="home-mosaic-overlay">
        <span class="home-mosaic-year"><?= $m['year'] ?></span>
        <span class="home-mosaic-team"><?= htmlspecialchars($m['team']) ?></span>
        <?php if ($m['driver']): ?>
          <span class="home-mosaic-driver"><?= htmlspecialchars($m['driver']) ?></span>
        <?php endif; ?>
        <span class="home-mosaic-cta">Ver detalle →</span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ÚLTIMO AUTO AGREGADO -->
<?php if ($latest): ?>
<div class="home-latest-section">
  <div class="home-section-header">
    <div class="home-section-title">⚡ ÚLTIMA INCORPORACIÓN</div>
    <a href="?page=collection" class="home-section-link">Ver toda la colección →</a>
  </div>
  <div class="home-latest-card">
    <div class="home-latest-img" <?= $latestJson ? "onclick=\"openModal($latestJson)\"" : '' ?>>
      <?php if ($latest['thumb']): ?>
        <img src="<?= htmlspecialchars($latest['thumb']) ?>" alt="<?= htmlspecialchars($latest['model']) ?>">
      <?php else: ?>
        <span class="home-latest-noimg">🏎️</span>
      <?php endif; ?>
      <span class="home-latest-badge">NUEVO</span>
    </div>
    <div class="home-latest-body">
      <div class="home-latest-year"><?= $latest['year'] ?></div>
      <div class="home-latest-team"><?= htmlspecialchars($latest['team']) ?></div>
      <div class="home-latest-model"><?= htmlspecialchars($latest['model']) ?></div>
      <?php if ($latest['driver']): ?>
        <div class="home-latest-driver">🧑‍✈️ <?= htmlspecialchars($latest['driver']) ?></div>
      <?php endif; ?>
      <?php if ($latest['maker']): ?>
        <div class="home-latest-maker">🏭 <?= htmlspecialchars($latest['maker']) ?></div>
      <?php endif; ?>
      <?php if ($latest['created_at']): ?>
        <div class="home-latest-date">📅 Incorporado el <?= date('d/m/Y', strtotime($latest['created_at'])) ?></div>
      <?php endif; ?>
      <?php if ($latest['note']): ?>
        <div class="home-latest-note">"<?= htmlspecialchars($latest['note']) ?>"</div>
      <?php endif; ?>
      <?php if ($latestJson): ?>
        <button class="btn btn-primary home-latest-btn" onclick="openModal(<?= $latestJson ?>)">🔍 VER DETALLE</button>
      <?php endif; ?>
    </div>
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

<!-- Modal (reutiliza funciones de app.js) -->
<div class="modal-overlay" id="carModal" onclick="closeModalOnBg(event)">
  <button class="modal-car-prev" id="modalCarPrev" style="display:none">&#8249;</button>
  <div class="modal-box">
    <div class="modal-img-wrap" id="modalImgWrap">
      <button class="modal-close-btn" onclick="closeModal()">✕</button>
      <span class="modal-no-img" id="modalNoImg">🏎️</span>
      <div id="modalGallery" style="display:none;width:100%;position:relative;">
        <img id="modalImg" src="" alt="" style="max-width:100%;max-height:360px;object-fit:contain;display:block;margin:0 auto;">
        <div class="gallery-nav" id="galleryNav" style="display:none;">
          <button class="gallery-prev" onclick="galleryPrev()">&#8249;</button>
          <span class="gallery-counter" id="galleryCounter"></span>
          <button class="gallery-next" onclick="galleryNext()">&#8250;</button>
        </div>
      </div>
    </div>
    <div class="modal-body">
      <div class="modal-year"   id="modalYear"></div>
      <div class="modal-title"  id="modalTitle"></div>
      <div class="modal-driver" id="modalDriver"></div>
      <div class="modal-note"   id="modalNote" style="display:none"></div>
      <div class="modal-meta"   id="modalMeta"></div>
      <div class="modal-footer" id="modalFooter"></div>
    </div>
  </div>
  <button class="modal-car-next" id="modalCarNext" style="display:none">&#8250;</button>
</div>
