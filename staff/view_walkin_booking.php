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
            background:#f6f7fb;
            min-height:100vh;
        }

        .page-container{
            margin-left:280px;
            width:calc(100% - 280px);

            min-height:100vh;

            display:flex;
            justify-content:center;
            align-items:center;

            padding:40px;
            box-sizing:border-box;
        }

        .details-card{
            width:100%;
            max-width:760px;

            background:#fff;

            border-radius:16px;

            padding:32px;

            border:1px solid #eef0f3;

            box-shadow:
            0 8px 24px rgba(0,0,0,.08);
        }

        .page-title{
            text-align:left;
            font-size:28px;
            font-weight:700;
            color:#212529;
            margin-bottom:6px;
        }

        .page-subtitle{
            text-align:left;
            color:#6c757d;
            font-size:14px;
            margin-bottom:24px;
        }

        .booking-info{
            background:#fff;

            border:1px solid #eef0f3;

            border-radius:12px;

            padding:0 18px;
        }

        .info-row{
            display:flex;
            justify-content:space-between;
            align-items:center;

            padding:14px 0;

            border-bottom:1px solid #eef0f3;
        }

        .info-row:last-child{
            border-bottom:none;
        }

        .info-label{
            color:#868e96;
            font-size:13px;
        }

        .info-value{
            font-weight:600;
            color:#212529;
            text-align:right;
        }

        .price-box{

            margin-top:24px;

            background:
            linear-gradient(
                135deg,
                #1f2328,
                #343a40
            );

            border-radius:14px;

            padding:30px;

            text-align:center;

            box-shadow:
            0 10px 25px rgba(0,0,0,.15);
        }

        .price-title{

            color:rgba(255,255,255,.75);

            font-size:14px;

            letter-spacing:1px;

            text-transform:uppercase;
        }

        .price-value{

            color:#f5c518;

            font-size:38px;

            font-weight:800;

            margin-top:6px;
        }

        .price-value{
            color:#fff;
            font-size:34px;
            font-weight:700;
        }

        .back-btn{

            background:#f7cf5b;

            color:#1f1f1f;

            border:none;

            border-radius:12px;

            padding:12px 24px;

            font-weight:700;

            text-decoration:none;

            display:inline-block;

            min-width:220px;

            margin-top:28px;

            transition:.2s;
        }

        .back-btn:hover{

            background:#f5c518 !important;

            transform:translateY(-2px);
        }

        .status-badge{
            display:inline-block;
            padding:5px 10px;
            border-radius:6px;
            font-size:12px;
            font-weight:700;
            letter-spacing:.3px;
        }

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

        .section-title{
            margin-top:28px;
            margin-bottom:14px;

            font-size:16px;
            font-weight:700;

            color:#212529;
        }

    </style>

</head>

<body>

<?php include '../includes/staff_sidebar.php'; ?>

<div class="page-container">

    <div class="details-card">

        <h1 class="page-title">
            Booking Details
        </h1>

        <p class="page-subtitle">
            View walk-in booking information
        </p>


        <!-- Movie Name -->
        <div class="booking-info">

            <div class="info-row">
                <span class="info-label">Booking Code</span>
                <span class="info-value">
                    <?= $booking['booking_code'] ?? 'N/A' ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Customer Name</span>
                <span class="info-value">
                    <?= $booking['customer_name'] ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Movie</span>
                <span class="info-value">
                    <?= $booking['title'] ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Show Date</span>
                <span class="info-value">
                    <?= date('d M Y', strtotime($booking['show_date'])) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Show Time</span>
                <span class="info-value">
                    <?= date('h:i A', strtotime($booking['show_time'])) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Seats</span>
                <span class="info-value">
                    <?= implode(', ', $seatNumbers) ?>
                </span>
            </div>

            <div class="info-row">
                <span class="info-label">Payment Status</span>

                <?php

                $status = strtolower($booking['payment_status']);

                $class = match($status){
                    'paid'      => 'status-badge status-paid',
                    'pending'   => 'status-badge status-pending',
                    'cancelled' => 'status-badge status-cancelled',
                    'expired'   => 'status-badge status-expired',
                    default     => 'status-badge status-pending'
                };

                ?>

                <span class="<?= $class ?>">
                    <?= strtoupper($booking['payment_status']) ?>
                </span>
            </div>

        </div>

        <h3 class="section-title">
            Ticket Summary
        </h3>

        <div class="booking-info mt-3">

            <?php if($booking['adult_qty'] > 0): ?>
                <div class="info-row">
                    <span class="info-label">Adult Ticket</span>
                    <span class="info-value">
                        <?= $booking['adult_qty'] ?> × RM12
                    </span>
                </div>
                <?php endif; ?>

                <?php if($booking['senior_qty'] > 0): ?>
                <div class="info-row">
                    <span class="info-label">Senior Ticket</span>
                    <span class="info-value">
                        <?= $booking['senior_qty'] ?> × RM8
                    </span>
                </div>
                <?php endif; ?>

                <?php if($booking['student_qty'] > 0): ?>
                <div class="info-row">
                    <span class="info-label">Student Ticket</span>
                    <span class="info-value">
                        <?= $booking['student_qty'] ?> × RM10
                    </span>
                </div>
                <?php endif; ?>

                <?php if($booking['children_qty'] > 0): ?>
                <div class="info-row">
                    <span class="info-label">Children Ticket</span>
                    <span class="info-value">
                        <?= $booking['children_qty'] ?> × RM6
                    </span>
                </div>
                <?php endif; ?>

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
        <div class="text-center mt-4">

            <a
                href="<?= BASE_URL ?>/staff/walkin_bookings.php"
                class="back-btn"
            >
                Back to Walk-In Bookings
            </a>

        </div>

    </div>

</div>

</body>
</html>