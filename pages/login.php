<?php
$error  = !empty($_SESSION['f1_login_error']);
$denied = !empty($_GET['denied']);
if ($error) unset($_SESSION['f1_login_error']);
?>

<div style="display:flex;align-items:center;justify-content:center;min-height:60vh;">
  <div class="form-card" style="max-width:420px;width:100%;text-align:center;">

    <div style="font-size:52px;margin-bottom:8px;">🏁</div>
    <div class="page-title" style="justify-content:center;margin-bottom:6px;">
      ACCESO <span>ADMIN</span>
    </div>
    <p style="color:var(--muted);font-size:15px;margin-bottom:28px;letter-spacing:1px;">
      ZONA RESTRINGIDA — SOLO ADMINISTRADOR
    </p>

    <?php if ($error): ?>
      <div class="alert alert-error" style="margin-bottom:20px;">
        ❌ Usuario o contraseña incorrectos
      </div>
    <?php endif; ?>

    <?php if ($denied): ?>
      <div class="alert alert-error" style="margin-bottom:20px;">
        🔒 Necesitás iniciar sesión para continuar
      </div>
    <?php endif; ?>

    <form method="post" action="?page=login" style="text-align:left;">
      <input type="hidden" name="f1_login" value="1">

      <div class="form-group" style="margin-bottom:16px;">
        <label>USUARIO</label>
        <input type="text" name="f1_user" placeholder="admin" autocomplete="username" required autofocus>
      </div>

      <div class="form-group" style="margin-bottom:24px;">
        <label>CONTRASEÑA</label>
        <input type="password" name="f1_pass" placeholder="••••••••" autocomplete="current-password" required>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;font-size:13px;padding:14px;">
        🏎️ ENTRAR AL PIT LANE
      </button>
    </form>

    <div style="margin-top:20px;">
      <a href="?page=collection" style="color:var(--muted);font-size:14px;font-family:'Orbitron',monospace;letter-spacing:1px;text-decoration:none;">
        ← VOLVER A LA COLECCIÓN
      </a>
    </div>

  </div>
</div>
