<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// 标记已读（如果点击了通知）
if (isset($_GET['mark_read'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = " . intval($_GET['mark_read']) . " AND user_id = {$_SESSION['user_id']}");
    header("Location: notifications.php");
    exit();
}

$notifications = $conn->query("
    SELECT * FROM notifications 
    WHERE user_id = {$_SESSION['user_id']} 
    ORDER BY created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notifications - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <h2>Notifications</h2>
    <?php if ($notifications->num_rows > 0): ?>
        <div class="list-group">
            <?php while($n = $notifications->fetch_assoc()):
                $class = $n['is_read'] ? '' : 'list-group-item-warning fw-bold';
            ?>
                <a href="?mark_read=<?= $n['id'] ?>"
                   class="list-group-item list-group-item-action <?= $class ?>">
                    <?= htmlspecialchars($n['message']) ?>
                    <br><small class="text-muted"><?= $n['created_at'] ?></small>
                </a>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No notifications yet.</div>
    <?php endif; ?>
</div>
</body>
</html>