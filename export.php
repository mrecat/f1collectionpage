<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$type = $_GET['type'] ?? '';   // 'csv' | 'print'

// ── Leer filtros activos (mismos que collection.php) ──────────────────────
$filters = [
    'q'      => trim($_GET['q']      ?? ''),
    'year'   => trim($_GET['year']   ?? ''),
    'team'   => trim($_GET['team']   ?? ''),
    'driver' => trim($_GET['driver'] ?? ''),
    'maker'  => trim($_GET['maker']  ?? ''),
];
$sortField = in_array($_GET['sort'] ?? '', ['year','team']) ? $_GET['sort'] : 'year';
$sortDir   = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

$cars = getCars($filters, $sortField, $sortDir);

// ════════════════════════════════════════════════════════════
//  CSV
// ════════════════════════════════════════════════════════════
if ($type === 'csv') {
    $filename = 'f1-coleccion-' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache');

    // BOM para que Excel abra correctamente con tildes
    echo "\xEF\xBB\xBF";

    $out = fopen('php://output', 'w');
    fputcsv($out, ['AÑO', 'ESCUDERÍA', 'MODELO', 'PILOTO', 'FABRICANTE'], ';');

    foreach ($cars as $car) {
        fputcsv($out, [
            $car['year'],
            $car['team'],
            $car['model'],
            $car['driver'],
            $car['maker'],
        ], ';');
    }
    fclose($out);
    exit;
}

// ════════════════════════════════════════════════════════════
//  PRINT / PDF
// ════════════════════════════════════════════════════════════
if ($type === 'print') {
    $total = count($cars);
    $filterDesc = [];
    if ($filters['year'])   $filterDesc[] = 'Año: ' . htmlspecialchars($filters['year']);
    if ($filters['team'])   $filterDesc[] = 'Escudería: ' . htmlspecialchars($filters['team']);
    if ($filters['driver']) $filterDesc[] = 'Piloto: ' . htmlspecialchars($filters['driver']);
    if ($filters['maker'])  $filterDesc[] = 'Fabricante: ' . htmlspecialchars($filters['maker']);
    if ($filters['q'])      $filterDesc[] = 'Búsqueda: ' . htmlspecialchars($filters['q']);
    $filterLine = $filterDesc ? implode(' · ', $filterDesc) : 'Colección completa';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>F1 Colección — Exportar</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Rajdhani:wght@400;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --red: #e10600;
    --gold: #ffc906;
    --text: #111;
    --muted: #555;
    --border: #ddd;
    --bg-head: #111;
  }

  body {
    font-family: 'Rajdhani', sans-serif;
    background: #fff;
    color: var(--text);
    padding: 32px 40px;
  }

  /* ── Header ── */
  .export-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 3px solid var(--red);
    gap: 16px;
  }
  .export-logo {
    display: flex;
    flex-direction: column;
  }
  .export-logo-title {
    font-family: 'Orbitron', monospace;
    font-size: 22px;
    font-weight: 900;
    color: var(--red);
    letter-spacing: 2px;
  }
  .export-logo-sub {
    font-family: 'Orbitron', monospace;
    font-size: 10px;
    color: var(--muted);
    letter-spacing: 3px;
    margin-top: 2px;
  }
  .export-meta {
    text-align: right;
    font-size: 13px;
    color: var(--muted);
    line-height: 1.8;
  }
  .export-meta strong { color: var(--text); }

  /* ── Filtros activos ── */
  .export-filters {
    font-size: 13px;
    color: var(--muted);
    margin-bottom: 20px;
    padding: 8px 14px;
    border-left: 3px solid var(--gold);
    background: #fffbee;
  }
  .export-filters strong { color: var(--text); }

  /* ── Tabla ── */
  table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
  }
  thead {
    background: var(--bg-head);
  }
  thead th {
    font-family: 'Orbitron', monospace;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 2px;
    color: #aaa;
    padding: 12px 14px;
    text-align: left;
    white-space: nowrap;
  }
  thead th:first-child { color: var(--red); }

  tbody tr { border-bottom: 1px solid var(--border); }
  tbody tr:last-child { border-bottom: none; }
  tbody tr:nth-child(even) { background: #f9f9f9; }
  tbody td { padding: 10px 14px; vertical-align: middle; }

  .td-year {
    font-family: 'Orbitron', monospace;
    font-size: 13px;
    font-weight: 900;
    color: var(--red);
    white-space: nowrap;
  }
  .td-team { font-weight: 700; font-size: 15px; }
  .td-model { color: var(--muted); font-size: 13px; margin-top: 2px; }
  .td-driver { font-weight: 600; font-size: 15px; }
  .td-maker {
    font-size: 12px;
    color: #fff;
    background: #333;
    padding: 2px 8px;
    border-radius: 4px;
    display: inline-block;
    white-space: nowrap;
  }

  /* ── Footer ── */
  .export-footer {
    margin-top: 28px;
    padding-top: 14px;
    border-top: 1px solid var(--border);
    font-size: 12px;
    color: var(--muted);
    display: flex;
    justify-content: space-between;
  }

  /* ── Print button (no se imprime) ── */
  .print-bar {
    position: fixed;
    bottom: 24px;
    right: 24px;
    display: flex;
    gap: 10px;
    z-index: 99;
  }
  .btn-print, .btn-back {
    font-family: 'Orbitron', monospace;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    border: none;
    box-shadow: 0 4px 16px rgba(0,0,0,.25);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
  }
  .btn-print { background: var(--red); color: #fff; }
  .btn-print:hover { background: #c00500; }
  .btn-back { background: #222; color: #fff; }
  .btn-back:hover { background: #444; }

  @media print {
    .print-bar { display: none !important; }
    body { padding: 16px 20px; }
    thead { background: #111 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    tbody tr:nth-child(even) { background: #f5f5f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .td-maker { background: #333 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .export-filters { background: #fffbee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>
</head>
<body>

<div class="print-bar">
  <a href="javascript:history.back()" class="btn-back">← VOLVER</a>
  <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / PDF</button>
</div>

<!-- Header -->
<div class="export-header">
  <div class="export-logo">
    <div class="export-logo-title">🏎️ F1 COLECCIÓN</div>
    <div class="export-logo-sub">LISTADO DE AUTOS</div>
  </div>
  <div class="export-meta">
    <strong><?= $total ?> AUTO<?= $total !== 1 ? 'S' : '' ?></strong> EN PARRILLA<br>
    Generado: <?= date('d/m/Y H:i') ?><br>
    Orden: <?= strtoupper($sortField) ?> <?= strtoupper($sortDir) ?>
  </div>
</div>

<!-- Filtros activos -->
<div class="export-filters">
  <strong>FILTROS:</strong> <?= $filterLine ?>
</div>

<!-- Tabla -->
<table>
  <thead>
    <tr>
      <th>#</th>
      <th>AÑO</th>
      <th>ESCUDERÍA / MODELO</th>
      <th>PILOTO</th>
      <th>FABRICANTE</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($cars as $i => $car): ?>
    <tr>
      <td style="color:#aaa;font-size:13px;"><?= $i + 1 ?></td>
      <td><span class="td-year"><?= $car['year'] ?></span></td>
      <td>
        <div class="td-team"><?= htmlspecialchars($car['team']) ?></div>
        <div class="td-model"><?= htmlspecialchars($car['model']) ?></div>
      </td>
      <td><span class="td-driver"><?= htmlspecialchars($car['driver']) ?></span></td>
      <td><span class="td-maker"><?= htmlspecialchars($car['maker']) ?></span></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Footer -->
<div class="export-footer">
  <span>F1 Colección &copy; <?= date('Y') ?></span>
  <span>Total: <?= $total ?> autos</span>
</div>

</body>
</html>
<?php
    exit;
}

// Fallback
header('Location: index.php?page=collection');
exit;
