<?php
/**
 * index.php — Central front-controller / router.
 *
 * Every request flows through here as ?page=slug (default "home").
 * This keeps header/footer consistent and gives us one place to
 * enforce access rules before a page's logic ever runs.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Whitelist of routable pages -> file in /pages.
// Using a whitelist (not the raw GET value) prevents local file inclusion attacks.
$routes = [
    'home'      => 'home.php',
    'login'     => 'login.php',
    'register'  => 'register.php',
    'logout'    => 'logout.php',
    'forum'     => 'forum.php',
    'category'  => 'category.php',
    'thread'    => 'thread.php',
    'apply'     => 'apply.php',
    'appeal'    => 'appeal.php',
];

$page = isset($_GET['page']) && array_key_exists($_GET['page'], $routes) ? $_GET['page'] : 'home';
$pageFile = __DIR__ . '/pages/' . $routes[$page];

// logout has no visual output of its own — handle then redirect, no header/footer needed
if ($page === 'logout') {
    require $pageFile;
    exit;
}

require_once __DIR__ . '/includes/header.php';
echo '<main class="page"><div class="wrapper">';

if (file_exists($pageFile)) {
    require $pageFile;
} else {
    echo '<div class="empty-state glass"><div class="icon">🧭</div><h2>Page not found</h2><p>That trail leads nowhere. Head back to camp.</p></div>';
}

echo '</div></main>';
require_once __DIR__ . '/includes/footer.php';
