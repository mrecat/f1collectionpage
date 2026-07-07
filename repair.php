<?php
/**
 * repair.php — Reconstruye la tabla car_images escaneando data/images/.
 *
 * Se usa UNA VEZ para recuperar los vínculos que la vieja limpieza automática
 * (cleanOrphanImages) borró cuando el sitio corrió con la carpeta de imágenes
 * vacía. Los archivos siguen su nombre original car_<id>_<timestamp>.<ext>,
 * así que se puede reconstruir el registro a partir de eso.
 *
 * Es seguro correrlo más de una vez: no duplica filas si ya existen.
 * Requiere sesión de admin.
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
requireAdmin();

$dir = __DIR__ . '/data/images/';
$added = 0;
$skippedExisting = 0;
$skippedNoMatch = 0;
$unknownCarId = 0;

$results = [];

if (!is_dir($dir)) {
    die('No existe la carpeta data/images/.');
}

$files = scandir($dir);
$db = getDB();

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $full = $dir . $file;
    if (!is_file($full)) continue;

    // Espera nombres tipo: car_123_1774664087.png
    if (!preg_match('/^car_(\d+)_(\d+)\.\w+$/i', $file, $m)) {
        $skippedNoMatch++;
        $results[] = "⚠️ Nombre no reconocido, se ignora: $file";
        continue;
    }

    $carId = (int)$m[1];
    $timestamp = (int)$m[2];
    $relPath = 'data/images/' . $file;

    // ¿Existe el auto?
    $chkCar = $db->prepare("SELECT id FROM cars WHERE id = ?");
    $chkCar->execute([$carId]);
    if (!$chkCar->fetch()) {
        $unknownCarId++;
        $results[] = "❌ El auto #$carId no existe en la base — se ignora: $file";
        continue;
    }

    // ¿Ya existe la referencia?
    $chk = $db->prepare("SELECT id FROM car_images WHERE car_id = ? AND path = ?");
    $chk->execute([$carId, $relPath]);
    if ($chk->fetch()) {
        $skippedExisting++;
        continue;
    }

    // Sort order: cuántas fotos ya tiene ese auto (para no pisar una portada ya fijada)
    $cnt = $db->prepare("SELECT COUNT(*) FROM car_images WHERE car_id = ?");
    $cnt->execute([$carId]);
    $sortOrder = (int)$cnt->fetchColumn();

    $db->prepare("INSERT INTO car_images (car_id, path, sort_order, created_at) VALUES (?, ?, ?, datetime(?, 'unixepoch'))")
       ->execute([$carId, $relPath, $sortOrder, $timestamp]);

    $added++;
    $results[] = "✅ Auto #$carId — vinculada: $file";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reparación de imágenes — F1 Collection</title>
<style>
  body { font-family: monospace; background:#111; color:#eee; padding:24px; line-height:1.5; }
  h1 { color:#4a8fd6; }
  .ok { color:#7cd992; }
  .warn { color:#e0c060; }
  .err { color:#e06060; }
  .summary { background:#1c1c1c; padding:16px; border-radius:8px; margin-bottom:20px; }
  a { color:#4a8fd6; }
</style>
</head>
<body>
<h1>🔧 Reparación de imágenes</h1>
<div class="summary">
  <strong><?= $added ?></strong> referencias nuevas creadas<br>
  <strong><?= $skippedExisting ?></strong> ya existían (sin cambios)<br>
  <strong><?= $unknownCarId ?></strong> archivos con ID de auto inexistente<br>
  <strong><?= $skippedNoMatch ?></strong> archivos con nombre no reconocido
</div>
<pre><?php foreach ($results as $r) {
    $cls = str_starts_with($r, '✅') ? 'ok' : (str_starts_with($r, '⚠️') ? 'warn' : 'err');
    echo "<span class=\"$cls\">" . htmlspecialchars($r) . "</span>\n";
} ?></pre>
<p><a href="index.php?page=home">← Volver al inicio</a></p>
</body>
</html>
