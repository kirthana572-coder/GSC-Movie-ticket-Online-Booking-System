<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: movies.php");
    exit();
}

$showtime_id = $_POST['showtime_id'] ?? 0;
$seat_ids_str = $_POST['seat_ids'] ?? '';
$seat_ids = array_filter(explode(',', $seat_ids_str));
$ticket_types_str = $_POST['ticket_types'] ?? '';
$ticketMap = [];
if (!empty($ticket_types_str)) {
    foreach (explode(',', $ticket_types_str) as $pair) {
        $parts = explode(':', $pair);
        if (count($parts) === 2) $ticketMap[$parts[0]] = $parts[1];
    }
}
if (empty($showtime_id) || empty($seat_ids)) {
    $_SESSION['error'] = "Please select at least one seat.";
    header("Location: " . BASE_URL . "/customer/select_seat.php?showtime_id=" . intval($showtime_id));
    exit();
}

// 获取电影信息和用户信息
$showtimeInfo = $conn->query("SELECT m.title, s.show_date, s.show_time FROM showtimes s JOIN movies m ON s.movie_id = m.id WHERE s.id = " . intval($showtime_id))->fetch_assoc();
$movie_title = $showtimeInfo['title'];
$show_date = $showtimeInfo['show_date'];
$show_time = $showtimeInfo['show_time'];

$user_id = $_SESSION['user_id'];
$user_info = $conn->query("SELECT email, full_name FROM users WHERE id = $user_id")->fetch_assoc();
$user_email = $user_info['email'];
$user_name = $user_info['full_name'];

$conn->begin_transaction();
try {
    // 锁定并更新座位
    foreach ($seat_ids as $sid) {
        $check = $conn->query("SELECT status FROM seats WHERE id = " . intval($sid) . " FOR UPDATE");
        $seat = $check->fetch_assoc();
        if (!$seat || $seat['status'] !== 'available') {
            throw new Exception("Seat $sid is no longer available.");
        }
        $conn->query("UPDATE seats SET status = 'pending' WHERE id = " . intval($sid));
    }

    // 创建预订
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, showtime_id, payment_status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("ii", $user_id, $showtime_id);
    $stmt->execute();
    $booking_id = $stmt->insert_id;
    $stmt->close();

    // 插入座位和票种
    $stmt = $conn->prepare("INSERT INTO booking_seats (booking_id, seat_id, ticket_type) VALUES (?, ?, ?)");
    foreach ($seat_ids as $sid) {
        $ticketType = $ticketMap[$sid] ?? 'Adult';
        $stmt->bind_param("iis", $booking_id, $sid, $ticketType);
        $stmt->execute();
    }
    $stmt->close();

    // 站内消息
    $msg = "Your booking (ID: $booking_id) has been created. Please pay at the counter.";
    sendStationNotification($user_id, $msg);

    // 发送购票成功邮件（立即提醒）
    $subject = "Booking Confirmation - Please Pay at Counter";
    $order_link = BASE_URL . "/customer/booking_details.php?booking_id=" . $booking_id;
    $body = "
    <html>
    <body>
        <h2>Booking Confirmed</h2>
        <p>Dear {$user_name},</p>
        <p>You have successfully booked tickets for <strong>{$movie_title}</strong>.</p>
        <p>Showtime: {$show_date} at {$show_time}</p>
        <p><strong>Please proceed to the cinema counter to complete your payment.</strong></p>
        <p>If you do not pay before the show, your booking will be automatically cancelled.</p>
        <p>View your booking: <a href='{$order_link}'>Booking Details</a></p>
        <p>Thank you for choosing GSC.</p>
    </body>
    </html>
    ";
    sendMail($user_email, $subject, $body);

    $conn->commit();
    header("Location: " . BASE_URL . "/customer/booking_summary.php?booking_id=" . $booking_id);
    exit();
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Booking failed: " . $e->getMessage();
    header("Location: " . BASE_URL . "/customer/select_seat.php?showtime_id=" . intval($showtime_id));
    exit();
}
?>