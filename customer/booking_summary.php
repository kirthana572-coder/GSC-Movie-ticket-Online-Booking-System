<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$booking_id = $_GET['booking_id'] ?? 0;
if (!$booking_id) die("Booking not found.");

// 查询预订信息，包含电影、场次、分店
$booking = $conn->query("
    SELECT b.id, b.payment_status, b.booking_date,
           m.title, s.show_date, s.show_time,
           br.name AS branch_name
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN branches br ON s.branch_id = br.id
    WHERE b.id = " . intval($booking_id) . "
      AND b.user_id = " . $_SESSION['user_id']
)->fetch_assoc();

if (!$booking) die("Booking not found.");

// 查询该预订的所有座位及票种
$seatDetails = $conn->query("
    SELECT se.seat_number, bs.ticket_type
    FROM booking_seats bs
    JOIN seats se ON bs.seat_id = se.id
    WHERE bs.booking_id = $booking_id
    ORDER BY se.seat_number
");

// 票价表（与 booking_confirmation.php 保持一致）
$prices = [
    'Adult'    => 12.00,
    'Senior'   => 8.00,
    'Student'  => 10.00,
    'Children' => 6.00
];

$total = 0;
$seatList = [];
while ($row = $seatDetails->fetch_assoc()) {
    $type = $row['ticket_type'] ?? 'Adult';
    $price = $prices[$type] ?? 12.00;
    $total += $price;
    $seatList[] = $row['seat_number'] . ' (' . $type . ' - RM' . number_format($price, 2) . ')';
}
$seatText = implode('<br>', $seatList);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Successful - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #fbf4e3, #ffe6bf);
            min-height: 100vh;
            color: #111;
        }
        .success-container {
            min-height: calc(100vh - 70px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .success-card {
            width: 100%;
            max-width: 650px;
            background: rgb(237, 237, 232);
            border-radius: 28px;
            padding: 45px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.35);
            text-align: center;
            animation: fadeUp 0.6s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .success-icon {
            width: 100px; height: 100px; margin: auto;
            border-radius: 50%;
            background: linear-gradient(135deg, #fad75b, #fae7a9);
            display: flex; align-items: center; justify-content: center;
            font-size: 50px; color: #111; margin-bottom: 25px;
        }
        .success-title {
            font-size: 38px; font-weight: 700; color: #c9a30e; margin-bottom: 10px;
        }
        .booking-info {
            background: rgba(255,255,255,0.7);
            border-radius: 18px;
            padding: 25px;
            text-align: left;
            margin: 25px 0;
        }
        .info-row {
            display: flex; justify-content: space-between;
            padding: 10px 0; border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        .info-label { color: #666; }
        .info-value { font-weight: 600; text-align: right; }
        .total-price {
            font-size: 24px; font-weight: 700; color: #f5c518;
            margin-top: 15px; text-align: right;
        }
        .status-badge {
            background: #ffdb58; color: #292828; padding: 7px 16px;
            border-radius: 30px; font-weight: 700;
        }
        .btn-warning {
            background: #f5c518 !important; border: none; color: #111 !important;
            font-weight: 700; border-radius: 30px; padding: 16px 24px !important;
            transition: 0.3s; text-decoration: none; display: inline-block; margin: 10px;
        }
        .btn-warning:hover { background: #ffd43b !important; transform: scale(1.03); }
        .btn-outline-dark {
            border-radius: 30px; padding: 14px 24px;
            border: 2px solid #6a6969; color: #222; font-weight: 600;
            text-decoration: none; display: inline-block; margin: 10px;
        }
        .btn-outline-dark:hover { background: #fff; color: #000; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="success-container">
    <div class="success-card">
        <div class="success-icon">🎟️</div>
        <h1 class="success-title">Booking Confirmed!</h1>
        <p class="text-muted">Your seats have been reserved. Please pay at the counter.</p>

        <div class="booking-info">
            <div class="info-row">
                <span class="info-label">Movie</span>
                <span class="info-value"><?= htmlspecialchars($booking['title']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Branch</span>
                <span class="info-value"><?= htmlspecialchars($booking['branch_name']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date</span>
                <span class="info-value"><?= date('d M Y', strtotime($booking['show_date'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Time</span>
                <span class="info-value"><?= date('h:i A', strtotime($booking['show_time'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Seats</span>
                <span class="info-value"><?= $seatText ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value"><span class="status-badge">Pending Payment</span></span>
            </div>
            <div class="total-price">Total: RM <?= number_format($total, 2) ?></div>
        </div>

        <div>
            <a href="movies.php" class="btn btn-warning">Book Another Movie</a>
            <a href="history.php" class="btn btn-outline-dark">My Bookings</a>
        </div>
    </div>
</div>
<div><a href="<?= BASE_URL ?>/customer/movies.php" class="btn btn-warning">Book Another Movie</a><a href="<?= BASE_URL ?>/customer/history.php" class="btn btn-outline-dark">My Bookings</a></div>
</div></div>

<script>var baseUrl = '<?= BASE_URL ?>';</script>

<script src="<?= BASE_URL ?>/notification.js"></script>

<script>if (typeof sendAlert === 'function') { sendAlert("Booking Successful", "Please proceed to the counter for payment. Thank you!", "🧾 Please pay at the counter."); } else { alert("Booking successful! Please pay at counter."); }</script>
</body>
</html>