<?php
/**
 * login.php — session-based authentication
 */

if (is_logged_in()) {
    redirect('home');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identity === '' || $password === '') {
        $errors[] = 'Please fill in both fields.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role, status FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Incorrect username/email or password.';
        } elseif ($user['status'] === 'banned') {
            $errors[] = 'This account has been suspended. You may submit an unban appeal.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'       => (int) $user['id'],
                'username' => $user['username'],
                'role'     => $user['role'],
            ];

            $upd = $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
            $upd->execute([$user['id']]);

            redirect('home');
        }
    }
}
?>

<div class="form-wrap glass">
    <div class="form-head">
        <h1>Welcome back</h1>
        <p>Log in to <?= e(SERVER_NAME) ?>.</p>
    </div>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="/index.php?page=login" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="field">
            <label for="identity">Username or email</label>
            <input type="text" id="identity" name="identity" required
                   value="<?= e($_POST['identity'] ?? '') ?>" placeholder="Steve123">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary btn-block">Log in</button>
    </form>

    <p class="form-footer-link">Don't have an account? <a href="/index.php?page=register">Register</a></p>
</div>
