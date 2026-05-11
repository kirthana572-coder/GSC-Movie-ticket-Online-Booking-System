<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$showtime_id = $_POST['showtime_id'] ?? 0;
$seat_ids_str = $_POST['seat_ids'] ?? '';
$seat_ids = array_filter(explode(',', $seat_ids_str));

if (empty($showtime_id) || empty($seat_ids)) {
    die("Invalid booking request. Please go back and select seats.");
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
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #fbf4e3, #ffe6bf);
            min-height: 100vh;
            color: #111;
        }
        .confirmation-container {
            min-height: calc(100vh - 70px);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .confirmation-card {
            width: 100%;
            max-width: 650px;
            background: rgb(237, 237, 232);
            border-radius: 28px;
            padding: 40px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.35);
            animation: fadeUp 0.6s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .movie-title {
            font-size: 32px;
            font-weight: 700;
            color: #222;
            text-align: center;
            margin-bottom: 5px;
        }
        .movie-meta {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            padding-bottom: 10px;
        }
        .info-label { color: #666; }
        .info-value { font-weight: 600; }
        .seat-ticket-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 8px 12px;
            background: rgba(255,255,255,0.7);
            border-radius: 12px;
        }
        .seat-number {
            font-weight: 700;
            font-size: 18px;
            width: 50px;
        }
        .ticket-select {
            border-radius: 10px;
            padding: 6px 12px;
            border: 1px solid #ddd;
        }
        .total-price {
            font-size: 24px;
            font-weight: 700;
            color: #f5c518;
            text-align: center;
            margin-top: 15px;
        }
        .btn-warning {
            background: #f5c518 !important;
            border: none;
            color: #111 !important;
            font-weight: 700;
            border-radius: 30px;
            padding: 16px 30px !important;
            font-size: 18px;
            transition: 0.3s;
            width: 100%;
        }
        .btn-warning:hover {
            background: #ffd43b !important;
            transform: scale(1.02);
        }
        .btn-outline-dark {
            border-radius: 30px;
            padding: 14px 24px;
            border: 2px solid #6a6969;
            color: #222;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            width: 100%;
            text-align: center;
        }
        .btn-outline-dark:hover {
            background: #fff;
            color: #000;
        }
    </style>
</head>
<body>
<?php include '../includes/navbar.php'; ?>

<div class="confirmation-container">
    <div class="confirmation-card">

        <h1 class="movie-title"><?= htmlspecialchars($showtime['movie_title']) ?></h1>
        <p class="movie-meta"><?= htmlspecialchars($showtime['branch_name']) ?></p>

        <div class="info-row">
            <span class="info-label">Date</span>
            <span class="info-value"><?= date('d M Y', strtotime($showtime['show_date'])) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Time</span>
            <span class="info-value"><?= date('h:i A', strtotime($showtime['show_time'])) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Duration</span>
            <span class="info-value"><?= $showtime['duration'] ?> mins</span>
        </div>

        <h5 class="mt-4 mb-3">Select Ticket Type</h5>
        <?php foreach ($seats as $seat): ?>
            <div class="seat-ticket-row">
                <span class="seat-number"><?= $seat['number'] ?></span>
                <select class="form-control ticket-select" name="ticket_type[<?= $seat['id'] ?>]" data-seat-id="<?= $seat['id'] ?>">
                    <option value="Adult">🧑 Adult - RM12.00</option>
                    <option value="Senior">👴 Senior - RM8.00</option>
                    <option value="Student">🎓 Student - RM10.00</option>
                    <option value="Children">👶 Children - RM6.00</option>
                </select>
            </div>
        <?php endforeach; ?>

        <div class="total-price">
            Total: <span id="totalPrice">RM 0.00</span>
        </div>

        <div class="mt-4">
            <form action="booking.php" method="POST" id="confirmForm">
                <input type="hidden" name="showtime_id" value="<?= $showtime_id ?>">
                <input type="hidden" name="seat_ids" value="<?= implode(',', $seat_ids) ?>">
                <input type="hidden" name="ticket_types" id="ticketTypesInput" value="">
                <button type="submit" class="btn btn-warning">✅ Confirm & Pay at Counter</button>
            </form>
            <a href="select_seat.php?showtime_id=<?= $showtime_id ?>" class="btn-outline-dark">← Back to Seat Selection</a>
        </div>

    </div>
</div>

<script>
    const prices = <?= json_encode($prices) ?>;
    const selects = document.querySelectorAll('.ticket-select');
    const totalSpan = document.getElementById('totalPrice');
    const ticketTypesInput = document.getElementById('ticketTypesInput');

    function updateTotal() {
        let total = 0;
        let pairs = [];
        selects.forEach(sel => {
            const type = sel.value;
            const seatId = sel.dataset.seatId;
            total += prices[type];
            pairs.push(seatId + ':' + type);
        });
        ticketTypesInput.value = pairs.join(',');
        totalSpan.textContent = 'RM ' + total.toFixed(2);
    }

    selects.forEach(sel => sel.addEventListener('change', updateTotal));
    updateTotal();
</script>
<script src="/GSC-Movie-ticket-Online-Booking-System/notification.js"></script>
</body>
</html>