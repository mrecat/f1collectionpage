<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_fav'])) {
    toggleFavorite((int)$_POST['toggle_fav']);
    header("Location: ?page=favorites");
    exit;
}

$cars = getCars(['favorites' => true]);
?>

<div class="page-title">⭐ MIS <span>FAVORITOS</span></div>

<?php if (empty($cars)): ?>
  <div class="empty">
    <div class="empty-icon">☆</div>
    <p>TODAVÍA NO MARCASTE NINGÚN FAVORITO</p>
    <br>
    <a href="?page=collection" class="btn btn-primary" style="margin-top:12px">IR A LA COLECCIÓN</a>
  </div>
<?php else: ?>
<div class="result-count"><strong><?= count($cars) ?></strong> FAVORITO<?= count($cars)!==1?'S':'' ?></div>
<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>⭐</th><th>AÑO</th><th>ESCUDERÍA</th><th>MODELO</th>
        <th>PILOTO</th><th>FABRICANTE</th><th>NOTA</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cars as $car): ?>
      <tr class="is-favorite">
        <td>
          <form method="post" style="display:inline">
            <button class="fav-btn" name="toggle_fav" value="<?= $car['id'] ?>" title="Quitar favorito">⭐</button>
          </form>
        </td>
        <td><span class="year-badge"><?= $car['year'] ?></span></td>
        <td><span class="team-name"><?= htmlspecialchars($car['team']) ?></span></td>
        <td><span class="model-name"><?= htmlspecialchars($car['model']) ?></span></td>
        <td><span class="driver-name"><?= htmlspecialchars($car['driver']) ?></span></td>
        <td><span class="maker-badge"><?= htmlspecialchars($car['maker']) ?></span></td>
        <td><span class="note-text"><?= htmlspecialchars($car['note']) ?></span></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
