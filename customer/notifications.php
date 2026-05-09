<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// 标记已读（如果点击了通知）
if (isset($_GET['mark_read'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = " . intval($_GET['mark_read']) . " AND user_id = {$_SESSION['user_id']}");
    header("Location: notifications.php");
    exit();
}

// 全部标记已读
if (isset($_GET['mark_all'])) {

    $conn->query("
        UPDATE notifications 
        SET is_read = 1 
        WHERE user_id = {$_SESSION['user_id']}
    ");

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

    <style>

        /* Page Background */
        body{
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background:
            linear-gradient(
                rgba(244,237,217,0.82),
                rgba(249,213,159,0.88)
            );

            min-height: 100vh;
        }

        /* Top Actions */
            .top-actions{
            display: flex;

            justify-content: flex-end;

            margin-bottom: 10px;
        }

        /* Mark All Button */
        .mark-all-btn{
            background: #ffc800;

            color: #fff;

            text-decoration: none;

            padding: 6px 15px;

            border-radius: 30px;

            font-weight: 600;

            transition: 0.25s;
        }

        .mark-all-btn:hover{
            background: #f5c518;

            color: #111;

            transform: translateY(-2px);
        }

        /* Main Container */
        .notification-container{
            max-width: 1000px;

            margin: 50px auto;
        }

        /* Notification Card */
        .notification-card{
            background: rgba(255, 255, 255, 0.77);

            border-radius: 24px;

            padding: 35px;

            backdrop-filter: blur(10px);

            box-shadow:
            0 10px 30px rgba(0,0,0,0.12);
        }

        /* Title */
        .page-title{
            font-size: 42px;

            font-weight: 700;

            color: #1c1f26;

            margin-bottom: 25px;
        }

        /* Notification Item */
        .notification-item{
            display: block;

            text-decoration: none;

            background: rgba(255,255,255,0.92);

            border-radius: 18px;

            padding: 10px;

            margin-bottom: 18px;

            transition: 0.25s;

            border: 1px solid rgba(0,0,0,0.06);

            color: #222;
        }

        /* Hover Effect */
        .notification-item:hover{
            transform: translateY(-3px);

            box-shadow:
            0 8px 20px rgba(0,0,0,0.08);

            background: rgba(255,255,255,1);
        }

        /* Unread Notification */
        .notification-unread{
            border-left: 6px solid #f5c518;

            background: rgba(245,197,24,0.10);
        }

        /* Notification Message */
        .notification-message{
            font-size: 15px;

            font-weight: 400;

            margin-bottom: 8px;
        }

        /* Notification Time */
        .notification-time{
            color: #777;

            font-size: 12px;
        }

        /* Empty State */
        .empty-alert{
            background: rgba(255,255,255,0.85);

            border: none;

            border-radius: 18px;

            padding: 20px;

            text-align: center;
        }

    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>
<div class="notification-container">

    <div class="notification-card">

        <h2 class="page-title">
            Notifications
        </h2>

        <div class="top-actions">
            <a href="?mark_all=1" class="mark-all-btn">
                Mark All as Read
            </a>
        </div>

        <?php if ($notifications->num_rows > 0): ?>
           <?php while($n = $notifications->fetch_assoc()):
                $class = $n['is_read'] ? '' : 'list-group-item-warning fw-bold';
            ?>

           <a href="?mark_read=<?= $n['id'] ?>"
                class="notification-item <?= !$n['is_read'] ? 'notification-unread' : '' ?>">
            
                    <div class="notification-message">
                        <?= htmlspecialchars($n['message']) ?>
                    </div>

                    <div class="notification-time">
                        <?= $n['created_at'] ?>
                    </div>
            
            </a>
        <?php endwhile; ?>

        <?php else: ?>
            <div class="empty-alert">No notifications yet.</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>