<?php

$cars  = getCars([], 'year', 'asc', 'fangio');
$admin = isAdmin();
$msg   = $_GET['msg'] ?? '';

?>

<div class="page-title fangio-title">🏁 MUSEO <span>FANGIO</span></div>
<p class="fangio-subtitle">
    La colección de Juan Manuel Fangio<br><br>

    Explorá los legendarios vehículos de competición que condujo el quíntuple campeón mundial argentino en las principales categorías fuera de la Fórmula 1.
</p>

<?php if ($msg === 'success'): ?>
  <div class="alert alert-success">✅ Auto agregado al Museo Fangio.</div>
<?php elseif ($msg === 'car_deleted'): ?>
  <div class="alert alert-success">🗑️ Auto eliminado del Museo Fangio.</div>
<?php endif; ?>

<div class="collection-topbar">
  <div class="result-count">
    <strong><?= count($cars) ?></strong> AUTO<?= count($cars)!==1?'S':'' ?> EN EL MUSEO
    <?php if ($admin): ?>
      <span style="color:var(--gold);margin-left:12px;">★ MODO ADMIN</span>
    <?php endif; ?>
  </div>
  <?php if ($admin): ?>
    <a href="?page=add&cat=fangio" class="btn btn-primary">➕ AGREGAR AUTO</a>
  <?php endif; ?>
</div>

<?php if (empty($cars)): ?>
  <div class="empty">
    <div class="empty-icon">🏁</div>
    <p>TODAVÍA NO HAY AUTOS CARGADOS EN EL MUSEO FANGIO</p>
    <?php if ($admin): ?>
      <a href="?page=add&cat=fangio" class="btn btn-primary" style="margin-top:16px;">➕ AGREGAR EL PRIMERO</a>
    <?php endif; ?>
  </div>
<?php else: ?>

<div class="coll-cards-grid">
  <?php foreach ($cars as $car): ?>
  <?php
    $imgPath = getFirstImage((int)$car['id']) ? htmlspecialchars(getFirstImage((int)$car['id'])) : '';
    $carSlug = htmlspecialchars(makeCarSlug($car));
  ?>
  <a href="?page=car&slug=<?= $carSlug ?>" class="coll-card">
    <div class="coll-card-img">
      <?php if ($imgPath): ?>
        <img src="<?= $imgPath ?>" alt="<?= htmlspecialchars($car['model']) ?>">
      <?php else: ?>
        <span class="coll-card-noimg">🏆</span>
      <?php endif; ?>
      <span class="coll-card-year"><?= $car['year'] ?></span>
      <?php if ($admin): ?>
        <button class="coll-card-edit" onclick="event.preventDefault();event.stopPropagation();window.location='?page=edit&id=<?= $car['id'] ?>'" title="Editar">✏️</button>
      <?php endif; ?>
    </div>
    <div class="coll-card-body">
      <div class="coll-card-team"><?= htmlspecialchars($car['team']) ?></div>
      <div class="coll-card-model"><?= htmlspecialchars($car['model']) ?></div>
      <?php if ($car['driver']): ?>
        <div class="coll-card-driver"><?= htmlspecialchars($car['driver']) ?></div>
      <?php endif; ?>
      <?php if ($car['note']): ?>
        <div class="coll-card-note"><?= htmlspecialchars($car['note']) ?></div>
      <?php endif; ?>
      <span class="coll-card-detail-link">Ver ficha →</span>
    </div>
  </a>
  <?php endforeach; ?>
</div>

<?php endif; ?>
