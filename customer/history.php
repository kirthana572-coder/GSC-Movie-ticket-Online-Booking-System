<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// 自动过期：30分钟未支付且场次未开始的订单标记为 Expired
$conn->query("
    UPDATE bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    SET b.payment_status = 'Expired'
    WHERE b.payment_status = 'Pending'
      AND b.booking_date < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
");

// 为刚过期的订单发送通知（避免重复）
$expired_orders = $conn->query("
    SELECT b.id, b.user_id 
    FROM bookings b
    WHERE b.payment_status = 'Expired' 
      AND b.id NOT IN (
          SELECT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(message, '#', -1), ' ', 1) AS UNSIGNED)
          FROM notifications 
          WHERE message LIKE '%expired%'
      )
");
while ($e = $expired_orders->fetch_assoc()) {
    $msg = "Your booking #{$e['id']} has expired due to non-payment.";
    $conn->query("INSERT INTO notifications (user_id, message) VALUES ({$e['user_id']}, '$msg')");
}

// 获取当前用户所有预订
$bookings = $conn->query("
    SELECT b.id, b.payment_status, b.booking_date,
           m.title, s.show_date, s.show_time, br.name AS branch_name
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN branches br ON s.branch_id = br.id
    WHERE b.user_id = " . $_SESSION['user_id'] . "
    ORDER BY b.booking_date DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking History - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <h2>My Bookings</h2>
    <?php if ($bookings->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Movie</th>
                    <th>Branch</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Booked On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($b = $bookings->fetch_assoc()): 
                    $statusClass = 'bg-warning text-dark';
                    if ($b['payment_status'] === 'Paid') $statusClass = 'bg-success';
                    if (in_array($b['payment_status'], ['Cancelled', 'Expired'])) $statusClass = 'bg-danger';
                ?>
                <tr>
                    <td><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= htmlspecialchars($b['branch_name']) ?></td>
                    <td><?= date('d M Y', strtotime($b['show_date'])) ?></td>
                    <td><?= date('h:i A', strtotime($b['show_time'])) ?></td>
                    <td><span class="badge <?= $statusClass ?>"><?= $b['payment_status'] ?></span></td>
                    <td><?= date('d M Y H:i', strtotime($b['booking_date'])) ?></td>
                    <td>
                        <?php if ($b['payment_status'] === 'Pending'): ?>
                            <a href="cancel_booking.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this booking?')">Cancel</a>
                        <?php elseif ($b['payment_status'] === 'Paid'): ?>
                            <a href="qr_ticket.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">View QR</a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No bookings yet. <a href="movies.php">Book now</a></div>
    <?php endif; ?>
</div>

</body>
</html>