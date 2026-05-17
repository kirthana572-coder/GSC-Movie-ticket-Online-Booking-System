<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

$booking_id = $_GET['booking_id'] ?? 0;

if (!$booking_id){
    die("Booking ID required.");
}

$booking = $conn->query("
    SELECT 
        wb.id,
        wb.booking_code,
        wb.customer_name,
        wb.payment_status,

        wb.adult_qty,
        wb.senior_qty,
        wb.student_qty,
        wb.children_qty,

        m.title,

        s.show_date,
        s.show_time,

        br.name AS branch_name

    FROM walkin_bookings wb

    JOIN showtimes s 
    ON wb.showtime_id = s.id

    JOIN movies m 
    ON s.movie_id = m.id

    JOIN branches br 
    ON s.branch_id = br.id

    WHERE wb.id = '$booking_id'
    AND wb.payment_status = 'Paid'
")->fetch_assoc();

if(!$booking){
    die("Ticket not available.");
}

$qr_data = "
Ticket ID: {$booking['booking_code']}
Customer: {$booking['customer_name']}
Movie: {$booking['title']}
Cinema: {$booking['branch_name']}
Date: {$booking['show_date']}
Time: {$booking['show_time']}
";

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qr_data);
?>

<!DOCTYPE html>
<html>
<head>

    <title>GSC Ticket</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            background: #f5f5f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .ticket-container{
            max-width: 700px;
            margin: 40px auto;
        }

        .ticket-card{

            background: white;

            border-radius: 25px;

            overflow: hidden;

            box-shadow:
            0 10px 30px rgba(0,0,0,0.15);
        }

        .ticket-header{

            background: #f5c518;

            padding: 25px;

            text-align: center;
        }

        .ticket-header h1{
            margin: 0;

            font-size: 40px;

            font-weight: 800;

            color: #111;
        }

        .ticket-body{
            padding: 35px;
        }

        .info-row{

            display: flex;

            justify-content: space-between;

            border-bottom:
            1px solid rgba(0,0,0,0.08);

            padding: 14px 0;
        }

        .label{
            color: #666;
        }

        .value{
            font-weight: 700;
            color: #111;

            text-align: right;
        }

        .qr-box{
            text-align: center;

            margin-top: 35px;
        }

        .qr-box img{
            width: 230px;
        }

        .ticket-id{

            margin-top: 15px;

            font-size: 18px;

            font-weight: 700;

            color: #444;
        }

        .btn-print{

            background: #f5c518;

            border: none;

            color: #111;

            font-weight: 700;

            border-radius: 30px;

            padding: 14px 40px;

            transition: 0.25s;
        }

        .btn-print:hover{
            transform: scale(1.03);
            background: #ffd53d;
        }

        .btn-back{

            background: #333;

            border: none;

            color: white;

            font-weight: 700;

            border-radius: 30px;

            padding: 14px 40px;

            text-decoration: none;
        }

        .btn-back:hover{
            transform: scale(1.03);
            background: #ffd53d;
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

        <div class="ticket-header">

            <h1>
                🎟️ GSC E-Ticket
            </h1>

        </div>

        <div class="ticket-body">

            <div class="info-row">
                <span class="label">Customer</span>

                <span class="value">
                    <?= htmlspecialchars($booking['customer_name']) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="label">Movie</span>

                <span class="value">
                    <?= htmlspecialchars($booking['title']) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="label">Cinema</span>

                <span class="value">
                    <?= htmlspecialchars($booking['branch_name']) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="label">Date</span>

                <span class="value">
                    <?= date('d M Y', strtotime($booking['show_date'])) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="label">Time</span>

                <span class="value">
                    <?= date('h:i A', strtotime($booking['show_time'])) ?>
                </span>
            </div>

            <div class="qr-box">

                <img src="<?= $qr_url ?>">

                <div class="ticket-id">
                    Ticket #<?= $booking['booking_code'] ?>
                </div>

            </div>

            <div class="text-center mt-5 no-print">

                <button onclick="window.print()" class="btn btn-print">
                    🖨️ Print Ticket
                </button>

                <a href="walkin_bookings.php" class="btn btn-back ms-2">
                    Back
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>