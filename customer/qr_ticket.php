<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$booking_id = $_GET['booking_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT 
        b.id,
        m.title,
        s.show_date,
        s.show_time,
        br.name AS branch_name,

        GROUP_CONCAT(
            se.seat_number
            SEPARATOR ', '
        ) AS seats

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

    WHERE b.id = ?
    AND b.user_id = ?
    AND b.payment_status = 'Paid'

    GROUP BY b.id
");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();

$b = $stmt->get_result()->fetch_assoc();

if (!$b){
    die("Ticket not available.");
}

$qrData =
"GSC Ticket #{$b['id']}
Movie: {$b['title']}
Cinema: {$b['branch_name']}
Date: {$b['show_date']}
Time: {$b['show_time']}
Seats: {$b['seats']}";

$qr =
"https://api.qrserver.com/v1/create-qr-code/?size=300x300&data="
. urlencode($qrData);
?>

<!DOCTYPE html>
<html>
<head>

    <title>My QR Ticket - GSC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background:
            linear-gradient(
                rgba(248,242,226,0.95),
                rgba(255,220,164,0.92)
            );

            min-height: 100vh;
        }

        .ticket-container{
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 40px;
        }

        .ticket-card{
            width: 100%;
            max-width: 650px;

            background: rgba(255,255,255,0.92);

            border-radius: 30px;

            padding: 40px;

            box-shadow:
            0 10px 35px rgba(0,0,0,0.15);

            text-align: center;
        }

        .ticket-title{
            font-size: 40px;

            font-weight: 700;

            color: #f5c518;

            margin-bottom: 10px;
        }

        .ticket-subtitle{
            color: #666;

            margin-bottom: 35px;
        }

        .movie-title{
            font-size: 28px;

            font-weight: 700;

            color: #222;

            margin-bottom: 25px;
        }

        .info-box{
            background: rgba(255,255,255,0.7);

            border-radius: 22px;

            padding: 25px;

            margin-bottom: 30px;
        }

        .info-row{
            display: flex;
            justify-content: space-between;

            padding: 12px 0;

            border-bottom:
            1px solid rgba(0,0,0,0.08);
        }

        .info-row:last-child{
            border-bottom: none;
        }

        .info-label{
            color: #666;
        }

        .info-value{
            font-weight: 700;

            color: #222;
        }

        .qr-box{
            background: white;

            padding: 25px;

            border-radius: 25px;

            display: inline-block;

            box-shadow:
            0 5px 20px rgba(0,0,0,0.08);

            margin-bottom: 25px;
        }

        .qr-box img{
            width: 280px;
            height: 280px;
        }

        .scan-text{
            color: #777;

            font-size: 15px;

            margin-top: 10px;
        }

        .btn-print{
            background: #f5c518 !important;

            border: none !important;

            color: #111 !important;

            border-radius: 30px !important;

            padding: 14px 35px !important;

            font-weight: 700 !important;

            transition: 0.25s !important;

            margin-right: 10px;
        }

        .btn-print:hover{
            background: #ffd84d !important;

            transform: scale(1.03);
        }

        .btn-back{
            background: #2f2f2f !important;

            border: none !important;

            color: white !important;

            border-radius: 30px !important;

            padding: 14px 35px !important;

            font-weight: 700 !important;

            transition: 0.25s !important;
        }

        .btn-back:hover{
            background: #444 !important;

            transform: scale(1.03);
        }

        @media print{

            .no-print{
                display: none;
            }

            body{
                background: white;
            }

            .ticket-card{
                box-shadow: none;
            }
        }

    </style>

</head>

<body>

<div class="ticket-container">

    <div class="ticket-card">

        <h1 class="ticket-title">
            🎟️ QR Ticket
        </h1>

        <p class="ticket-subtitle">
            Show this QR code at the cinema entrance
        </p>

        <div class="movie-title">
            <?= htmlspecialchars($b['title']) ?>
        </div>

        <div class="info-box">

            <div class="info-row">
                <span class="info-label">
                    Booking ID
                </span>

                <span class="info-value">
                    #<?= $b['id'] ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">
                    Cinema
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($b['branch_name']) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">
                    Date
                </span>

                <span class="info-value">
                    <?= date('d M Y', strtotime($b['show_date'])) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">
                    Time
                </span>

                <span class="info-value">
                    <?= date('h:i A', strtotime($b['show_time'])) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">
                    Seats
                </span>

                <span class="info-value">
                    <?= htmlspecialchars($b['seats']) ?>
                </span>
            </div>

        </div>

        <div class="qr-box">

            <img src="<?= $qr ?>">

            <div class="scan-text">
                Scan QR code for ticket validation
            </div>

        </div>

        <div class="no-print">

            <button onclick="window.print()" class="btn btn-print">
                🖨️ Print Ticket
            </button>

            <a href="history.php" class="btn btn-back">
                Back
            </a>

        </div>

    </div>

</div>

</body>
</html>