<?php

require_once '../includes/admin_auth.php';
require_once '../config/db.php';

if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

// Total Movies
$totalMovies = $conn->query("
    SELECT COUNT(*) AS total 
    FROM movies
")->fetch_assoc()['total'];

// Total Users
$totalCustomers = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'customer'
")->fetch_assoc()['total'];

// Total Bookings
$totalTicketsSold = $conn->query("
    SELECT COUNT(*) AS total
    FROM booking_seats bs
    JOIN bookings b
    ON bs.booking_id = b.id
    WHERE b.payment_status = 'Paid'
")->fetch_assoc()['total'];

// Total Sales
$totalSales = $conn->query("
    SELECT SUM(
        CASE
            WHEN bs.ticket_type = 'Adult' THEN 12
            WHEN bs.ticket_type = 'Senior' THEN 8
            WHEN bs.ticket_type = 'Student' THEN 10
            WHEN bs.ticket_type = 'Children' THEN 6
            ELSE 0
        END
    ) AS total_sales
    FROM booking_seats bs
    JOIN bookings b
    ON bs.booking_id = b.id
    WHERE b.payment_status = 'Paid'
")->fetch_assoc()['total_sales'];

if (!$totalSales) {
    $totalSales = 0;
}

$weeklyLabels = [];
$weeklySales  = [];

for($i = 6; $i >= 0; $i--){

    $date = date('Y-m-d', strtotime("-$i days"));

    $weeklyLabels[] =
        date('D', strtotime($date));

    $stmt = $conn->prepare("

        SELECT
            COALESCE(
                SUM(
                    CASE
                        WHEN bs.ticket_type = 'Adult' THEN 12
                        WHEN bs.ticket_type = 'Student' THEN 10
                        WHEN bs.ticket_type = 'Senior' THEN 8
                        WHEN bs.ticket_type = 'Children' THEN 6
                        ELSE 0
                    END
                ),
                0
            ) AS total

        FROM bookings b

        JOIN booking_seats bs
        ON b.id = bs.booking_id

        WHERE b.payment_status = 'Paid'
        AND DATE(b.booking_date) = ?

    ");

    $stmt->bind_param("s", $date);

    $stmt->execute();

    $row = $stmt
        ->get_result()
        ->fetch_assoc();

    $weeklySales[] =
        (float)$row['total'];
}

// Recent Bookings
$recentBookings = $conn->query("
    SELECT 
        b.id,
        b.payment_status,
        m.title,
        u.full_name
    FROM bookings b

    JOIN users u
    ON b.user_id = u.id

    JOIN showtimes s
    ON b.showtime_id = s.id

    JOIN movies m
    ON s.movie_id = m.id

    ORDER BY b.id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Segoe UI',sans-serif;

            background:
            linear-gradient(
                135deg,
                #f8fafc,
                #eef2ff
            );

            min-height:100vh;
        }

        .layout{
            display:flex;
        }

        /* SIDEBAR */

        .sidebar{
            width:260px;
            min-height:100vh;

            background:
            linear-gradient(
                180deg,
                #111827,
                #1f2937
            );

            padding:30px 20px;

            position:fixed;
            left:0;
            top:0;

            box-shadow:
            4px 0 25px rgba(0,0,0,0.2);
        }

        .logo{
            color:#f5c518;
            font-size:35px;
            font-weight:500;
            margin-bottom:40px;
        }

        .sidebar a{
            display:block;

            text-decoration:none;

            color:white;

            padding:12px 16px;

            border-radius:14px;

            margin-bottom:3px;

            transition:0.25s;

            font-weight:500;
        }

        .sidebar a:hover{
            background:rgba(255,255,255,0.08);
            transform:translateX(5px);
        }

        .sidebar a.active{
            background:rgb(255, 231, 46);
            color:#111 !important;
        }

        .logout-btn{
            margin-top:40px !important;

            background:rgba(213, 213, 213, 0.72);
            color:rgb(10, 10, 10) !important;
        }

        .logout-btn:hover{
            background:rgba(255, 253, 207, 0.89) !important;
            color:#000 !important;
        }

        /*MAIN CONTENT*/

        .main{
            margin-left:270px;
            width:100%;
            padding:40px;
        }

        .topbar{
            background:white;

            padding:20px 30px;

            border-radius:22px;

            display:flex;

            justify-content:space-between;

            align-items:center;

            box-shadow:
            0 8px 20px rgba(0,0,0,0.08);

            margin-bottom:35px;
        }

        .topbar h1{
            font-size:42px;
            font-weight:700;
            color:#111827;
        }

        .admin-info{
            font-weight:600;
            color:#555;
        }

        /* CARDS */

        .cards{
            display:grid;

            grid-template-columns:
            repeat(auto-fit,minmax(250px,1fr));

            gap:25px;

            margin-bottom:35px;
        }

        .card-box{
            background:white;

            border-radius:24px;

            padding:30px;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);

            transition:0.3s;
        }

        .card-box:hover{
            transform:translateY(-6px);
        }

        .card-title{
            font-size:18px;
            color:#666;

            margin-bottom:18px;
        }

        .card-value{
            font-size:43px;
            font-weight:500;
            color:#f5c518;
        }

        /* CHART + RECENT */

        .bottom-grid{
            display:grid;

            grid-template-columns:2fr 1fr;

            gap:25px;
        }

        .panel{
            background:white;

            border-radius:24px;

            padding:30px;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);
        }

        .panel h3{
            margin-bottom:25px;

            font-size:28px;

            color:#111827;
        }

        .recent-item{
            padding:14px 0;

            border-bottom:
            1px solid rgba(0,0,0,0.08);
        }

        .recent-item:last-child{
            border:none;
        }

        .booking-title{
            font-weight:700;
            color:#111;
        }

        .booking-user{
            color:#666;
            font-size:14px;
        }

        .badge{
            padding:8px 14px;
            border-radius:20px;
            font-size:13px;
        }

        .dashboard-card{

            text-decoration:none;

            display:block;

            color:inherit;
        }

        .dashboard-card:hover{

            text-decoration:none;

            color:inherit;

            transform:translateY(-6px);

            box-shadow:
            0 15px 35px rgba(0,0,0,.12);
        }

    </style>

</head>

<body class="admin-page admin-dashboard-page">
    
<?php include '../includes/admin_sidebar.php'; ?>

<div class="layout">

    <!-- MAIN -->
    <div class="main">

        <!-- TOPBAR -->

        <div class="topbar">

            <h1>
                Admin Dashboard
            </h1>

            <div class="admin-info">
                👤 Welcome, <?= $_SESSION['full_name'] ?>
            </div>

        </div>


        <!-- CARDS -->

        <div class="cards">

            <!-- Total Movies -->
            <a href="<?= BASE_URL ?>/admin/movies/admin_movies.php" class="card-box dashboard-card">
                <div class="card-title">Total Movies</div>
                <div class="card-value"><?= $totalMovies ?></div>
            </a>

            <!-- Total Customers -->
            <a href="<?= BASE_URL ?>/admin/users/users.php" class="card-box dashboard-card">
                <div class="card-title">Total Customers</div>
                <div class="card-value"><?= $totalCustomers ?></div>
            </a>

            <!-- Total Tickets -->
            <div class="card-box">
                <div class="card-title">Total Tickets Sold</div>
                <div class="card-value"><?= $totalTicketsSold ?></div>
            </div>

            <!-- Total Revenue -->
            <div class="card-box">
                <div class="card-title">Total Revenue</div>
                <div class="card-value">RM <?= number_format($totalSales, 2) ?></div>
            </div>

        </div>


        <!-- CHART + RECENT -->

        <div class="bottom-grid">

            <!-- SALES CHART -->

            <div class="panel">

                <h3>
                    Weekly Sales
                </h3>

                <div style="height:400px;">
                    <canvas id="salesChart"></canvas>
                </div>

            </div>


            <!-- RECENT BOOKINGS -->

            <div class="panel">

                <h3>
                    Recent Bookings
                </h3>

                <?php while($r = $recentBookings->fetch_assoc()): ?>

                    <?php

                    $statusClass = 'bg-warning text-dark';

                    if($r['payment_status'] == 'Paid'){
                        $statusClass = 'bg-success';
                    }

                    if(
                        $r['payment_status'] == 'Cancelled' ||
                        $r['payment_status'] == 'Expired'
                    ){
                        $statusClass = 'bg-danger';
                    }

                    ?>

                    <div class="recent-item">

                        <div class="booking-title">
                            #<?= $r['id'] ?>
                            —
                            <?= htmlspecialchars($r['title']) ?>
                        </div>

                        <div class="booking-user">
                            <?= htmlspecialchars($r['full_name']) ?>
                        </div>

                        <div class="mt-2">
                            <span class="badge <?= $statusClass ?>">
                                <?= $r['payment_status'] ?>
                            </span>
                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        </div>

    </div>

</div>


<script>

new Chart(document.getElementById('salesChart'), {

    type: 'line',

    data: {

        labels: <?= json_encode($weeklyLabels) ?>,

        datasets: [{

            label: 'Sales (RM)',

            data: <?= json_encode($weeklySales) ?>,

            borderColor: '#f5c518',

            backgroundColor: 'rgba(245,197,24,0.2)',

            fill: true,

            tension: 0.4,

            borderWidth: 3
        }]
    },

    options: {

        responsive: true,

        maintainAspectRatio: false,

        scales: {

            y: {

                beginAtZero: true,

                ticks: {

                    stepSize: 50,

                    callback: function(value){

                        return 'RM ' + value;
                    }
                }
            }
        },

        plugins: {

            legend: {

                display: true
            }
        }
    }
});

</script>

</body>
</html>