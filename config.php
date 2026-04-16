<?php
// ═══════════════════════════════════════════════════
//  F1 COLLECTION — CONFIGURACIÓN
//  Editá estas líneas antes de subir al servidor
// ═══════════════════════════════════════════════════

// Usuario y contraseña del administrador
// Para generar un hash seguro podés usar:
//   php -r "echo password_hash('TU_CONTRASEÑA', PASSWORD_DEFAULT);"
// O reemplazá directamente ADMIN_PASSWORD con tu contraseña en texto plano
// y cambiá la verificación en auth.php (ver comentario ahí)

define('ADMIN_USER',     'admin');
define('ADMIN_PASSWORD', 'f1collection2025');   // ← cambiá esto antes de subir

// Nombre del sitio (aparece en el título y el header)
define('SITE_NAME', 'F1 COLLECTION');
define('SITE_SUB',  'MUSEO PRIVADO');

// Minutos de inactividad antes de cerrar sesión automáticamente (0 = sin límite)
define('SESSION_TIMEOUT', 60);
