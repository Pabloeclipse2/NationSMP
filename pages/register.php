<?php
/**
 * register.php — new account creation
 */

if (is_logged_in()) {
    redirect('home');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (!preg_match('/^[A-Za-z0-9_]{3,32}$/', $username)) {
        $errors[] = 'Username must be 3-32 characters (letters, numbers, underscore only).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        // Check for existing username / email — uses prepared statement
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'That username or email is already registered.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$username, $email, $hash, 'member']);

        $userId = (int) $pdo->lastInsertId();

        // Auto-login after registration
        $_SESSION['user'] = [
            'id'       => $userId,
            'username' => $username,
            'role'     => 'member',
        ];

        redirect('home');
    }
}
?>

<div class="form-wrap glass">
    <div class="form-head">
        <h1>Create your account</h1>
        <p>Join the <?= e(SERVER_NAME) ?> community.</p>
    </div>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="/index.php?page=register" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required
                   value="<?= e($_POST['username'] ?? '') ?>" placeholder="Steve123">
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required
                   value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8" placeholder="••••••••">
            <p class="field-hint">At least 8 characters.</p>
        </div>

        <div class="field">
            <label for="confirm_password">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary btn-block">Create account</button>
    </form>

    <p class="form-footer-link">Already have an account? <a href="/index.php?page=login">Log in</a></p>
</div>
