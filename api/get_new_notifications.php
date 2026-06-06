<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => '未登录']);
    exit;
}
$user_id = $_SESSION['user_id'];
require_once '../config/db.php';

$sql = "SELECT id, message, created_at FROM notifications WHERE user_id = ? AND is_popup_shown = 0 ORDER BY created_at ASC";
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

if (!empty($ids)) {
    $ids_str = implode(',', $ids);
    $conn->query("UPDATE notifications SET is_popup_shown = 1 WHERE id IN ($ids_str)");
}
$stmt->close();
$conn->close();
echo json_encode(['notifications' => $notifications]);
?>