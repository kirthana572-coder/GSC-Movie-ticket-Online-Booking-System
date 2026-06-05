<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// 自动过期逻辑（30分钟未支付且场次未开始）
$conn->query("
    UPDATE bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    SET b.payment_status = 'Expired'
    WHERE b.payment_status = 'Pending'
      AND NOW() >= DATE_SUB(
            CONCAT(s.show_date, ' ', s.show_time),
            INTERVAL 30 MINUTE
      )
");

// 释放座位
$conn->query("
    UPDATE seats s
    JOIN booking_seats bs ON s.id = bs.seat_id
    JOIN bookings b ON bs.booking_id = b.id
    SET s.status = 'available'
    WHERE b.payment_status = 'Expired'
");


// 为刚过期的订单发送通知
$expired_orders = $conn->query("
    SELECT b.id, b.user_id 
    FROM bookings b
    WHERE b.payment_status = 'Expired' 
      AND b.id NOT IN (
          SELECT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(message, '#', -1), ' ', 1) AS UNSIGNED)
          FROM notifications 
          WHERE message LIKE '%expired%'
      )
");
while ($e = $expired_orders->fetch_assoc()) {
    $msg = "Your booking #{$e['id']} has expired due to non-payment.";
    $conn->query("INSERT INTO notifications (user_id, message) VALUES ({$e['user_id']}, '$msg')");
}

// 获取当前用户所有预订
$bookings = $conn->query("
    SELECT 
    b.id,
    b.payment_status,
    b.booking_date,
    b.cancel_reason,

           m.title, s.show_date, s.show_time, br.name AS branch_name
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN branches br ON s.branch_id = br.id
    WHERE b.user_id = " . $_SESSION['user_id'] . "
    ORDER BY b.booking_date DESC
");
?>
<!DOCTYPE html>
<html>
<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Booking History - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">

    <style>

        /*Page Background*/
        body{
            background:
            linear-gradient(
                180deg,
                #faf8f2,
                #f3ede0
            );

            min-height:100vh;
            font-family:'Segoe UI',sans-serif;
        }

        .history-hero{

            background:white;

            border-radius:24px;

            padding:45px;

            display:flex;
            justify-content:space-between;
            align-items:center;

            box-shadow:
            0 10px 30px rgba(0,0,0,.08);

            margin-bottom:25px;
        }

        .history-hero h1{

            font-size:42px;

            font-weight:800;

            color:#222;
        }

        @media (max-width:768px){

            .history-hero{

                flex-direction:column;

                gap:20px;

                text-align:center;
            }

            .history-hero h1{

                font-size:32px;
            }
        }

        .history-hero p{
            color:#666;

            font-size:15px;

            margin-top:6px;
        }

        /* Back Home Button */
        .back-home-btn{

            background:#f5c518;

            color:#222;

            text-decoration:none;

            border-radius:30px;

            padding:12px 24px;

            font-weight:600;

            transition:.3s;
        }

        .back-home-btn:hover{
            background:#e0b400;

            color:#222;

            transform:translateY(-2px);

        }

        .booking-table tbody tr{

            transition:.25s;
        }

        .booking-table tbody tr:hover{

            transform:scale(1.005);
        }

        /*Hover effect*/
        .booking-table tbody tr:hover td{
            background:rgba(245,197,24,.08);
            transition:.2s;
        }

        /*Status badge*/
        .badge{

            padding:8px 14px !important;

            border-radius:30px !important;

            font-size:12px !important;

            font-weight:700;
        }

        .status-paid{

            background:#dcfce7;
            color:#166534;
        }

        .status-pending{

            background:#fef3c7;
            color:#92400e;
        }

        .status-cancelled{

            background:#fee2e2;
            color:#991b1b;
        }

        .status-expired{

            background:#e5e7eb;
            color:#374151;
        }

        /* View Details Button */
        .btn-details{

            background:#f5c518;
            color:#222;

            border:none;

            border-radius:30px;

            font-weight:600;
        }

        .btn-details,
        .btn-qr,
        .btn-cancel{

            min-height:44px;

            display:flex;
            align-items:center;
            justify-content:center;
        }

        .btn-details:hover{

            background:#e0b400;

            color:#222;

            transform:translateY(-2px);
        }
        
        .btn-action:hover{
            transform:translateY(-2px);
        }

        .booking-card{

            background:white;

            border-radius:24px;

            padding:32px;

            border:none;

            box-shadow:
            0 10px 30px rgba(0,0,0,.08);
        }

        .booking-table{
            overflow:hidden;
            border-radius:18px;
        }

        .booking-table thead th:first-child{
            border-top-left-radius:16px;
        }

        .booking-table thead th:last-child{
            border-top-right-radius:16px;
        }

        .booking-table thead{
            background:#fff8dc;
        }

        .booking-table thead th{
            color:#444;

            font-weight:700;

            border:none;

            padding:16px;
        }

        .booking-table tbody td{
            padding:18px;
            vertical-align:middle;
            border-color:#e5e7eb;
        }

        .movie-title{
            font-weight:700;
            color:#111827;
        }

        .btn-action{
            border-radius:12px;

            padding:8px 15px;

            font-weight:600;

            transition:.25s;
        }

        .btn-action:hover{
            transform:translateY(-2px);
        }

        .empty-booking{

            background:white;

            padding:80px 40px;

            border-radius:24px;

            text-align:center;

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);
        }

        .empty-icon{

            font-size:72px;

            color:#f5c518;

            margin-bottom:20px;
        }

        .history-card{
            padding-bottom:40px;
        }

        .btn-qr{

            background:#0d6efd;

            color:white;

            border:none;

            border-radius:30px;
        }

        .btn-qr:hover{

            background:#0b5ed7;

            color:white;

            transform:translateY(-2px);
        }

        .btn-cancel{

            border-radius:30px;

            font-weight:600;
        }
    </style>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">

</head>
<body class="history-page">

<?php include '../includes/navbar.php'; ?>

<div class="container py-5">

    <div class="history-card">

        <div class="history-hero">

            <div>

                <h1>
                    My Bookings
                </h1>

                <p>
                    View your movie tickets, booking status and payment records.
                </p>

            </div>

            <a href="<?= BASE_URL ?>/index.php"
            class="back-home-btn">

                <i class="bi bi-arrow-left"></i>
                    Back Home

            </a>

        </div>

    <?php if ($bookings->num_rows > 0): ?>

        <div class="booking-card">
        <div class="table-responsive">

            <table class="table booking-table">
                <thead>
                    <tr>
                        <th>Movie</th>
                        <th>Branch</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Booked On</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while($b = $bookings->fetch_assoc()):

                        $statusClass = '';

                        switch($b['payment_status']){
                            case 'Paid':
                                $statusClass = 'status-paid';
                                $statusText = 'Paid';
                                break;

                            case 'Pending':
                                $statusClass = 'status-pending';
                                $statusText = 'Pending';
                                break;

                            case 'Cancelled':
                                $statusClass = 'status-cancelled';
                                $statusText = 'Cancelled';
                                break;

                            case 'Expired':
                                $statusClass = 'status-expired';
                                $statusText = 'Expired';
                                break;

                            default:
                                $statusText = $b['payment_status'];
                        }

                        $expireTimestamp =
                            strtotime($b['show_date'] . ' ' . $b['show_time'])
                            - (30 * 60);

                    ?>
                    <tr>
                        <td>
                            <div class="movie-title">

                                <?= htmlspecialchars($b['title']) ?>

                            </div>
                        </td>
                        <td><?= htmlspecialchars($b['branch_name']) ?></td>
                        <td><?= date('d M Y', strtotime($b['show_date'])) ?></td>
                        <td><?= date('h:i A', strtotime($b['show_time'])) ?></td>
                        <td>
                            <span class="badge <?= $statusClass ?>">
                                <?= $statusText ?>
                            </span>
                            
                            <?php if($b['payment_status'] === 'Cancelled' && !empty($b['cancel_reason'])): ?>

                            <br>

                            <small class="text-danger fw-semibold">

                                <?= htmlspecialchars($b['cancel_reason']) ?>

                            </small>

                            <?php endif; ?>

                            <?php if ($b['payment_status'] === 'Pending'): ?>
                                <br>
                                <small class="text-danger countdown" data-expire="<?= $expireTimestamp ?>">
                                    Calculating...
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y H:i', strtotime($b['booking_date'])) ?></td>
                        <td>

                            <!-- View Details Button -->
                            <a href="booking_details.php?booking_id=<?= $b['id'] ?>"
                                class="btn btn-details w-100">
                                    View Details
                                </a>

                            <br>

                            <?php if ($b['payment_status'] === 'Pending'): ?>

                                <a href="cancel_booking.php?id=<?= $b['id'] ?>"
                                    class="btn btn-outline-danger btn-cancel w-100 mt-2"
                                    onclick="return confirm('Cancel this booking?')">
                                    Cancel Booking
                                    </a>
                                                            
                            <?php elseif ($b['payment_status'] === 'Paid'): ?>
                                <a href="qr_ticket.php?booking_id=<?= $b['id'] ?>"
                                    class="btn btn-qr w-100 mt-2">
                                    View QR Ticket
                                    </a>
                            
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                    
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        </div>

    <?php else: ?>
        <div class="empty-booking">

            <div class="empty-icon">
                <i class="bi bi-ticket-perforated"></i>
            </div>

            <h4>No Booking History</h4>

            <p>
                You haven't booked any movie tickets yet.
            </p>

            <a href="movies.php"
                class="btn btn-details px-4">
                Browse Movies
            </a>

        </div>
    <?php endif; ?>

    </div>
</div>
<script>
function updateCountdowns() {
    const now = Math.floor(Date.now() / 1000); // 当前时间戳（秒）
    const elements = document.querySelectorAll('.countdown');
    elements.forEach(el => {
        const expire = parseInt(el.dataset.expire);
        if (!expire) return;
        const remaining = expire - now;
        if (remaining <= 0) {
            el.textContent = 'Expired';
            el.classList.remove('text-danger');
            el.classList.add('text-muted');
            // 可选：自动刷新页面以更新状态
            // location.reload();
        } else {
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            el.textContent = `Expires in: ${minutes}m ${seconds}s`;
        }
    });
}

// 每秒更新一次
setInterval(updateCountdowns, 1000);
// 页面加载后立刻更新一次
updateCountdowns();
</script>

<script src="<?= BASE_URL ?>/notification.js"></script>
</body>
</html>