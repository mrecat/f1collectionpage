<?php
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
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

// ── Protección básica contra fuerza bruta ─────────
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_SECONDS', 300); // 5 minutos

function loginBlocked(): bool {
    if (empty($_SESSION['f1_login_attempts'])) {
        return false;
    }
    $attempts = $_SESSION['f1_login_attempts'];
    $lastAttempt = $_SESSION['f1_login_last_attempt'] ?? 0;

    if ($attempts >= LOGIN_MAX_ATTEMPTS) {
        if (time() - $lastAttempt < LOGIN_LOCKOUT_SECONDS) {
            return true;
        }
        // Ya pasó el tiempo de bloqueo, reseteamos contador
        $_SESSION['f1_login_attempts'] = 0;
    }
    return false;
}

// ── Procesar login / logout ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['f1_login'])) {
    if (loginBlocked()) {
        $_SESSION['f1_login_error'] = true;
        $_SESSION['f1_login_locked'] = true;
    } else {
        $user = trim($_POST['f1_user'] ?? '');
        $pass = $_POST['f1_pass'] ?? '';

        if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASSWORD_HASH)) {
            session_regenerate_id(true);
            $_SESSION['f1_admin'] = true;
            $_SESSION['f1_admin_last'] = time();
            $_SESSION['f1_login_attempts'] = 0;
            header('Location: ?page=collection');
            exit;
        } else {
            $_SESSION['f1_login_attempts'] = ($_SESSION['f1_login_attempts'] ?? 0) + 1;
            $_SESSION['f1_login_last_attempt'] = time();
            $_SESSION['f1_login_error'] = true;
        }
    }
}

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: ?page=collection');
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
