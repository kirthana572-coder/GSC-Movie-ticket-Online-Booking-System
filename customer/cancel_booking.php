<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$booking_id = $_GET['id'] ?? 0;
if (!$booking_id) die("Invalid booking.");

$stmt = $conn->prepare("SELECT id, payment_status FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking || $booking['payment_status'] !== 'Pending') {
    die("Booking cannot be cancelled.");
}

$conn->begin_transaction();
try {
    // 更新预订状态为 Cancelled
    $conn->query("UPDATE bookings SET payment_status = 'Cancelled' WHERE id = $booking_id");
    
    // 释放座位
        $conn->query("
            UPDATE seats s
            JOIN booking_seats bs ON s.id = bs.seat_id
            SET s.status = 'available'
            WHERE bs.booking_id = $booking_id
        ");
    
    $conn->query("DELETE FROM booking_seats WHERE booking_id = $booking_id");
    
    // 记录取消历史
    $user_id = $_SESSION['user_id'];

    $conn->query("
        INSERT INTO booking_cancellations
        (user_id, booking_id)
        VALUES
        ($user_id, $booking_id)
    ");
    
    // 添加通知
    $user_id = $_SESSION['user_id'];
    $msg = "Your booking (ID: $booking_id) has been cancelled.";
    $conn->query("INSERT INTO notifications (user_id, message) VALUES ($user_id, '$msg')");
    
    $conn->commit();
    header("Location: " . BASE_URL . "/customer/history.php?msg=cancelled");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Cancellation failed: " . $e->getMessage());
}
?>