<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

// Get booking ID
$booking_id = $_GET['booking_id'] ?? 0;

// Validate booking ID
if (!$booking_id) {
    die("Booking ID required.");
}

// Get booking details
$booking = $conn->query("
    SELECT 
        wb.id,
        wb.booking_code,
        wb.customer_name,
        wb.payment_status,
        wb.qr_used,
        wb.adult_qty,
        wb.senior_qty,
        wb.student_qty,
        wb.children_qty,
        m.title,
        s.show_date,
        s.show_time,
        br.name AS branch_name,

        GROUP_CONCAT(
            (
                SELECT seat_number
                FROM seats
                WHERE id = wbs.seat_id
            )
            SEPARATOR ', '
        ) AS seats

    FROM walkin_bookings wb

    LEFT JOIN walkin_booking_seats wbs
    ON wb.id = wbs.walkin_booking_id

    LEFT JOIN seats se
    ON wbs.seat_id = se.id

    JOIN showtimes s
    ON wb.showtime_id = s.id

    JOIN movies m
    ON s.movie_id = m.id

    JOIN branches br
    ON s.branch_id = br.id

    WHERE wb.id = " . intval($booking_id) . "
    AND wb.payment_status = 'Paid'

    GROUP BY wb.id
")->fetch_assoc();

// Validate booking
if (!$booking) {
    die("Ticket not available.");
}

// QR expiry logic
$showDatetime = strtotime(
    $booking['show_date'] . ' ' . $booking['show_time']
);

$expiryTime = $showDatetime + (60 * 60);

$remaining = max(
    0,
    $expiryTime - time()
);

$isExpired = $remaining <= 0;

// Generate QR data
$qr_data = "WALKIN:" . $booking['booking_code'];

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qr_data);

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GSC Ticket</title>

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
        background:#f6f7fb;
        font-family:'Segoe UI',sans-serif;
        padding-left:280px;
    }

    .ticket-container{
        max-width:760px;
        margin:40px auto;
        padding:0 20px;
    }

    .ticket-card{
        background:#fff;

        border-radius:22px;

        overflow:hidden;

        border:1px solid rgba(0,0,0,.05);

        box-shadow:
        0 10px 25px rgba(0,0,0,.08);
    }

    .ticket-header{
        display:flex;
        align-items:center;
        justify-content:space-between;

        padding:22px 28px;

        background:
        linear-gradient(
            135deg,
            #1f1f1f,
            #2b2b2b
        );

        border-bottom:
        1px solid rgba(255,255,255,.06);

        color:#fff;
    }

    .ticket-header h1{
        font-size:20px;
        font-weight:800;
        letter-spacing:1px;

        display:flex;
        align-items:center;
        gap:10px;

        margin:0;
    }

    .ticket-header h1::before{
        content:"";

        width:10px;
        height:10px;

        background:#f5c518;

        border-radius:50%;

        box-shadow:
        0 0 12px rgba(245,197,24,.6);
    }

    .ticket-body{
        padding:28px;
    }

    .info-row{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:20px;
        border-bottom:1px solid rgba(0,0,0,0.08);
        padding:14px 0;
    }

    .label{
        color:#666;
    }

    .value{
        font-weight:700;
        color:#111;
        text-align:right;
        max-width:60%;
        word-break:break-word;
    }

    .qr-box{
        text-align:center;
        margin-top:35px;
    }

    .qr-box img{
        width:230px;
    }

    .ticket-id{
        margin-top:12px;
        font-size:14px;
        font-weight:700;
        color:#495057;
    }

    .btn-print{
        background:#f7cf5b !important;
        border:none !important;
        color:#1f1f1f !important;

        font-weight:700 !important;

        border-radius:10px !important;

        padding:10px 22px !important;

        transition:.2s;
    }

    .btn-print:hover{
        background:#f5c518 !important;
        transform:scale(1.03);
    }

    .btn-back{
        background:#f8f9fa;

        border:1px solid #dee2e6;

        color:#495057;

        font-weight:600;

        border-radius:10px;

        padding:10px 22px;

        text-decoration:none;
    }

    .btn-back:hover{
        background:#e9ecef;
    }

    @media print{

        .no-print{
            display:none !important;
        }

        .sidebar,
        aside,
        #sidebar,
        .staff-sidebar{
            display:none !important;
        }

        body{
            background:#fff !important;
            padding-left:0 !important;
        }

        .ticket-container{
            max-width:760px;
            margin:40px auto;
            padding:0 20px;
        }

        .ticket-card{
            box-shadow:none !important;
            border:1px solid #ddd !important;
        }

        .expiry-box{
            display:none !important;
        }

        .ticket-header{
            background:#1f1f1f !important;

            -webkit-print-color-adjust:exact;
            print-color-adjust:exact;
        }
    }

    .ticket-status{
        display:inline-flex;

        gap:8px;

        padding:8px 14px;

        border-radius:999px;

        font-size:12px;

        font-weight:800;

        letter-spacing:.6px;

        text-transform:uppercase;

        margin-bottom:18px;

        box-shadow:
        0 6px 18px rgba(0,0,0,.08);
    }

    .ticket-status.valid{
        background:
        linear-gradient(
            135deg,
            #e7f8ee,
            #d2f5df
        );

        color:#1e7e34;

        border:
        1px solid rgba(30,126,52,.15);
    }

    .ticket-status.used{
        background:
        linear-gradient(
            135deg,
            #fde8e8,
            #f8cfcf
        );

        color:#c92a2a;

        border:
        1px solid rgba(201,42,42,.15);
    }

    .ticket-status.expired{
        background:
        linear-gradient(
            135deg,
            #fff4db,
            #ffe6a6
        );

        color:#8a6d00;

        border:
        1px solid rgba(138,109,0,.15);
    }

    .expiry-box{
        display:inline-flex;
        align-items:center;
        gap:8px;

        background:linear-gradient(
            135deg,
            #fff8e1,
            #ffe9a8
        );

        color:#8a6d00;

        padding:10px 18px;

        border-radius:999px;

        font-size:13px;
        font-weight:700;

        border:1px solid rgba(138,109,0,.15);

        margin-bottom:18px;

        box-shadow:
        0 6px 18px rgba(255,193,7,.15);
    }

    .qr-box{
        text-align:center;

        margin-top:30px;
        padding-top:24px;

        border-top:1px solid #eef1f5;
    }

    .qr-box img{
        width:220px;
        display:block;
        margin:0 auto;
    }

    </style>

</head>

<body class="staff-page walkin-ticket-page">

<?php include '../includes/staff_sidebar.php'; ?>

<div class="ticket-container">

    <div class="ticket-card">

        <!-- Header -->
        <div class="ticket-header">

            <h1>
                GSC E-Ticket
            </h1>

        </div>

        <div class="ticket-body">

            <!-- Ticket Status -->
            <?php if($booking['qr_used'] == 1): ?>

                <div class="ticket-status used">
                    ❌ TICKET USED
                </div>

            <?php elseif($isExpired): ?>

                <div class="ticket-status expired">
                    ⌛ QR CODE EXPIRED
                </div>

            <?php else: ?>

                <div class="ticket-status valid">
                    ✅ VALID TICKET
                </div>

            <?php endif; ?>

            <!-- Customer -->
            <div class="info-row">

                <span class="label">
                    Customer
                </span>

                <span class="value">
                    <?= htmlspecialchars($booking['customer_name']) ?>
                </span>

            </div>

            <!-- Movie -->
            <div class="info-row">

                <span class="label">
                    Movie
                </span>

                <span class="value">
                    <?= htmlspecialchars($booking['title']) ?>
                </span>

            </div>

            <!-- Cinema -->
            <div class="info-row">

                <span class="label">
                    Cinema
                </span>

                <span class="value">
                    <?= htmlspecialchars($booking['branch_name']) ?>
                </span>

            </div>

            <!-- Date -->
            <div class="info-row">

                <span class="label">
                    Date
                </span>

                <span class="value">
                    <?= date('d M Y', strtotime($booking['show_date'])) ?>
                </span>

            </div>

            <!-- Time -->
            <div class="info-row">

                <span class="label">
                    Time
                </span>

                <span class="value">
                    <?= date('h:i A', strtotime($booking['show_time'])) ?>
                </span>

            </div>

            <!-- Seats -->
            <div class="info-row">

                <span class="label">
                    Seats
                </span>

                <span class="value">
                    <?= htmlspecialchars($booking['seats']) ?>
                </span>

            </div>

            <?php if($booking['adult_qty'] > 0): ?>
            <div class="info-row">
                <span class="label">Adult</span>
                <span class="value"><?= $booking['adult_qty'] ?></span>
            </div>
            <?php endif; ?>

            <?php if($booking['senior_qty'] > 0): ?>
            <div class="info-row">
                <span class="label">Senior</span>
                <span class="value"><?= $booking['senior_qty'] ?></span>
            </div>
            <?php endif; ?>

            <?php if($booking['student_qty'] > 0): ?>
            <div class="info-row">
                <span class="label">Student</span>
                <span class="value"><?= $booking['student_qty'] ?></span>
            </div>
            <?php endif; ?>

            <?php if($booking['children_qty'] > 0): ?>
            <div class="info-row">
                <span class="label">Children</span>
                <span class="value"><?= $booking['children_qty'] ?></span>
            </div>
            <?php endif; ?>

            
            <?php

            // Countdown time
            $hours = floor($remaining / 3600);

            $minutes = floor(
                ($remaining % 3600) / 60
            );

            $seconds = $remaining % 60;

            ?>

            <!-- QR Section -->
            <div class="qr-box">

                <!-- Countdown -->
                <?php if(!$isExpired && $booking['qr_used'] == 0): ?>

                   <div class="expiry-box">

                        ⏳ QR expires in:

                        <span id="countdown">

                            <?= $hours ?>h
                            <?= $minutes ?>m
                            <?= $seconds ?>s

                        </span>

                    </div>

                <?php endif; ?>

                <!-- QR Image -->
                <img
                    src="<?= $qr_url ?>"
                    alt="QR Code"
                >

                <!-- Ticket ID -->
                <div class="ticket-id">

                    Ticket #
                    <?= htmlspecialchars($booking['booking_code']) ?>

                </div>

            </div>

            <!-- Buttons -->
            <div class="text-center mt-5 no-print">

                <button
                    onclick="window.print()"
                    class="btn btn-print"
                >
                    🖨️ Print Ticket
                </button>

                <a
                    href="<?= BASE_URL ?>/staff/walkin_bookings.php"
                    class="btn btn-back ms-2"
                >
                    Back
                </a>

            </div>

        </div>

    </div>

</div>

<script>

// Remaining seconds
let remaining = <?= max($remaining,0) ?>;

// Countdown element
const countdownEl = document.getElementById('countdown');

// Update countdown
function updateCountdown(){

    if(!countdownEl) return;

    if(remaining <= 0){

        countdownEl.innerHTML = "Expired";

        return;
    }

    let hours = Math.floor(remaining / 3600);

    let minutes = Math.floor(
        (remaining % 3600) / 60
    );

    let seconds = remaining % 60;

    countdownEl.innerHTML =
        hours + "h " +
        minutes + "m " +
        seconds + "s";

    remaining--;
}

// Start countdown
updateCountdown();

setInterval(updateCountdown, 1000);

</script>

</body>
</html>