<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$showtime_id = $_GET['showtime_id'] ?? 0;
if (!$showtime_id) {
    die("Invalid showtime.");
}

// 获取场次信息
$showtime = $conn->query("
    SELECT s.id, s.show_date, s.show_time, m.title, b.name AS branch_name
    FROM showtimes s
    JOIN movies m ON s.movie_id = m.id
    JOIN branches b ON s.branch_id = b.id
    WHERE s.id = " . intval($showtime_id)
)->fetch_assoc();

if (!$showtime) die("Showtime not found.");

// 获取该场次所有座位
$seats = $conn->query("SELECT * FROM seats WHERE showtime_id = " . intval($showtime_id) . " ORDER BY seat_number");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Select Seat - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .seat-grid { display: flex; flex-wrap: wrap; gap: 10px; }
        .seat-btn { width: 50px; height: 50px; border-radius: 8px; font-weight: bold; }
        .available { background-color: #28a745; color: white; border: none; }
        .booked, .pending { background-color: #dc3545; color: white; border: none; cursor: not-allowed; }
        .selected { background-color: #ffc107; color: black; border: 2px solid #000; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="/GSC-Movie-ticket-Online-Booking-System/index.php">GSC Cinema</a>
    <div class="text-white">
        <?= htmlspecialchars($_SESSION['full_name']) ?>
        <a href="/GSC-Movie-ticket-Online-Booking-System/auth/logout.php" class="btn btn-sm btn-outline-light ms-3">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h2>Select Seats</h2>
    <p><strong><?= htmlspecialchars($showtime['title']) ?></strong> at <?= htmlspecialchars($showtime['branch_name']) ?></p>
    <p>Date: <?= date('d M Y', strtotime($showtime['show_date'])) ?> | Time: <?= date('h:i A', strtotime($showtime['show_time'])) ?></p>

    <form method="POST" action="booking.php">
        <input type="hidden" name="showtime_id" value="<?= $showtime['id'] ?>">
        <h5>Screen</h5>
        <div class="seat-grid mb-3" id="seatGrid">
            <?php while($seat = $seats->fetch_assoc()): 
                $status = $seat['status'];
                $disabled = ($status !== 'available') ? 'disabled' : '';
                $btnClass = 'available';
                if ($status === 'booked' || $status === 'pending') $btnClass = 'booked';
            ?>
                <button type="button" class="seat-btn <?= $btnClass ?>"
                        data-seat-id="<?= $seat['id'] ?>"
                        data-seat-number="<?= $seat['seat_number'] ?>"
                        onclick="toggleSeat(this)"
                        <?= $disabled ?>>
                    <?= $seat['seat_number'] ?>
                </button>
            <?php endwhile; ?>
        </div>
        
        <!-- 隐藏字段存储选中的座位ID -->
        <input type="hidden" name="seat_ids" id="seatIdsInput" value="">
        
        <p>Selected Seats: <span id="selectedSeatsDisplay"></span></p>
        <button type="submit" class="btn btn-warning btn-lg">Confirm Booking</button>
    </form>
</div>

<script>
let selectedSeats = [];

function toggleSeat(btn) {
    const seatId = btn.dataset.seatId;
    const seatNum = btn.dataset.seatNumber;
    
    if (btn.classList.contains('selected')) {
        // 取消选择
        btn.classList.remove('selected');
        btn.classList.add('available');
        selectedSeats = selectedSeats.filter(s => s.id !== seatId);
    } else {
        // 选择
        btn.classList.remove('available');
        btn.classList.add('selected');
        selectedSeats.push({id: seatId, number: seatNum});
    }
    
    updateDisplay();
}

function updateDisplay() {
    document.getElementById('selectedSeatsDisplay').textContent = 
        selectedSeats.map(s => s.number).join(', ');
    document.getElementById('seatIdsInput').value = 
        selectedSeats.map(s => s.id).join(',');
}
</script>

</body>
</html>