<?php
/**
 * category.php — lists threads within one category.
 * Enforces: private threads (applications/appeals) are only listed
 * for their author or staff — everyone else sees nothing of them.
 */

$slug = $_GET['slug'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    echo '<div class="empty-state glass"><div class="icon">🧭</div><h3>Category not found</h3></div>';
    return;
}

$user = current_user();

// Build the thread query with correct visibility rules via bound params (no string concatenation of user input)
if ($category['is_private']) {
    require_login(); // must be logged in to even view a private category's index

    if (is_staff()) {
        $stmt = $pdo->prepare('
            SELECT t.*, u.username
            FROM threads t JOIN users u ON u.id = t.user_id
            WHERE t.category_id = ?
            ORDER BY t.is_pinned DESC, t.created_at DESC
        ');
        $stmt->execute([$category['id']]);
    } else {
        // Regular members only see their own threads in a private category
        $stmt = $pdo->prepare('
            SELECT t.*, u.username
            FROM threads t JOIN users u ON u.id = t.user_id
            WHERE t.category_id = ? AND t.user_id = ?
            ORDER BY t.created_at DESC
        ');
        $stmt->execute([$category['id'], $user['id']]);
    }
} else {
    $stmt = $pdo->prepare('
        SELECT t.*, u.username
        FROM threads t JOIN users u ON u.id = t.user_id
        WHERE t.category_id = ? AND t.is_private = 0
        ORDER BY t.is_pinned DESC, t.created_at DESC
    ');
    $stmt->execute([$category['id']]);
}

$threads = $stmt->fetchAll();
?>

<div class="breadcrumb">
    <a href="/index.php?page=forum">Forum</a> <span>/</span> <span><?= e($category['name']) ?></span>
</div>

<div class="section-head">
    <div>
        <span class="eyebrow"><?= e($category['icon']) ?> Category</span>
        <h2><?= e($category['name']) ?></h2>
        <p><?= e($category['description']) ?></p>
    </div>
    <?php if ($category['slug'] === 'staff-apps' && is_logged_in()): ?>
        <a href="/index.php?page=apply" class="btn btn-primary">Submit Application</a>
    <?php elseif ($category['slug'] === 'appeals' && is_logged_in()): ?>
        <a href="/index.php?page=appeal" class="btn btn-primary">Submit Appeal</a>
    <?php endif; ?>
</div>

<div class="glass" style="overflow:hidden;">
<?php if (empty($threads)): ?>
    <div class="empty-state">
        <div class="icon">🗒️</div>
        <h3>No threads yet</h3>
        <p>Be the first to post here.</p>
    </div>
<?php else: ?>
    <?php foreach ($threads as $t): ?>
        <a class="thread-row" href="/index.php?page=thread&id=<?= (int) $t['id'] ?>">
            <div>
                <div class="thread-title">
                    <?php if ($t['is_pinned']): ?><span class="pin-tag">📌</span><?php endif; ?>
                    <?= e($t['title']) ?>
                </div>
                <div class="thread-meta">
                    by <?= e($t['username']) ?> · <?= date('M j, Y', strtotime($t['created_at'])) ?>
                    <?= $t['is_private'] ? ' · 🔒 Private' : '' ?>
                </div>
            </div>
            <span class="status-tag <?= e($t['status']) ?>"><?= e($t['status']) ?></span>
        </a>
    <?php endforeach; ?>
<?php endif; ?>
</div>
