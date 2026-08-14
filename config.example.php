<?php
// ═══════════════════════════════════════════════════
// F1 COLLECTION — CONFIGURACIÓN (PLANTILLA)
// Copiá este archivo como config.php y completá tus datos.
// config.php NO debe subirse al repo (ver .gitignore).
// ═══════════════════════════════════════════════════

// Usuario del administrador
define('ADMIN_USER', 'admin');

// Hash de la contraseña del administrador (NO poner la contraseña en texto plano).
// Generalo con:
// php -r "echo password_hash('TU_CONTRASEÑA', PASSWORD_DEFAULT);"
define('ADMIN_PASSWORD_HASH', 'PEGÁ_ACÁ_TU_HASH_GENERADO');

// Nombre del sitio (aparece en el título y el header)
define('SITE_NAME', 'F1 COLLECTION');
define('SITE_SUB', 'MUSEO PRIVADO');

// Minutos de inactividad antes de cerrar sesión automáticamente (0 = sin límite)
define('SESSION_TIMEOUT', 60);
