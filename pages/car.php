<?php
$slug = trim($_GET['slug'] ?? '');
$car  = $slug ? getCarBySlug($slug) : null;

if (!$car) {
    echo '<div class="empty" style="margin-top:60px;">
            <div class="empty-icon">🏁</div>
            <p>AUTO NO ENCONTRADO</p>
            <a href="?page=collection" class="btn btn-primary" style="margin-top:20px;">← VOLVER A LA COLECCIÓN</a>
          </div>';
    return;
}

$images  = getCarImages((int)$car['id']);
$admin   = isAdmin();
$adj     = getAdjacentCars((int)$car['id']);
$prevSlug = $adj['prev'] ? makeCarSlug($adj['prev']) : null;
$nextSlug = $adj['next'] ? makeCarSlug($adj['next']) : null;
?>

<!-- Breadcrumb -->
<nav class="car-breadcrumb">
  <a href="?page=home">Inicio</a>
  <span>›</span>
  <a href="?page=collection">Colección</a>
  <span>›</span>
  <span><?= htmlspecialchars($car['team']) ?> · <?= $car['year'] ?></span>
</nav>

<!-- Navegación prev/next -->
<div class="car-nav-bar">
  <?php if ($adj['prev']): ?>
    <a href="?page=car&slug=<?= htmlspecialchars($prevSlug) ?>" class="car-nav-btn car-nav-prev">
      ‹ <span><?= htmlspecialchars($adj['prev']['team']) ?> <?= $adj['prev']['year'] ?></span>
    </a>
  <?php else: ?>
    <span class="car-nav-btn car-nav-disabled">‹</span>
  <?php endif; ?>

  <a href="?page=collection" class="car-nav-mid">🏁 COLECCIÓN</a>

  <?php if ($adj['next']): ?>
    <a href="?page=car&slug=<?= htmlspecialchars($nextSlug) ?>" class="car-nav-btn car-nav-next">
      <span><?= htmlspecialchars($adj['next']['team']) ?> <?= $adj['next']['year'] ?></span> ›
    </a>
  <?php else: ?>
    <span class="car-nav-btn car-nav-disabled">›</span>
  <?php endif; ?>
</div>

<!-- Contenido principal -->
<div class="car-detail">

  <!-- ── Galería ── -->
  <div class="car-detail-gallery">
    <?php if (!empty($images)): ?>
      <div class="car-gallery-main" id="galleryMain">
        <img id="galleryMainImg"
             src="<?= htmlspecialchars($images[0]['path']) ?>"
             alt="<?= htmlspecialchars($car['model']) ?>">
        <?php if (count($images) > 1): ?>
          <button class="car-gal-arrow car-gal-prev" onclick="galShift(-1)">&#8249;</button>
          <button class="car-gal-arrow car-gal-next" onclick="galShift(1)">&#8250;</button>
          <div class="car-gal-counter"><span id="galCurr">1</span> / <?= count($images) ?></div>
        <?php endif; ?>
      </div>
      <?php if (count($images) > 1): ?>
      <div class="car-gallery-thumbs" id="galleryThumbs">
        <?php foreach ($images as $i => $img): ?>
          <div class="car-gal-thumb <?= $i===0?'active':'' ?>"
               onclick="galTo(<?= $i ?>)"
               style="background-image:url('<?= htmlspecialchars($img['path']) ?>')">
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    <?php else: ?>
      <div class="car-gallery-empty">🏎️</div>
    <?php endif; ?>
  </div>

  <!-- ── Info ── -->
  <div class="car-detail-info">

    <div class="car-detail-year">
      <?= $car['year'] ?>
      <?php if (!empty($car['is_champion'])): ?>
        <span class="car-champion-trophy" title="🏆 Campeón Mundial <?= $car['year'] ?>">🏆 CAMPEÓN <?= $car['year'] ?></span>
      <?php endif; ?>
    </div>
    <h1 class="car-detail-team"><?= htmlspecialchars($car['team']) ?></h1>
    <div class="car-detail-model"><?= htmlspecialchars($car['model']) ?></div>

    <div class="car-detail-pills">
      <?php if ($car['driver']): ?>
        <div class="car-detail-pill">
          <span class="car-pill-label">PILOTO</span>
          <span class="car-pill-value">🧑‍✈️ <?= htmlspecialchars($car['driver']) ?></span>
        </div>
      <?php endif; ?>
      <?php if ($car['maker']): ?>
        <div class="car-detail-pill">
          <span class="car-pill-label">FABRICANTE</span>
          <span class="car-pill-value">🏭 <?= htmlspecialchars($car['maker']) ?></span>
        </div>
      <?php endif; ?>
      <?php if ($car['collection']): ?>
        <div class="car-detail-pill">
          <span class="car-pill-label">COLECCIÓN</span>
          <span class="car-pill-value">📦 <?= htmlspecialchars($car['collection']) ?></span>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($car['note']): ?>
    <div class="car-detail-note">
      <div class="car-detail-note-label">HISTORIA</div>
      <p><?= nl2br(htmlspecialchars($car['note'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- ── Desempeño en temporada ── -->
    <div class="car-detail-performance" id="perfSection">
      <div class="car-detail-perf-header">
        <div class="car-detail-note-label" style="color:var(--red)">⚡ DESEMPEÑO EN <?= $car['year'] ?></div>
        <?php if ($admin): ?>
          <button class="car-perf-edit-btn" id="perfToggleBtn" onclick="togglePerfEdit()">✏️ EDITAR</button>
        <?php endif; ?>
      </div>

      <!-- Texto generado/guardado -->
      <div id="perfDisplay">
        <?php if (!empty($car['performance'])): ?>
          <p class="car-perf-text"><?= nl2br(htmlspecialchars($car['performance'])) ?></p>
        <?php else: ?>
          <p class="car-perf-empty">
            <?php if ($admin): ?>
              Sin información de desempeño aún. Usá el botón ✏️ para generar o escribir.
            <?php else: ?>
              Información de desempeño próximamente.
            <?php endif; ?>
          </p>
        <?php endif; ?>
      </div>

      <!-- Panel de edición (solo admin) -->
      <?php if ($admin): ?>
      <div id="perfEdit" style="display:none;">
        <div class="car-perf-ai-bar">
          <label class="car-perf-champ-label">
            <input type="checkbox" id="perfChampion" <?= !empty($car['is_champion']) ? 'checked' : '' ?>>
            🏆 Campeón ese año
          </label>
          <span id="perfGenStatus" class="car-perf-status"></span>
        </div>
        <textarea id="perfTextarea" class="car-perf-textarea"><?= htmlspecialchars($car['performance'] ?? '') ?></textarea>
        <div class="car-perf-save-bar">
          <button class="btn btn-primary btn-sm" onclick="savePerformance()">💾 GUARDAR</button>
          <button class="btn btn-ghost btn-sm" onclick="togglePerfEdit()">CANCELAR</button>
          <span id="perfSaveStatus" class="car-perf-status"></span>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($admin): ?>
    <div class="car-detail-admin">
      <a href="?page=edit&id=<?= $car['id'] ?>" class="btn btn-ghost">✏️ EDITAR AUTO</a>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- ── Navegación inferior ── -->
<div class="car-bottom-nav">
  <?php if ($adj['prev']): ?>
  <a href="?page=car&slug=<?= htmlspecialchars($prevSlug) ?>" class="car-bottom-card car-bottom-prev">
    <span class="car-bottom-dir">← AUTO ANTERIOR</span>
    <span class="car-bottom-name"><?= htmlspecialchars($adj['prev']['team']) ?> · <?= htmlspecialchars($adj['prev']['model']) ?></span>
    <span class="car-bottom-driver"><?= htmlspecialchars($adj['prev']['driver']) ?> · <?= $adj['prev']['year'] ?></span>
  </a>
  <?php else: ?>
  <div></div>
  <?php endif; ?>

  <?php if ($adj['next']): ?>
  <a href="?page=car&slug=<?= htmlspecialchars($nextSlug) ?>" class="car-bottom-card car-bottom-next">
    <span class="car-bottom-dir">PRÓXIMO AUTO →</span>
    <span class="car-bottom-name"><?= htmlspecialchars($adj['next']['team']) ?> · <?= htmlspecialchars($adj['next']['model']) ?></span>
    <span class="car-bottom-driver"><?= htmlspecialchars($adj['next']['driver']) ?> · <?= $adj['next']['year'] ?></span>
  </a>
  <?php endif; ?>
</div>

<script>
var _imgs = <?= json_encode(array_map(fn($i) => $i['path'], $images)) ?>;
var _curr = 0;

function galTo(n) {
  _curr = n;
  document.getElementById('galleryMainImg').src = _imgs[n];
  document.getElementById('galCurr') && (document.getElementById('galCurr').textContent = n + 1);
  document.querySelectorAll('.car-gal-thumb').forEach(function(t, i) {
    t.classList.toggle('active', i === n);
  });
  // Scroll thumb into view
  var thumbs = document.querySelectorAll('.car-gal-thumb');
  if (thumbs[n]) thumbs[n].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
}

function galShift(d) {
  galTo((_curr + d + _imgs.length) % _imgs.length);
}

// ── Performance edit / AI generate ────────────────
<?php if ($admin): ?>
function togglePerfEdit() {
  var display = document.getElementById('perfDisplay');
  var edit    = document.getElementById('perfEdit');
  var btn     = document.getElementById('perfToggleBtn');
  var open    = edit.style.display === 'none';
  edit.style.display    = open ? 'block' : 'none';
  display.style.display = open ? 'none'  : 'block';
  btn.textContent       = open ? '✕ CERRAR' : '✏️ EDITAR';
}

function savePerformance() {
  var perf   = document.getElementById('perfTextarea').value.trim();
  var champ  = document.getElementById('perfChampion').checked ? 1 : 0;
  var status = document.getElementById('perfSaveStatus');
  status.textContent = '⏳ Guardando...';

  var fd = new FormData();
  fd.append('action',      'save_performance');
  fd.append('car_id',      '<?= $car['id'] ?>');
  fd.append('performance', perf);
  fd.append('is_champion', champ);

  fetch('action.php', { method: 'POST', body: fd })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.ok) {
      // Actualizar display
      var display = document.getElementById('perfDisplay');
      display.innerHTML = perf
        ? '<p class="car-perf-text">' + perf.replace(/\n/g,'<br>') + '</p>'
        : '<p class="car-perf-empty">Sin información de desempeño aún.</p>';

      // Actualizar trofeo
      var trophy = document.querySelector('.car-champion-trophy');
      var yearEl = document.querySelector('.car-detail-year');
      if (champ && !trophy) {
        var sp = document.createElement('span');
        sp.className = 'car-champion-trophy';
        sp.title = '🏆 Campeón Mundial <?= $car['year'] ?>';
        sp.textContent = '🏆 CAMPEÓN <?= $car['year'] ?>';
        yearEl.appendChild(sp);
      } else if (!champ && trophy) {
        trophy.remove();
      }

      status.textContent = '✅ Guardado';
      status.style.color = '#4caf7d';
      setTimeout(function() { togglePerfEdit(); }, 800);
    }
  })
  .catch(function() {
    status.textContent = '❌ Error al guardar';
    status.style.color = 'var(--red)';
  });
}
<?php endif; ?>
</script>
