<?php
// ═══════════════════════════════════════════════════
// F1 COLLECTION — CONFIGURACIÓN (PLANTILLA)
// Copiá este archivo como config.php y completá tus datos.
// ═══════════════════════════════════════════════════
//
// En producción conviene no guardar el hash en este archivo, sino en
// variables de entorno, que tienen prioridad sobre los valores de abajo:
//
//   F1_ADMIN_USER           = tu usuario
//   F1_ADMIN_PASSWORD_HASH  = el hash que devuelve este comando
//
//   php -r "echo password_hash('TU_CONTRASEÑA', PASSWORD_DEFAULT);"

$envUser = getenv('F1_ADMIN_USER');
$envHash = getenv('F1_ADMIN_PASSWORD_HASH');

// Usuario del administrador
define('ADMIN_USER', ($envUser !== false && $envUser !== '') ? $envUser : 'admin');

// Hash de la contraseña del administrador (NUNCA la contraseña en texto plano)
define('ADMIN_PASSWORD_HASH', ($envHash !== false && $envHash !== '')
    ? $envHash
    : 'PEGÁ_ACÁ_TU_HASH_GENERADO');

// Nombre del sitio (aparece en el título y el header)
define('SITE_NAME', 'F1 COLLECTION');
define('SITE_SUB', 'MUSEO PRIVADO');

// Minutos de inactividad antes de cerrar sesión automáticamente (0 = sin límite)
define('SESSION_TIMEOUT', 60);
