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

// ── Procesar login / logout ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['f1_login'])) {
    $ip          = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $attempt_key = 'login_attempts_' . md5($ip);
    $lockout_key = 'login_lockout_'  . md5($ip);

    // Verificar bloqueo activo
    if (!empty($_SESSION[$lockout_key]) && time() < $_SESSION[$lockout_key]) {
        $wait = ceil(($_SESSION[$lockout_key] - time()) / 60);
        $_SESSION['f1_login_error'] = "Demasiados intentos. Esperá {$wait} minuto(s).";
    } else {
        $user = trim($_POST['f1_user'] ?? '');
        $pass = $_POST['f1_pass'] ?? '';

        if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASSWORD_HASH)) {
            // Login exitoso — resetear contador y limpiar bloqueo
            unset($_SESSION[$attempt_key], $_SESSION[$lockout_key]);
            session_regenerate_id(true);
            $_SESSION['f1_admin']      = true;
            $_SESSION['f1_admin_last'] = time();
            header('Location: ?page=collection');
            exit;
        } else {
            // Login fallido — incrementar contador
            $attempts = ($_SESSION[$attempt_key] ?? 0) + 1;
            $_SESSION[$attempt_key] = $attempts;

            if ($attempts >= 5) {
                $_SESSION[$lockout_key]  = time() + 15 * 60; // bloqueo 15 minutos
                unset($_SESSION[$attempt_key]);
                $_SESSION['f1_login_error'] = 'Demasiados intentos. Cuenta bloqueada 15 minutos.';
            } else {
                $_SESSION['f1_login_error'] = true;
            }
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
