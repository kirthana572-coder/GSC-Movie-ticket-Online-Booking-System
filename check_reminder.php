<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

require_once __DIR__ . '/config/db.php';

// 查询未来30分钟内开场且尚未提醒的订单（无论是否付款）
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
    // 根据付款状态生成英文消息
    if ($row['payment_status'] == 'Paid') {
        $msg = "🎬 Movie '{$row['title']}' starts at {$row['start_time']}. Please come to the cinema on time!";
    } else {
        $msg = "🎬 Movie '{$row['title']}' starts at {$row['start_time']}. Please complete payment and come to the cinema!";
    }
    $reminders[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start_time' => $row['start_time'],
        'message' => $msg
    ];
    // 标记为已提醒，防止重复
    $update = $conn->prepare("UPDATE bookings SET is_notified = 1 WHERE id = ?");
    $update->bind_param('i', $row['id']);
    $update->execute();
    $update->close();
}

$stmt->close();
$conn->close();

echo json_encode(['reminders' => $reminders]);
?>