<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';
$walkins = $conn->query("SELECT * FROM walkin_bookings ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head><title>Walk-in Bookings</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-4">
    <h2>Walk-in Bookings</h2>
    <a href="walkin_add.php" class="btn btn-warning mb-3">+ New Walk-in</a>
    <table class="table table-bordered bg-white">
        <thead class="table-dark"><tr><th>ID</th><th>Code</th><th>Customer</th><th>Movie</th><th>Date</th><th>Time</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php while($w = $walkins->fetch_assoc()): ?>
        <tr>
            <td><?= $w['id'] ?></td><td><?= $w['booking_code'] ?></td><td><?= htmlspecialchars($w['customer_name']) ?></td>
            <td><?= htmlspecialchars($w['movie']) ?></td><td><?= $w['show_date'] ?></td><td><?= $w['show_time'] ?></td>
            <td>RM <?= number_format($w['total_price'],2) ?></td><td><?= $w['payment_status'] ?></td>
            <td><a href="walkin_edit.php?id=<?= $w['id'] ?>" class="btn btn-sm btn-info">Edit</a> <a href="walkin_delete.php?id=<?= $w['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Del</a></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <a href="staff_dashboard.php" class="btn btn-secondary">Back</a>
</div>
</body>
</html>