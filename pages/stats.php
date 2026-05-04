<?php
$s = getStats();

// Extra queries for new sections
$db = getDB();

// Records
$oldest   = $db->query("SELECT year, team, model, driver FROM cars ORDER BY year ASC LIMIT 1")->fetch();
$newest   = $db->query("SELECT year, team, model, driver FROM cars ORDER BY year DESC LIMIT 1")->fetch();
$topDrv   = $db->query("SELECT driver, COUNT(*) as cnt FROM cars WHERE driver != '' GROUP BY driver ORDER BY cnt DESC LIMIT 1")->fetch();
$topTeam  = $db->query("SELECT team, COUNT(*) as cnt FROM cars GROUP BY team ORDER BY cnt DESC LIMIT 1")->fetch();
$topYear  = $db->query("SELECT year, COUNT(*) as cnt FROM cars GROUP BY year ORDER BY cnt DESC LIMIT 1")->fetch();
$champCnt = (int)$db->query("SELECT COUNT(*) FROM cars WHERE is_champion=1")->fetchColumn();
$withPhoto= (int)$db->query("SELECT COUNT(*) FROM cars WHERE EXISTS(SELECT 1 FROM car_images ci WHERE ci.car_id=cars.id)")->fetchColumn();
$photoPct = $s['total'] ? round($withPhoto / $s['total'] * 100) : 0;

// By year for histogram
$byYear   = $db->query("SELECT year, COUNT(*) as cnt FROM cars GROUP BY year ORDER BY year")->fetchAll();
$maxByYear= max(array_column($byYear,'cnt') ?: [1]);

// Champions list (distinct year+driver)
$champList= $db->query("SELECT DISTINCT year, driver, team FROM cars WHERE is_champion=1 ORDER BY year")->fetchAll();

$maxMaker  = max(array_column($s['by_maker'],  'cnt') ?: [1]);
$maxTeam   = max(array_column($s['by_team'],   'cnt') ?: [1]);
$maxDriver = max(array_column($s['by_driver'], 'cnt') ?: [1]);
$maxDecade = max(array_column($s['by_decade'], 'cnt') ?: [1]);
?>

<!-- ══ KPI CARDS ══════════════════════════════════════ -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-number"><?= $s['total'] ?></div>
    <div class="stat-label">TOTAL AUTOS</div>
  </div>
  <div class="stat-card">
    <div class="stat-number"><?= $s['teams'] ?></div>
    <div class="stat-label">ESCUDERÍAS</div>
  </div>
  <div class="stat-card">
    <div class="stat-number"><?= $s['drivers'] ?></div>
    <div class="stat-label">PILOTOS</div>
  </div>
  <div class="stat-card">
    <div class="stat-number"><?= $champCnt ?></div>
    <div class="stat-label">AUTOS CAMPEÓN</div>
  </div>
  <div class="stat-card">
    <div class="stat-number"><?= $s['years'] ?></div>
    <div class="stat-label">AÑOS DISTINTOS</div>
  </div>
 <!--  <div class="stat-card">
    <div class="stat-number"><?= $photoPct ?>%</div>
    <div class="stat-label">CON FOTO</div>
  </div> -->
</div>

<!-- ══ RÉCORDS ══════════════════════════════════════════ -->
<div class="stats-records">
  <div class="stats-section-title">🎯 RÉCORDS DE LA COLECCIÓN</div>
  <div class="records-grid">

    <div class="record-card">
      <div class="record-icon">🏺</div>
      <div class="record-body">
        <div class="record-label">AUTO MÁS ANTIGUO</div>
        <div class="record-value"><?= $oldest['year'] ?> · <?= htmlspecialchars($oldest['team']) ?></div>
        <div class="record-sub"><?= htmlspecialchars($oldest['driver']) ?></div>
      </div>
    </div>

    <div class="record-card">
      <div class="record-icon">⚡</div>
      <div class="record-body">
        <div class="record-label">AUTO MÁS MODERNO</div>
        <div class="record-value"><?= $newest['year'] ?> · <?= htmlspecialchars($newest['team']) ?></div>
        <div class="record-sub"><?= htmlspecialchars($newest['driver']) ?></div>
      </div>
    </div>

    <div class="record-card">
      <div class="record-icon">🧑‍✈️</div>
      <div class="record-body">
        <div class="record-label">PILOTO MÁS REPRESENTADO</div>
        <div class="record-value"><?= htmlspecialchars($topDrv['driver']) ?></div>
        <div class="record-sub"><?= $topDrv['cnt'] ?> modelos en la colección</div>
      </div>
    </div>

    <div class="record-card">
      <div class="record-icon">🏎️</div>
      <div class="record-body">
        <div class="record-label">ESCUDERÍA MÁS REPRESENTADA</div>
        <div class="record-value"><?= htmlspecialchars($topTeam['team']) ?></div>
        <div class="record-sub"><?= $topTeam['cnt'] ?> modelos en la colección</div>
      </div>
    </div>

    <div class="record-card">
      <div class="record-icon">📅</div>
      <div class="record-body">
        <div class="record-label">AÑO CON MÁS MODELOS</div>
        <div class="record-value"><?= $topYear['year'] ?></div>
        <div class="record-sub"><?= $topYear['cnt'] ?> autos de ese año</div>
      </div>
    </div>

    <div class="record-card">
      <div class="record-icon">📸</div>
      <div class="record-body">
        <div class="record-label">COBERTURA FOTOGRÁFICA</div>
        <div class="record-value"><?= $withPhoto ?> de <?= $s['total'] ?> autos</div>
        <div class="record-sub"><?= $photoPct ?>% de la colección con foto</div>
      </div>
    </div>

  </div>
</div>

<!-- ══ HISTOGRAMA POR AÑO ═══════════════════════════════ -->
<div class="chart-card stats-histogram-card">
  <div class="chart-title">📅 DISTRIBUCIÓN POR AÑO <span class="chart-title-sub"><?= count($byYear) ?> años cubiertos</span></div>
  <div class="histogram-wrap">
    <?php foreach ($byYear as $row): ?>
    <div class="histo-col" title="<?= $row['year'] ?>: <?= $row['cnt'] ?> auto<?= $row['cnt']>1?'s':'' ?>">
      <div class="histo-bar" style="height:<?= round($row['cnt']/$maxByYear*100) ?>%"
           data-cnt="<?= $row['cnt'] ?>"></div>
      <?php if ($row['cnt'] == $maxByYear || in_array($row['year'], [1950,1970,1980,1990,2000,2010,2020])): ?>
        <div class="histo-label"><?= $row['year'] ?></div>
      <?php else: ?>
        <div class="histo-label histo-label-hidden"><?= $row['year'] ?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ══ CHARTS ROW 1 ══════════════════════════════════════ -->
<div class="charts-row">

  <!-- Por fabricante — dona -->
  <div class="chart-card">
    <div class="chart-title">🏭 AUTOS POR FABRICANTE</div>
    <div class="donut-wrap">
      <canvas id="donutMaker" width="180" height="180"></canvas>
      <div class="donut-center">
        <div class="donut-total"><?= $s['total'] ?></div>
        <div class="donut-sub">AUTOS</div>
      </div>
    </div>
    <div class="donut-legend">
      <?php
      $donutColors = ['#e10600','#ffc906','#4caf7d','#4a9eff','#b060ff','#ff6b35'];
      foreach ($s['by_maker'] as $i => $row):
        $pct = round($row['cnt']/$s['total']*100);
        $color = $donutColors[$i % count($donutColors)];
      ?>
      <div class="donut-legend-item">
        <span class="donut-legend-dot" style="background:<?= $color ?>"></span>
        <span class="donut-legend-name"><?= htmlspecialchars($row['maker']) ?></span>
        <span class="donut-legend-val"><?= $row['cnt'] ?> <span style="color:var(--muted)">(<?= $pct ?>%)</span></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Por década — barras verticales -->
  <div class="chart-card">
    <div class="chart-title">🕰️ AUTOS POR DÉCADA</div>
    <div class="vbar-wrap">
      <?php foreach ($s['by_decade'] as $row):
        $pct = round($row['cnt']/$maxDecade*100);
        $isMax = $row['cnt'] == $maxDecade;
      ?>
      <div class="vbar-col">
        <div class="vbar-val <?= $isMax ? 'vbar-val-max' : '' ?>"><?= $row['cnt'] ?></div>
        <div class="vbar-bar-wrap">
          <div class="vbar-bar <?= $isMax ? 'vbar-bar-gold' : '' ?>" style="height:<?= $pct ?>%"></div>
        </div>
        <div class="vbar-label"><?= $row['decade'] ?>s</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- ══ CHARTS ROW 2 ══════════════════════════════════════ -->
<div class="charts-row">

  <!-- Top escuderías -->
  <div class="chart-card">
    <div class="chart-title">🏎️ TOP ESCUDERÍAS</div>
    <?php foreach ($s['by_team'] as $i => $row):
      $pct = round($row['cnt']/$maxTeam*100);
    ?>
    <div class="bar-row">
      <span class="bar-rank"><?= $i+1 ?></span>
      <span class="bar-label"><?= htmlspecialchars($row['team']) ?></span>
      <div class="bar-track">
        <div class="bar-fill" style="width:<?= $pct ?>%"></div>
      </div>
      <span class="bar-val"><?= $row['cnt'] ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Top pilotos -->
  <div class="chart-card">
    <div class="chart-title">🧑‍✈️ TOP PILOTOS</div>
    <?php foreach ($s['by_driver'] as $i => $row): if(!$row['driver']) continue; ?>
    <div class="bar-row">
      <span class="bar-rank"><?= $i+1 ?></span>
      <span class="bar-label"><?= htmlspecialchars($row['driver']) ?></span>
      <div class="bar-track">
        <div class="bar-fill bar-fill-gold" style="width:<?= round($row['cnt']/$maxDriver*100) ?>%"></div>
      </div>
      <span class="bar-val"><?= $row['cnt'] ?></span>
    </div>
    <?php endforeach; ?>
  </div>

</div>

<!-- ══ CAMPEONES ═════════════════════════════════════════ -->
<div class="chart-card stats-champions-card">
  <div class="chart-title">🏆 CAMPEONES EN LA COLECCIÓN <span class="chart-title-sub"><?= count($champList) ?> autos campeones</span></div>
  <div class="champions-grid">
    <?php foreach ($champList as $c): ?>
    <div class="champion-pill">
      <span class="champion-year"><?= $c['year'] ?></span>
      <span class="champion-name"><?= htmlspecialchars($c['driver']) ?></span>
      <span class="champion-team"><?= htmlspecialchars($c['team']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ══ POR COLECCIÓN ══════════════════════════════════════ -->
<div class="chart-card" style="margin-bottom:0">
  <div class="chart-title">📦 AUTOS POR COLECCIÓN</div>
  <?php $maxCol = max(array_column($s['by_collection'],'cnt') ?: [1]); ?>
  <?php foreach ($s['by_collection'] as $i => $row): ?>
  <div class="bar-row">
    <span class="bar-rank"><?= $i+1 ?></span>
    <span class="bar-label" style="min-width:240px"><?= htmlspecialchars($row['collection']) ?></span>
    <div class="bar-track">
      <div class="bar-fill" style="width:<?= round($row['cnt']/$maxCol*100) ?>%"></div>
    </div>
    <span class="bar-val"><?= $row['cnt'] ?></span>
  </div>
  <?php endforeach; ?>
</div>

<script>
// ── Donut chart fabricantes ─────────────────────────────
(function() {
  var canvas = document.getElementById('donutMaker');
  if (!canvas) return;
  var ctx = canvas.getContext('2d');
  var data = [
    <?php foreach ($s['by_maker'] as $i => $row):
      $color = $donutColors[$i % count($donutColors)];
    ?>
    { val: <?= $row['cnt'] ?>, color: '<?= $color ?>' },
    <?php endforeach; ?>
  ];
  var total = data.reduce(function(s,d){ return s+d.val; }, 0);
  var cx = 90, cy = 90, r = 78, ri = 52;
  var angle = -Math.PI / 2;
  var gap = 0.03;

  data.forEach(function(d) {
    var sweep = (d.val / total) * (Math.PI * 2) - gap;
    ctx.beginPath();
    ctx.moveTo(cx, cy);
    ctx.arc(cx, cy, r, angle + gap/2, angle + sweep + gap/2);
    ctx.arc(cx, cy, ri, angle + sweep + gap/2, angle + gap/2, true);
    ctx.closePath();
    ctx.fillStyle = d.color;
    ctx.fill();
    angle += sweep + gap;
  });
})();

// ── Animate bars on scroll ──────────────────────────────
(function() {
  var bars = document.querySelectorAll('.bar-fill, .vbar-bar, .histo-bar');
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(e) {
      if (e.isIntersecting) {
        e.target.classList.add('bar-animated');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  bars.forEach(function(b) { observer.observe(b); });
})();
</script>
