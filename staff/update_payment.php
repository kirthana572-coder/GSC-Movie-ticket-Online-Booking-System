<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';


// Check form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data
    $booking_id = intval($_POST['booking_id']);
    $payment_status = $_POST['payment_status'];


    // Allow Paid status only
    if ($payment_status !== 'Paid') {
        die("Invalid status change.");
    }


    // Check booking status
    $check = $conn->query("
        SELECT payment_status, user_id
        FROM bookings
        WHERE id = $booking_id
    ");

    $row = $check->fetch_assoc();


    // Validate booking
    if (!$row || $row['payment_status'] !== 'Pending') {
        die("Booking cannot be updated.");
    }


    // Update payment status
    $stmt = $conn->prepare("
        UPDATE bookings
        SET payment_status = 'Paid'
        WHERE id = ?
    ");

    if ($stmt->execute()) {

    // UPDATE SEATS TO BOOKED

        $seatStmt = $conn->prepare("

            UPDATE seats s

            JOIN booking_seats bs
            ON s.id = bs.seat_id

            SET s.status = 'booked'

            WHERE bs.booking_id = ?

        ");

        $seatStmt->bind_param(
            "i",
            $booking_id
        );

        $seatStmt->execute();

        $seatStmt->close();


        // Get user ID

        $user_id = $row['user_id'];

        $msg =
            "Your booking #$booking_id has been paid. You can now download your QR ticket.";

        $conn->query("
            INSERT INTO notifications (user_id, message)
            VALUES ($user_id, '$msg')
        ");

        echo "
        <script>
            alert('Payment status updated successfully!');
            window.location.href='" . BASE_URL . "/staff/staff_dashboard.php';
        </script>
        ";

    } else {

        // Failed message
        echo "
        <script>
            alert('Failed to update.');
            window.history.back();
        </script>
        ";
    }

    $stmt->close();
    exit;
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Update Payment Status - GSC</title>

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
            background:#f6f7fb;
            min-height:100vh;
        }

        /* center container */
        .page-container{
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:40px;
            margin-left:280px;
        }

        /* card - match ticket style */
        .payment-card{
            width:100%;
            max-width:620px;

            background:#ffffff;

            border-radius:22px;

            padding:45px;

            box-shadow:0 10px 25px rgba(0,0,0,.08);

            border:1px solid rgba(0,0,0,.05);
        }

        /* title */
        .page-title{
            font-size:28px;
            font-weight:800;
            color:#1f1f1f;
            text-align:center;
            margin-bottom:12px;
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

        /* label */
        .form-label{
            font-weight:600;
            color:#495057;
            margin-bottom:8px;
            font-size:13px;
        }

        /* inputs */
        .form-control,
        .form-select{
            border-radius:12px;
            padding:12px 14px;

            border:1px solid #e9ecef;

            box-shadow:none;
        }

        .form-control:focus,
        .form-select:focus{
            border-color:#f5c518;
            box-shadow:0 0 0 0.15rem rgba(245,197,24,0.25);
        }

        /* buttons layout */
        .button-group{
            text-align:center;
            margin-top:32px;
        }

        /* primary button */
        .btn-update{
            min-width:220px;
            height:50px;

            border:none;
            border-radius:12px;

            font-size:15px;
            font-weight:700;
            letter-spacing:.3px;

            transition:all .2s ease;
        }

        /* Enabled */
        .btn-update:not(:disabled){
            background:#f7cf5b;
            color:#1f1f1f;
            cursor:pointer;

            box-shadow:0 4px 12px rgba(245,197,24,.25);
        }

        .btn-update:not(:disabled):hover{
            background:#f5c518;
            transform:translateY(-2px);

            box-shadow:0 8px 18px rgba(245,197,24,.35);
        }

        .btn-update:not(:disabled):active{
            transform:translateY(0);
        }

        /* Disabled */
        .btn-update:disabled{
            background:#e9ecef;
            color:#adb5bd;
            cursor:not-allowed;
            box-shadow:none;
        }


    </style>

</head>

<body class="staff-page staff-payment-status-page">
    
<?php include '../includes/staff_sidebar.php'; ?>

<div class="page-container">

    <div class="payment-card">

        <h1 class="page-title">
            Update Payment Status
        </h1>

        <p class="page-subtitle">
            Staff can update customer payment records here.
        </p>

        <form method="POST">

            <div class="mb-4">

                <label class="form-label">
                    Booking ID
                </label>

                <input
                    type="text"
                    id="bookingId"
                    name="booking_id"
                    class="form-control"
                    placeholder="Enter booking ID"
                >

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Payment Status
                </label>

                <select
                    id="paymentStatus"
                    name="payment_status"
                    class="form-select"
                    required
                >
                    <option selected disabled>
                        Select Status
                    </option>

                    <option>
                        Paid
                    </option>

                </select>

            </div>

            <div class="button-group">

                <button
                    type="submit"
                    class="btn-update"
                    id="updateBtn"
                    disabled
                >
                    Mark as Paid
                </button>


            </div>

        </form>

    </div>

</div>

<script>

const bookingId =
    document.getElementById('bookingId');

const paymentStatus =
    document.getElementById('paymentStatus');

const updateBtn =
    document.getElementById('updateBtn');

function validateForm(){

    const bookingFilled =
        bookingId.value.trim() !== '';

    const statusSelected =
        paymentStatus.value === 'Paid';

    updateBtn.disabled =
        !(bookingFilled && statusSelected);
}

bookingId.addEventListener(
    'input',
    validateForm
);

paymentStatus.addEventListener(
    'change',
    validateForm
);

validateForm();

</script>

</body>
</html>