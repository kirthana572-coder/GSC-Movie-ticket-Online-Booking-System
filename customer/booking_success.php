<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$booking_id = $_GET['booking_id'] ?? 0;
$booking = $conn->query("
    SELECT b.id, b.payment_status, b.booking_date,
           m.title, s.show_date, s.show_time,
           br.name AS branch_name
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN branches br ON s.branch_id = br.id
    WHERE b.id = " . intval($booking_id) . " AND b.user_id = " . $_SESSION['user_id']
)->fetch_assoc();

if (!$booking) die("Booking not found.");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Successful - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GSC-Movie-ticket-Online-Booking-System/frontend/gsc-style.css">
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="container mt-5 text-center">
    <div class="card shadow p-4">
        <h2 class="text-success">Booking Confirmed!</h2>
        <hr>
        <p><strong>Movie:</strong> <?= htmlspecialchars($booking['title']) ?></p>
        <p><strong>Branch:</strong> <?= htmlspecialchars($booking['branch_name']) ?></p>
        <p><strong>Date:</strong> <?= date('d M Y', strtotime($booking['show_date'])) ?></p>
        <p><strong>Time:</strong> <?= date('h:i A', strtotime($booking['show_time'])) ?></p>
        <p><strong>Status:</strong> <span class="badge bg-warning text-dark">Pending Payment</span></p>
        <p class="mt-3"><small>Please proceed to the cinema counter to make payment.</small></p>
        <a href="movies.php" class="btn btn-warning mt-3">Book Another Movie</a>
        <a href="history.php" class="btn btn-outline-dark mt-3 ms-2">My Bookings</a>
    </div>
</div>
</body>
</html>