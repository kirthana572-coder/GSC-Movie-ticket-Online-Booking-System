<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$showtime_id = $_POST['showtime_id'] ?? 0;
$seat_ids_str = $_POST['seat_ids'] ?? '';
$seat_ids = array_filter(explode(',', $seat_ids_str));

if (empty($showtime_id) || empty($seat_ids)) {
    die("Invalid booking request.");
}

// 获取场次信息
$showtime = $conn->query("
    SELECT s.id, s.show_date, s.show_time,
           m.title AS movie_title, m.duration,
           b.name AS branch_name
    FROM showtimes s
    JOIN movies m ON s.movie_id = m.id
    JOIN branches b ON s.branch_id = b.id
    WHERE s.id = " . intval($showtime_id)
)->fetch_assoc();

if (!$showtime) die("Showtime not found.");

// 获取选中座位信息
$seats = [];
foreach ($seat_ids as $sid) {
    $res = $conn->query("SELECT seat_number FROM seats WHERE id = " . intval($sid));
    if ($s = $res->fetch_assoc()) {
        $seats[] = ['id' => $sid, 'number' => $s['seat_number']];
    }
}

// 票价设定
$prices = [
    'Adult'    => 12.00,
    'Senior'   => 8.00,
    'Student'  => 10.00,
    'Children' => 6.00
];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Confirm Booking - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GSC-Movie-ticket-Online-Booking-System/frontend/gsc-style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f4edd9, #f9d59f);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        .confirmation-card {
            max-width: 600px;
            margin: 40px auto;
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .movie-title {
            font-size: 28px;
            font-weight: 700;
            color: #222;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-label { color: #666; }
        .detail-value { font-weight: 600; color: #222; }
        .seat-ticket-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .seat-number {
            font-weight: 600;
            width: 50px;
        }
        .ticket-select {
            border-radius: 8px;
            padding: 4px 8px;
        }
        .btn-warning {
            background-color: #f5c518;
            border: none;
            border-radius: 30px;
            padding: 14px;
            font-weight: 700;
            font-size: 18px;
            transition: 0.3s;
        }
        .btn-warning:hover { background-color: #e0b400; transform: scale(1.02); }
        .btn-outline-dark {
            border-radius: 30px;
            padding: 14px;
            font-weight: 600;
            border: 2px solid #333;
        }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="confirmation-card">
    <h2 class="text-center mb-3">Confirm Your Booking</h2>
    <p class="text-center text-muted mb-4">Please review your booking details and select ticket types.</p>

    <div class="movie-title text-center mb-3"><?= htmlspecialchars($showtime['movie_title']) ?></div>

    <div class="detail-row">
        <span class="detail-label">Cinema</span>
        <span class="detail-value"><?= htmlspecialchars($showtime['branch_name']) ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Date</span>
        <span class="detail-value"><?= date('d M Y', strtotime($showtime['show_date'])) ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Time</span>
        <span class="detail-value"><?= date('h:i A', strtotime($showtime['show_time'])) ?></span>
    </div>
    <div class="detail-row">
        <span class="detail-label">Duration</span>
        <span class="detail-value"><?= $showtime['duration'] ?> mins</span>
    </div>

    <!-- 座位 & 票种选择 -->
    <div class="mb-3">
        <h5 class="mb-3">Select Ticket Type for Each Seat</h5>
        <?php foreach ($seats as $seat): ?>
            <div class="seat-ticket-row">
                <span class="seat-number"><?= $seat['number'] ?></span>
                <select class="form-control ticket-select" name="ticket_type[<?= $seat['id'] ?>]" data-seat-id="<?= $seat['id'] ?>">
                    <option value="Adult">🧑 Adult - RM12</option>
                    <option value="Senior">👴 Senior - RM8</option>
                    <option value="Student">🎓 Student - RM10</option>
                    <option value="Children">👶 Children - RM6</option>
                </select>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 总价 -->
    <div class="detail-row">
        <span class="detail-label">Total Price</span>
        <span class="detail-value" id="totalPrice">RM 0.00</span>
    </div>

    <div class="text-center mt-4">
        <form action="booking.php" method="POST" id="confirmForm">
            <input type="hidden" name="showtime_id" value="<?= $showtime_id ?>">
            <input type="hidden" name="seat_ids" value="<?= implode(',', $seat_ids) ?>">
            <!-- 动态填充 ticket_types -->
            <input type="hidden" name="ticket_types" id="ticketTypesInput" value="">
            <button type="submit" class="btn btn-warning px-5">✅ Confirm & Pay at Counter</button>
        </form>
        <a href="select_seat.php?showtime_id=<?= $showtime_id ?>" class="btn btn-outline-dark px-4 ms-2 mt-2">← Back</a>
    </div>
</div>

<script>
    const prices = <?= json_encode($prices) ?>;
    const selects = document.querySelectorAll('.ticket-select');
    const totalSpan = document.getElementById('totalPrice');
    const ticketTypesInput = document.getElementById('ticketTypesInput');

    function updateTotal() {
        let total = 0;
        let ticketMap = {};
        selects.forEach(sel => {
            const type = sel.value;
            total += prices[type];
        });
        // 构建票种字符串：seat_id:type,seat_id:type
        let pairs = [];
        selects.forEach(sel => {
            const seatId = sel.dataset.seatId;
            const type = sel.value;
            pairs.push(seatId + ':' + type);
        });
        ticketTypesInput.value = pairs.join(',');
        totalSpan.textContent = 'RM ' + total.toFixed(2);
    }

    selects.forEach(sel => sel.addEventListener('change', updateTotal));
    updateTotal(); // 初始化
</script>

</body>
</html>