<?php
// ═══════════════════════════════════════════════════
// F1 COLLECTION — CONFIGURACIÓN
// ═══════════════════════════════════════════════════
//
// Las credenciales del administrador se leen primero de variables de
// entorno (así no quedan en el repo). Si no están definidas, se usan los
// valores de abajo como respaldo para desarrollo local.
//
// En Render: Dashboard → el servicio → Environment → Add Environment Variable
//   F1_ADMIN_USER           = tu usuario
//   F1_ADMIN_PASSWORD_HASH  = el hash que devuelve este comando
//
//   php -r "echo password_hash('TU_CONTRASEÑA', PASSWORD_DEFAULT);"
//
// Mientras la variable de entorno esté definida, el hash de abajo se ignora.

$envUser = getenv('F1_ADMIN_USER');
$envHash = getenv('F1_ADMIN_PASSWORD_HASH');

// Usuario del administrador
define('ADMIN_USER', ($envUser !== false && $envUser !== '') ? $envUser : 'admin');

// Hash de la contraseña del administrador (NUNCA la contraseña en texto plano)
define('ADMIN_PASSWORD_HASH', ($envHash !== false && $envHash !== '')
    ? $envHash
    : '$2b$10$i.540NvTw.Q88zcRctwcIOnEJMhK4WgqyuQmU/ClFTt8TMV/./1LC');

// Nombre del sitio (aparece en el título y el header)
define('SITE_NAME', 'F1 COLLECTION');
define('SITE_SUB', 'MUSEO PRIVADO');

// Minutos de inactividad antes de cerrar sesión automáticamente (0 = sin límite)
define('SESSION_TIMEOUT', 60);
