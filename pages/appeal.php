<?php
/**
 * appeal.php — unban appeal form.
 * Submissions are saved as PRIVATE threads (is_private = 1, type = 'appeal')
 * in the "appeals" category, visible only to the author + staff.
 */

require_login();

$catStmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
$catStmt->execute(['appeals']);
$category = $catStmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $ign        = trim($_POST['ign'] ?? '');
    $banReason  = trim($_POST['ban_reason'] ?? '');
    $explanation = trim($_POST['explanation'] ?? '');
    $whyUnban   = trim($_POST['why_unban'] ?? '');

    if ($ign === '' || $banReason === '' || $explanation === '' || $whyUnban === '') {
        $errors[] = 'Please fill in every field.';
    }

    if (empty($errors) && $category) {
        $user = current_user();

        $title = "Unban Appeal — {$ign}";
        $body  = "In-game name: {$ign}\n"
               . "Reason given for ban:\n{$banReason}\n\n"
               . "What happened (your side of the story):\n{$explanation}\n\n"
               . "Why should we unban you?\n{$whyUnban}";

        $stmt = $pdo->prepare('
            INSERT INTO threads (category_id, user_id, title, body, type, status, is_private)
            VALUES (?, ?, ?, ?, "appeal", "pending", 1)
        ');
        $stmt->execute([$category['id'], $user['id'], $title, $body]);
        $newId = (int) $pdo->lastInsertId();

        redirect('thread', ['id' => $newId]);
    }
}
?>

<div class="form-wrap wide glass">
    <div class="form-head">
        <h1>⚖️ Unban Appeal</h1>
        <p>This submission is private — only you and the staff team can see it.</p>
    </div>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <?php if (!$category): ?>
        <div class="alert alert-error">The "Unban Appeals" category is missing. Please run the seed data in schema.sql.</div>
    <?php else: ?>
    <form method="POST" action="/index.php?page=appeal">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="field">
            <label for="ign">In-game name (IGN)</label>
            <input type="text" id="ign" name="ign" required value="<?= e($_POST['ign'] ?? '') ?>">
        </div>

        <div class="field">
            <label for="ban_reason">Reason given for your ban</label>
            <input type="text" id="ban_reason" name="ban_reason" required value="<?= e($_POST['ban_reason'] ?? '') ?>" placeholder="e.g. Griefing, Hacking, Chat abuse">
        </div>

        <div class="field">
            <label for="explanation">What happened — your side of the story</label>
            <textarea id="explanation" name="explanation" required placeholder="Explain the situation honestly."><?= e($_POST['explanation'] ?? '') ?></textarea>
        </div>

        <div class="field">
            <label for="why_unban">Why should we unban you?</label>
            <textarea id="why_unban" name="why_unban" required placeholder="What will you do differently?"><?= e($_POST['why_unban'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Submit Appeal</button>
    </form>
    <?php endif; ?>
</div>
