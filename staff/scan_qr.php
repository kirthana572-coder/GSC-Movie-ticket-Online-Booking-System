<?php

// Include authentication and database connection
require_once '../includes/staff_auth.php';
require_once '../config/db.php';


// Initialize variables
$error = '';
$success = '';
$booking_id = '';


// Check form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form data
    $type = $_POST['type'] ?? '';
    $booking_id = $_POST['booking_id'] ?? '';


    // Validate booking ID
    if (!$booking_id) {

        $error = "Booking ID required.";

    } else {

        // =========================
        // Validate Online Booking
        // =========================
        if ($type == 'BOOKING') {

            $result = $conn->query("
                SELECT qr_used, payment_status
                FROM bookings
                WHERE id = " . intval($booking_id)
            );

            if ($row = $result->fetch_assoc()) {

                if ($row['payment_status'] !== 'Paid') {

                    $error = "Ticket not paid.";

                } elseif ($row['qr_used'] == 1) {

                    $error = "Ticket already used.";

                } else {

                    // Update QR status
                    $conn->query("
                        UPDATE bookings
                        SET qr_used = 1
                        WHERE id = " . intval($booking_id)
                    );

                    $success = "Online ticket validated.";
                }

            } else {

                $error = "Invalid online booking.";
            }


        // =========================
        // Validate Walk-in Booking
        // =========================
        } elseif ($type == 'WALKIN') {

            $result = $conn->query("
                SELECT qr_used, payment_status
                FROM walkin_bookings
                WHERE booking_code = '$booking_id'
            ");

            if ($row = $result->fetch_assoc()) {

                if ($row['payment_status'] !== 'Paid') {

                    $error = "Ticket not paid.";

                } elseif ($row['qr_used'] == 1) {

                    $error = "Ticket already used.";

                } else {

                    // Update QR status
                    $conn->query("
                        UPDATE walkin_bookings
                        SET qr_used = 1
                        WHERE booking_code = '$booking_id'
                    ");

                    $success = "Walk-in ticket validated.";
                }

            } else {

                $error = "Invalid walk-in booking.";
            }

        } else {

            $error = "Invalid QR Code.";
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Scan QR Ticket - GSC</title>

    <!-- Bootstrap -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <!-- QR Scanner -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

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
            background:#f6f7fb;
            min-height:100vh;
        }

        .container-custom{
            margin-left:280px;

            padding:40px;

            display:flex;
            justify-content:center;

            box-sizing:border-box;
        }

        .card{
            width:100%;
            max-width:720px !important;

            background:#fff;

            border-radius:22px;

            padding:30px !important;

            border:1px solid rgba(0,0,0,.05);

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);
        }

        .page-title{
            font-size:28px;
            font-weight:800;
            color:#1f1f1f;
            text-align:center;
            margin-bottom:10px;
        }

        .page-subtitle{
            text-align:center;
            color:#6c757d;

            margin-bottom:30px;

            position:relative;
            padding-bottom:18px;
        }

        .page-subtitle::after{
            content:"";

            position:absolute;

            left:50%;
            bottom:0;

            transform:translateX(-50%);

            width:70px;
            height:2px;

            background:#dee2e6;

            border-radius:999px;
        }

        /* QR Scanner */
        #reader{
            width:100% !important;

            border:1px solid #e9ecef;
            border-radius:16px;

            overflow:hidden;

            padding:15px;

            box-sizing:border-box;
        }


        #reader video{
            width:100% !important;
            border-radius:12px;
        }

        #reader img{
            max-width:100% !important;
        }

        #reader__scan_region{
            height:240px !important;
        }

        #reader__dashboard{
            padding-top:15px;
        }

        .manual-box{
            background:#f8f9fa;

            border:1px solid #eef1f5;

            border-radius:16px;

            padding:20px;

            margin-top:20px;
        }

        .manual-title{
            text-align:center;
            font-size:16px;
            font-weight:700;
            color:#212529;
            margin-bottom:6px;
        }

        .manual-subtitle{
            text-align:center;
            font-size:13px;
            color:#6c757d;
            margin-bottom:20px;
        }

        .form-control,
        .form-select{
            border-radius:12px !important;

            padding:12px 14px !important;

            border:1px solid #e9ecef !important;

            box-shadow:none !important;
        }

        .form-control:focus,
        .form-select:focus{
            border-color:#f5c518 !important;

            box-shadow:
            0 0 0 .15rem rgba(245,197,24,.25)
            !important;
        }

        .btn-validate{
            background:#f7cf5b !important;

            color:#1f1f1f !important;

            border:none !important;

            border-radius:12px !important;

            font-weight:700 !important;

            transition:.2s ease;
        }

        .btn-validate:hover{
            background:#f5c518 !important;

            transform:translateY(-2px);
        }

        .alert{
            border:none;
            border-radius:14px;
            font-weight:600;
        }

        .alert-success{
            background:#e7f8ee;
            color:#1e7e34;
        }

        .alert-danger{
            background:#fde8e8;
            color:#c92a2a;
        }

        .manual-form{
            display:flex;
            gap:12px;
            align-items:center;
        }

        .manual-form .form-select{
            width:180px;
            flex:none;
        }

        .manual-form .form-control{
            flex:1;
        }

        .btn-validate{
            min-width:120px;
            height:48px;
        }


    </style>

</head>

<body class="staff-page scan-qr-page">

<?php include '../includes/staff_sidebar.php'; ?>

<div class="container-custom">

    <div class="card">

        <h1 class="page-title">Scan QR Ticket</h1>

        <p class="page-subtitle">
            Scan customer QR ticket for cinema entry validation
        </p>


        <!-- QR Scanner -->
        <div class="scanner-wrapper">

            <div id="reader"></div>

            <div class="scanner-overlay">

                <span class="corner top-left"></span>
                <span class="corner top-right"></span>
                <span class="corner bottom-left"></span>
                <span class="corner bottom-right"></span>

                <div class="scan-line"></div>

            </div>

        </div>

        <hr>


        <!-- Manual Validation -->
        <div class="manual-box">

            <p class="manual-title">
                Manual Ticket Validation
            </p>

            <p class="manual-subtitle">
                Validate customer tickets using Booking ID or Booking Code.
            </p>

            <form method="POST">

                <div class="manual-form">

                    <select name="type" class="form-select">

                        <option value="BOOKING">Online</option>

                        <option value="WALKIN">Walk-in</option>

                    </select>

                    <input
                        type="text"
                        name="booking_id"
                        class="form-control"
                        placeholder="Booking ID / Booking Code"
                        value="<?= htmlspecialchars($booking_id) ?>"
                        required
                    >

                    <button type="submit" class="btn btn-validate">
                        Validate
                    </button>

                </div>

            </form>

        </div>


        <!-- Error Message -->
        <?php if($error): ?>

            <div class="alert alert-danger mt-3">
                <?= $error ?>
            </div>

        <?php endif; ?>


        <!-- Success Message -->
        <?php if($success): ?>

            <div class="alert alert-success mt-3">
                <?= $success ?>
            </div>

        <?php endif; ?>

    </div>

</div>


<script>

    // Initialize QR scanner
    const html5QrCode = new Html5Qrcode("reader");


    // QR scan success
    const qrCodeSuccessCallback = (decodedText, decodedResult) => {

        html5QrCode.stop();

        let type = '';
        let bookingId = '';


        // Detect online booking QR
        if (decodedText.startsWith('BOOKING:')) {

            type = 'BOOKING';
            bookingId = decodedText.replace('BOOKING:', '');

        }

        // Detect walk-in QR
        else if (decodedText.startsWith('WALKIN:')) {

            type = 'WALKIN';
            bookingId = decodedText.replace('WALKIN:', '');

        }

        // Invalid QR
        else {

            alert('Invalid QR Code');
            return;
        }


        // Send validation request
        const formData = new FormData();

        formData.append('type', type);
        formData.append('booking_id', bookingId);

        fetch(window.location.href, {
            method:'POST',
            body:formData
        })

        .then(response => response.text())

        .then(html => {

            document.open();
            document.write(html);
            document.close();

        })

        .catch(err => console.error(err));
    };


    // Scanner configuration
    const config = {
        fps:10,
        qrbox:{
            width:300,
            height:300
        }
    };


    // Start scanner
    html5QrCode.start(
        { facingMode:"environment" },
        config,
        qrCodeSuccessCallback
    )

    .catch(err => console.log("Unable to start scanning", err));

</script>

</body>
</html>