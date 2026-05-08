<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$booking_id = $_GET['booking_id'] ?? 0;

$user_id = $_SESSION['user_id'];

// 避免重复通知
$check = $conn->query("
    SELECT id FROM notifications
    WHERE user_id = $user_id
    AND message = 'Your booking (ID: $booking_id) has been confirmed successfully.'
");

if ($check->num_rows == 0) {

    $msg = "Your booking (ID: $booking_id) has been confirmed successfully.";

    $conn->query("
        INSERT INTO notifications (user_id, message)
        VALUES ($user_id, '$msg')
    ");
}

$booking = $conn->query("
    SELECT b.id, b.payment_status, b.booking_date,
           m.title
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    WHERE b.id = " . intval($booking_id) . "
    AND b.user_id = " . $_SESSION['user_id']
)->fetch_assoc();

if (!$booking) die("Booking not found.");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Confirmation - GSC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    margin: 0;
    font-family: 'Segoe UI', sans-serif;

    background: linear-gradient(
        135deg,
        #fbf4e3,
        #ffe6bf
    );

    min-height: 100vh;
}

.confirm-container{
    min-height: calc(100vh - 70px);

    display: flex;
    justify-content: center;
    align-items: center;

    padding: 40px;
}

.confirm-card{
    width: 100%;
    max-width: 520px;

    background: rgb(237,237,232);

    border-radius: 28px;

    padding: 45px;

    text-align: center;

    box-shadow:
    0 10px 35px rgba(0,0,0,0.25);

    animation: fadeUp 0.6s ease;
}

@keyframes fadeUp{
    from{
        opacity: 0;
        transform: translateY(20px);
    }

    to{
        opacity: 1;
        transform: translateY(0);
    }
}

.confirm-icon{
    width: 100px;
    height: 100px;

    margin: auto;

    border-radius: 50%;

    background:
    linear-gradient(135deg,#fad75b,#fae7a9);

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 45px;

    box-shadow:
    0 10px 30px rgba(245,197,24,0.35);

    margin-bottom: 25px;
}

.confirm-title{
    font-size: 38px;
    font-weight: 700;

    color: #f5c518;

    margin-bottom: 15px;
}

.confirm-text{
    color: #666;

    font-size: 18px;

    margin-bottom: 10px;
}

.booking-id{
    margin-top: 20px;

    font-size: 20px;
    font-weight: 700;

    color: #222;
}

.btn-warning{
    background: #fcd23b !important;

    border: none !important;

    color: #111 !important;

    font-weight: 700 !important;

    border-radius: 30px !important;

    padding: 14px 24px !important;

    margin-top: 30px;
}

.btn-warning:hover{
    background: #ffd43b !important;

    transform: scale(1.03);
}

.small-text{
    margin-top: 25px;

    color: #777;
}

</style>

</head>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="confirm-container">

    <div class="confirm-card">

        <div class="confirm-icon">
            ⏰
        </div>

        <h1 class="confirm-title">
            Booking Confirmation
        </h1>

        <p class="confirm-text">
            Your booking is pending.
        </p>

        <p class="confirm-text">
            Please complete your payment within 6.5 hours.
        </p>

        <div class="booking-id">
            Booking ID:
            #<?= $booking['id'] ?>
        </div>

        <br>

        <a href="booking_details.php?booking_id=<?= $booking['id'] ?>"
           class="btn btn-warning">
            View Booking Details
        </a>

        <p class="small-text">
            You will receive your e-ticket once payment is completed.
        </p>

    </div>

</div>

</body>
</html>