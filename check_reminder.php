<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => '未登录']);
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ 引入統一的資料庫設定，不再寫死 localhost
// (如果你的 check_reminder.php 和 config 資料夾在同一層，就用下面的路徑)
require_once __DIR__ . '/config/db.php'; 

// 查询未来30分钟内开场且未取票/未提醒的订单
$sql = "
    SELECT b.id, m.title, CONCAT(s.show_date, ' ', s.show_time) AS start_time
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    WHERE b.user_id = ?
      AND b.ticket_status NOT IN ('checked_in', 'cancelled')
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
    $reminders[] = $row;
    $update = $conn->prepare("UPDATE bookings SET is_notified = 1 WHERE id = ?");
    $update->bind_param('i', $row['id']);
    $update->execute();
    $update->close();
}

$stmt->close();
$conn->close();

echo json_encode(['reminders' => $reminders]);
?>