<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$booking_id = $_GET['booking_id'] ?? 0;
$booking = $conn->query("
    SELECT b.id, b.payment_status, b.booking_date,
           m.title, s.show_date, s.show_time,
           br.name AS branch_name,

           GROUP_CONCAT(se.seat_number SEPARATOR ', ') AS seats

    FROM bookings b

    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN branches br ON s.branch_id = br.id

    JOIN booking_seats bs ON b.id = bs.booking_id
    JOIN seats se ON bs.seat_id = se.id

    WHERE b.id = " . intval($booking_id) . "
    AND b.user_id = " . $_SESSION['user_id'] . "

    GROUP BY b.id
")->fetch_assoc();

if (!$booking) die("Booking not found.");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Successful - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Segoe UI', sans-serif;

            background: linear-gradient(
                135deg,
              #fbf4e3,
              #ffe6bf
            );

            min-height: 100vh;

            color: #111;
        }   

        .success-container{
            min-height: calc(100vh - 70px);

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 40px;
        }

        .success-card{
            width: 100%;
            max-width: 650px;

            background: rgb(237, 237, 232) !important;

            border-radius: 28px;

            padding: 45px;

            backdrop-filter: blur(12px);

            border: 1px solid rgba(255,255,255,0.1);

            box-shadow:
            0 10px 35px rgba(0,0,0,0.35);

            text-align: center;

            animation: fadeUp 0.6s ease;
        }

        @keyframes fadeUp{
            from{
                opacity: 0;
                transform: translateY(25px);
            }

            to{
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon{
            width: 100px;
            height: 100px;

            margin: auto;

            border-radius: 50%;

            background:
            linear-gradient(135deg, #fad75b, #fae7a9);

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 50px;

            color: #111111ad;

            box-shadow:
            0 10px 30px rgba(245,197,24,0.35);

            margin-bottom: 25px;
        }

        .success-title{
            font-size: 38px;
            font-weight: 700;

            color: #fed12e;

            margin-bottom: 10px;
        }

        .success-subtitle{
            color: #666;
        }

        .booking-info{
            background: rgba(255, 255, 255, 0.64);

            border-radius: 18px;

            padding: 25px;

            text-align: left;

            margin-bottom: 30px;
        }

        .info-row{
            display: flex;
            justify-content: space-between;

            margin-bottom: 14px;

            border-bottom:
            1px solid rgba(255,255,255,0.08);

            padding-bottom: 10px;
        }

        .info-label{
            color: #666;
        }

        .info-value{
            font-weight: 600;
        }

        .status-badge{
            background: #ffdb58ef;

            color: #292828ca;

            padding: 7px 16px;

            border-radius: 30px;

            font-weight: 700;
        }

        .notice-text{
            color: #666;

            margin-top: 20px;
        }

        .btn-warning{
            background: #f5c518 !important;
            border: none;

            color: #111 !important;

            font-weight: 700;

            border-radius: 30px;

            padding: 16px 24px !important;

            transition: 0.3s;
        }

        .btn-warning:hover{
            background: #ffd43b !important;

            transform: scale(1.03) !important;
        }

    
        .booking-btn-outline{
            border-radius: 30px;

            padding: 14px 24px;

            border: 2px solid #6a6969 !important;

            color: #222 !important;

            font-weight: 600;
        }

        .booking-btn-outline:hover{
            background: #ffffff;

            color: #000000 !important;
            
            transform: scale(1.01) !important;
        }

    </style>
</head>

<body>
<?php include '../includes/navbar.php'; ?>

<div class="success-container">
    <div class="success-card">

        <div class="success-icon">
            🎟️
        </div>

        <h1 class="success-title">
            Booking Summary
        </h1>

        <p class="success-subtitle">
            Your booking details are shown below.
        </p>

        <div class="booking-info">

            <div class="info-row">
                <span class="info-label">Movie</span>

                <span class="info-value">
                    <?= htmlspecialchars($booking['title']) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Branch</span>

                <span class="info-value">
                    <?= htmlspecialchars($booking['branch_name']) ?>
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
                <span class="info-label">Seats</span>

                <span class="info-value">
                    <?= htmlspecialchars($booking['seats']) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Status</span>

                <span class="status-badge">
                    Pending Payment
                </span>
            </div>

        </div>

        <p class="notice-text">
            Please proceed to the cinema counter to make payment.
        </p>

        <div class="mt-4">

            <a href="javascript:history.back()"
               class="btn btn-warning me-2">
                Back
            </a>

            <a href="booking_confirmation.php?booking_id=<?= $booking['id'] ?>"
                class="btn booking-btn-outline">
                    Confirm Booking
            </a>

        </div>

    </div>

</div>

</body>
</html>