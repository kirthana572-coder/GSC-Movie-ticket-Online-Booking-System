<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

$booking_id = $_GET['booking_id'] ?? 0;
if (!$booking_id) die("Booking ID required.");

$booking = $conn->query("
    SELECT b.id, m.title, s.show_date, s.show_time, br.name AS branch
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN branches br ON s.branch_id = br.id
    WHERE b.id = $booking_id AND b.payment_status = 'Paid'
")->fetch_assoc();

if (!$booking) die("Ticket not available for printing.");

$qr_data = "GSC Ticket #{$booking['id']}\n{$booking['title']}\n{$booking['branch']}\n{$booking['show_date']} {$booking['show_time']}";
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qr_data);
?>
<!DOCTYPE html>
<html>
<head><title>Print Ticket</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-5 text-center" style="max-width:500px">
    <div class="card p-4">
        <h2>🎟️ Ticket</h2>
        <p><strong><?= htmlspecialchars($booking['title']) ?></strong><br><?= htmlspecialchars($booking['branch']) ?><br><?= date('d M Y', strtotime($booking['show_date'])) ?> @ <?= date('h:i A', strtotime($booking['show_time'])) ?></p>
        <img src="<?= $qr_url ?>" class="img-fluid mb-3">
        <a href="javascript:window.print()" class="btn btn-warning">Print Ticket</a>
        <a href="staff_dashboard.php" class="btn btn-secondary mt-2">Back</a>
    </div>
</div>
</body>
</html>