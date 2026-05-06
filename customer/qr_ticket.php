<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$booking_id = $_GET['booking_id'] ?? 0;
$stmt = $conn->prepare("
    SELECT b.id, m.title, s.show_date, s.show_time, br.name
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN branches br ON s.branch_id = br.id
    WHERE b.id = ? AND b.user_id = ? AND b.payment_status = 'Paid'
");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$b = $stmt->get_result()->fetch_assoc();
if (!$b) die("Ticket not available.");
$qr = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode("GSC Ticket #{$b['id']} - {$b['title']} - {$b['name']} - {$b['show_date']} {$b['show_time']}");
?>
<!DOCTYPE html>
<html>
<head>
    <title>QR Ticket - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GSC-Movie-ticket-Online-Booking-System/frontend/gsc-style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-5 text-center">
    <img src="<?= $qr ?>" class="img-fluid mb-3">
    <p><?= $b['title'] ?> | <?= $b['name'] ?> | <?= $b['show_date'] ?> <?= $b['show_time'] ?></p>
    <a href="history.php" class="btn btn-warning">Back</a>
</div>
</body>
</html>