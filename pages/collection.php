<?php

$filters = [
    'q'      => trim($_GET['q']      ?? ''),
    'year'   => trim($_GET['year']   ?? ''),
    'team'   => trim($_GET['team']   ?? ''),
    'driver' => trim($_GET['driver'] ?? ''),
    'maker'  => trim($_GET['maker']  ?? ''),
];
$sortField = in_array($_GET['sort'] ?? '', ['year','team']) ? $_GET['sort'] : 'year';
$sortDir   = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

$cars    = getCars($filters, $sortField, $sortDir);
$teams   = getDistinct('team');
$drivers = getDistinct('driver');
$years   = getDB()->query("SELECT DISTINCT year FROM cars ORDER BY year")->fetchAll(PDO::FETCH_COLUMN);
$makers  = getDistinct('maker');
$admin   = isAdmin();



// Lista completa de autos en el orden activo — para navegación del modal
$allCarsNav = array_map(fn($c) => [
    'id'     => $c['id'],
    'year'   => $c['year'],
    'team'   => $c['team'],
    'model'  => $c['model'],
    'driver' => $c['driver'],
    'maker'  => $c['maker'],
    'note'   => $c['note'],
    'img'    => getFirstImage((int)$c['id']) ? htmlspecialchars(getFirstImage((int)$c['id'])) : '',
    'imgs'   => array_map(fn($i) => htmlspecialchars($i['path']), getCarImages((int)$c['id'])),
    'admin'  => $admin,
], $cars);
?>

<div class="page-title">🏎️ MODELOS <span> REALES Y A ESCALA</span></div>

<script>
window._carList = <?= json_encode($allCarsNav) ?>;
</script>

<!-- <div class="page-title">🏎️ LA <span>COLECCIÓN</span></div> -->

<form method="get" class="filters" id="filterForm">
  <input type="hidden" name="page" value="collection">
  <input type="text" name="q" placeholder="🔍  Buscar piloto, auto, nota…" value="<?= htmlspecialchars($filters['q']) ?>">

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

  <select name="driver" onchange="this.form.submit()">
    <option value="">— Piloto —</option>
    <?php foreach ($drivers as $d): ?>
      <option value="<?= htmlspecialchars($d) ?>" <?= $filters['driver']===$d?'selected':'' ?>><?= htmlspecialchars($d) ?></option>
    <?php endforeach; ?>
  </select>

  <select name="maker" onchange="this.form.submit()">
    <option value="">— Fabricante —</option>
    <?php foreach ($makers as $m): ?>
      <option value="<?= htmlspecialchars($m) ?>" <?= $filters['maker']===$m?'selected':'' ?>><?= htmlspecialchars($m) ?></option>
    <?php endforeach; ?>
  </select>

  <!-- <button type="submit" class="btn btn-primary">FILTRAR</button>
  <?php if (array_filter($filters)): ?>
    <a href="?page=collection" class="btn btn-ghost">✕ LIMPIAR</a>
  <?php endif; ?> -->

<?php if (array_filter($filters)): ?>
  <a href="?page=collection" class="btn btn-primary">✕ LIMPIAR</a>
<?php endif; ?>

</form>


<div class="collection-topbar">
  <div class="result-count">
    <strong><?= count($cars) ?></strong> AUTO<?= count($cars)!==1?'S':'' ?> EN PARRILLA
    <?php if ($admin): ?>
      <span style="color:var(--gold);margin-left:12px;">★ MODO ADMIN</span>
    <?php endif; ?>
  </div>
  <div class="view-toggle">
    <button class="view-toggle-btn active" id="btnViewTable" onclick="setView('table')" title="Vista tabla">
      <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="1" y="1" width="16" height="3.5" rx="1" fill="currentColor" opacity=".5"/><rect x="1" y="7.25" width="16" height="3.5" rx="1" fill="currentColor"/><rect x="1" y="13.5" width="16" height="3.5" rx="1" fill="currentColor" opacity=".5"/></svg>
      TABLA
    </button>
    <button class="view-toggle-btn" id="btnViewCards" onclick="setView('cards')" title="Vista cards">
      <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="1" y="1" width="7" height="7" rx="1.5" fill="currentColor"/><rect x="10" y="1" width="7" height="7" rx="1.5" fill="currentColor" opacity=".5"/><rect x="1" y="10" width="7" height="7" rx="1.5" fill="currentColor" opacity=".5"/><rect x="10" y="10" width="7" height="7" rx="1.5" fill="currentColor" opacity=".5"/></svg>
      CARDS
    </button>
  </div>
</div>

<?php if (empty($cars)): ?>
  <div class="empty">
    <div class="empty-icon">🏁</div>
    <p>SIN RESULTADOS — AJUSTÁ LOS FILTROS</p>
  </div>
<?php else: ?>

<div class="table-wrap" id="collTableWrap">
  <table>
    <thead>
      <tr>
        <th>FOTO</th>
        <th>
          <?php $qs = http_build_query(array_merge($filters,['sort'=>'year','dir'=>($sortField==='year'&&$sortDir==='asc'?'desc':'asc'),'page'=>'collection'])); ?>
          <a href="?<?= $qs ?>" style="color:inherit;text-decoration:none;">AÑO <?= $sortField==='year' ? ($sortDir==='asc'?'↑':'↓') : '' ?></a>
        </th>
        <th>
          <?php $qs = http_build_query(array_merge($filters,['sort'=>'team','dir'=>($sortField==='team'&&$sortDir==='asc'?'desc':'asc'),'page'=>'collection'])); ?>
          <a href="?<?= $qs ?>" style="color:inherit;text-decoration:none;">ESCUDERÍA / MODELO <?= $sortField==='team' ? ($sortDir==='asc'?'↑':'↓') : '' ?></a>
        </th>
        <th>PILOTO</th>
        <th>FABRICANTE</th>
        <th>COLECCIÓN</th>
        <th>NOTA</th>
        <?php if ($admin): ?><th>ACCIONES</th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cars as $car): ?>
      <?php
        $imgPath = getFirstImage((int)$car['id']) ? htmlspecialchars(getFirstImage((int)$car['id'])) : '';
        $allImgs = array_map(fn($i) => htmlspecialchars($i['path']), getCarImages((int)$car['id']));
        $carJson = htmlspecialchars(json_encode([
          'id'     => $car['id'],
          'year'   => $car['year'],
          'team'   => $car['team'],
          'model'  => $car['model'],
          'driver' => $car['driver'],
          'maker'  => $car['maker'],
          'note'   => $car['note'],
          'img'    => $imgPath,
          'imgs'   => $allImgs,
          'admin'  => $admin,
        ]), ENT_QUOTES);
      ?>
      <tr>

        <!-- Miniatura -->
        <td>
          <?php if ($imgPath): ?>
            <img src="<?= $imgPath ?>" class="car-thumb" alt="<?= htmlspecialchars($car['model']) ?>"
                 onclick="openModal(<?= $carJson ?>)" title="Ver detalle">
          <?php elseif ($admin): ?>
            <a href="?page=edit&id=<?= $car['id'] ?>" class="no-img" title="Agregar foto">📷</a>
          <?php else: ?>
            <span style="font-size:24px;color:var(--border2)">🏎️</span>
          <?php endif; ?>
        </td>

        <!-- Año -->
        <td><span class="year-badge"><?= $car['year'] ?></span></td>

        <!-- Escudería + Modelo -->
        <td>
          <a href="?page=car&slug=<?= htmlspecialchars(makeCarSlug($car)) ?>" class="car-detail-link">
            <div class="team-name"><?= htmlspecialchars($car['team']) ?></div>
            <div class="model-name"><?= htmlspecialchars($car['model']) ?></div>
          </a>
        </td>

        <!-- Piloto -->
        <td><span class="driver-name"><?= htmlspecialchars($car['driver']) ?></span></td>

        <!-- Fabricante -->
        <td><span class="maker-badge"><?= htmlspecialchars($car['maker']) ?></span></td>

        <!-- Colección -->
        <td style="font-size:15px;color:var(--muted)"><?= htmlspecialchars($car['collection']) ?></td>

        <!-- Nota -->
        <td><span class="note-text"><?= htmlspecialchars($car['note']) ?></span></td>

        <!-- Acciones (solo admin) -->
        <?php if ($admin): ?>
        <td>
          <div class="actions">
            <?php if ($imgPath): ?>
              <button class="btn btn-ghost btn-sm" onclick="openModal(<?= $carJson ?>)" title="Ver detalle">🔍</button>
            <?php endif; ?>
            <a href="?page=edit&id=<?= $car['id'] ?>" class="btn btn-ghost btn-sm" title="Editar">✏️</a>
            <form method="post" action="action.php" style="display:inline"
                  onsubmit="return confirm('¿Eliminar este auto?')">
              <input type="hidden" name="action"  value="delete_car">
              <input type="hidden" name="car_id"  value="<?= $car['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑️</button>
            </form>
          </div>
        </td>
        <?php endif; ?>

      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- ══ CARDS VIEW ═══════════════════════════════════ -->
<?php if (!empty($cars)): ?>
<div class="coll-cards-grid" id="collCardsGrid" style="display:none;">
  <?php foreach ($cars as $car): ?>
  <?php
    $imgPath2 = getFirstImage((int)$car['id']) ? htmlspecialchars(getFirstImage((int)$car['id'])) : '';
    $allImgs2 = array_map(fn($i) => htmlspecialchars($i['path']), getCarImages((int)$car['id']));
    $carJson2 = htmlspecialchars(json_encode([
      'id'     => $car['id'],
      'year'   => $car['year'],
      'team'   => $car['team'],
      'model'  => $car['model'],
      'driver' => $car['driver'],
      'maker'  => $car['maker'],
      'note'   => $car['note'],
      'img'    => $imgPath2,
      'imgs'   => $allImgs2,
      'admin'  => $admin,
    ]), ENT_QUOTES);
  ?>
  <div class="coll-card" onclick="openModal(<?= $carJson2 ?>)">
    <div class="coll-card-img">
      <?php if ($imgPath2): ?>
        <img src="<?= $imgPath2 ?>" alt="<?= htmlspecialchars($car['model']) ?>">
      <?php else: ?>
        <span class="coll-card-noimg">🏎️</span>
      <?php endif; ?>
      <span class="coll-card-year"><?= $car['year'] ?></span>
      <?php if ($admin): ?>
        <a href="?page=edit&id=<?= $car['id'] ?>" class="coll-card-edit" onclick="event.stopPropagation()" title="Editar">✏️</a>
      <?php endif; ?>
    </div>
    <div class="coll-card-body">
      <div class="coll-card-team"><?= htmlspecialchars($car['team']) ?></div>
      <div class="coll-card-model"><?= htmlspecialchars($car['model']) ?></div>
      <div class="coll-card-driver"><?= htmlspecialchars($car['driver']) ?></div>
      <?php if ($car['note']): ?>
        <div class="coll-card-note"><?= htmlspecialchars($car['note']) ?></div>
      <?php endif; ?>
      <a href="?page=car&slug=<?= htmlspecialchars(makeCarSlug($car)) ?>" class="coll-card-detail-link" onclick="event.stopPropagation()">Ver página →</a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<?php if (!empty($cars)): ?>
<?php
  $exportParams = http_build_query(array_merge($filters, [
    'sort' => $sortField,
    'dir'  => $sortDir,
  ]));
?>
<div class="export-bar">
  <span class="export-bar-label">EXPORTAR LISTADO</span>
  <div class="export-btns">
    <a href="export.php?type=csv&<?= $exportParams ?>" class="btn-export btn-export-csv" title="Descargar Excel/CSV">
      <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><rect x="1" y="1" width="13" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M4 5h7M4 7.5h7M4 10h5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
      CSV / EXCEL
    </a>
    <a href="export.php?type=print&<?= $exportParams ?>" target="_blank" class="btn-export btn-export-pdf" title="Ver para imprimir / PDF">
      <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><rect x="2" y="4" width="11" height="8" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M4 4V2.5A.5.5 0 0 1 4.5 2h6a.5.5 0 0 1 .5.5V4" stroke="currentColor" stroke-width="1.3"/><rect x="4.5" y="7" width="6" height="1" rx=".5" fill="currentColor"/><rect x="4.5" y="9" width="4" height="1" rx=".5" fill="currentColor"/></svg>
      IMPRIMIR / PDF
    </a>
  </div>
</div>
<?php endif; ?>

<script>
// ── View toggle ────────────────────────────────────
function setView(v) {
  var table = document.getElementById('collTableWrap');
  var cards = document.getElementById('collCardsGrid');
  var btnT  = document.getElementById('btnViewTable');
  var btnC  = document.getElementById('btnViewCards');
  if (v === 'cards') {
    if (table) table.style.display = 'none';
    if (cards) cards.style.display = 'grid';
    btnT && btnT.classList.remove('active');
    btnC && btnC.classList.add('active');
  } else {
    if (table) table.style.display = '';
    if (cards) cards.style.display = 'none';
    btnT && btnT.classList.add('active');
    btnC && btnC.classList.remove('active');
  }
  try { localStorage.setItem('f1collView', v); } catch(e) {}
}
(function() {
  var saved = '';
  try { saved = localStorage.getItem('f1collView') || ''; } catch(e) {}
  if (saved === 'cards') setView('cards');
})();
</script>

<!-- ══ MODAL ══════════════════════════════════════ -->
<div class="modal-overlay" id="carModal" onclick="closeModalOnBg(event)">
  <button class="modal-car-prev" id="modalCarPrev" title="Auto anterior">&#8249;</button>
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
      <div class="modal-year" id="modalYear"></div>
      <div class="modal-title" id="modalTitle"></div>
      <div class="modal-driver" id="modalDriver"></div>
      <div class="modal-note" id="modalNote" style="display:none"></div>
      <div class="modal-meta" id="modalMeta"></div>
      <div class="modal-footer" id="modalFooter"></div>
    </div>
  </div>
  <button class="modal-car-next" id="modalCarNext" title="Auto siguiente">&#8250;</button>
</div>
