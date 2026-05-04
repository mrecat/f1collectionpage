<?php
$byYear = getTimelineData();

$eras = [
    [1936, 1949, 'PRE F1',           '#6b4c2a', 'Los primeros monoplazas de carrera, antes del campeonato oficial.'],
    [1950, 1961, 'ERA PREMODERNA',   '#8a6a35', 'El nacimiento del Campeonato Mundial. Alfa Romeo, Ferrari y Maserati dominan.'],
    [1962, 1972, 'ERA ALAS',         '#2d6a4a', 'La aerodinámica cambia todo. Lotus, Brabham y Tyrrell revolucionan el diseño.'],
    [1973, 1982, 'EFECTO SUELO',     '#1a5a7a', 'El efecto suelo convierte los autos en aspiradoras. Velocidades imposibles.'],
    [1983, 1988, 'ERA TURBO',        '#6a2d7a', 'Los turbo de 1500 CV. La era más potente y peligrosa de la historia.'],
    [1989, 1994, 'ERA ASPIRADA',     '#7a4a1a', 'Vuelta a los aspirados. Senna, Prost y Mansell. Tragedia en Imola 1994.'],
    [1995, 2004, 'ERA SCHUMACHER',   '#8f2020', 'Michael Schumacher y Ferrari. Siete títulos, cinco consecutivos. Dominio total.'],
    [2005, 2013, 'ERA V8',           '#3a6a1a', 'Alonso, Hamilton, Vettel. Los V8 de 18.000 RPM. El sonido más puro de la F1.'],
    [2014, 2021, 'ERA HÍBRIDA',      '#1a4a8a', 'Mercedes arrasa. Los híbridos turbo llegan para quedarse. Hamilton hace historia.'],
    [2022, 2099, 'EFECTO SUELO 2.0', '#5a1a8a', 'Nuevo reglamento, nuevos dominadores. Verstappen y Red Bull, y el resurgir de Ferrari.'],
];

// Agrupar autos por era
$eraData = [];
foreach ($eras as $idx => $era) {
    $cars = [];
    foreach ($byYear as $year => $yearCars) {
        if ($year >= $era[0] && $year <= $era[1]) {
            foreach ($yearCars as $car) $cars[] = $car;
        }
    }
    if (!empty($cars)) $eraData[$idx] = $cars;
}
?>

<div class="page-title">📅 HISTORIA DE LA <span>COLECCIÓN</span></div>

<div class="eras-grid" id="erasGrid">
<?php foreach ($eras as $idx => $era):
    if (!isset($eraData[$idx])) continue;
    $cars  = $eraData[$idx];
    $total = count($cars);
    $teams = count(array_unique(array_column($cars, 'team')));
    // Hasta 4 thumbs para mostrar en la card
    $thumbs = array_filter(array_slice($cars, 0, 8), fn($c) => !empty($c['thumb']));
    $thumbs = array_values($thumbs);
    $endYear = min($era[1], 2025);
?>
<div class="era-card" id="era-<?= $idx ?>" style="--ec:<?= $era[3] ?>">

  <!-- Header clickeable -->
  <div class="era-card-header" onclick="toggleEra(<?= $idx ?>)">
    <div class="era-card-left">
      <div class="era-card-years"><?= $era[0] ?> — <?= $endYear ?></div>
      <div class="era-card-name"><?= $era[2] ?></div>
      <div class="era-card-desc"><?= $era[4] ?></div>
    </div>
    <div class="era-card-right">
      <div class="era-card-stats">
        <div class="era-stat">
          <span class="era-stat-n"><?= $total ?></span>
          <span class="era-stat-l">AUTOS</span>
        </div>
        <div class="era-stat">
          <span class="era-stat-n"><?= $teams ?></span>
          <span class="era-stat-l">EQUIPOS</span>
        </div>
      </div>
      <!-- Thumbs preview -->
      <?php if (!empty($thumbs)): ?>
      <div class="era-card-thumbs">
        <?php foreach (array_slice($thumbs, 0, 4) as $c): ?>
          <div class="era-thumb" style="background-image:url('<?= htmlspecialchars($c['thumb']) ?>')"></div>
        <?php endforeach; ?>
        <?php if ($total > 4): ?>
          <div class="era-thumb era-thumb-more">+<?= $total - 4 ?></div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <div class="era-card-toggle" id="toggle-<?= $idx ?>">▼</div>
    </div>
  </div>

  <!-- Contenido expandible -->
  <div class="era-card-body" id="body-<?= $idx ?>">
    <div class="era-cars-grid">
      <?php foreach ($cars as $car):
        $slug = htmlspecialchars(makeCarSlug($car));
      ?>
      <a href="?page=car&slug=<?= $slug ?>" class="era-car-card">
        <?php if (!empty($car['thumb'])): ?>
          <div class="era-car-img">
            <img src="<?= htmlspecialchars($car['thumb']) ?>" alt="<?= htmlspecialchars($car['model']) ?>">
          </div>
        <?php else: ?>
          <div class="era-car-img era-car-noimg">🏎️</div>
        <?php endif; ?>
        <div class="era-car-info">
          <div class="era-car-year"><?= $car['year'] ?></div>
          <div class="era-car-team"><?= htmlspecialchars($car['team']) ?></div>
          <div class="era-car-model"><?= htmlspecialchars($car['model']) ?></div>
          <?php if ($car['driver']): ?>
            <div class="era-car-driver">🧑‍✈️ <?= htmlspecialchars($car['driver']) ?></div>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

</div>
<?php endforeach; ?>
</div>

<script>
var _eraTransitionMs = 450;

function toggleEra(idx) {
  var body   = document.getElementById('body-'   + idx);
  var toggle = document.getElementById('toggle-' + idx);
  var card   = document.getElementById('era-'    + idx);
  var isOpen = body.classList.contains('open');

  // Cerrar todos primero
  document.querySelectorAll('.era-card-body.open').forEach(function(b) {
    b.classList.remove('open');
    b.style.maxHeight = '0';
  });
  document.querySelectorAll('.era-card-toggle').forEach(function(t) {
    t.textContent = '▼';
    t.classList.remove('open');
  });
  document.querySelectorAll('.era-card.open').forEach(function(c) {
    c.classList.remove('open');
  });

  if (!isOpen) {
    // Esperar a que colapse todo antes de abrir y scrollear
    setTimeout(function() {
      body.classList.add('open');
      body.style.maxHeight = body.scrollHeight + 'px';
      toggle.textContent = '▲';
      toggle.classList.add('open');
      card.classList.add('open');

      // Ahora scrollear al header del card (ya está en su posición final)
      var headerOffset = 80;
      var top = card.getBoundingClientRect().top + window.pageYOffset - headerOffset;
      window.scrollTo({ top: top, behavior: 'smooth' });
    }, _eraTransitionMs);
  }
}
</script>
