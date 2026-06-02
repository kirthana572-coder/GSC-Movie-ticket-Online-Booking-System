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
        b.cancel_reason,
        b.cancelled_by,
        b.cancelled_at,
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
            font-family:'Segoe UI', sans-serif;
            background:#f6f7fb;
            min-height:100vh;
        }

        /* container */
        .details-container{
            margin-left:280px;
            width:calc(100% - 280px);
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:flex-start;
            padding:40px;
            box-sizing:border-box;
            transform:translateY(-10px);
        }

        /* card */
        .details-card{
            width:100%;
            max-width:780px;
            background:#fff;

            border-radius:22px; 
            padding:32px;

            border:1px solid rgba(0,0,0,.05);

            box-shadow:0 10px 25px rgba(0,0,0,.08);
        }

        /* title */
        .page-title{
            font-size:30px;
            font-weight:800;
            color:#2f2f2f;
            margin-bottom:6px;
        }

        .page-subtitle{
            color:#777;
            font-size:14px;
            margin-bottom:25px;
        }

        /* info box */
        .booking-info{
            display:flex;
            flex-direction:column;
            gap:0;
        }

        /* row */
        .info-row{
            display:flex;
            justify-content:space-between;
            align-items:center;

            padding:16px 0;
            border-bottom:1px solid #eef1f5;
        }

        .info-row:last-child{
            border-bottom:none;
        }

        /* label */
        .info-label{
            color:#868e96;
            font-size:13px;
        }

        /* value */
        .info-value{
            font-weight:600;
            color:#212529;
            text-align:right;
            max-width:60%;
        }

        /* status badge (professional) */
        .status-badge{
            padding:8px 14px;
            border-radius:20px;
            font-size:12px;
            font-weight:700;
        }

        /* status colors */
        .status-paid{
            background:#e6f4ea;
            color:#1e7e34;
        }

        .status-pending{
            background:#fff8e1;
            color:#b08900;
        }

        .status-cancelled{
            background:#fdecea;
            color:#c92a2a;
        }

        .status-expired{
            background:#f1f3f5;
            color:#495057;
        }

        /* button */
        .btn-history{
            background:#212529 !important;
            border:none !important;
            color:#fff !important;

            font-weight:600 !important;
            border-radius:10px !important;

            padding:10px 22px !important;

            transition:.2s;
        }

        .btn-history:hover{
            background:#343a40 !important;
            transform:scale(1.03);
        }

    </style>

</head>

<body>

<?php include '../includes/staff_sidebar.php'; ?>

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

                    <?php
                    $status = strtolower($booking['payment_status']);

                    $class = match($status){
                        'paid' => 'status-badge status-paid',
                        'pending' => 'status-badge status-pending',
                        'cancelled' => 'status-badge status-cancelled',
                        'expired' => 'status-badge status-expired',
                        default => 'status-badge status-pending'
                    };
                    ?>

                    <span class="<?= $class ?>">
                        <?= strtoupper($booking['payment_status']) ?>
                    </span>
                </div>

                <?php if($booking['payment_status'] === 'Cancelled'): ?>

                    <div class="info-row">

                        <span class="info-label">
                            Cancel Reason
                        </span>

                        <span class="info-value text-danger">

                            <?= htmlspecialchars($booking['cancel_reason'] ?: 'No reason provided') ?>

                        </span>

                    </div>

                <?php endif; ?>

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