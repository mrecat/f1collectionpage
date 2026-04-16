<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    // Configuración robusta de sesiones para entornos cloud
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// ── Timeout automático ────────────────────────────
if (SESSION_TIMEOUT > 0 && isset($_SESSION['f1_admin_last'])) {
    if (time() - $_SESSION['f1_admin_last'] > SESSION_TIMEOUT * 60) {
        $_SESSION = [];
        session_destroy();
        session_start();
    }
}
if (!empty($_SESSION['f1_admin'])) {
    $_SESSION['f1_admin_last'] = time();
}

// ── Procesar login / logout ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['f1_login'])) {
    $user = trim($_POST['f1_user'] ?? '');
    $pass = $_POST['f1_pass'] ?? '';
    if ($user === ADMIN_USER && $pass === ADMIN_PASSWORD) {
        session_regenerate_id(true);
        $_SESSION['f1_admin']      = true;
        $_SESSION['f1_admin_last'] = time();
        // Redirect relativo, funciona en cualquier hosting/dominio
        $redirect = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                  . '://' . $_SERVER['HTTP_HOST']
                  . strtok($_SERVER['REQUEST_URI'], '?')
                  . '?page=collection';
        header('Location: ' . $redirect);
        exit;
    } else {
        $_SESSION['f1_login_error'] = true;
    }
}

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    $redirect = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
              . '://' . $_SERVER['HTTP_HOST']
              . strtok($_SERVER['REQUEST_URI'], '?')
              . '?page=collection';
    header('Location: ' . $redirect);
    exit;
}

function isAdmin(): bool {
    return !empty($_SESSION['f1_admin']);
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ?page=login&denied=1');
        exit;
    }
}
