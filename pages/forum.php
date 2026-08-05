<?php
/**
 * forum.php — forum hub: lists all categories with thread counts
 */

$sql = "
    SELECT c.*,
           COUNT(t.id) AS thread_count,
           MAX(t.created_at) AS last_activity
    FROM categories c
    LEFT JOIN threads t
           ON t.category_id = c.id
          AND (t.is_private = 0 OR ? = 1)   -- staff can see counts including private threads
    GROUP BY c.id
    ORDER BY c.sort_order ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([is_staff() ? 1 : 0]);
$categories = $stmt->fetchAll();
?>

<div class="section-head">
    <div>
        <span class="eyebrow">Community Forum</span>
        <h2>Where the <?= e(SERVER_NAME) ?> community talks</h2>
        <p>News, discussion, suggestions, and private staff/appeal channels.</p>
    </div>
</div>

<div class="category-list">
<?php foreach ($categories as $cat): ?>
    <a class="category-row glass" href="/index.php?page=category&slug=<?= urlencode($cat['slug']) ?>">
        <div class="category-icon"><?= e($cat['icon']) ?></div>
        <div class="category-info">
            <h3>
                <?= e($cat['name']) ?>
                <?php if ($cat['is_private']): ?><span class="lock-tag">🔒 Private</span><?php endif; ?>
            </h3>
            <p><?= e($cat['description']) ?></p>
        </div>
        <div class="category-meta">
            <strong><?= (int) $cat['thread_count'] ?></strong>
            threads
        </div>
    </a>
<?php endforeach; ?>

<?php if (empty($categories)): ?>
    <div class="empty-state glass">
        <div class="icon">📭</div>
        <h3>No categories yet</h3>
        <p>Run the schema.sql seed data to populate the forum.</p>
    </div>
<?php endif; ?>
</div>
