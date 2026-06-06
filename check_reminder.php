<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$user_info = $conn->query("SELECT email, full_name FROM users WHERE id = $user_id")->fetch_assoc();
$user_email = $user_info['email'];
$user_name = $user_info['full_name'];

$sql = "
    SELECT b.id, m.title, CONCAT(s.show_date, ' ', s.show_time) AS start_time, b.payment_status
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    WHERE b.user_id = ?
      AND b.is_notified = 0
      AND TIMESTAMP(s.show_date, s.show_time) > NOW()
      AND TIMESTAMP(s.show_date, s.show_time) <= DATE_ADD(NOW(), INTERVAL 30 MINUTE)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$reminders = [];
while ($row = $result->fetch_assoc()) {
    if ($row['payment_status'] == 'Paid') {
        $msg = "🎬 Movie '{$row['title']}' starts at {$row['start_time']}. Please come to the cinema on time!";
    } else {
        $msg = "🎬 Movie '{$row['title']}' starts at {$row['start_time']}. Please complete payment and come to the cinema!";
    }

    // 站内消息
    sendStationNotification($user_id, $msg);

    // 发送邮件
    $subject = "Movie Reminder: " . $row['title'];
    if ($row['payment_status'] == 'Paid') {
        $body = "<h2>Movie Starting Soon</h2>
                 <p>Dear {$user_name},</p>
                 <p>The movie <strong>{$row['title']}</strong> will start at <strong>{$row['start_time']}</strong>.</p>
                 <p>Please arrive on time.</p>
                 <p>Thank you!</p>";
    } else {
        $body = "<h2>Payment Required</h2>
                 <p>Dear {$user_name},</p>
                 <p>The movie <strong>{$row['title']}</strong> will start at <strong>{$row['start_time']}</strong>.</p>
                 <p><strong>Please complete payment at the counter before the show.</strong></p>
                 <p>Thank you!</p>";
    }
    sendMail($user_email, $subject, $body);

    $reminders[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start_time' => $row['start_time'],
        'message' => $msg
    ];

    $update = $conn->prepare("UPDATE bookings SET is_notified = 1 WHERE id = ?");
    $update->bind_param('i', $row['id']);
    $update->execute();
    $update->close();
}

$stmt->close();
$conn->close();
echo json_encode(['reminders' => $reminders]);
?>