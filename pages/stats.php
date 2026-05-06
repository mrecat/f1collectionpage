<?php
$s = getStats();
$db = getDB();

// ── Récords ────────────────────────────────────────────────
$oldest    = $db->query("SELECT year, team, model, driver FROM cars ORDER BY year ASC  LIMIT 1")->fetch();
$newest    = $db->query("SELECT year, team, model, driver FROM cars ORDER BY year DESC LIMIT 1")->fetch();
$topDrv    = $db->query("SELECT driver, COUNT(*) as cnt FROM cars WHERE driver != '' GROUP BY driver ORDER BY cnt DESC LIMIT 1")->fetch();
$topTeam   = $db->query("SELECT team,   COUNT(*) as cnt FROM cars GROUP BY team   ORDER BY cnt DESC LIMIT 1")->fetch();
$topYear   = $db->query("SELECT year,   COUNT(*) as cnt FROM cars GROUP BY year   ORDER BY cnt DESC LIMIT 1")->fetch();
$champCnt  = (int)$db->query("SELECT COUNT(*) FROM cars WHERE is_champion=1")->fetchColumn();

// ── Miniaturas ─────────────────────────────────────────────
// scale_img = imagen con sort_order=1 en car_images
// real_img  = imagen con sort_order=0 en car_images
$withScale = (int)$db->query("SELECT COUNT(DISTINCT car_id) FROM car_images WHERE sort_order = 1")->fetchColumn();
$withReal  = (int)$db->query("SELECT COUNT(DISTINCT car_id) FROM car_images WHERE sort_order = 0")->fetchColumn();
$withBoth  = (int)$db->query("
    SELECT COUNT(*) FROM (
        SELECT car_id FROM car_images WHERE sort_order = 0
        INTERSECT
        SELECT car_id FROM car_images WHERE sort_order = 1
    ) t
")->fetchColumn();
$total     = (int)$s['total'];
$withPhoto = (int)$db->query("SELECT COUNT(*) FROM cars WHERE EXISTS(SELECT 1 FROM car_images ci WHERE ci.car_id=cars.id)")->fetchColumn();
$scalePct  = $total ? round($withScale / $total * 100) : 0;
$bothPct   = $total ? round($withBoth  / $total * 100) : 0;

// ── Por fabricante (maker) ─────────────────────────────────
$byMaker   = $s['by_maker'];
$maxMaker  = max(array_column($byMaker, 'cnt') ?: [1]);

// ── Campeones ──────────────────────────────────────────────
$champList = $db->query("SELECT DISTINCT year, driver, team FROM cars WHERE is_champion=1 ORDER BY year")->fetchAll();

// ── Top escuderías y pilotos ───────────────────────────────
$maxTeam   = max(array_column($s['by_team'],   'cnt') ?: [1]);
$maxDriver = max(array_column($s['by_driver'], 'cnt') ?: [1]);

// ── Por década ─────────────────────────────────────────────
$maxDecade = max(array_column($s['by_decade'], 'cnt') ?: [1]);
?>

<style>
/* ══ Stats 2.0 ══════════════════════════════════════════ */

.s2-kpi-strip {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1px;
  background: var(--border);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  margin-bottom: 32px;
}
@media (max-width: 900px) { .s2-kpi-strip { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 500px)  { .s2-kpi-strip { grid-template-columns: repeat(2, 1fr); } }

.s2-kpi {
  background: var(--bg2);
  padding: 28px 20px;
  text-align: center;
  position: relative;
  transition: background .2s;
}
.s2-kpi:hover { background: var(--bg3); }
.s2-kpi::after {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 3px; background: var(--red);
  opacity: 0; transition: opacity .2s;
}
.s2-kpi:hover::after { opacity: 1; }
.s2-kpi-n {
  font-family: 'Formula1', sans-serif;
  font-family: 'Formula1', sans-serif;
  font-size: 28px; font-weight: 900;
  color: var(--text); line-height: 1; margin-bottom: 8px;
}
.s2-kpi-n.red  { color: var(--red); }
.s2-kpi-n.gold { color: var(--gold); }
.s2-kpi-l {
  font-family: 'Formula1', sans-serif;
  font-size: 9px; letter-spacing: 2.5px; color: var(--muted);
}

.s2-section { margin-bottom: 32px; }
.s2-section-head {
  display: flex; align-items: center; gap: 12px;
  margin-bottom: 18px; padding-bottom: 12px;
  border-bottom: 1px solid var(--border);
}
.s2-section-icon { font-size: 18px; }
.s2-section-title {
  font-family: 'Formula1', sans-serif;
  font-size: 11px; font-weight: 700; letter-spacing: 3px; color: var(--muted);
}
.s2-section-badge {
  margin-left: auto;
  font-family: 'Formula1', sans-serif; font-size: 10px;
  color: var(--red); background: var(--red-glow);
  border: 1px solid rgba(225,6,0,.3);
  padding: 3px 10px; border-radius: 20px; letter-spacing: 1px;
}

.s2-records {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;
}
@media (max-width: 900px) { .s2-records { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px)  { .s2-records { grid-template-columns: 1fr; } }

.s2-rec {
  background: var(--bg2); border: 1px solid var(--border);
  border-radius: var(--radius-lg); padding: 18px 20px;
  display: flex; gap: 14px; align-items: flex-start;
  transition: border-color .2s, transform .18s;
}
.s2-rec:hover { border-color: var(--border2); transform: translateY(-2px); }
.s2-rec-icon { font-size: 22px; flex-shrink: 0; margin-top: 2px; }
.s2-rec-label {
  font-family: 'Formula1', sans-serif;
  font-size: 8.5px; letter-spacing: 2px; color: var(--muted); margin-bottom: 5px;
}
.s2-rec-val  { font-size: 14px; font-weight: 700; color: var(--text); line-height: 1.25; margin-bottom: 3px; }
.s2-rec-sub  { font-size: 13px; color: var(--text2); }

.s2-vitrina {
  display: grid; grid-template-columns: 1fr 1fr; gap: 22px; margin-bottom: 32px;
}
@media (max-width: 700px) { .s2-vitrina { grid-template-columns: 1fr; } }

.s2-card {
  background: var(--bg2); border: 1px solid var(--border);
  border-radius: var(--radius-lg); padding: 24px;
}

.s2-coverage-row { display: flex; flex-direction: column; gap: 14px; }
.s2-cov-header {
  display: flex; justify-content: space-between;
  align-items: baseline; margin-bottom: 7px;
}
.s2-cov-label { font-size: 14px; color: var(--text2); font-weight: 600; }
.s2-cov-val   { font-family: 'Formula1', sans-serif; font-size: 13px; color: var(--text); }
.s2-cov-pct   { font-family: 'Formula1', sans-serif; font-size: 11px; color: var(--muted); margin-left: 6px; }
.s2-progress  { height: 8px; background: var(--bg4); border-radius: 4px; overflow: hidden; }
.s2-progress-fill {
  height: 100%; border-radius: 4px;
  transition: width .8s cubic-bezier(.4,0,.2,1);
}
.s2-progress-fill.red  { background: var(--red); }
.s2-progress-fill.gold { background: var(--gold); }
.s2-progress-fill.blue { background: #4a9eff; }
.s2-progress-fill:not(.pfx-animated) { width: 0 !important; }
.s2-progress-fill.pfx-animated { width: var(--target-w) !important; }

.s2-bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 11px; }
.s2-bar-rank { font-family: 'Formula1', sans-serif; font-size: 9px; color: var(--muted); min-width: 14px; text-align: right; }
.s2-bar-label { font-size: 14px; color: var(--text2); min-width: 90px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.s2-bar-track { flex: 1; height: 8px; background: var(--bg4); border-radius: 4px; overflow: hidden; }
.s2-bar-fill  { height: 100%; background: var(--red); border-radius: 4px; transition: width .7s cubic-bezier(.4,0,.2,1); }
.s2-bar-fill.gold { background: var(--gold); }
.s2-bar-fill:not(.bfx-animated) { width: 0 !important; }
.s2-bar-val { font-family: 'Formula1', sans-serif; font-size: 11px; color: var(--muted); min-width: 22px; text-align: right; }

.s2-decades { display: flex; align-items: flex-end; gap: 8px; height: 120px; padding-top: 24px; }
.s2-dec-col { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end; gap: 5px; }
.s2-dec-val { font-family: 'Formula1', sans-serif; font-size: 10px; color: var(--muted); }
.s2-dec-val.peak { color: var(--gold); font-weight: 700; }
.s2-dec-bar-wrap { flex: 1; width: 100%; display: flex; align-items: flex-end; }
.s2-dec-bar { width: 100%; background: var(--red); border-radius: 4px 4px 0 0; min-height: 4px; height: 0; transition: height .7s cubic-bezier(.4,0,.2,1); }
.s2-dec-bar.peak { background: var(--gold); }
.s2-dec-label { font-family: 'Formula1', sans-serif; font-size: 8px; color: var(--muted); white-space: nowrap; }

.s2-ranking-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; margin-bottom: 32px; }
@media (max-width: 700px) { .s2-ranking-cols { grid-template-columns: 1fr; } }

.s2-champ-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.s2-champ-pill {
  display: inline-flex; align-items: center; gap: 9px;
  background: var(--bg3); border: 1px solid rgba(255,201,6,.2);
  border-radius: 22px; padding: 7px 15px;
  transition: border-color .2s, background .2s, transform .15s;
}
.s2-champ-pill:hover { border-color: var(--gold); background: rgba(255,201,6,.06); transform: translateY(-1px); }
.s2-champ-year  { font-family: 'Formula1', sans-serif; font-size: 10px; font-weight: 900; color: var(--gold); letter-spacing: 1px; }
.s2-champ-name  { font-size: 14px; font-weight: 700; color: var(--text); }
.s2-champ-team  { font-size: 12px; color: var(--muted); }
</style>

<!-- ══ TÍTULO ══════════════════════════════════════════════ -->
<div class="page-title">📊 ESTADÍSTICAS <span>DE LA COLECCIÓN</span></div>

<!-- ══ KPI STRIP ═══════════════════════════════════════════ -->
<div class="s2-kpi-strip">
  <div class="s2-kpi">
    <div class="s2-kpi-n red"><?= $total ?></div>
    <div class="s2-kpi-l">AUTOS</div>
  </div>
  <div class="s2-kpi">
    <div class="s2-kpi-n"><?= $s['teams'] ?></div>
    <div class="s2-kpi-l">ESCUDERÍAS</div>
  </div>
  <div class="s2-kpi">
    <div class="s2-kpi-n"><?= $s['drivers'] ?></div>
    <div class="s2-kpi-l">PILOTOS</div>
  </div>
  <div class="s2-kpi">
    <div class="s2-kpi-n gold"><?= $champCnt ?></div>
    <div class="s2-kpi-l">CAMPEONES</div>
  </div>
  <div class="s2-kpi">
    <div class="s2-kpi-n"><?= $s['years'] ?></div>
    <div class="s2-kpi-l">TEMPORADAS</div>
  </div>
</div>

<!-- ══ RÉCORDS ══════════════════════════════════════════════ -->
<div class="s2-section">
  <div class="s2-section-head">
    <span class="s2-section-icon">🎯</span>
    <span class="s2-section-title">RÉCORDS DE LA COLECCIÓN</span>
  </div>
  <div class="s2-records">
    <div class="s2-rec">
      <div class="s2-rec-icon">🏺</div>
      <div>
        <div class="s2-rec-label">AUTO MÁS ANTIGUO</div>
        <div class="s2-rec-val"><?= $oldest['year'] ?> · <?= htmlspecialchars($oldest['team']) ?></div>
        <div class="s2-rec-sub"><?= htmlspecialchars($oldest['driver'] ?: '—') ?></div>
      </div>
    </div>
    <div class="s2-rec">
      <div class="s2-rec-icon">⚡</div>
      <div>
        <div class="s2-rec-label">AUTO MÁS MODERNO</div>
        <div class="s2-rec-val"><?= $newest['year'] ?> · <?= htmlspecialchars($newest['team']) ?></div>
        <div class="s2-rec-sub"><?= htmlspecialchars($newest['driver'] ?: '—') ?></div>
      </div>
    </div>
    <div class="s2-rec">
      <div class="s2-rec-icon">🧑‍✈️</div>
      <div>
        <div class="s2-rec-label">PILOTO MÁS REPRESENTADO</div>
        <div class="s2-rec-val"><?= htmlspecialchars($topDrv['driver']) ?></div>
        <div class="s2-rec-sub"><?= $topDrv['cnt'] ?> modelos</div>
      </div>
    </div>
    <div class="s2-rec">
      <div class="s2-rec-icon">🏎️</div>
      <div>
        <div class="s2-rec-label">ESCUDERÍA MÁS REPRESENTADA</div>
        <div class="s2-rec-val"><?= htmlspecialchars($topTeam['team']) ?></div>
        <div class="s2-rec-sub"><?= $topTeam['cnt'] ?> modelos</div>
      </div>
    </div>
    <div class="s2-rec">
      <div class="s2-rec-icon">📅</div>
      <div>
        <div class="s2-rec-label">AÑO CON MÁS MODELOS</div>
        <div class="s2-rec-val"><?= $topYear['year'] ?></div>
        <div class="s2-rec-sub"><?= $topYear['cnt'] ?> autos de ese año</div>
      </div>
    </div>
    <div class="s2-rec">
      <div class="s2-rec-icon">📐</div>
      <div>
        <div class="s2-rec-label">ESCALA DE LA COLECCIÓN</div>
        <div class="s2-rec-val">1:43</div>
        <div class="s2-rec-sub"><?= $s['years'] ?> años de F1 en miniatura</div>
      </div>
    </div>
  </div>
</div>

<!-- ══ VITRINA EN NÚMEROS ═══════════════════════════════════ -->
<div class="s2-section">
  <div class="s2-section-head">
    <span class="s2-section-icon">🔬</span>
    <span class="s2-section-title">LA VITRINA EN NÚMEROS</span>
    <span class="s2-section-badge">COLECCIÓN 1:43</span>
  </div>
  <div class="s2-vitrina">

    <div class="s2-card">
      <div class="chart-title">📸 COBERTURA FOTOGRÁFICA</div>
      <div class="s2-coverage-row">
        <div class="s2-cov-item">
          <div class="s2-cov-header">
            <span class="s2-cov-label">Foto del modelo 1:43</span>
            <span><span class="s2-cov-val"><?= $withScale ?></span><span class="s2-cov-pct">(<?= $scalePct ?>%)</span></span>
          </div>
          <div class="s2-progress"><div class="s2-progress-fill red" data-w="<?= $scalePct ?>"></div></div>
        </div>
        <div class="s2-cov-item">
          <div class="s2-cov-header">
            <span class="s2-cov-label">Foto del auto real</span>
            <?php $realPct = $total ? round($withReal/$total*100) : 0; ?>
            <span><span class="s2-cov-val"><?= $withReal ?></span><span class="s2-cov-pct">(<?= $realPct ?>%)</span></span>
          </div>
          <div class="s2-progress"><div class="s2-progress-fill blue" data-w="<?= $realPct ?>"></div></div>
        </div>
        <div class="s2-cov-item">
          <div class="s2-cov-header">
            <span class="s2-cov-label">Escala + real (comparación)</span>
            <span><span class="s2-cov-val"><?= $withBoth ?></span><span class="s2-cov-pct">(<?= $bothPct ?>%)</span></span>
          </div>
          <div class="s2-progress"><div class="s2-progress-fill gold" data-w="<?= $bothPct ?>"></div></div>
        </div>
        <div class="s2-cov-item">
          <div class="s2-cov-header">
            <span class="s2-cov-label">Con foto de colección</span>
            <?php $photoPct = $total ? round($withPhoto/$total*100) : 0; ?>
            <span><span class="s2-cov-val"><?= $withPhoto ?></span><span class="s2-cov-pct">(<?= $photoPct ?>%)</span></span>
          </div>
          <div class="s2-progress"><div class="s2-progress-fill red" data-w="<?= $photoPct ?>"></div></div>
        </div>
      </div>
    </div>

    <div class="s2-card">
      <div class="chart-title">🏭 FABRICANTES DE MINIATURAS</div>
      <?php foreach ($byMaker as $i => $row):
        $pct = round($row['cnt'] / $maxMaker * 100);
      ?>
      <div class="s2-bar-row">
        <span class="s2-bar-rank"><?= $i+1 ?></span>
        <span class="s2-bar-label"><?= htmlspecialchars($row['maker'] ?: '—') ?></span>
        <div class="s2-bar-track">
          <div class="s2-bar-fill" data-w="<?= $pct ?>"></div>
        </div>
        <span class="s2-bar-val"><?= $row['cnt'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<!-- ══ RANKINGS ═════════════════════════════════════════════ -->
<div class="s2-section">
  <div class="s2-section-head">
    <span class="s2-section-icon">📊</span>
    <span class="s2-section-title">RANKINGS</span>
  </div>
  <div class="s2-ranking-cols">
    <div class="s2-card">
      <div class="chart-title">🏎️ TOP ESCUDERÍAS</div>
      <?php foreach ($s['by_team'] as $i => $row): ?>
      <div class="s2-bar-row">
        <span class="s2-bar-rank"><?= $i+1 ?></span>
        <span class="s2-bar-label"><?= htmlspecialchars($row['team']) ?></span>
        <div class="s2-bar-track">
          <div class="s2-bar-fill" data-w="<?= round($row['cnt']/$maxTeam*100) ?>"></div>
        </div>
        <span class="s2-bar-val"><?= $row['cnt'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="s2-card">
      <div class="chart-title">🧑‍✈️ TOP PILOTOS</div>
      <?php foreach ($s['by_driver'] as $i => $row): if (!$row['driver']) continue; ?>
      <div class="s2-bar-row">
        <span class="s2-bar-rank"><?= $i+1 ?></span>
        <span class="s2-bar-label"><?= htmlspecialchars($row['driver']) ?></span>
        <div class="s2-bar-track">
          <div class="s2-bar-fill gold" data-w="<?= round($row['cnt']/$maxDriver*100) ?>"></div>
        </div>
        <span class="s2-bar-val"><?= $row['cnt'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ══ DÉCADAS ══════════════════════════════════════════════ -->
<div class="s2-section">
  <div class="s2-section-head">
    <span class="s2-section-icon">🕰️</span>
    <span class="s2-section-title">AUTOS POR DÉCADA</span>
  </div>
  <div class="s2-card" style="margin-bottom:32px">
    <div class="s2-decades">
      <?php foreach ($s['by_decade'] as $row):
        $pct    = round($row['cnt'] / $maxDecade * 100);
        $isPeak = $row['cnt'] == $maxDecade;
      ?>
      <div class="s2-dec-col">
        <div class="s2-dec-val <?= $isPeak ? 'peak' : '' ?>"><?= $row['cnt'] ?></div>
        <div class="s2-dec-bar-wrap">
          <div class="s2-dec-bar <?= $isPeak ? 'peak' : '' ?>" data-h="<?= $pct ?>"></div>
        </div>
        <div class="s2-dec-label"><?= $row['decade'] ?>s</div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ══ CAMPEONES ════════════════════════════════════════════ -->
<div class="s2-section">
  <div class="s2-section-head">
    <span class="s2-section-icon">🏆</span>
    <span class="s2-section-title">CAMPEONES EN LA COLECCIÓN</span>
    <span class="s2-section-badge"><?= count($champList) ?> AUTOS</span>
  </div>
  <div class="s2-card" style="margin-bottom:0">
    <div class="s2-champ-grid">
      <?php foreach ($champList as $c): ?>
      <div class="s2-champ-pill">
        <span class="s2-champ-year"><?= $c['year'] ?></span>
        <span class="s2-champ-name"><?= htmlspecialchars($c['driver']) ?></span>
        <span class="s2-champ-team"><?= htmlspecialchars($c['team']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
(function() {
  function animateEls() {
    // Progress fills
    document.querySelectorAll('.s2-progress-fill[data-w]').forEach(function(el) {
      var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
          if (e.isIntersecting) {
            el.style.width = el.getAttribute('data-w') + '%';
            obs.unobserve(el);
          }
        });
      }, { threshold: 0.1 });
      obs.observe(el);
    });
    // Bar fills
    document.querySelectorAll('.s2-bar-fill[data-w]').forEach(function(el) {
      var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
          if (e.isIntersecting) {
            el.style.width = el.getAttribute('data-w') + '%';
            obs.unobserve(el);
          }
        });
      }, { threshold: 0.1 });
      obs.observe(el);
    });
    // Decade bars
    document.querySelectorAll('.s2-dec-bar[data-h]').forEach(function(el) {
      var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
          if (e.isIntersecting) {
            requestAnimationFrame(function() {
              el.style.height = el.getAttribute('data-h') + '%';
            });
            obs.unobserve(el);
          }
        });
      }, { threshold: 0.1 });
      obs.observe(el);
    });
  }
  animateEls();
})();
</script>
