<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die("Invalid booking ID.");

// 获取现有预订信息
$result = $conn->query("SELECT walkin_bookings.*, movies.id AS movie_id, movies.title, showtimes.show_date, showtimes.show_time 
                        FROM walkin_bookings 
                        JOIN showtimes ON walkin_bookings.showtime_id = showtimes.id 
                        JOIN movies ON showtimes.movie_id = movies.id 
                        WHERE walkin_bookings.id = $id");
$booking = $result->fetch_assoc();
if (!$booking) die("Booking not found.");

$currentSeats = [];
$getSeats = $conn->query("SELECT seat_id FROM walkin_booking_seats WHERE walkin_booking_id = $id");
while ($seat = $getSeats->fetch_assoc()) $currentSeats[] = $seat['seat_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = $conn->real_escape_string(trim($_POST['customer_name']));
    $movie_id = intval($_POST['movie_id']);
    $show_date = $conn->real_escape_string($_POST['show_date']);
    $show_time = $conn->real_escape_string($_POST['show_time']);
    $payment_status = $conn->real_escape_string($_POST['payment_status']); // 'Pending', 'Paid', 'Cancelled'
    $selectedSeats = $_POST['seats'] ?? [];

    // 获取 showtime_id
    $stmt = $conn->prepare("SELECT id FROM showtimes WHERE movie_id = ? AND show_date = ? AND show_time = ?");
    $stmt->bind_param("iss", $movie_id, $show_date, $show_time);
    $stmt->execute();
    $showtime = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$showtime) {
        echo "<script>alert('Showtime not found!'); history.back();</script>";
        exit();
    }
    $showtime_id = $showtime['id'];

    // 座位冲突检查（保留原样，这个没问题）
    foreach ($selectedSeats as $seat_id) {
        $seat_id = intval($seat_id);
        $stmt = $conn->prepare("SELECT * FROM booking_seats bs JOIN bookings b ON bs.booking_id = b.id WHERE b.showtime_id = ? AND bs.seat_id = ?");
        $stmt->bind_param("ii", $showtime_id, $seat_id);
        $stmt->execute();
        $checkOnline = $stmt->get_result();
        $stmt->close();

        $stmt2 = $conn->prepare("SELECT * FROM walkin_booking_seats wbs JOIN walkin_bookings wb ON wbs.walkin_booking_id = wb.id WHERE wb.showtime_id = ? AND wbs.seat_id = ? AND wb.id != ?");
        $stmt2->bind_param("iii", $showtime_id, $seat_id, $id);
        $stmt2->execute();
        $checkWalkin = $stmt2->get_result();
        $stmt2->close();

        if ($checkOnline->num_rows > 0 || $checkWalkin->num_rows > 0) {
            echo "<script>alert('One or more seats already booked.'); history.back();</script>";
            exit();
        }
    }

    $adult_qty = intval($_POST['adult_qty']);
    $senior_qty = intval($_POST['senior_qty']);
    $student_qty = intval($_POST['student_qty']);
    $children_qty = intval($_POST['children_qty']);

    $totalTickets = $adult_qty + $senior_qty + $student_qty + $children_qty;
    if ($totalTickets <= 0) {
        echo "<script>alert('Please select at least one ticket.'); history.back();</script>";
        exit();
    }
    if (count($selectedSeats) == 0) {
        echo "<script>alert('Please select at least one seat.'); history.back();</script>";
        exit();
    }
    if (count($selectedSeats) != $totalTickets) {
        echo "<script>alert('Selected seats must equal total tickets.'); history.back();</script>";
        exit();
    }

    $total = ($adult_qty * 12) + ($senior_qty * 8) + ($student_qty * 10) + ($children_qty * 6);

    // ========== 关键：直接用 real_escape_string 拼接 SQL，避免 bind_param 错误 ==========
    $sql = "UPDATE walkin_bookings 
            SET customer_name = '$customer_name', 
                showtime_id = $showtime_id, 
                adult_qty = $adult_qty, 
                senior_qty = $senior_qty, 
                student_qty = $student_qty, 
                children_qty = $children_qty, 
                total_price = $total, 
                payment_status = '$payment_status' 
            WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        // 删除旧座位
        $conn->query("DELETE FROM walkin_booking_seats WHERE walkin_booking_id = $id");
        
        // 构建票种队列
        $ticketQueue = [];
        for ($i = 0; $i < $adult_qty; $i++) $ticketQueue[] = 'Adult';
        for ($i = 0; $i < $senior_qty; $i++) $ticketQueue[] = 'Senior';
        for ($i = 0; $i < $student_qty; $i++) $ticketQueue[] = 'Student';
        for ($i = 0; $i < $children_qty; $i++) $ticketQueue[] = 'Children';
        
        $selectedSeats = array_values($selectedSeats);
        $stmt2 = $conn->prepare("INSERT INTO walkin_booking_seats (walkin_booking_id, seat_id, ticket_type) VALUES (?, ?, ?)");
        for ($i = 0; $i < count($selectedSeats); $i++) {
            $seat_id = intval($selectedSeats[$i]);
            $ticket_type = $ticketQueue[$i];
            $stmt2->bind_param("iis", $id, $seat_id, $ticket_type);
            $stmt2->execute();
        }
        $stmt2->close();
        
        echo "<script>alert('Booking updated successfully!'); window.location.href='" . BASE_URL . "/staff/walkin_bookings.php';</script>";
        exit();
    } else {
        echo "<script>alert('Update failed: " . $conn->error . "'); history.back();</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Walk-in Booking - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ margin:0; font-family:'Segoe UI',sans-serif; background:linear-gradient(rgba(245,242,234,0.92),rgba(255,220,164,0.92)); min-height:100vh; }
        .page-container{ min-height:100vh; display:flex; justify-content:center; align-items:center; padding:40px; }
        .booking-card{ width:100%; max-width:700px; background:rgba(255,255,255,0.82); border-radius:28px; padding:45px; box-shadow:0 10px 30px rgba(0,0,0,0.15); }
        .page-title{ text-align:center; font-size:38px; font-weight:700; color:#f5c518; margin-bottom:10px; }
        .page-subtitle{ text-align:center; color:#777; margin-bottom:35px; }
        .form-label{ font-weight:600; color:#333; margin-bottom:10px; }
        .form-control,.form-select{ border-radius:14px; padding:14px; border:1px solid rgba(0,0,0,0.1); box-shadow:none; }
        .form-control:focus,.form-select:focus{ border-color:#f5c518; box-shadow:0 0 0 0.15rem rgba(245,197,24,0.25); }
        .ticket-box{ background:rgba(255,255,255,0.7); border-radius:18px; padding:20px; margin-bottom:20px; }
        .ticket-title{ font-size:18px; font-weight:700; margin-bottom:15px; color:#444; }
        .price-tag{ color:#f5c518; font-weight:700; }
        .total-box{ background:rgba(245,197,24,0.15); border-radius:18px; padding:18px; text-align:center; margin-top:25px; }
        .total-title{ font-size:18px; color:#555; }
        .total-price{ font-size:32px; font-weight:700; color:#f5c518; }
        .btn-save{ width:100%; background:#ffd53b; color:#111; border:none; border-radius:16px; padding:15px; font-size:18px; font-weight:700; margin-top:25px; transition:0.25s; }
        .btn-save:hover{ background:#ffdc5f; transform:scale(1.02); }
        .back-btn{ width:100%; display:block; text-align:center; text-decoration:none; background:#2f2f2f; color:white; border-radius:16px; padding:15px; font-size:18px; font-weight:700; margin-top:15px; transition:0.25s; }
        .back-btn:hover{ background:#f5c518; color:#111; transform:scale(1.02); }
    </style>
</head>
<body>
<div class="page-container">
    <div class="booking-card">
        <h1 class="page-title">Edit Walk-in Booking</h1>
        <p class="page-subtitle">Update booking information</p>
        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Customer Name</label>
                <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($booking['customer_name']) ?>" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Select Movie</label>
                <select name="movie_id" id="movie_id" class="form-select" required>
                    <?php
                    $movies = $conn->query("SELECT * FROM movies");
                    while ($movie = $movies->fetch_assoc()):
                        $selected = ($booking['movie_id'] == $movie['id']) ? 'selected' : '';
                    ?>
                        <option value="<?= $movie['id'] ?>" <?= $selected ?>><?= htmlspecialchars($movie['title']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label">Select Date</label>
                <select name="show_date" id="show_date" class="form-select" required>
                    <option disabled selected>Select Date</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label">Select Time</label>
                <select name="show_time" id="show_time" class="form-select" required>
                    <option disabled selected>Select Time</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label">Select Seats</label>
                <div id="seatContainer" class="d-flex flex-wrap gap-2"></div>
            </div>
            <div class="ticket-box">
                <div class="ticket-title">Edit Ticket Quantity</div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Adult Ticket</label>
                        <input type="number" name="adult_qty" id="adultQty" class="form-control" value="<?= $booking['adult_qty'] ?>" min="0">
                        <small class="price-tag">🧑 Adult - RM12.00</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Senior Ticket</label>
                        <input type="number" name="senior_qty" id="seniorQty" class="form-control" value="<?= $booking['senior_qty'] ?>" min="0">
                        <small class="price-tag">👴 Senior - RM8.00</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Student Ticket</label>
                        <input type="number" name="student_qty" id="studentQty" class="form-control" value="<?= $booking['student_qty'] ?>" min="0">
                        <small class="price-tag">🎓 Student - RM10.00</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Children Ticket</label>
                        <input type="number" name="children_qty" id="childrenQty" class="form-control" value="<?= $booking['children_qty'] ?>" min="0">
                        <small class="price-tag">👶 Children - RM6.00</small>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Payment Status</label>
                <select name="payment_status" class="form-select">
                    <option value="Pending" <?= ($booking['payment_status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Paid" <?= ($booking['payment_status'] == 'Paid') ? 'selected' : '' ?>>Paid</option>
                    <option value="Cancelled" <?= ($booking['payment_status'] == 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="total-box">
                <div class="total-title">Updated Total Price</div>
                <div class="total-price" id="totalPrice">RM <?= number_format($booking['total_price'], 2) ?></div>
            </div>
            <button type="submit" class="btn-save">Save Changes</button>
            <a href="<?= BASE_URL ?>/staff/walkin_bookings.php" class="back-btn">Back</a>
        </form>
    </div>
</div>
<script>
    const adultQty = document.getElementById('adultQty');
    const seniorQty = document.getElementById('seniorQty');
    const studentQty = document.getElementById('studentQty');
    const childrenQty = document.getElementById('childrenQty');
    const totalPrice = document.getElementById('totalPrice');
    function updateTotal() {
        let adult = parseInt(adultQty.value) || 0;
        let senior = parseInt(seniorQty.value) || 0;
        let student = parseInt(studentQty.value) || 0;
        let children = parseInt(childrenQty.value) || 0;
        let total = (adult * 12) + (senior * 8) + (student * 10) + (children * 6);
        totalPrice.textContent = 'RM ' + total.toFixed(2);
    }
    adultQty.addEventListener('input', updateTotal);
    seniorQty.addEventListener('input', updateTotal);
    studentQty.addEventListener('input', updateTotal);
    childrenQty.addEventListener('input', updateTotal);
    updateTotal();

    document.addEventListener('DOMContentLoaded', async function() {
        const movieId = document.getElementById('movie_id').value;
        const selectedDate = "<?= $booking['show_date'] ?>";
        const selectedTime = "<?= $booking['show_time'] ?>";
        const dateSelect = document.getElementById('show_date');
        const timeSelect = document.getElementById('show_time');

        let res1 = await fetch('<?= BASE_URL ?>/staff/get_dates.php?movie_id=' + movieId);
        dateSelect.innerHTML = await res1.text();
        if (selectedDate) dateSelect.value = selectedDate;
        let res2 = await fetch('<?= BASE_URL ?>/staff/get_times.php?movie_id=' + movieId + '&date=' + dateSelect.value);
        timeSelect.innerHTML = await res2.text();
        if (selectedTime) timeSelect.value = selectedTime;
        loadSeats();
    });

    document.getElementById('movie_id').addEventListener('change', function() {
        const movieId = this.value;
        fetch('<?= BASE_URL ?>/staff/get_dates.php?movie_id=' + movieId)
            .then(r => r.text())
            .then(d => {
                document.getElementById('show_date').innerHTML = d;
                document.getElementById('show_time').innerHTML = '<option>Select Time</option>';
                document.getElementById('seatContainer').innerHTML = '';
            });
    });

    document.getElementById('show_date').addEventListener('change', function() {
        const movieId = document.getElementById('movie_id').value;
        const date = this.value;
        if (!date) return;
        fetch('<?= BASE_URL ?>/staff/get_times.php?movie_id=' + movieId + '&date=' + date)
            .then(r => r.text())
            .then(d => {
                document.getElementById('show_time').innerHTML = d;
                document.getElementById('seatContainer').innerHTML = '';
            });
    });

    document.getElementById('show_time').addEventListener('change', function() {
        if (this.value) loadSeats();
    });

    function loadSeats() {
        const movieId = document.getElementById('movie_id').value;
        const date = document.getElementById('show_date').value;
        const time = document.getElementById('show_time').value;
        if (!movieId || !date || !time) return;
        fetch('<?= BASE_URL ?>/staff/get_edit_walkin_seats.php?movie_id=' + movieId + '&date=' + date + '&time=' + time + '&booking_id=<?= $id ?>')
            .then(res => res.text())
            .then(data => {
                document.getElementById('seatContainer').innerHTML = data;
            })
            .catch(err => console.error(err));
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        let selectedSeats = document.querySelectorAll('input[name="seats[]"]:checked').length;
        let totalTickets = (parseInt(adultQty.value)||0) + (parseInt(seniorQty.value)||0) + (parseInt(studentQty.value)||0) + (parseInt(childrenQty.value)||0);
        if (selectedSeats === 0) { e.preventDefault(); alert('Please select at least one seat.'); return; }
        if (totalTickets <= 0) { e.preventDefault(); alert('Please select at least one ticket.'); return; }
        if (selectedSeats !== totalTickets) { e.preventDefault(); alert('Selected seats must equal total tickets.'); }
    });
</script>
</body>
</html>