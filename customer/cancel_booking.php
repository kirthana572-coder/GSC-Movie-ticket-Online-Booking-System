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
    $conn->query("UPDATE bookings SET payment_status = 'Cancelled' WHERE id = $booking_id");
    $conn->query("UPDATE seats s JOIN booking_seats bs ON s.id = bs.seat_id SET s.status = 'available' WHERE bs.booking_id = $booking_id");
    $conn->query("DELETE FROM booking_seats WHERE booking_id = $booking_id");

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO booking_cancellations (user_id, booking_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $booking_id);
    $stmt->execute();
    $stmt->close();

    $msg = "Your booking (ID: $booking_id) has been cancelled.";
    $conn->query("INSERT INTO notifications (user_id, message, is_read, is_popup_shown, created_at) VALUES ($user_id, '$msg', 0, 0, NOW())");

    $conn->commit();
    header("Location: " . BASE_URL . "/customer/history.php?msg=cancelled");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Cancellation failed: " . $e->getMessage());
}
?>