<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

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

// ── Protección contra fuerza bruta ────────────────
// Los intentos fallidos se guardan en la base, no en la sesión: así el
// bloqueo también aplica a quien no manda cookies (que es justamente como
// trabaja un bot).
define('LOGIN_MAX_ATTEMPTS', 5);        // por IP
define('LOGIN_WINDOW_SECONDS', 900);    // ventana de 15 minutos
define('LOGIN_GLOBAL_MAX', 50);         // total, para IPs rotativas
define('LOGIN_GLOBAL_LOCKOUT', 300);    // 5 minutos

// IP del visitante. En Render la app está detrás de un proxy, así que
// REMOTE_ADDR es siempre el proxy; ahí sirve X-Forwarded-For. Como esa
// cabecera la puede falsear el cliente, el tope global de más abajo cubre
// el caso de alguien que rote IPs inventadas.
function loginClientIp(): string {
    $fwd = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($fwd !== '') {
        $first = trim(explode(',', $fwd)[0]);
        if (filter_var($first, FILTER_VALIDATE_IP)) {
            return $first;
        }
    }
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    return $remote !== '' ? $remote : 'desconocida';
}

function loginRecordFailure(string $ip): void {
    try {
        getDB()->prepare("INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)")
               ->execute([$ip, time()]);
    } catch (Exception $e) {
        // Si la base no se puede escribir, no bloqueamos el login por eso
    }
}

function loginClearFailures(string $ip): void {
    try {
        getDB()->prepare("DELETE FROM login_attempts WHERE ip = ?")->execute([$ip]);
    } catch (Exception $e) {
    }
}

function loginPurgeOldFailures(): void {
    try {
        getDB()->prepare("DELETE FROM login_attempts WHERE attempted_at < ?")
               ->execute([time() - 86400]);
    } catch (Exception $e) {
    }
}

// Devuelve los segundos que faltan para poder volver a intentar, o 0.
function loginBlockedSeconds(string $ip): int {
    try {
        $db    = getDB();
        $since = time() - LOGIN_WINDOW_SECONDS;

        $stmt = $db->prepare("SELECT COUNT(*) AS n, MAX(attempted_at) AS last
                              FROM login_attempts WHERE ip = ? AND attempted_at >= ?");
        $stmt->execute([$ip, $since]);
        $row = $stmt->fetch();
        if ($row && (int)$row['n'] >= LOGIN_MAX_ATTEMPTS) {
            $left = LOGIN_WINDOW_SECONDS - (time() - (int)$row['last']);
            if ($left > 0) return $left;
        }

        $stmt = $db->prepare("SELECT COUNT(*) AS n, MAX(attempted_at) AS last
                              FROM login_attempts WHERE attempted_at >= ?");
        $stmt->execute([$since]);
        $row = $stmt->fetch();
        if ($row && (int)$row['n'] >= LOGIN_GLOBAL_MAX) {
            $left = LOGIN_GLOBAL_LOCKOUT - (time() - (int)$row['last']);
            if ($left > 0) return $left;
        }
    } catch (Exception $e) {
        return 0;
    }
    return 0;
}

// ── Token CSRF ────────────────────────────────────
function csrfToken(): string {
    if (empty($_SESSION['f1_csrf'])) {
        $_SESSION['f1_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['f1_csrf'];
}

function csrfField(): string {
    return '<input type="hidden" name="f1_csrf" value="'
         . htmlspecialchars(csrfToken(), ENT_QUOTES) . '">';
}

function csrfValid(): bool {
    $sent = $_POST['f1_csrf'] ?? '';
    return is_string($sent) && $sent !== ''
        && !empty($_SESSION['f1_csrf'])
        && hash_equals($_SESSION['f1_csrf'], $sent);
}

function requireCsrf(): void {
    if (!csrfValid()) {
        http_response_code(403);
        exit('Token de seguridad inválido. Volvé a la página anterior, recargala y probá otra vez.');
    }
}

// ── Procesar login / logout ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['f1_login'])) {
    $ip      = loginClientIp();
    $blocked = loginBlockedSeconds($ip);

    if ($blocked > 0) {
        $_SESSION['f1_login_error']  = true;
        $_SESSION['f1_login_locked'] = (int)ceil($blocked / 60);
    } else {
        loginPurgeOldFailures();

        $user = trim($_POST['f1_user'] ?? '');
        $pass = $_POST['f1_pass']      ?? '';

        $userOk = hash_equals(ADMIN_USER, $user);
        $passOk = password_verify($pass, ADMIN_PASSWORD_HASH);

        if ($userOk && $passOk) {
            session_regenerate_id(true);
            $_SESSION['f1_admin']      = true;
            $_SESSION['f1_admin_last'] = time();
            loginClearFailures($ip);
            header('Location: ?page=collection');
            exit;
        }

        loginRecordFailure($ip);
        // Frena los intentos automáticos sin molestar a una persona
        usleep(400000);
        $_SESSION['f1_login_error'] = true;
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
