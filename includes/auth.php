<?php
/**
 * auth.php — session bootstrap + auth/permission helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
    return isset($_SESSION['user']);
}

function is_staff(): bool {
    $u = current_user();
    return $u && in_array($u['role'], ['moderator', 'admin'], true);
}

function is_admin(): bool {
    $u = current_user();
    return $u && $u['role'] === 'admin';
}

/** Redirect helper */
function redirect(string $page, array $params = []): void {
    $query = array_merge(['page' => $page], $params);
    header('Location: /index.php?' . http_build_query($query));
    exit;
}

/** Require login, else bounce to login page */
function require_login(): void {
    if (!is_logged_in()) {
        redirect('login');
    }
}

/** Simple CSRF token helpers */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(): bool {
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/** Escape output helper */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
