<?php
$filters = [
    'year'       => trim($_GET['year'] ?? ''),
    'team'       => trim($_GET['team'] ?? ''),
    'collection' => trim($_GET['collection'] ?? ''),
    'sort' => in_array($_GET['sort'] ?? '', ['year','team']) ? $_GET['sort'] : 'year',
    'dir'  => ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
];

$items       = getMiniaturas($filters);
$years       = getDB()->query("SELECT DISTINCT year FROM cars ORDER BY year")->fetchAll(PDO::FETCH_COLUMN);
$teams       = getDistinct('team');
$collections = getDistinct('collection');
$admin       = isAdmin();
?>

<div class="page-title">🔬 MODELOS <span>A ESCALA</span></div>

<!-- Filtros -->
<form method="get" class="filters" id="filterForm">
  <input type="hidden" name="page" value="miniaturas">
  <input type="hidden" name="sort" value="<?= $filters['sort'] ?>">
  <input type="hidden" name="dir"  value="<?= $filters['dir'] ?>">

  <select name="year" onchange="this.form.submit()">
    <option value="">— Año —</option>
    <?php foreach ($years as $y): ?>
      <option value="<?= $y ?>" <?= $filters['year']==$y?'selected':'' ?>><?= $y ?></option>
    <?php endforeach; ?>
  </select>

  <select name="team" onchange="this.form.submit()">
    <option value="">— Escudería —</option>
    <?php foreach ($teams as $t): ?>
      <option value="<?= htmlspecialchars($t) ?>" <?= $filters['team']===$t?'selected':'' ?>><?= htmlspecialchars($t) ?></option>
    <?php endforeach; ?>
  </select>

  <select name="collection" onchange="this.form.submit()">
    <option value="">— Colección —</option>
    <?php foreach ($collections as $c): ?>
      <option value="<?= htmlspecialchars($c) ?>" <?= $filters['collection']===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
    <?php endforeach; ?>
  </select>

  <button type="submit" class="btn btn-primary">FILTRAR</button>
  <?php if ($filters['year'] || $filters['team'] || $filters['collection']): ?>
    <a href="?page=miniaturas" class="btn btn-ghost">✕ LIMPIAR</a>
  <?php endif; ?>
</form>

<!-- Ordenamiento -->
<div class="mini-sort-bar">
  <span class="mini-sort-label">ORDENAR POR</span>
  <?php
    foreach (['year'=>'AÑO','team'=>'ESCUDERÍA'] as $sf => $sl):
      $active = $filters['sort'] === $sf;
      $newDir = ($active && $filters['dir'] === 'asc') ? 'desc' : 'asc';
      $arrow  = $active ? ($filters['dir']==='asc' ? ' ↑' : ' ↓') : '';
      $qs = http_build_query(array_merge($filters, ['sort'=>$sf,'dir'=>$newDir,'page'=>'miniaturas']));
  ?>
  <a href="?<?= $qs ?>" class="sort-btn <?= $active?'active':'' ?>"><?= $sl . $arrow ?></a>
  <?php endforeach; ?>
</div>

<!-- Contador -->
<div class="result-count" style="margin-bottom:20px;">
  <strong><?= count($items) ?></strong> MODELO<?= count($items)!==1?'S':'' ?> EN LA VITRINA
</div>

<!-- Grid -->
<?php if (empty($items)): ?>
  <div class="empty">
    <div class="empty-icon">🔬</div>
    <p>SIN RESULTADOS — AJUSTÁ LOS FILTROS</p>
  </div>
<?php else: ?>
<div class="mini-grid">
  <?php foreach ($items as $item): ?>
  <div class="mini-card" onclick="openMiniModal(<?= htmlspecialchars(json_encode([
    'scale_img' => $item['scale_img'],
    'real_img'  => $item['real_img'],
    'year'      => $item['year'],
    'team'      => $item['team'],
    'model'     => $item['model'],
    'driver'    => $item['driver'],
    'maker'     => $item['maker'],
    'collection'=> $item['collection'],
    'id'        => $item['id'],
  ]), ENT_QUOTES) ?>)">

    <!-- Foto del modelo a escala -->
    <div class="mini-card-img">
      <?php if ($item['scale_img']): ?>
        <img src="<?= htmlspecialchars($item['scale_img']) ?>" alt="<?= htmlspecialchars($item['model']) ?>">
      <?php else: ?>
        <span class="mini-card-noimg">🔬</span>
      <?php endif; ?>
      <span class="mini-card-year"><?= $item['year'] ?></span>
    </div>

    <!-- Datos -->
    <div class="mini-card-body">
      <div class="mini-card-team"><?= htmlspecialchars($item['team']) ?></div>
      <div class="mini-card-model"><?= htmlspecialchars($item['model']) ?></div>
      <div class="mini-card-divider"></div>
      <div class="mini-card-pills">
        <?php if ($item['driver']): ?>
        <div class="mini-card-pill">
          <span class="mini-pill-label">PILOTO</span>
          <span class="mini-pill-value">🧑‍✈️ <?= htmlspecialchars($item['driver']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($item['maker']): ?>
        <div class="mini-card-pill">
          <span class="mini-pill-label">FABRICANTE</span>
          <span class="mini-pill-value">🏭 <?= htmlspecialchars($item['maker']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($item['collection']): ?>
        <div class="mini-card-pill">
          <span class="mini-pill-label">COLECCIÓN</span>
          <span class="mini-pill-value">📦 <?= htmlspecialchars($item['collection']) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal comparación real vs escala -->
<div class="modal-overlay" id="miniModal" onclick="closeMiniModal(event)">
  <div class="mini-modal-box">
    <button class="modal-close-btn" onclick="closeMiniModalBtn()">✕</button>

    <div class="mini-modal-header" id="miniModalHeader"></div>

    <div class="mini-modal-imgs">
      <div class="mini-modal-side">
        <div class="mini-modal-side-label">🏎️ AUTO REAL</div>
        <div class="mini-modal-img-wrap" id="miniRealImg"></div>
      </div>
      <div class="mini-modal-divider"></div>
      <div class="mini-modal-side">
        <div class="mini-modal-side-label">🔬 MODELO A ESCALA</div>
        <div class="mini-modal-img-wrap" id="miniScaleImg"></div>
      </div>
    </div>

    <div class="mini-modal-body" id="miniModalBody"></div>

    <div class="mini-modal-footer">
      <?php if ($admin): ?>
        <a href="#" class="btn btn-ghost btn-sm" id="miniEditBtn">✏️ EDITAR</a>
      <?php endif; ?>
      <button class="btn btn-ghost btn-sm" onclick="closeMiniModalBtn()">✕ CERRAR</button>
    </div>
  </div>
</div>

<script>
function openMiniModal(car) {
  // Header
  document.getElementById('miniModalHeader').innerHTML =
    '<span class="mini-mh-year">' + car.year + '</span>' +
    '<span class="mini-mh-sep">·</span>' +
    '<span class="mini-mh-team">' + car.team + '</span>' +
    '<span class="mini-mh-model">' + car.model + '</span>';

  // Imágenes
  var realWrap  = document.getElementById('miniRealImg');
  var scaleWrap = document.getElementById('miniScaleImg');

  realWrap.innerHTML = car.real_img
    ? '<img src="' + car.real_img + '" alt="Auto real">'
    : '<span class="mini-modal-noimg">🏎️</span>';

  scaleWrap.innerHTML = car.scale_img
    ? '<img src="' + car.scale_img + '" alt="Modelo escala">'
    : '<span class="mini-modal-noimg">🔬</span>';

  // Pills con etiqueta + dato
  var pills = '';
  if (car.driver)     pills += '<div class="mini-modal-pill"><span class="mini-modal-pill-label">PILOTO</span><span class="mini-modal-pill-value">🧑‍✈️ ' + car.driver + '</span></div>';
  if (car.maker)      pills += '<div class="mini-modal-pill"><span class="mini-modal-pill-label">FABRICANTE</span><span class="mini-modal-pill-value">🏭 ' + car.maker + '</span></div>';
  if (car.collection) pills += '<div class="mini-modal-pill"><span class="mini-modal-pill-label">COLECCIÓN</span><span class="mini-modal-pill-value">📦 ' + car.collection + '</span></div>';

  document.getElementById('miniModalBody').innerHTML = '<div class="mini-modal-pills">' + pills + '</div>';

  // Edit link
  var editBtn = document.getElementById('miniEditBtn');
  if (editBtn) editBtn.href = '?page=edit&id=' + car.id;

  document.getElementById('miniModal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeMiniModal(e) {
  if (e.target === document.getElementById('miniModal')) closeMiniModalBtn();
}
function closeMiniModalBtn() {
  document.getElementById('miniModal').classList.remove('open');
  document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeMiniModalBtn();
});
</script>
