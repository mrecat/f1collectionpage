<?php
requireAdmin();

$msg = '';

// Guardar texto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['about_text'])) {
    saveSetting('about_text', trim($_POST['about_text']));
    $msg = 'text_saved';
}

// Subir foto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['about_photo']) && $_FILES['about_photo']['error'] === 0) {
    $file    = $_FILES['about_photo'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (in_array($file['type'], $allowed)) {
        $dir = __DIR__ . '/../img/about/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'about_photo.' . $ext;
        move_uploaded_file($file['tmp_name'], $dir . $filename);
        saveSetting('about_photo', 'img/about/' . $filename);
        $msg = 'photo_saved';
    }
}

// Borrar foto
if (isset($_POST['delete_photo'])) {
    $current = getSetting('about_photo');
    if ($current && file_exists(__DIR__ . '/../' . $current)) {
        unlink(__DIR__ . '/../' . $current);
    }
    saveSetting('about_photo', '');
    $msg = 'photo_deleted';
}

$aboutText  = getSetting('about_text');
$aboutPhoto = getSetting('about_photo');
?>

<div class="page-title">✏️ EDITAR <span>SOBRE LA COLECCIÓN</span></div>

<?php if ($msg === 'text_saved'): ?>
  <div class="alert alert-success">✅ Texto guardado correctamente.</div>
<?php elseif ($msg === 'photo_saved'): ?>
  <div class="alert alert-success">✅ Foto actualizada correctamente.</div>
<?php elseif ($msg === 'photo_deleted'): ?>
  <div class="alert alert-success">✅ Foto eliminada.</div>
<?php endif; ?>

<div class="about-edit-wrap">

  <!-- Foto -->
  <div class="about-edit-section">
    <h3 class="about-edit-title">📷 FOTO</h3>
    <div class="about-edit-photo-preview">
      <?php if ($aboutPhoto): ?>
        <img src="<?= htmlspecialchars($aboutPhoto) ?>" alt="Foto actual" class="about-edit-photo-img">
        <form method="POST">
          <button type="submit" name="delete_photo" value="1" class="btn btn-ghost btn-sm" style="margin-top:8px;">🗑️ BORRAR FOTO</button>
        </form>
      <?php else: ?>
        <div class="about-photo-placeholder">Sin foto todavía</div>
      <?php endif; ?>
    </div>
    <form method="POST" enctype="multipart/form-data" class="about-edit-upload">
      <label class="about-edit-file-label">
        <input type="file" name="about_photo" accept="image/jpeg,image/png,image/webp" class="about-edit-file-input">
        <span class="btn btn-ghost">📁 ELEGIR FOTO</span>
      </label>
      <button type="submit" class="btn btn-primary">⬆️ SUBIR</button>
    </form>
    <p class="about-edit-hint">JPG, PNG o WEBP. Recomendado: cuadrada, mínimo 400×400px.</p>
  </div>

  <!-- Texto -->
  <div class="about-edit-section">
    <h3 class="about-edit-title">📝 TEXTO</h3>
    <form method="POST" class="about-edit-text-form">
      <textarea name="about_text" class="about-edit-textarea" placeholder="Contá tu historia con la colección..."><?= htmlspecialchars($aboutText) ?></textarea>
      <div class="about-edit-actions">
        <button type="submit" class="btn btn-primary">💾 GUARDAR</button>
        <a href="?page=about" class="btn btn-ghost">👁️ VER PÁGINA</a>
      </div>
    </form>
  </div>

</div>
