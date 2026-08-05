<?php
/**
 * header.php — shared site header, expects $page (current page slug) to be set
 */
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e(SERVER_NAME) ?> — Community</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header class="site-header">
    <nav class="nav">
        <a href="/index.php?page=home" class="brand">
            <span class="brand-mark">⛏️</span>
            <?= e(SERVER_NAME) ?>
        </a>

        <div class="nav-links">
            <a href="/index.php?page=home" class="<?= $page === 'home' ? 'active' : '' ?>">Home</a>
            <a href="/index.php?page=forum" class="<?= $page === 'forum' || $page === 'category' || $page === 'thread' ? 'active' : '' ?>">Forum</a>
            <a href="/index.php?page=apply" class="<?= $page === 'apply' ? 'active' : '' ?>">Staff App</a>
            <a href="/index.php?page=appeal" class="<?= $page === 'appeal' ? 'active' : '' ?>">Appeal</a>
        </div>

        <div class="nav-auth">
            <?php if ($user): ?>
                <span class="badge-role <?= e($user['role']) ?>"><?= e($user['role']) ?></span>
                <span style="font-size:0.9rem; font-weight:600;"><?= e($user['username']) ?></span>
                <a href="/index.php?page=logout" class="btn btn-secondary btn-sm">Log out</a>
            <?php else: ?>
                <a href="/index.php?page=login" class="btn btn-secondary btn-sm">Log in</a>
                <a href="/index.php?page=register" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
