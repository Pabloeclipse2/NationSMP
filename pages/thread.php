<?php
/**
 * thread.php — view a single thread + replies.
 * Enforces: private threads are only visible to the author or staff.
 */

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT t.*, u.username, u.role AS author_role, c.name AS category_name, c.slug AS category_slug, c.is_private AS category_private
    FROM threads t
    JOIN users u ON u.id = t.user_id
    JOIN categories c ON c.id = t.category_id
    WHERE t.id = ?
    LIMIT 1
');
$stmt->execute([$id]);
$thread = $stmt->fetch();

if (!$thread) {
    echo '<div class="empty-state glass"><div class="icon">🧭</div><h3>Thread not found</h3></div>';
    return;
}

$user = current_user();

// --- Access control: private thread => author or staff only ---
if ($thread['is_private']) {
    require_login();
    $isOwner = $user['id'] === (int) $thread['user_id'];
    if (!$isOwner && !is_staff()) {
        echo '<div class="empty-state glass"><div class="icon">🔒</div><h3>This thread is private</h3><p>Only the author and staff can view it.</p></div>';
        return;
    }
}

// --- Handle staff status change ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (is_staff() && csrf_check()) {
        $newStatus = $_POST['status'] ?? '';
        if (in_array($newStatus, ['open', 'pending', 'accepted', 'denied', 'closed'], true)) {
            $upd = $pdo->prepare('UPDATE threads SET status = ? WHERE id = ?');
            $upd->execute([$newStatus, $id]);
            $thread['status'] = $newStatus;
        }
    }
}

// --- Handle new reply ---
$replyError = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply') {
    require_login();
    if (!csrf_check()) {
        $replyError = 'Your session expired. Please try again.';
    } else {
        $body = trim($_POST['body'] ?? '');
        if ($body === '') {
            $replyError = 'Reply cannot be empty.';
        } else {
            $ins = $pdo->prepare('INSERT INTO replies (thread_id, user_id, body) VALUES (?, ?, ?)');
            $ins->execute([$id, $user['id'], $body]);
            redirect('thread', ['id' => $id]);
        }
    }
}

// Bump view count (only for non-private, to keep it simple/light)
if (!$thread['is_private']) {
    $pdo->prepare('UPDATE threads SET views = views + 1 WHERE id = ?')->execute([$id]);
}

// Fetch replies
$stmt = $pdo->prepare('
    SELECT r.*, u.username, u.role
    FROM replies r JOIN users u ON u.id = r.user_id
    WHERE r.thread_id = ?
    ORDER BY r.created_at ASC
');
$stmt->execute([$id]);
$replies = $stmt->fetchAll();
?>

<div class="breadcrumb">
    <a href="/index.php?page=forum">Forum</a> <span>/</span>
    <a href="/index.php?page=category&slug=<?= urlencode($thread['category_slug']) ?>"><?= e($thread['category_name']) ?></a>
    <span>/</span> <span><?= e($thread['title']) ?></span>
</div>

<div class="glass" style="overflow:hidden; margin-bottom:20px;">
    <div class="thread-body" style="padding-bottom:0;">
        <div class="flex-between">
            <div>
                <h1 style="font-family:var(--font-display); font-size:1.5rem; margin:0 0 6px;">
                    <?php if ($thread['is_private']): ?>🔒 <?php endif; ?><?= e($thread['title']) ?>
                </h1>
                <div class="thread-meta">
                    by <strong><?= e($thread['username']) ?></strong>
                    <span class="badge-role <?= e($thread['author_role']) ?>" style="margin-left:6px;"><?= e($thread['author_role']) ?></span>
                    · <?= date('M j, Y \a\t g:i A', strtotime($thread['created_at'])) ?>
                    · <?= (int) $thread['views'] ?> views
                </div>
            </div>
            <span class="status-tag <?= e($thread['status']) ?>"><?= e($thread['status']) ?></span>
        </div>
        <hr style="border:none; border-top:1px solid var(--border-soft); margin:20px 0;">
        <p><?= nl2br(e($thread['body'])) ?></p>
    </div>

    <?php if (is_staff() && $thread['type'] !== 'discussion'): ?>
        <div style="padding:20px 30px; border-top:1px solid var(--border-soft); background:rgba(15,23,42,0.3);">
            <form method="POST" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <label style="font-size:0.85rem; color:var(--text-mid); font-weight:600;">Staff: set status</label>
                <select name="status" style="width:auto; padding:8px 12px;" class="glass">
                    <?php foreach (['open','pending','accepted','denied','closed'] as $s): ?>
                        <option value="<?= $s ?>" <?= $thread['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Update</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($replies)): ?>
<div class="glass" style="overflow:hidden; margin-bottom:20px;">
    <?php foreach ($replies as $r): ?>
        <div class="reply">
            <div class="reply-meta">
                <strong><?= e($r['username']) ?></strong>
                <span class="badge-role <?= e($r['role']) ?>"><?= e($r['role']) ?></span>
                <span>· <?= date('M j, Y \a\t g:i A', strtotime($r['created_at'])) ?></span>
            </div>
            <div class="reply-body"><?= nl2br(e($r['body'])) ?></div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="glass" style="padding:26px;">
    <?php if (is_logged_in()): ?>
        <?php if ($replyError): ?><div class="alert alert-error"><?= e($replyError) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="action" value="reply">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="field">
                <label for="body">Post a reply</label>
                <textarea id="body" name="body" required placeholder="Write something helpful..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Reply</button>
        </form>
    <?php else: ?>
        <p style="color:var(--text-mid);">
            <a href="/index.php?page=login" style="color:var(--emerald); font-weight:600;">Log in</a> to reply to this thread.
        </p>
    <?php endif; ?>
</div>
