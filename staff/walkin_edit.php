<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';
$id = intval($_GET['id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['customer_name'];
    $movie = $_POST['movie'];
    $date = $_POST['show_date'];
    $time = $_POST['show_time'];
    $adult = intval($_POST['adult_qty']);
    $senior = intval($_POST['senior_qty']);
    $student = intval($_POST['student_qty']);
    $children = intval($_POST['children_qty']);
    $total = ($adult*12) + ($senior*8) + ($student*10) + ($children*6);
    $status = $_POST['payment_status'];
    $stmt = $conn->prepare("UPDATE walkin_bookings SET customer_name=?, movie=?, show_date=?, show_time=?, adult_qty=?, senior_qty=?, student_qty=?, children_qty=?, total_price=?, payment_status=? WHERE id=?");
    $stmt->bind_param("ssssiiiiisi", $name, $movie, $date, $time, $adult, $senior, $student, $children, $total, $status, $id);
    $stmt->execute();
    header("Location: walkin_bookings.php");
    exit;
}
$w = $conn->query("SELECT * FROM walkin_bookings WHERE id = $id")->fetch_assoc();
if(!$w) die("Not found");
?>
<!DOCTYPE html>
<html>
<head><title>Edit Walk-in</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-4" style="max-width:500px">
    <div class="card p-4">
        <h3>Edit Walk-in Booking</h3>
        <form method="POST">
            <div class="mb-2"><label>Customer Name</label><input name="customer_name" value="<?= htmlspecialchars($w['customer_name']) ?>" class="form-control" required></div>
            <div class="mb-2"><label>Movie</label><input name="movie" value="<?= htmlspecialchars($w['movie']) ?>" class="form-control" required></div>
            <div class="mb-2"><label>Show Date</label><input type="date" name="show_date" value="<?= $w['show_date'] ?>" class="form-control" required></div>
            <div class="mb-2"><label>Show Time</label><input type="time" name="show_time" value="<?= $w['show_time'] ?>" class="form-control" required></div>
            <div class="row">
                <div class="col"><label>Adult</label><input type="number" name="adult_qty" value="<?= $w['adult_qty'] ?>" class="form-control"></div>
                <div class="col"><label>Senior</label><input type="number" name="senior_qty" value="<?= $w['senior_qty'] ?>" class="form-control"></div>
                <div class="col"><label>Student</label><input type="number" name="student_qty" value="<?= $w['student_qty'] ?>" class="form-control"></div>
                <div class="col"><label>Children</label><input type="number" name="children_qty" value="<?= $w['children_qty'] ?>" class="form-control"></div>
            </div>
            <div class="mb-2"><label>Payment Status</label>
                <select name="payment_status" class="form-select">
                    <option value="Pending" <?= $w['payment_status']=='Pending'?'selected':'' ?>>Pending</option>
                    <option value="Paid" <?= $w['payment_status']=='Paid'?'selected':'' ?>>Paid</option>
                </select>
            </div>
            <button type="submit" class="btn btn-warning w-100 mt-3">Update</button>
        </form>
        <a href="walkin_bookings.php" class="btn btn-secondary mt-2">Back</a>
    </div>
</div>
</body>
</html>