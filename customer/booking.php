<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: movies.php");
    exit();
}

$showtime_id = $_POST['showtime_id'] ?? 0;
$seat_ids_str = $_POST['seat_ids'] ?? '';
$seat_ids = array_filter(explode(',', $seat_ids_str));

if (empty($showtime_id) || empty($seat_ids)) {
    $_SESSION['error'] = "Please select at least one seat.";
    header("Location: select_seat.php?showtime_id=" . intval($showtime_id));
    exit();
}

$conn->begin_transaction();
try {
    // 检查并锁定座位
    foreach ($seat_ids as $sid) {
        $check = $conn->query("SELECT status FROM seats WHERE id = " . intval($sid) . " FOR UPDATE");
        $seat = $check->fetch_assoc();
        if (!$seat || $seat['status'] !== 'available') {
            throw new Exception("Seat $sid is no longer available.");
        }
        $conn->query("UPDATE seats SET status = 'pending' WHERE id = " . intval($sid));
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, showtime_id, payment_status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("ii", $user_id, $showtime_id);
    $stmt->execute();
    $booking_id = $stmt->insert_id;
    $stmt->close();

    // 关联座位
    $stmt = $conn->prepare("INSERT INTO booking_seats (booking_id, seat_id) VALUES (?, ?)");
    foreach ($seat_ids as $sid) {
        $stmt->bind_param("ii", $booking_id, $sid);
        $stmt->execute();
    }
    $stmt->close();

    $conn->commit();
    header("Location: booking_summary.php?booking_id=" . $booking_id);
    exit();
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Booking failed: " . $e->getMessage();
    header("Location: select_seat.php?showtime_id=" . intval($showtime_id));
    exit();
}