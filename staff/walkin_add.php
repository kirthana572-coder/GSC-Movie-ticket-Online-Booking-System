<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = 'W' . time();
    $name = $_POST['customer_name'];
    $movie = $_POST['movie'];
    $date = $_POST['show_date'];
    $time = $_POST['show_time'];
    $adult = intval($_POST['adult_qty']);
    $senior = intval($_POST['senior_qty']);
    $student = intval($_POST['student_qty']);
    $children = intval($_POST['children_qty']);
    $total = ($adult*12) + ($senior*8) + ($student*10) + ($children*6);
    $status = 'Pending';
    $stmt = $conn->prepare("INSERT INTO walkin_bookings (booking_code, customer_name, movie, show_date, show_time, adult_qty, senior_qty, student_qty, children_qty, total_price, payment_status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssiiiiis", $code, $name, $movie, $date, $time, $adult, $senior, $student, $children, $total, $status);
    $stmt->execute();
    header("Location: walkin_bookings.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Add Walk-in</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-4" style="max-width:500px">
    <div class="card p-4">
        <h3>New Walk-in Booking</h3>
        <form method="POST">
            <div class="mb-2"><label>Customer Name</label><input name="customer_name" class="form-control" required></div>
            <div class="mb-2"><label>Movie</label><input name="movie" class="form-control" required></div>
            <div class="mb-2"><label>Show Date</label><input type="date" name="show_date" class="form-control" required></div>
            <div class="mb-2"><label>Show Time</label><input type="time" name="show_time" class="form-control" required></div>
            <div class="row">
                <div class="col"><label>Adult (RM12)</label><input type="number" name="adult_qty" class="form-control" value="0"></div>
                <div class="col"><label>Senior (RM8)</label><input type="number" name="senior_qty" class="form-control" value="0"></div>
                <div class="col"><label>Student (RM10)</label><input type="number" name="student_qty" class="form-control" value="0"></div>
                <div class="col"><label>Children (RM6)</label><input type="number" name="children_qty" class="form-control" value="0"></div>
            </div>
            <button type="submit" class="btn btn-warning w-100 mt-3">Save</button>
        </form>
        <a href="walkin_bookings.php" class="btn btn-secondary mt-2">Cancel</a>
    </div>
</div>
</body>
</html>