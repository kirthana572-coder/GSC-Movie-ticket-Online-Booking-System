<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';


// Get booking ID from URL
$booking_id = $_GET['booking_id'] ?? 0;


// Get booking details
$booking = $conn->query("
    SELECT 
        b.id,
        b.payment_status,
        b.booking_date,
        m.title,
        s.show_date,
        s.show_time,
        br.name AS branch_name,

        GROUP_CONCAT(
            CONCAT(se.seat_number,' (',bs.ticket_type,')')
            SEPARATOR ', '
        ) AS seats,

        SUM(
            CASE bs.ticket_type
                WHEN 'Adult' THEN 12
                WHEN 'Senior' THEN 8
                WHEN 'Student' THEN 10
                WHEN 'Children' THEN 6
                ELSE 12
            END
        ) AS total_price

    FROM bookings b

    JOIN showtimes s
    ON b.showtime_id = s.id

    JOIN movies m
    ON s.movie_id = m.id

    JOIN branches br
    ON s.branch_id = br.id

    LEFT JOIN booking_seats bs
    ON b.id = bs.booking_id

    LEFT JOIN seats se
    ON bs.seat_id = se.id

    WHERE b.id = " . intval($booking_id) . "

    GROUP BY b.id

")->fetch_assoc();


// Show error if booking not found
if (!$booking) {
    die("Booking not found.");
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Staff Booking Details - GSC</title>

    <!-- Bootstrap CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <style>

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(
                rgba(248,242,226,0.92),
                rgba(255,230,191,0.92)
            );
            min-height:100vh;
        }

        .details-container{
            min-height:calc(100vh - 70px);
            display:flex;
            justify-content:center;
            align-items:center;
            padding:40px;
        }

        .details-card{
            width:100%;
            max-width:650px;
            background:#f3f1ec;
            border-radius:28px;
            padding:40px;
            box-shadow:0 10px 30px rgba(0,0,0,0.18);
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
            color:#666;
            margin-bottom:30px;
        }

        .booking-info{
            background:rgba(255,255,255,0.7);
            border-radius:20px;
            padding:25px;
        }

        .info-row{
            display:flex;
            justify-content:space-between;
            margin-bottom:16px;
            padding-bottom:10px;
            border-bottom:1px solid rgba(0,0,0,0.08);
        }

        .info-label{
            color:#666;
        }

        .info-value{
            font-weight:600;
            color:#222;
        }

        .status-badge{
            background:#ffd95d;
            color:#222;
            padding:8px 18px;
            border-radius:30px;
            font-weight:700;
        }

        .btn-history{
            background:#ffcf23 !important;
            border:none !important;
            color:#111 !important;
            font-weight:700 !important;
            border-radius:30px !important;
            padding:18px 60px !important;
            transition:0.3s !important;
        }

        .btn-history:hover{
            background:#ffd43b !important;
            transform:scale(1.03);
        }

    </style>

</head>

<body>

    <div class="details-container">

        <div class="details-card">

            <!-- Page Title -->
            <h1 class="page-title">
                Customer Booking Details
            </h1>

            <p class="page-subtitle">
                Staff can review customer booking information here.
            </p>


            <!-- Booking Information -->
            <div class="booking-info">

                <div class="info-row">
                    <span class="info-label">Movie</span>

                    <span class="info-value">
                        <?= htmlspecialchars($booking['title']) ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Date</span>

                    <span class="info-value">
                        <?= date('d M Y', strtotime($booking['show_date'])) ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Time</span>

                    <span class="info-value">
                        <?= date('h:i A', strtotime($booking['show_time'])) ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Cinema</span>

                    <span class="info-value">
                        <?= htmlspecialchars($booking['branch_name']) ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Seats</span>

                    <span class="info-value">
                        <?= $booking['seats'] ? htmlspecialchars($booking['seats']) : 'No Seats' ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Booking ID</span>

                    <span class="info-value">
                        #<?= $booking['id'] ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Total Price</span>

                    <span class="info-value">
                        RM <?= number_format($booking['total_price'], 2) ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Payment Status</span>

                    <span class="status-badge">
                        <?= strtoupper($booking['payment_status']) ?>
                    </span>
                </div>

            </div>


            <!-- Back Button -->
            <div class="text-center mt-4">

                <a 
                    href="<?= BASE_URL ?>/staff/customer_bookings.php" 
                    class="btn btn-history"
                >
                    Back to Customer Booking
                </a>

            </div>

        </div>

    </div>

</body>
</html>