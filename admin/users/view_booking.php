<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

$booking_id = intval($_GET['id'] ?? 0);

if(!$booking_id){

    die("Invalid Booking.");
}

$booking = $conn->query("

    SELECT

        b.id,
        b.user_id,
        b.payment_status,
        b.booking_date,
        b.ticket_status,

        b.cancel_reason,
        b.cancelled_by,
        b.cancelled_at,

        u.full_name,
        u.email,

        m.title,

        s.show_date,
        s.show_time,

        br.name AS branch_name,

        GROUP_CONCAT(

            CONCAT(
                se.seat_number,
                ' (',
                bs.ticket_type,
                ')'
            )

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

    JOIN users u
    ON b.user_id = u.id

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

    WHERE b.id = {$booking_id}

    GROUP BY b.id

")->fetch_assoc();


if(!$booking){

    die("Booking not found.");
}

?>

<!DOCTYPE html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
    Booking Details - GSC
</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<!-- Bootstrap Icons -->
<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
<style>

body{

    margin:0;

    font-family:'Segoe UI',sans-serif;

    background:
    linear-gradient(
        135deg,
        #f8fafc,
        #eef2ff
    );

    min-height:100vh;
}

.main{

    margin-left:260px;
    padding:40px;
}

.page-title{

    font-size:48px;
    font-weight:800;

    color:#111827;

    letter-spacing:-1px;

    text-align:center;

    margin-bottom:50px;
}

.card-box{

    background:white;

    border-radius:24px;

    padding:30px;

    box-shadow:
    0 10px 25px rgba(0,0,0,.08);

    max-width:900px;

    margin:0 auto;
}

.info-row{

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:14px 0;

    border-bottom:
    1px solid #f1f5f9;
}

.info-label{

    color:#64748b;
}

.info-value{

    font-weight:600;
    color:#111827;
}

.status{

    padding:8px 14px;

    border-radius:999px;

    font-size:13px;

    font-weight:700;
}

.paid{

    background:#dcfce7;
    color:#166534;
}

.pending{

    background:#fef3c7;
    color:#92400e;
}

.cancelled{

    background:#fee2e2;
    color:#991b1b;
}

.back-btn{

    background:#f3f4f6;

    color:#111827;

    font-weight:600;

    padding:15px 30px;

    border-radius:18px;

    text-decoration:none;

    transition:0.25s;

    border:1px solid #e5e7eb;

    margin-top: 30px;

    display: inline-block;

}

.back-btn:hover{

    background:#e5e7eb;

    color:#111827;

    transform:translateY(-2px);
}

.section-title{

    font-size:22px;

    font-weight:700;

    color:#111827;

    margin-bottom:20px;
}

.back-wrapper{

    max-width:900px;

    margin:20px auto 0;

    display:flex;

    justify-content:center;
}

</style>

</head>

<body class = "admin-page admin-view-booking-page">

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

<div class="card-box">

<h1 class="page-title text-center">

    Booking Details

</h1>

    <div class="info-row">

        <span class="info-label">
            Booking ID
        </span>

        <span class="info-value">
            #<?= $booking['id'] ?>
        </span>

    </div>

    <div class="info-row">

        <span class="info-label">
            Movie
        </span>

        <span class="info-value">
            <?= htmlspecialchars($booking['title']) ?>
        </span>

    </div>

    <div class="info-row">

        <span class="info-label">
            Branch
        </span>

        <span class="info-value">
            <?= htmlspecialchars($booking['branch_name']) ?>
        </span>

    </div>

    <div class="info-row">

        <span class="info-label">
            Show Date
        </span>

        <span class="info-value">
            <?= date('d M Y', strtotime($booking['show_date'])) ?>
        </span>

    </div>

    <div class="info-row">

        <span class="info-label">
            Show Time
        </span>

        <span class="info-value">
            <?= date('h:i A', strtotime($booking['show_time'])) ?>
        </span>

    </div>

    <div class="info-row">

        <span class="info-label">
            Seats
        </span>

        <span class="info-value">
            <?= htmlspecialchars($booking['seats'] ?: 'No Seats') ?>
        </span>

    </div>

    <div class="info-row">

        <span class="info-label">
            Total Price
        </span>

        <span class="info-value">
            RM <?= number_format($booking['total_price'],2) ?>
        </span>

    </div>

<?php

$statusClass = 'pending';

if(strtolower($booking['payment_status']) == 'paid'){
    $statusClass = 'paid';
}

if(strtolower($booking['payment_status']) == 'cancelled'){
    $statusClass = 'cancelled';
}

?>

    <div class="info-row">

        <span class="info-label">
            Payment Status
        </span>

        <span class="status <?= $statusClass ?>">
            <?= htmlspecialchars($booking['payment_status']) ?>
        </span>

    </div>

    <div class="info-row">

        <span class="info-label">
            Booking Date
        </span>

        <span class="info-value">
            <?= date('d M Y h:i A', strtotime($booking['booking_date'])) ?>
        </span>

    </div>

<?php if($booking['payment_status'] == 'Cancelled'): ?>

    <div class="info-row">

        <span class="info-label">
            Cancel Reason
        </span>

        <span class="info-value text-danger">
            <?= htmlspecialchars($booking['cancel_reason'] ?: 'No reason provided') ?>
        </span>

    </div>

    <div class="info-row">

        <span class="info-label">
            Cancelled By
        </span>

        <span class="info-value">
            <?= htmlspecialchars($booking['cancelled_by'] ?: '-') ?>
        </span>

    </div>

    <?php if($booking['cancelled_at']): ?>

    <div class="info-row">

        <span class="info-label">
            Cancelled At
        </span>

        <span class="info-value">
            <?= date('d M Y h:i A', strtotime($booking['cancelled_at'])) ?>
        </span>

    </div>

    <?php endif; ?>

<?php endif; ?>

    <div class="back-wrapper">

    <a
        href="view_user.php?id=<?= $booking['user_id'] ?>"
        class="back-btn"
    >
        Back
    </a>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>