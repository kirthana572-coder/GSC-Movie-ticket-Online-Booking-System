<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = intval($_POST['booking_id']);
    $payment_status = $_POST['payment_status'];

    if ($payment_status !== 'Paid') {
        die("Invalid status change.");
    }

    $check = $conn->query("SELECT payment_status, user_id FROM bookings WHERE id = $booking_id");
    $row = $check->fetch_assoc();
    if (!$row || $row['payment_status'] !== 'Pending') {
        die("Booking cannot be updated.");
    }

    $stmt = $conn->prepare("UPDATE bookings SET payment_status = 'Paid' WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
    if ($stmt->execute()) {
        $user_id = $row['user_id'];
        $msg = "Your booking #$booking_id has been paid. You can now download your QR ticket.";
        $conn->query("INSERT INTO notifications (user_id, message) VALUES ($user_id, '$msg')");
        echo "<script>alert('Payment status updated successfully!'); window.location.href='staff_dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed to update.'); window.history.back();</script>";
    }
    $stmt->close();
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Payment Status - GSC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>

        body{
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background:
            linear-gradient(
                rgba(245,242,234,0.92),
                rgba(255,220,164,0.92)
            );

            min-height: 100vh;
        }

        .page-container{
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 40px;
        }

        .payment-card{
            width: 100%;
            max-width: 620px;

            background: rgba(255,255,255,0.78);

            border-radius: 28px;

            padding: 45px;

            box-shadow:
            0 10px 30px rgba(0,0,0,0.15);
        }

        .page-title{
            font-size: 38px;

            font-weight: 700;

            color: #f5c518;

            margin-bottom: 10px;

            text-align: center;
        }

        .page-subtitle{
            text-align: center;

            color: #757575;

            margin-bottom: 35px;
        }

        .form-label{
            font-weight: 600;

            color: #333;

            margin-bottom: 10px;
        }

        .form-control,
        .form-select{
            border-radius: 14px;

            padding: 14px;

            border: 1px solid rgba(0,0,0,0.1);

            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus{
            border-color: #f5c518;

            box-shadow:
            0 0 0 0.15rem rgba(245,197,24,0.25) !important;
        }

        .button-group{
            display: flex;

            gap: 15px;

            margin-top: 20px;
        }

        .btn-update{
            flex: 1;

            height: 58px;

            background: #ffd53b;

            color: #111;

            border: none;

            border-radius: 14px;

            font-size: 18px;

            font-weight: 700;

            transition: 0.25s;
        }

        .btn-update:hover{
            background: #ffdc5f;

            transform: scale(1.02);
        }

        .back-btn{
            flex: 1;

            height: 58px;

            text-decoration: none;

            background: #2f2f2f;

            color: white;

            border-radius: 14px;

            font-size: 18px;

            font-weight: 700;

            display: flex;

            align-items: center;

            justify-content: center;

            transition: 0.25s;

            cursor: pointer;
        }

        .back-btn:hover{
            background: #ffdd64;

            color: #111;

            transform: scale(1.02);
        }
    </style>
</head>
<body>
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

                <input type="text"
                        name="booking_id"
                        class="form-control"
                        placeholder="Enter booking ID">

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Amount
                </label>

                <input type="text"
                        name="amount"
                        class="form-control"
                        placeholder="Enter payment amount">

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Payment Status
                </label>

                <select name="payment_status" class="form-select">

                    <option selected disabled>
                        Select Status
                    </option>

                    <option>
                        Paid
                    </option>

                    <option>
                        Pending
                    </option>

                    <option>
                        Cancelled
                    </option>

                    <option>
                        Expired
                    </option>

                </select>

            </div>

            <div class="button-group">

                <button type="submit" class="btn-update">
                    Update Status
                </button>

                <a href="staff_dashboard.php" class="back-btn">
                    Back Dashboard
                </a>

            </div>

        </form>
    </div>

</div>

</body>
</html>