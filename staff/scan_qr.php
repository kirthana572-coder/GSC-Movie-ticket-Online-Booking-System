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

    <title>Scan QR Ticket - GSC</title>

    <!-- Bootstrap -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <!-- QR Scanner -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <style>

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(
                rgba(245,242,234,0.95),
                rgba(255,220,164,0.92)
            );
            min-height:100vh;
        }

        .container-custom{
            max-width:700px;
            margin:50px auto;
            padding:20px;
        }

        .card{
            border:none;
            border-radius:30px;
            background:rgba(255,255,255,0.88);
            padding:40px;
            box-shadow:0 10px 35px rgba(0,0,0,0.15);
        }

        .page-title{
            text-align:center;
            font-size:40px;
            font-weight:800;
            color:#f5c518;
            margin-bottom:8px;
        }

        .page-subtitle{
            text-align:center;
            color:#777;
            margin-bottom:35px;
        }

        #reader{
            width:100%;
            overflow:hidden;
            border-radius:25px;
            border:4px solid rgba(245,197,24,0.4);
            margin-bottom:30px;
            box-shadow:0 5px 20px rgba(0,0,0,0.08);
        }

        .manual-box{
            background:rgba(255,255,255,0.7);
            border-radius:20px;
            padding:25px;
            margin-top:20px;
        }

        .form-control{
            border-radius:15px !important;
            padding:14px !important;
            border:1px solid rgba(0,0,0,0.1) !important;
            box-shadow:none !important;
        }

        .form-control:focus{
            border-color:#f5c518 !important;
            box-shadow:0 0 0 0.15rem rgba(245,197,24,0.25) !important;
        }

        .btn-validate{
            background:#f5c518 !important;
            color:#111 !important;
            border:none !important;
            border-radius:15px !important;
            padding:12px 22px !important;
            font-weight:700 !important;
            transition:0.25s;
        }

        .btn-validate:hover{
            background:#ffd83d !important;
            transform:scale(1.03);
        }

        .alert{
            border-radius:18px;
            border:none;
            padding:18px;
            font-weight:600;
        }

        .alert-danger{
            background:rgba(220,53,69,0.12);
            color:#b02a37;
        }

        .alert-success{
            background:rgba(25,135,84,0.12);
            color:#146c43;
        }

        .btn-back{
            background:#2f2f2f !important;
            color:white !important;
            border:none !important;
            border-radius:16px !important;
            padding:12px 28px !important;
            font-weight:700 !important;
            transition:0.25s;
        }

        .btn-back:hover{
            background:#f5c518 !important;
            color:#111 !important;
            transform:scale(1.03);
        }

    </style>

</head>

<body>

<div class="container-custom">

    <div class="card">

        <h1 class="page-title">Scan QR Ticket</h1>

        <p class="page-subtitle">
            Scan customer QR ticket for cinema entry validation
        </p>


        <!-- QR Scanner -->
        <div id="reader"></div>

        <hr>


        <!-- Manual Validation -->
        <div class="manual-box">

            <p class="text-center fw-bold mb-4">
                Or Enter Booking ID Manually
            </p>

            <form method="POST">

                <div class="input-group mb-3">

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


        <!-- Back Button -->
        <div class="text-center mt-3">

            <a 
                href="<?= BASE_URL ?>/staff/staff_dashboard.php" 
                class="btn btn-back"
            >
                Back
            </a>

        </div>

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
            width:250,
            height:250
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