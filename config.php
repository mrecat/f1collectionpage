<?php
// ═══════════════════════════════════════════════════
//  F1 COLLECTION — CONFIGURACIÓN
//  Las credenciales se leen desde variables de entorno.
//  Configurarlas en Render → Environment Variables:
//
//    ADMIN_USER          → nombre de usuario admin
//    ADMIN_PASSWORD_HASH → hash bcrypt de la contraseña
//                          (generarlo con: php -r "echo password_hash('tu-pass', PASSWORD_BCRYPT);")
// ═══════════════════════════════════════════════════

define('ADMIN_USER',          getenv('ADMIN_USER')          ?: 'admin');
define('ADMIN_PASSWORD_HASH', getenv('ADMIN_PASSWORD_HASH') ?: '');

// Nombre del sitio (aparece en el título y el header)
define('SITE_NAME', 'F1 COLLECTION');
define('SITE_SUB',  'MUSEO PRIVADO');

// Minutos de inactividad antes de cerrar sesión automáticamente (0 = sin límite)
define('SESSION_TIMEOUT', 60);
