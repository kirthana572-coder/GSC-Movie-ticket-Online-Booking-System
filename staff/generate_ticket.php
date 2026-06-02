<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';


// Get booking ID
$booking_id = $_GET['booking_id'] ?? 0;


// Check booking ID
if (!$booking_id) {
    die("Booking ID required.");
}


// Get booking details
$booking = $conn->query("
    SELECT 
        b.id,
        b.payment_status,
        b.qr_used,
        b.booking_date,
        u.full_name,
        m.title,
        s.show_date,
        s.show_time,
        br.name AS branch_name,

        GROUP_CONCAT(
            CONCAT(se.seat_number,' (',bs.ticket_type,')')
            SEPARATOR ', '
        ) AS seats

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

    WHERE b.id = " . intval($booking_id) . "
    AND b.payment_status = 'Paid'

    GROUP BY b.id

")->fetch_assoc();


// Show error if ticket not found
if (!$booking) {
    die("Ticket not available.");
}


// Calculate expiry time
$showDatetime = strtotime(
    $booking['show_date'] . ' ' . $booking['show_time']
);

$expiryTime = $showDatetime + (60 * 60);

$remaining = max(0, $expiryTime - time());

$isExpired = $remaining <= 0;


// Generate QR code
$qr_data = "BOOKING:" . $booking['id'];

$qr_data = "BOOKING_ID:" . intval($booking['id']);

$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qr_data);

?>

<!DOCTYPE html>
<html>

<head>

    <title>GSC Ticket</title>

    <!-- Bootstrap CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <style>

       body{
            margin:0;
            background:#f6f7fb;
            font-family:'Segoe UI', sans-serif;
            padding-left:280px; 
        }

        /* container */
        .ticket-container{
            max-width:760px;
            margin:40px auto;
            padding:0 20px;
        }

        /* card */
        .ticket-card{
            background:#fff;
            border-radius:22px;
            overflow:hidden;

            box-shadow:0 10px 25px rgba(0,0,0,.08);
            border:1px solid rgba(0,0,0,.05);
        }

        /* header  */
        .ticket-header{
            display:flex;
            align-items:center;
            justify-content:space-between;

            padding:22px 28px;

            background: linear-gradient(135deg, #1f1f1f, #2b2b2b);

            border-bottom:1px solid rgba(255,255,255,.06);

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

        /* small brand dot */
        .ticket-header h1::before{
            content:"";
            width:10px;
            height:10px;
            background:#f5c518;
            border-radius:50%;
            display:inline-block;
            box-shadow:0 0 12px rgba(245,197,24,.6);
        }

        /* body */
        .ticket-body{
            padding:28px;
        }

        /* info rows  */
        .info-row{
            display:flex;
            justify-content:space-between;
            align-items:center;

            padding:14px 0;
            border-bottom:1px solid #eef1f5;
        }

        .label{
            color:#5f6b76;
            font-size:12.5px;
            font-weight:500;
        }

        .value{
            font-weight:600;
            color:#212529;
            text-align:right;
            padding-left:20px;
        }

        /* status box */
        .ticket-status{
            display:inline-flex;
            align-items:center;
            gap:8px;

            padding:8px 14px;
            border-radius:999px;

            font-size:12px;
            font-weight:800;
            letter-spacing:.6px;

            text-transform:uppercase;

            box-shadow:0 6px 18px rgba(0,0,0,.08);

            margin-bottom:18px;
        }

        /* VALID */
        .ticket-status.valid{
            background:linear-gradient(135deg,#e7f8ee,#d2f5df);
            color:#1e7e34;
            border:1px solid rgba(30,126,52,.15);
        }

        /* USED */
        .ticket-status.used{
            background:linear-gradient(135deg,#fde8e8,#f8cfcf);
            color:#c92a2a;
            border:1px solid rgba(201,42,42,.15);
        }

        /* EXPIRED */
        .ticket-status.expired{
            background:linear-gradient(135deg,#fff4db,#ffe6a6);
            color:#8a6d00;
            border:1px solid rgba(138,109,0,.15);
        }

        /* expiry */
        .expiry-box{
            background:#fff8e1;
            color:#b08900;
            padding:10px 16px;
            border-radius:12px;
            font-weight:700;
            margin:0 auto 18px;
            width:fit-content;
            font-size:14px;
        }

        /* QR */
        .qr-box{
            text-align:center;
            margin-top:28px;
            padding-top:22px;
            border-top:1px solid #eef1f5;
        }
        .qr-box img{
            width:220px;
        }

        /* ticket id */
        .ticket-id{
            margin-top:12px;
            font-size:14px;
            font-weight:700;
            color:#495057;
        }

        /* buttons */
        .btn-print{
            background:#f7cf5b !important; 
            border:none !important;
            color:#1f1f1f !important;

            font-weight:700 !important;
            border-radius:10px !important;

            padding:10px 22px !important;

            transition:0.2s ease;
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

        /* print */
        @media print{
            .no-print{ display:none !important; }

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
                margin:0 auto !important;
                max-width:760px;
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
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

    </style>

</head>

<body>

<?php include '../includes/staff_sidebar.php'; ?>

    <div class="ticket-container">

        <div class="ticket-card">

            <!-- Ticket header -->
            <div class="ticket-header">

                <h1>
                    GSC E-Ticket
                </h1>

            </div>


            <div class="ticket-body">

                <!-- Ticket status -->
                <?php if ($booking['qr_used'] == 1): ?>

                    <div class="ticket-status used">
                        ❌ TICKET USED
                    </div>

                <?php elseif ($isExpired): ?>

                    <div class="ticket-status used">
                        ⌛ QR CODE EXPIRED
                    </div>

                <?php else: ?>

                    <div class="ticket-status valid">
                        ✅ VALID TICKET
                    </div>

                <?php endif; ?>


                <!-- Ticket information -->
                <div class="info-row">

                    <span class="label">
                        Customer
                    </span>

                    <span class="value">
                        <?= htmlspecialchars($booking['full_name']) ?>
                    </span>

                </div>

                <div class="info-row">

                    <span class="label">
                        Movie
                    </span>

                    <span class="value">
                        <?= htmlspecialchars($booking['title']) ?>
                    </span>

                </div>

                <div class="info-row">

                    <span class="label">
                        Cinema
                    </span>

                    <span class="value">
                        <?= htmlspecialchars($booking['branch_name']) ?>
                    </span>

                </div>

                <div class="info-row">

                    <span class="label">
                        Date
                    </span>

                    <span class="value">
                        <?= date('d M Y', strtotime($booking['show_date'])) ?>
                    </span>

                </div>

                <div class="info-row">

                    <span class="label">
                        Time
                    </span>

                    <span class="value">
                        <?= date('h:i A', strtotime($booking['show_time'])) ?>
                    </span>

                </div>

                <div class="info-row">

                    <span class="label">
                        Seats
                    </span>

                    <span class="value">
                        <?= htmlspecialchars($booking['seats']) ?>
                    </span>

                </div>


                <?php

                // Countdown calculation
                $hours = floor($remaining / 3600);

                $minutes = floor(($remaining % 3600) / 60);

                $seconds = $remaining % 60;

                ?>


                <!-- QR section -->
                <div class="qr-box">

                    <!-- Countdown -->
                    <?php if (!$isExpired && $booking['qr_used'] == 0): ?>

                        <div class="expiry-box">

                            ⏳ QR expires in:

                            <span id="countdown">
                                <?= $hours ?>h
                                <?= $minutes ?>m
                                <?= $seconds ?>s
                            </span>

                        </div>

                    <?php endif; ?>


                    <!-- QR image -->
                    <img 
                        src="<?= $qr_url ?>" 
                        style="
                            width:230px;
                            display:block;
                            margin:0 auto;
                        "
                    >


                    <!-- Ticket ID -->
                    <div class="ticket-id">

                        Ticket #<?= $booking['id'] ?>

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
                        href="<?= BASE_URL ?>/staff/customer_bookings.php"
                        class="btn btn-back ms-2"
                    >
                        Back
                    </a>

                </div>

            </div>

        </div>

    </div>


    <script>

        // Countdown timer
        let remaining = <?= max($remaining,0) ?>;

        const countdownEl = document.getElementById('countdown');


        function updateCountdown(){

            if(!countdownEl) return;

            if(remaining <= 0){

                countdownEl.innerHTML = "Expired";

                return;
            }

            let hours = Math.floor(remaining / 3600);

            let minutes = Math.floor((remaining % 3600) / 60);

            let seconds = remaining % 60;

            countdownEl.innerHTML =
                hours + "h " +
                minutes + "m " +
                seconds + "s";

            remaining--;
        }

        updateCountdown();

        setInterval(updateCountdown, 1000);

    </script>

</body>
</html>