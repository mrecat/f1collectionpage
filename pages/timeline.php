<?php
$byYear = getTimelineData();
$years  = array_keys($byYear);
$minYear = min($years);
$maxYear = max($years);

// F1 eras with colors
$eras = [
    [1900, 1949, 'PRE HISTORIA',      '#8b7355'],
    [1950, 1961, 'ERA PREMODERNA',    '#8b7355'],
    [1962, 1972, 'ERA ALAS',          '#4a7c59'],
    [1973, 1982, 'EFECTO SUELO',      '#2d6a8f'],
    [1983, 1988, 'ERA TURBO',         '#7c3d8f'],
    [1989, 1994, 'ERA ASPIRADA',      '#8f5a2d'],
    [1995, 2004, 'DOMINIO SCHUMACHER','#8f2d2d'],
    [2005, 2013, 'ERA V8',            '#5a7c2d'],
    [2014, 2021, 'ERA HÍBRIDA',       '#2d5a8f'],
    [2022, 2099, 'EFECTO SUELO 2.0',  '#6b2d8f'],
];

function getEra(int $year, array $eras): array {
    foreach ($eras as $era) {
        if ($year >= $era[0] && $year <= $era[1]) return $era;
    }
    return [0, 0, 'DESCONOCIDA', '#555'];
}
?>

<!-- <div class="page-title">📅 LÍNEA DE <span>TIEMPO</span></div> -->

<div class="timeline-legend">
  <?php foreach ($eras as $era): ?>
    <?php
      $hasAny = false;
      foreach ($years as $y) { if ($y >= $era[0] && $y <= $era[1]) { $hasAny = true; break; } }
      if (!$hasAny) continue;
    ?>
    <div class="legend-item">
      <span class="legend-dot" style="background:<?= $era[3] ?>"></span>
      <span><?= $era[2] ?> (<?= $era[0] ?>–<?= min($era[1], 2099)==2099?'hoy':$era[1] ?>)</span>
    </div>
  <?php endforeach; ?>
</div>

<div class="timeline-wrap">
  <div class="timeline-axis">

    <?php
    $prevEraName = '';
    foreach ($byYear as $year => $cars):
      $era = getEra($year, $eras);
      $eraName = $era[2];
      $eraColor = $era[3];
      $showEraLabel = ($eraName !== $prevEraName);
      $prevEraName = $eraName;
    ?>

    <?php if ($showEraLabel): ?>
    <div class="era-marker" style="--era-color:<?= $eraColor ?>">
      <div class="era-label"><?= $eraName ?></div>
    </div>
    <?php endif; ?>

    <div class="timeline-row">
      <div class="timeline-year" style="color:<?= $eraColor ?>"><?= $year ?></div>
      <div class="timeline-dot" style="background:<?= $eraColor ?>; box-shadow: 0 0 8px <?= $eraColor ?>"></div>
      <div class="timeline-cards">
        <?php foreach ($cars as $car):
          $thumb = $car['thumb'] ?? null;
          $carJson = htmlspecialchars(json_encode([
            'id'     => $car['id'],
            'year'   => $car['year'],
            'team'   => $car['team'],
            'model'  => $car['model'],
            'driver' => $car['driver'],
            'maker'  => '',
            'note'   => '',
            'img'    => $thumb ? htmlspecialchars($thumb) : '',
            'imgs'   => $thumb ? [htmlspecialchars($thumb)] : [],
            'admin'  => false,
          ]), ENT_QUOTES);
        ?>
        <div class="tl-card" onclick="openModal(<?= $carJson ?>)" style="--era-color:<?= $eraColor ?>">
          <?php if ($thumb): ?>
            <div class="tl-card-img">
              <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($car['model']) ?>">
            </div>
          <?php else: ?>
            <div class="tl-card-noimg">🏎️</div>
          <?php endif; ?>
          <div class="tl-card-body">
            <div class="tl-card-team"><?= htmlspecialchars($car['team']) ?></div>
            <div class="tl-card-model"><?= htmlspecialchars($car['model']) ?></div>
            <?php if ($car['driver']): ?>
            <div class="tl-card-driver"><?= htmlspecialchars($car['driver']) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php endforeach; ?>
  </div>
</div>

<!-- Modal (same as collection) -->
<div class="modal-overlay" id="carModal" onclick="closeModalOnBg(event)">
  <div class="modal-box">
    <div class="modal-img-wrap" id="modalImgWrap">
      <button class="modal-close-btn" onclick="closeModal()">✕</button>
      <span class="modal-no-img" id="modalNoImg">🏎️</span>
      <div id="modalGallery" style="display:none;width:100%;position:relative;">
        <img id="modalImg" src="" alt="" style="max-width:100%;max-height:360px;object-fit:contain;display:block;margin:0 auto;">
        <div class="gallery-nav" id="galleryNav" style="display:none;">
          <button class="gallery-prev" onclick="galleryPrev()">‹</button>
          <span class="gallery-counter" id="galleryCounter"></span>
          <button class="gallery-next" onclick="galleryNext()">›</button>
        </div>
      </div>
    </div>
    <div class="modal-body">
      <div class="modal-year" id="modalYear"></div>
      <div class="modal-title" id="modalTitle"></div>
      <div class="modal-driver" id="modalDriver"></div>
      <div class="modal-note" id="modalNote" style="display:none"></div>
      <div class="modal-meta" id="modalMeta"></div>
      <div class="modal-footer" id="modalFooter"></div>
    </div>
  </div>
</div>
