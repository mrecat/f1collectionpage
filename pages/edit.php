<?php
requireAdmin();

$id  = (int)($_GET['id'] ?? 0);
$car = $id ? getCarById($id) : null;

if (!$car) {
    echo '<div class="alert alert-error">❌ Auto no encontrado.</div>';
    echo '<a href="?page=collection" class="btn btn-ghost">← Volver</a>';
    return;
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'year'        => (int)($_POST['year']       ?? 0),
        'team'        => trim($_POST['team']        ?? ''),
        'model'       => trim($_POST['model']       ?? ''),
        'driver'      => trim($_POST['driver']      ?? ''),
        'maker'       => trim($_POST['maker']       ?? ''),
        'collection'  => trim($_POST['collection']  ?? ''),
        'note'        => trim($_POST['note']        ?? ''),
        'is_champion'      => isset($_POST['is_champion']) ? 1 : 0,
        'is_team_champion' => isset($_POST['is_team_champion']) ? 1 : 0,
    ];
    if ($data['year'] && $data['team'] && $data['model']) {
        saveCar($data, $id);
        handleMultiImageUpload($id);
        $msg = 'success';
        $car = getCarById($id);
    } else {
        $msg = 'error';
    }
}

if (isset($_GET['msg'])) $msg = $_GET['msg'];

$teams       = getDistinct('team');
$makers      = getDistinct('maker');
$collections = getDistinct('collection');
$images      = getCarImages($id);
$coverId     = !empty($images) ? $images[0]['id'] : 0;

$fv = fn(string $key) => htmlspecialchars($car[$key] ?? '');
?>

<div class="page-title">✏️ EDITAR <span>AUTO</span></div>

<?php if ($msg === 'success'): ?>
  <div class="alert alert-success">✅ Cambios guardados. <a href="?page=collection" style="color:inherit;font-weight:700">Ver colección →</a></div>
<?php elseif ($msg === 'img_deleted'): ?>
  <div class="alert alert-success">🗑️ Imagen eliminada.</div>
<?php elseif ($msg === 'cover_set'): ?>
  <div class="alert alert-success">⭐ Foto de portada actualizada.</div>
<?php elseif ($msg === 'error'): ?>
  <div class="alert alert-error">❌ Completá al menos Año, Escudería y Modelo.</div>
<?php endif; ?>

<!-- ══ GALERÍA DE FOTOS ACTUALES ══════════════════ -->
<?php if (!empty($images)): ?>
<div class="form-card" style="margin-bottom:20px;">
  <div style="font-family:'Formula1',sans-serif;font-size:11px;letter-spacing:2px;color:var(--muted);margin-bottom:16px;">
    📷 FOTOS ACTUALES — <span style="color:var(--gold)">⭐ = PORTADA</span>
  </div>
  <div class="gallery-edit">
    <?php foreach ($images as $idx => $img):
      $isCover = ($img['id'] === $coverId);
    ?>
    <div class="gallery-edit-item" style="<?= $isCover ? 'border-color:var(--gold);box-shadow:0 0 10px rgba(255,201,6,.3)' : '' ?>">
      <div style="position:relative;">
        <img src="<?= htmlspecialchars($img['path']) ?>" alt="Foto <?= $idx+1 ?>">
        <?php if ($isCover): ?>
          <span style="position:absolute;top:4px;right:4px;font-size:16px;filter:drop-shadow(0 1px 2px #000)">⭐</span>
        <?php endif; ?>
      </div>
      <div class="gallery-edit-label"><?= $isCover ? '⭐ PORTADA' : 'Foto ' . ($idx+1) ?></div>

      <!-- Marcar como portada (solo si no es portada ya) -->
      <?php if (!$isCover): ?>
      <form method="post" action="action.php" style="margin-bottom:6px;">
        <input type="hidden" name="action"   value="set_cover">
        <input type="hidden" name="car_id"   value="<?= $id ?>">
        <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
        <button type="submit" class="btn btn-gold btn-sm" style="width:100%">⭐ PORTADA</button>
      </form>
      <?php endif; ?>

      <!-- Eliminar foto -->
      <form method="post" action="action.php"
            onsubmit="return confirm('¿Eliminar esta foto?')">
        <input type="hidden" name="action"   value="delete_image">
        <input type="hidden" name="car_id"   value="<?= $id ?>">
        <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
        <button type="submit" class="btn btn-danger btn-sm" style="width:100%">🗑️ ELIMINAR</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- ══ FORMULARIO PRINCIPAL ══════════════════════ -->
<div class="form-card">
  <form method="post" action="index.php?page=edit&id=<?= $id ?>" enctype="multipart/form-data">
    <div class="form-grid">

      <div class="form-group">
        <label>AÑO *</label>
        <input type="number" name="year" min="1900" max="2099" value="<?= $fv('year') ?>" required>
      </div>

      <div class="form-group">
        <label>ESCUDERÍA *</label>
        <input type="text" name="team" list="list-teams" value="<?= $fv('team') ?>" required>
        <datalist id="list-teams">
          <?php foreach ($teams as $t): ?><option value="<?= htmlspecialchars($t) ?>"><?php endforeach; ?>
        </datalist>
      </div>

      <div class="form-group full">
        <label>MODELO *</label>
        <input type="text" name="model" value="<?= $fv('model') ?>" required>
      </div>

      <div class="form-group full">
        <label>PILOTO</label>
        <input type="text" name="driver" value="<?= $fv('driver') ?>">
      </div>

      <div class="form-group full">
        <label>DISTINCIÓN</label>
        <label class="champion-toggle">
          <input type="checkbox" name="is_champion" value="1" <?= !empty($car['is_champion']) ? 'checked' : '' ?>>
          <span class="champion-toggle-box">
            <span class="champion-toggle-icon">🏆</span>
            <span class="champion-toggle-text">CAMPEÓN MUNDIAL DE PILOTOS</span>
            <span class="champion-toggle-sub">Activa el trofeo dorado en la página del auto y en las estadísticas</span>
          </span>
        </label>
        <label class="champion-toggle" style="margin-top:10px;">
          <input type="checkbox" name="is_team_champion" value="1" <?= !empty($car['is_team_champion']) ? 'checked' : '' ?>>
          <span class="champion-toggle-box champion-toggle-box--blue">
            <span class="champion-toggle-icon">🏗️</span>
            <span class="champion-toggle-text">CAMPEÓN MUNDIAL DE CONSTRUCTORES</span>
            <span class="champion-toggle-sub">Activa el badge azul de constructores en la página del auto</span>
          </span>
        </label>
      </div>

      <div class="form-group">
        <label>FABRICANTE</label>
        <input type="text" name="maker" list="list-makers" value="<?= $fv('maker') ?>">
        <datalist id="list-makers">
          <?php foreach ($makers as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?>
        </datalist>
      </div>

      <div class="form-group">
        <label>COLECCIÓN</label>
        <input type="text" name="collection" list="list-cols" value="<?= $fv('collection') ?>">
        <datalist id="list-cols">
          <?php foreach ($collections as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?>
        </datalist>
      </div>

      <div class="form-group full">
        <label>NOTA / HITO HISTÓRICO</label>
        <textarea name="note"><?= $fv('note') ?></textarea>
      </div>

      <!-- Upload nuevas fotos -->
      <?php if (count($images) < 5): ?>
      <div class="form-group full">
        <label>AGREGAR FOTOS (<?= count($images) ?>/5 — podés seleccionar varias a la vez)</label>
        <div class="upload-zone" id="uploadZone">
          <input type="file" name="car_images[]" id="fileInput" accept="image/*" multiple onchange="previewImages(this)">
          <div class="upload-zone-icon">📷</div>
          <div class="upload-zone-text">Hacé clic o arrastrá imágenes</div>
          <div class="upload-zone-sub">JPG, PNG, WebP · máx. 5 MB · hasta <?= 5 - count($images) ?> foto(s) más</div>
          <div class="upload-preview" id="uploadPreview"></div>
        </div>
      </div>
      <?php else: ?>
      <div class="form-group full">
        <div class="alert alert-success">✅ Límite de 5 fotos alcanzado. Eliminá alguna para agregar otra.</div>
      </div>
      <?php endif; ?>

    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">💾 GUARDAR CAMBIOS</button>
      <a href="?page=collection" class="btn btn-ghost">CANCELAR</a>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var zone = document.getElementById('uploadZone');
  if (!zone) return;
  zone.addEventListener('click', function(e) {
    if (e.target === document.getElementById('fileInput')) return;
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('fileInput').click();
  });
  zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
  zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
  zone.addEventListener('drop', function(e) {
    e.preventDefault();
    zone.classList.remove('dragover');
    var fi = document.getElementById('fileInput');
    if (e.dataTransfer.files.length && fi) {
      fi.files = e.dataTransfer.files;
      previewImages(fi);
    }
  });
});
</script>
