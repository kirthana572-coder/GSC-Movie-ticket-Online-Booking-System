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

// 票种解析：seat_id:type,seat_id:type
$ticket_types_str = $_POST['ticket_types'] ?? '';
$ticketMap = [];
if (!empty($ticket_types_str)) {
    foreach (explode(',', $ticket_types_str) as $pair) {
        $parts = explode(':', $pair);
        if (count($parts) === 2) {
            $ticketMap[ $parts[0] ] = $parts[1];
        }
    }
}

if (empty($showtime_id) || empty($seat_ids)) {
    $_SESSION['error'] = "Please select at least one seat.";
    header("Location: select_seat.php?showtime_id=" . intval($showtime_id));
    exit();
}

$conn->begin_transaction();
try {
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

    // 插入 booking_seats，包含票种
    $stmt = $conn->prepare("INSERT INTO booking_seats (booking_id, seat_id, ticket_type) VALUES (?, ?, ?)");
    foreach ($seat_ids as $sid) {
        $ticketType = $ticketMap[$sid] ?? 'Adult'; // 默认 Adult
        $stmt->bind_param("iis", $booking_id, $sid, $ticketType);
        $stmt->execute();
    }
    $stmt->close();

    // 添加通知
    $msg = "Your booking (ID: $booking_id) has been created. Please pay at the counter.";
    $conn->query("INSERT INTO notifications (user_id, message) VALUES ($user_id, '$msg')");

    $conn->commit();
    header("Location: booking_summary.php?booking_id=" . $booking_id);
    exit();
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Booking failed: " . $e->getMessage();
    header("Location: select_seat.php?showtime_id=" . intval($showtime_id));
    exit();
}