<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => '未登录']);
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli('localhost', 'root', '', 'gsc_booking_db');
if ($conn->connect_error) {
    echo json_encode(['error' => '数据库连接失败']);
    exit;
}
$conn->set_charset('utf8');

// 获取未读通知（按时间旧到新，避免顺序乱）
$sql = "SELECT id, message, created_at FROM notifications 
        WHERE user_id = ? AND is_read = 0 
        ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
$ids = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
    $ids[] = $row['id'];
}

// 立即标记这些通知为已读（避免重复弹出）
if (!empty($ids)) {
    $ids_str = implode(',', $ids);
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id IN ($ids_str)");
}

$stmt->close();
$conn->close();

echo json_encode(['notifications' => $notifications]);
?>