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
    <style>/* 保持原样式 */</style>
</head>
<body>
<div class="page-container">
    <div class="payment-card">
        <h1 class="page-title">Update Payment Status</h1>
        <p class="page-subtitle">Staff can update customer payment records here.</p>
        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Booking ID</label>
                <input type="text" name="booking_id" class="form-control" placeholder="Enter booking ID" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Payment Status</label>
                <select name="payment_status" class="form-select" required>
                    <option selected disabled>Select Status</option>
                    <option value="Paid">Paid</option>
                </select>
            </div>
            <div class="button-group">
                <button type="submit" class="btn-update">Update Status</button>
                <a href="staff_dashboard.php" class="back-btn">Back Dashboard</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>