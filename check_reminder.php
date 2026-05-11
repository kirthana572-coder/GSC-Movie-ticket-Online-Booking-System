<?php
session_start();
header('Content-Type: application/json');

// 检查登录
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => '未登录']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 连接数据库
$conn = new mysqli('localhost', 'root', '', 'gsc_booking_db');
if ($conn->connect_error) {
    echo json_encode(['error' => '数据库连接失败']);
    exit;
}
$conn->set_charset('utf8');

// 查询1小时内即将开场且未取票、未提醒的订单
$sql = "
    SELECT b.id, m.title, CONCAT(s.show_date, ' ', s.show_time) AS start_time
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    WHERE b.user_id = ?
      AND b.ticket_status NOT IN ('checked_in', 'cancelled')
      AND b.is_notified = 0
      AND CONCAT(s.show_date, ' ', s.show_time) > NOW() 
      AND CONCAT(s.show_date, ' ', s.show_time) <= DATE_ADD(NOW(), INTERVAL 30 MINUTE)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$reminders = [];
while ($row = $result->fetch_assoc()) {
    $reminders[] = $row;
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