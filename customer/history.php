<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// 自动过期逻辑（30分钟未支付且场次未开始）
$conn->query("
    UPDATE bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    SET b.payment_status = 'Expired'
    WHERE b.payment_status = 'Pending'
      AND b.booking_date < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
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
    SELECT b.id, b.payment_status, b.booking_date,
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
    <title>Booking History - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        /*Page Background*/
        body{
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background:
            linear-gradient(
                rgba(244,237,217,0.50),
                rgba(46, 45, 45, 0.85)
            ),
            url('https://images.unsplash.com/photo-1524985069026-dd778a71c7b4?q=80&w=1974&auto=format&fit=crop')
            center center / cover no-repeat fixed;

            min-height: 100vh;
        }

        /* Top Section */
        .top-bar{
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;

            flex-wrap: wrap;
        }

        /* Back Home Button */
        .back-home-btn{
            background: rgb(64, 64, 68);

            color: #fff;

            text-decoration: none;

            padding: 12px 22px;

            border-radius: 30px;

            font-weight: 600;

            transition: 0.25s;

            box-shadow:
            0 6px 16px rgba(0,0,0,0.2);
        }

        .back-home-btn:hover{
            background: #ffd230;

            color: #111;

            transform: translateY(-2px);
        }

        /* Title */
        .history-card h2{
            font-size: 42px;

            font-weight: 700;

            color: #31343c;

            margin: 0;
        }

        /*Booking Table*/
        .table{
            overflow: hidden !important;

            border-radius: 18px !important; 

            margin-top: 25px !important;

            background: rgba(252, 251, 240, 0.92) !important; 
        }

        /*Table Header*/
        .table thead th{
            border: none !important;

            padding: 18px !important;

            font-size: 18px !important;
        }

        /*Table Body*/
        .table tbody td{
            padding: 22px 16px !important;

            vertical-align: middle !important;

            border-color: rgba(0,0,0,0.08) !important;

            background: rgba(255, 255, 255, 0) !important;
        }

        /*Hover effect*/
        .table tbody tr:hover td{
            background: rgba(255, 200, 0, 0.08) !important;

            transition: 0.2s !important;
        }

        /*Status badge*/
        .badge{
            padding: 8px 14px !important;

            border-radius: 30px !important;

            font-size: 14px !important;
        }

        /*cancel button*/
        .btn-danger{
            border-radius: 20px !important;

            padding: 6px 16px !important;

            background: #e65867 !important;

            border: none !important;

            font-weight: 600;

        }

        /* View Details Button */
        .btn-details{
            background: #f8d146 !important;

            color: #111 !important;

            border: none !important;

            border-radius: 20px !important;

            padding: 6px 16px !important;

            font-weight: 600 !important;

            transition: 0.2s;
        }

        .btn-details:hover{
            background: #ffd84c !important;

            transform: scale(1.05);
        }

        .btn-danger:hover{
            transform: scale(1.05);

            transition: 0.2s;
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-5">

    <div class="history-card">

    <div class="top-bar">

        <a href=http://localhost/GSC-Movie-ticket-Online-Booking-System/index.php class="back-home-btn">
            ←  Back to Home
        </a>

        <h2>My Bookings</h2>

    </div>

    <?php if ($bookings->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead class="table-dark">
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
                    $statusClass = 'bg-warning text-dark';
                    if ($b['payment_status'] === 'Paid') $statusClass = 'bg-success';
                    if (in_array($b['payment_status'], ['Cancelled', 'Expired'])) $statusClass = 'bg-danger';

                    // 计算过期时间戳（booking_date + 30分钟）
                    $expireTimestamp = strtotime($b['booking_date']) + (30 * 60);
                ?>
                <tr>
                    <td><?= htmlspecialchars($b['title']) ?></td>
                    <td><?= htmlspecialchars($b['branch_name']) ?></td>
                    <td><?= date('d M Y', strtotime($b['show_date'])) ?></td>
                    <td><?= date('h:i A', strtotime($b['show_time'])) ?></td>
                    <td>
                        <span class="badge <?= $statusClass ?>"><?= $b['payment_status'] ?></span>
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
                            class="btn btn-sm btn-details mb-2">
                            View Details
                        </a>

                        <br>

                        <?php if ($b['payment_status'] === 'Pending'): ?>

                            <a href="cancel_booking.php?id=<?= $b['id'] ?>" 
                                class="btn btn-sm btn-danger" 
                                onclick="return confirm('Cancel this booking?')">Cancel</a>
                       
                        <?php elseif ($b['payment_status'] === 'Paid'): ?>
                            <a href="qr_ticket.php?booking_id=<?= $b['id'] ?>" class="btn btn-sm btn-primary">View QR</a>
                        
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                   
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No bookings yet. <a href="movies.php">Book now</a></div>
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