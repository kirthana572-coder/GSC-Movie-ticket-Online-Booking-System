<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';


// Get booking ID
$id = $_GET['id'];


// Get booking details
$stmt = $conn->prepare("
    SELECT
        walkin_bookings.*,
        movies.title,
        showtimes.show_date,
        showtimes.show_time
    FROM walkin_bookings
    JOIN showtimes
    ON walkin_bookings.showtime_id = showtimes.id
    JOIN movies
    ON showtimes.movie_id = movies.id
    WHERE walkin_bookings.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$booking = $stmt->get_result()->fetch_assoc();


// Stop if booking not found
if (!$booking) {
    die("Booking not found.");
}


// Get selected seats
$seatResult = $conn->query("
    SELECT seats.seat_number
    FROM walkin_booking_seats
    JOIN seats
    ON walkin_booking_seats.seat_id = seats.id
    WHERE walkin_booking_seats.walkin_booking_id = $id
    ORDER BY seats.seat_number
");

$seatNumbers = [];

while ($seat = $seatResult->fetch_assoc()) {
    $seatNumbers[] = $seat['seat_number'];
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>
        View Walk-in Booking - GSC
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(rgba(245,242,234,0.92),rgba(255,220,164,0.92));
            min-height:100vh;
        }

        .page-container{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:40px;
        }

        .details-card{
            width:100%;
            max-width:700px;
            background:rgba(254,255,246,0.91);
            border-radius:28px;
            padding:45px;
            box-shadow:0 10px 30px rgba(0,0,0,0.15);
        }

        .page-title{
            text-align:center;
            font-size:38px;
            font-weight:700;
            color:#f5c518;
            margin-bottom:10px;
        }

        .page-subtitle{
            text-align:center;
            color:#8f8f8f;
            margin-bottom:35px;
        }

        .detail-box{
            background:rgba(239,239,239,0.57);
            border-radius:18px;
            padding:20px;
            margin-bottom:18px;
        }

        .detail-label{
            font-size:12px;
            font-weight:700;
            color:#777;
            margin-bottom:6px;
            text-transform:uppercase;
        }

        .detail-value{
            font-size:18px;
            font-weight:600;
            color:#222;
        }

        .price-box{
            background:rgba(245,197,24,0.15);
            border-radius:18px;
            padding:25px;
            text-align:center;
            margin-top:25px;
        }

        .price-title{
            font-size:18px;
            color:#666;
        }

        .price-value{
            font-size:38px;
            font-weight:600;
            color:#f5c518;
        }

        .back-btn{
            width:100%;
            display:block;
            text-align:center;
            text-decoration:none;
            background:#2f2f2f;
            color:white;
            border-radius:16px;
            padding:15px;
            font-size:18px;
            font-weight:700;
            margin-top:30px;
            transition:0.25s;
        }

        .back-btn:hover{
            background:#f5c518;
            color:#111;
            transform:scale(1.02);
        }

    </style>

</head>

<body>

<div class="page-container">

    <div class="details-card">

        <h1 class="page-title">
            Booking Details
        </h1>

        <p class="page-subtitle">
            View walk-in booking information
        </p>


        <!-- Booking Code -->
        <div class="detail-box">

            <div class="detail-label">
                Booking Code
            </div>

            <div class="detail-value">
                <?= $booking['booking_code'] ?? 'N/A' ?>
            </div>

        </div>


        <!-- Customer Name -->
        <div class="detail-box">

            <div class="detail-label">
                Customer Name
            </div>

            <div class="detail-value">
                <?= $booking['customer_name'] ?>
            </div>

        </div>


        <!-- Movie Name -->
        <div class="detail-box">

            <div class="detail-label">
                Movie Name
            </div>

            <div class="detail-value">
                <?= $booking['title'] ?>
            </div>

        </div>


        <!-- Show Date -->
        <div class="detail-box">

            <div class="detail-label">
                Show Date
            </div>

            <div class="detail-value">
                <?= !empty($booking['show_date']) ? date('d M Y', strtotime($booking['show_date'])) : '-' ?>
            </div>

        </div>


        <!-- Show Time -->
        <div class="detail-box">

            <div class="detail-label">
                Show Time
            </div>

            <div class="detail-value">
                <?= !empty($booking['show_time']) ? date('h:i A', strtotime($booking['show_time'])) : '-' ?>
            </div>

        </div>


        <!-- Selected Seats -->
        <div class="detail-box">

            <div class="detail-label">
                Selected Seats
            </div>

            <div class="detail-value">
                <?= !empty($seatNumbers) ? implode(', ', $seatNumbers) : 'No seats selected' ?>
            </div>

        </div>


        <!-- Ticket Selection -->
        <div class="detail-box">

            <div class="detail-label">
                Ticket Selection
            </div>

            <div
                class="detail-value"
                style="line-height:2;"
            >
                🧑 Adult (RM12.00): <?= $booking['adult_qty'] ?><br>

                👴 Senior (RM8.00): <?= $booking['senior_qty'] ?><br>

                🎓 Student (RM10.00): <?= $booking['student_qty'] ?><br>

                👶 Children (RM6.00): <?= $booking['children_qty'] ?>

            </div>

        </div>


        <!-- Payment Status -->
        <div class="detail-box">

            <div class="detail-label">
                Payment Status
            </div>

            <div class="detail-value">
                <?= $booking['payment_status'] ?>
            </div>

        </div>


        <!-- Total Price -->
        <div class="price-box">

            <div class="price-title">
                Total Price
            </div>

            <div class="price-value">
                RM <?= number_format($booking['total_price'],2) ?>
            </div>

        </div>


        <!-- Back Button -->
        <a
            href="<?= BASE_URL ?>/staff/walkin_bookings.php"
            class="back-btn"
        >
            Back
        </a>

    </div>

</div>

</body>
</html>