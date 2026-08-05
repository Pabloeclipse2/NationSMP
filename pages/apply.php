<?php
/**
 * apply.php — staff application form.
 * Submissions are saved as PRIVATE threads (is_private = 1, type = 'application')
 * in the "staff-apps" category, visible only to the author + staff.
 */

require_login();

// Ensure the category exists
$catStmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
$catStmt->execute(['staff-apps']);
$category = $catStmt->fetch();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors[] = 'Your session expired. Please try again.';
    }

    $ign        = trim($_POST['ign'] ?? '');
    $age        = trim($_POST['age'] ?? '');
    $position   = trim($_POST['position'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $why        = trim($_POST['why'] ?? '');

    if ($ign === '' || $age === '' || $position === '' || $experience === '' || $why === '') {
        $errors[] = 'Please fill in every field.';
    }
    if (!empty($age) && (!ctype_digit($age) || (int) $age < 13 || (int) $age > 99)) {
        $errors[] = 'Please enter a valid age.';
    }

    if (empty($errors) && $category) {
        $user = current_user();

        $title = "Staff Application — {$ign} ({$position})";
        $body  = "In-game name: {$ign}\n"
               . "Age: {$age}\n"
               . "Position applying for: {$position}\n\n"
               . "Relevant experience:\n{$experience}\n\n"
               . "Why should we pick you?\n{$why}";

        $stmt = $pdo->prepare('
            INSERT INTO threads (category_id, user_id, title, body, type, status, is_private)
            VALUES (?, ?, ?, ?, "application", "pending", 1)
        ');
        $stmt->execute([$category['id'], $user['id'], $title, $body]);
        $newId = (int) $pdo->lastInsertId();

        redirect('thread', ['id' => $newId]);
    }
}
?>

<div class="form-wrap wide glass">
    <div class="form-head">
        <h1>🛡️ Staff Application</h1>
        <p>This submission is private — only you and the staff team can see it.</p>
    </div>

    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <?php if (!$category): ?>
        <div class="alert alert-error">The "Staff Applications" category is missing. Please run the seed data in schema.sql.</div>
    <?php else: ?>
    <form method="POST" action="/index.php?page=apply">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="field">
            <label for="ign">In-game name (IGN)</label>
            <input type="text" id="ign" name="ign" required value="<?= e($_POST['ign'] ?? '') ?>">
        </div>

        <div class="field">
            <label for="age">Age</label>
            <input type="number" id="age" name="age" min="13" max="99" required value="<?= e($_POST['age'] ?? '') ?>">
        </div>

        <div class="field">
            <label for="position">Position applying for</label>
            <select id="position" name="position" required>
                <option value="">Select a position…</option>
                <option value="Moderator">Moderator</option>
                <option value="Builder">Builder</option>
                <option value="Helper">Helper</option>
                <option value="Event Coordinator">Event Coordinator</option>
            </select>
        </div>

        <div class="field">
            <label for="experience">Relevant experience</label>
            <textarea id="experience" name="experience" required placeholder="Previous staff roles, moderation tools you've used, etc."><?= e($_POST['experience'] ?? '') ?></textarea>
        </div>

        <div class="field">
            <label for="why">Why should we pick you?</label>
            <textarea id="why" name="why" required placeholder="Tell us what makes you a great fit."><?= e($_POST['why'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Submit Application</button>
    </form>
    <?php endif; ?>
</div>
