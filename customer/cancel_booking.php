<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$booking_id = $_GET['id'] ?? 0;
if (!$booking_id) die("Invalid booking.");

$stmt = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ? AND payment_status = 'Pending'");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) die("Cannot cancel.");
$stmt->close();

$conn->begin_transaction();
try {
    $conn->query("UPDATE bookings SET payment_status = 'Cancelled' WHERE id = $booking_id");
    $conn->query("UPDATE seats s JOIN booking_seats bs ON s.id = bs.seat_id SET s.status = 'available' WHERE bs.booking_id = $booking_id");
    $conn->query("DELETE FROM booking_seats WHERE booking_id = $booking_id");
    $conn->commit();
    header("Location: history.php?msg=cancelled");
} catch (Exception $e) {
    $conn->rollback();
    die("Error: " . $e->getMessage());
}