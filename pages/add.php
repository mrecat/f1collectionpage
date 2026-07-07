<?php
requireAdmin();

// Leer msg del redirect de action.php
$msg      = $_GET['msg'] ?? '';
$category = ($_GET['cat'] ?? '') === 'fangio' ? 'fangio' : 'f1';
$isFangio = $category === 'fangio';
$backPage = $isFangio ? 'museo_fangio' : 'collection';

$teams       = getDistinct('team', $category);
$makers      = getDistinct('maker', $category);
$collections = getDistinct('collection', $category);
?>

<div class="page-title<?= $isFangio ? ' fangio-title' : '' ?>">
  ➕ AGREGAR <span><?= $isFangio ? 'AUTO — MUSEO FANGIO' : 'AUTO' ?></span>
</div>

<?php if ($msg === 'success'): ?>
  <div class="alert alert-success">✅ Auto agregado. <a href="?page=<?= $backPage ?>" style="color:inherit;font-weight:700">Ver colección →</a></div>
<?php elseif ($msg === 'error'): ?>
  <div class="alert alert-error">❌ Completá al menos Año, Escudería y Modelo.</div>
<?php endif; ?>

<div class="form-card">
  <!-- action.php maneja el POST y el upload, sin depender del routing de index.php -->
  <form method="post" action="action.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save_new_car">
    <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
    <div class="form-grid">

      <div class="form-group">
        <label>AÑO *</label>
        <input type="number" name="year" min="1900" max="2099" placeholder="ej: 1988" required>
      </div>

      <div class="form-group">
        <label>ESCUDERÍA *</label>
        <input type="text" name="team" list="list-teams" placeholder="ej: McLaren" required>
        <datalist id="list-teams">
          <?php foreach ($teams as $t): ?><option value="<?= htmlspecialchars($t) ?>"><?php endforeach; ?>
        </datalist>
      </div>

      <div class="form-group full">
        <label>MODELO *</label>
        <input type="text" name="model" placeholder="ej: McLaren MP4/4" required>
      </div>

      <div class="form-group full">
        <label>PILOTO</label>
        <input type="text" name="driver" placeholder="ej: Ayrton Senna">
      </div>

      <div class="form-group">
        <label>FABRICANTE</label>
        <input type="text" name="maker" list="list-makers" placeholder="ej: IXO / Salvat">
        <datalist id="list-makers">
          <?php foreach ($makers as $m): ?><option value="<?= htmlspecialchars($m) ?>"><?php endforeach; ?>
        </datalist>
      </div>

      <div class="form-group">
        <label>COLECCIÓN</label>
        <input type="text" name="collection" list="list-cols" placeholder="ej: BBurago F1">
        <datalist id="list-cols">
          <?php foreach ($collections as $c): ?><option value="<?= htmlspecialchars($c) ?>"><?php endforeach; ?>
        </datalist>
      </div>

      <div class="form-group full">
        <label>NOTA / HITO HISTÓRICO</label>
        <textarea name="note" placeholder="Descripción del auto, hito histórico, curiosidad…"></textarea>
      </div>

      <div class="form-group full">
        <label>FOTOS DEL AUTO (hasta 5, podés seleccionar varias)</label>
        <div class="upload-zone" id="uploadZone">
          <input type="file" name="car_images[]" id="fileInput" accept="image/*" multiple onchange="previewImages(this)">
          <div class="upload-zone-icon">📷</div>
          <div class="upload-zone-text">Hacé clic o arrastrá imágenes</div>
          <div class="upload-zone-sub">JPG, PNG, WebP · máx. 5 MB por foto</div>
          <div class="upload-preview" id="uploadPreview"></div>
        </div>
      </div>

    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">🏁 <?= $isFangio ? 'AGREGAR AL MUSEO' : 'AGREGAR A LA PARRILLA' ?></button>
      <a href="?page=<?= $backPage ?>" class="btn btn-ghost">CANCELAR</a>
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
