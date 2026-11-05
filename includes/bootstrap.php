<?php
declare(strict_types=1);

/**
 * Every page starts with:  require_once __DIR__ . '/includes/bootstrap.php';
 * (pages in views/ use '/../includes/bootstrap.php').
 *
 * Sets up autoloading, a hardened session, security headers, error handling,
 * and the request-scoped helpers pages use ($auth, $csrf, db(), guards).
 */

define('APP_ROOT', dirname(__DIR__));

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/helpers.php';

// --- errors: never show internals to visitors, always log them ---------------
ini_set('display_errors', '0');
ini_set('log_errors', '1');
set_exception_handler(static function (Throwable $e): void {
    error_log(sprintf('Uncaught %s: %s in %s:%d', $e::class, $e->getMessage(), $e->getFile(), $e->getLine()));
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'Something went wrong. Please try again later.';
});

// --- session ----------------------------------------------------------------
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// --- security headers -------------------------------------------------------
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
}

// --- request-scoped services -------------------------------------------------
$auth = new Auth($_SESSION);
$csrf = new Csrf($_SESSION);

function db(): PDO
{
    return Database::getInstance();
}

/** Site-relative URL that works whether the app lives at the web root or in a subfolder. */
function url(string $path): string
{
    static $base = null;
    if ($base === null) {
        $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
        $root = realpath(APP_ROOT) ?: APP_ROOT;
        $base = ($docRoot !== '' && str_starts_with($root, $docRoot))
            ? rtrim(str_replace('\\', '/', substr($root, strlen($docRoot))), '/')
            : '';
    }
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

/** Page requires a logged-in user; otherwise send them to log in and back. */
function require_user(): void
{
    global $auth;
    if (!$auth->isUser()) {
        redirect('login.php?redirect=' . urlencode(ltrim($_SERVER['REQUEST_URI'] ?? '', '/')));
    }
}

/** Page requires an admin; call BEFORE producing any output. */
function require_admin(): void
{
    global $auth;
    if (!$auth->isAdmin()) {
        redirect('admin_login.php');
    }
}

/** Abort the request unless the POST carries a valid CSRF token. */
function require_csrf(): void
{
    global $csrf;
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!$csrf->validate(is_string($token) ? $token : null)) {
        http_response_code(403);
        exit('Invalid or expired form token. Go back, refresh the page and try again.');
    }
}

/** One-shot message shown on the next admin page render (see includes/adminSidebar.php). */
function flash(string $message, string $type = 'error'): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_pull(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/** Send a JSON response and stop. */
function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/** A logout control. Logout is POST + CSRF, so it must be a form, not a link. */
function logout_button(string $label, string $classes): string
{
    global $csrf;
    return '<form method="POST" action="' . e(url('logout.php')) . '" class="inline">'
        . $csrf->field()
        . '<button type="submit" class="' . e($classes) . '">' . e($label) . '</button></form>';
}

/** Fully end the session, including the cookie. */
function destroy_session(): void
{
    global $auth;
    $auth->logout();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
