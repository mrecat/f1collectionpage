<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=home');
    exit;
}

requireAdmin();

// Ruta base de la app — funciona sin importar dónde esté instalada
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

$action   = $_POST['action']  ?? '';
$car_id   = (int)($_POST['car_id']   ?? 0);
$image_id = (int)($_POST['image_id'] ?? 0);

switch ($action) {

    case 'delete_car':
        if ($car_id) {
            deleteAllCarImages($car_id);
            deleteCar($car_id);
        }
        header("Location: {$base}/index.php?page=home");
        exit;

    case 'delete_image':
        if ($car_id && $image_id) {
            deleteCarImageByIndex($car_id, $image_id);
        }
        header("Location: {$base}/index.php?page=edit&id={$car_id}&msg=img_deleted");
        exit;

    case 'set_cover':
        if ($car_id && $image_id) {
            setCoverImage($car_id, $image_id);
        }
        header("Location: {$base}/index.php?page=edit&id={$car_id}&msg=cover_set");
        exit;

    case 'save_performance':
        $perf  = trim($_POST['performance'] ?? '');
        $champ = isset($_POST['is_champion']) ? (int)$_POST['is_champion'] : 0;
        if ($car_id) {
            getDB()->prepare("UPDATE cars SET performance=?, is_champion=? WHERE id=?")
                   ->execute([$perf, $champ, $car_id]);
        }
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;

    case 'save_new_car':
        $data = [
            'year'       => (int)($_POST['year']       ?? 0),
            'team'       => trim($_POST['team']        ?? ''),
            'model'      => trim($_POST['model']       ?? ''),
            'driver'     => trim($_POST['driver']      ?? ''),
            'maker'      => trim($_POST['maker']       ?? ''),
            'collection' => trim($_POST['collection']  ?? ''),
            'note'       => trim($_POST['note']        ?? ''),
        ];
        if ($data['year'] && $data['team'] && $data['model']) {
            saveCar($data);
            $newId = (int)getDB()->lastInsertId();
            handleMultiImageUpload($newId);
            header("Location: {$base}/index.php?page=add&msg=success");
        } else {
            header("Location: {$base}/index.php?page=add&msg=error");
        }
        exit;

    default:
        header("Location: {$base}/index.php?page=home");
        exit;
}
