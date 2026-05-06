<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$conn->query("
    UPDATE bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    SET b.payment_status = 'Expired'
    WHERE b.payment_status = 'Pending'
      AND b.booking_date < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
");

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
                    <th>Movie</th><th>Branch</th><th>Date</th><th>Time</th><th>Status</th><th>Booked On</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($b = $bookings->fetch_assoc()): 
                    $cls = 'bg-warning text-dark';
                    if ($b['payment_status'] === 'Paid') $cls = 'bg-success';
                    if (in_array($b['payment_status'], ['Cancelled','Expired'])) $cls = 'bg-danger';
                ?>
                <tr>
                    <td><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= htmlspecialchars($b['branch_name']) ?></td>
                    <td><?= date('d M Y', strtotime($b['show_date'])) ?></td>
                    <td><?= date('h:i A', strtotime($b['show_time'])) ?></td>
                    <td><span class="badge <?= $cls ?>"><?= $b['payment_status'] ?></span></td>
                    <td><?= date('d M Y H:i', strtotime($b['booking_date'])) ?></td>
                    <td>
                        <?php if ($b['payment_status'] === 'Pending'): ?>
                            <a href="cancel_booking.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this booking?')">Cancel</a>
                        <?php elseif ($b['payment_status'] === 'Paid'): ?>
                            <a href="qr_ticket.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">View QR</a>
                        <?php else: ?>
                            —
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