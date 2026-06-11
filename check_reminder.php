<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$user_info = $conn->query("SELECT email, full_name FROM users WHERE id = $user_id")->fetch_assoc();
if (!$user_info) {
    echo json_encode(['error' => 'User not found']);
    exit;
}
$user_email = $user_info['email'];
$user_name = $user_info['full_name'];

// ==================== 1. 自动取消 1 小时内未付款的订单 ====================
$cancelSql = "
    SELECT b.id, m.title, CONCAT(s.show_date, ' ', s.show_time) AS start_time
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    WHERE b.user_id = ?
      AND b.payment_status = 'Pending'
      AND TIMESTAMP(s.show_date, s.show_time) > NOW()
      AND TIMESTAMP(s.show_date, s.show_time) <= DATE_ADD(NOW(), INTERVAL 1 HOUR)
";
$stmt = $conn->prepare($cancelSql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $booking_id = $row['id'];
    $movie_title = $row['title'];
    $start_time = $row['start_time'];

    $conn->begin_transaction();
    try {
        $conn->query("UPDATE bookings SET payment_status = 'Cancelled' WHERE id = $booking_id");
        $conn->query("
            UPDATE seats s
            JOIN booking_seats bs ON s.id = bs.seat_id
            SET s.status = 'available'
            WHERE bs.booking_id = $booking_id
        ");
        $conn->query("DELETE FROM booking_seats WHERE booking_id = $booking_id");
        $msg = "Your booking #$booking_id (Movie: $movie_title) has been automatically cancelled because payment was not completed within the allowed time.";
        $conn->query("INSERT INTO notifications (user_id, message, is_read, is_popup_shown, created_at) VALUES ($user_id, '$msg', 0, 0, NOW())");
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Auto-cancel failed for booking $booking_id: " . $e->getMessage());
    }
}
$stmt->close();

// ==================== 2. 2小时提醒（未付款，且尚未提醒过） ====================
$reminderSql = "
    SELECT b.id, m.title, CONCAT(s.show_date, ' ', s.show_time) AS start_time
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    WHERE b.user_id = ?
      AND b.payment_status = 'Pending'
      AND b.two_hour_notified = 0
      AND TIMESTAMP(s.show_date, s.show_time) > NOW()
      AND TIMESTAMP(s.show_date, s.show_time) <= DATE_ADD(NOW(), INTERVAL 2 HOUR)
";
$stmt = $conn->prepare($reminderSql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $booking_id = $row['id'];
    $start_time = $row['start_time'];
    $payment_deadline = date('h:i A', strtotime($start_time) - 3600);
    $msg = "⏰ Reminder: Your booking for '{$row['title']}' starts at {$start_time}. Please complete payment before {$payment_deadline}, otherwise your booking will be automatically cancelled.";
    
    sendStationNotification($user_id, $msg);

    $subject = "Action Required: Complete Payment Before {$payment_deadline}";
    $order_link = BASE_URL . "/customer/booking_details.php?booking_id=" . $booking_id;
    $body = "
    <html>
    <body>
        <h2>Payment Reminder – Urgent</h2>
        <p>Dear {$user_name},</p>
        <p>Your booking for <strong>{$row['title']}</strong> at <strong>{$start_time}</strong> is still pending payment.</p>
        <p><strong>Please complete your payment at the cinema counter by {$payment_deadline}.</strong> Otherwise, your booking will be automatically cancelled.</p>
        <p>View your booking: <a href='{$order_link}'>Booking Details</a></p>
        <p>Thank you.</p>
    </body>
    </html>
    ";
    try {
        sendMail($user_email, $subject, $body);
    } catch (\Exception $e) {
        error_log("2h reminder email failed for booking {$booking_id}: " . $e->getMessage());
    }

    $update = $conn->prepare("UPDATE bookings SET two_hour_notified = 1 WHERE id = ?");
    $update->bind_param('i', $booking_id);
    $update->execute();
    $update->close();
}
$stmt->close();

// ==================== 3. 30分钟提醒（原有逻辑，区分付款状态） ====================
$thirtyMinSql = "
    SELECT b.id, m.title, CONCAT(s.show_date, ' ', s.show_time) AS start_time, b.payment_status
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    WHERE b.user_id = ?
      AND b.is_notified = 0
      AND TIMESTAMP(s.show_date, s.show_time) > NOW()
      AND TIMESTAMP(s.show_date, s.show_time) <= DATE_ADD(NOW(), INTERVAL 30 MINUTE)
";
$stmt = $conn->prepare($thirtyMinSql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$reminders = [];
while ($row = $result->fetch_assoc()) {
    if ($row['payment_status'] == 'Paid') {
        $msg = "🎬 Movie '{$row['title']}' starts at {$row['start_time']}. Please come to the cinema on time!";
    } else {
        $msg = "🎬 Movie '{$row['title']}' starts at {$row['start_time']}. Please complete payment and come to the cinema!";
    }
    sendStationNotification($user_id, $msg);

    $subject = "Movie Starting Soon: {$row['title']}";
    $order_link = BASE_URL . "/customer/booking_details.php?booking_id=" . $row['id'];
    $body = "
    <html>
    <body>
        <h2>" . ($row['payment_status'] == 'Paid' ? 'Movie Reminder' : 'Payment Required') . "</h2>
        <p>Dear {$user_name},</p>
        <p>The movie <strong>{$row['title']}</strong> will start at <strong>{$row['start_time']}</strong>.</p>
        " . ($row['payment_status'] == 'Paid' ? '<p>Please arrive on time.</p>' : '<p><strong>You have not completed payment yet.</strong> Please pay at the counter immediately.</p>') . "
        <p>View your booking: <a href='{$order_link}'>Booking Details</a></p>
        <p>Thank you.</p>
    </body>
    </html>
    ";
    try {
        sendMail($user_email, $subject, $body);
    } catch (\Exception $e) {
        error_log("30min reminder email failed for booking {$row['id']}: " . $e->getMessage());
    }

    $reminders[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start_time' => $row['start_time'],
        'message' => $msg
    ];

    $update = $conn->prepare("UPDATE bookings SET is_notified = 1 WHERE id = ?");
    $update->bind_param('i', $row['id']);
    $update->execute();
    $update->close();
}
$stmt->close();

$conn->close();
echo json_encode(['reminders' => $reminders]);
?>