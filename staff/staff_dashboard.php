<?php

// Include authentication and database connection
require_once '../includes/staff_auth.php';
require_once '../config/db.php';


// Get dashboard statistics
$totalBookings = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
")->fetch_assoc()['total'];

$pendingPayments = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE payment_status = 'Pending'
")->fetch_assoc()['total'];

$confirmedTickets = $conn->query("
    SELECT COUNT(*) AS total
    FROM bookings
    WHERE payment_status = 'Paid'
")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - GSC</title>

    <!-- Bootstrap -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">

    <style>

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:#f6f7fb;

            min-height:100vh;
        }

        .dashboard-container{
            display:flex;
            min-height:100vh;
        }


        /* Main Content */
        .main-content{

            margin-left:280px;

            width:calc(100% - 280px);

            min-height:100vh;

            padding:50px;
        }


        /* Quick Actions */
        .quick-actions{
            margin-top:50px;
        }

        .quick-actions h3{

            font-size:24px;

            font-weight:700;

            color:#2f2f2f;

            margin-bottom:25px;
        }

        .quick-grid{

            display:grid;

            grid-template-columns:
            repeat(auto-fit,minmax(220px,1fr));

            gap:20px;

            margin-top:25px;
        }

        .quick-card{

            background:#fff;

            padding:28px;

            border-radius:20px;

            border:1px solid rgba(0,0,0,.05);

            text-decoration:none;

            color:#333;

            font-weight:700;

            box-shadow:
            0 8px 20px rgba(0,0,0,.08);

            transition:.25s;
        }

        .quick-card:hover{

            transform:translateY(-4px);

            color:#f5c518;

            box-shadow:
            0 14px 28px rgba(0,0,0,.12);
        }


        .dashboard-header{

            margin-bottom:30px;

            padding-bottom:25px;

        }

        .dashboard-header h1{

            color:#2f2f2f;

            font-size:42px;

            font-weight:800;

            margin-bottom:15px;

            text-align:center;
        }

        .dashboard-header p{

            color:#777;

            font-size:16px;

            text-align:center;
        }

        .welcome-text{
            margin-top:40px;
            margin-bottom:-20px;

            display:flex;
            justify-content:flex-end;
            padding-right:4px;
        }

        /* Statistics */
        .stats-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
            gap:25px;
        }

        .stats-card{

            background:#fff;

            border-radius:22px;

            padding:32px;

            border:1px solid rgba(0,0,0,.05);

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);

            transition:.25s;

            position:relative;

            overflow:hidden;
        }

        .stats-card::before{

            content:'';

            position:absolute;

            top:0;
            left:0;

            width:100%;
            height:4px;

            background:#f5c518;
        }

        .stats-card:hover{

            transform:translateY(-5px);

            box-shadow:
            0 16px 35px rgba(0,0,0,.12);
        }

        .stats-title{

            color:#777;

            font-size:15px;

            text-transform:uppercase;

            letter-spacing:1px;
        }

        .stats-number{

            margin-top:12px;

            font-size:52px;

            font-weight:800;

            color:#222;
        }

        .dashboard-header {
            margin-bottom: 25px;
        }

        .stats-grid {
            margin-top: 10px;
        }

    </style>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">


</head>

<body class="staff-page staff-dashboard-page">

<?php include '../includes/staff_sidebar.php'; ?>

<div class="dashboard-container">

    <!-- Main Content -->
    <div class="main-content">

        <div class="dashboard-header">

            <h1>Staff Dashboard</h1>

            <p>
                Monitor bookings, payments, tickets and walk-in customers.
                
            </p>
            
            <div class="welcome-text">
                <span class="text-muted">
                    • Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>
                </span>

            </div>

        </div>


        <!-- Statistics -->
        <div class="stats-grid">

            <!-- Total Bookings -->
            <div class="stats-card">

                <div class="stats-title">
                    Total Bookings
                </div>

                <div class="stats-number">
                    <?= $totalBookings ?>
                </div>

            </div>


            <!-- Pending Payments -->
            <div class="stats-card">

                <div class="stats-title">
                    Pending Payments
                </div>

                <div class="stats-number">
                    <?= $pendingPayments ?>
                </div>

            </div>


            <!-- Confirmed Tickets -->
            <div class="stats-card">

                <div class="stats-title">
                    Confirmed Tickets
                </div>

                <div class="stats-number">
                    <?= $confirmedTickets ?>
                </div>

            </div>

        </div>


        <!-- Quick Actions -->
        <div class="quick-actions">

            <h3>Quick Actions</h3>

            <div class="quick-grid">

                <a
                    href="<?= BASE_URL ?>/staff/customer_bookings.php"
                    class="quick-card"
                >
                    Customer Bookings
                </a>

                <a
                    href="<?= BASE_URL ?>/staff/scan_qr.php"
                    class="quick-card"
                >
                    Scan QR Ticket
                </a>

                <a
                    href="<?= BASE_URL ?>/staff/walkin_bookings.php"
                    class="quick-card"
                >
                    Walk-In Booking
                </a>

                <a
                    href="<?= BASE_URL ?>/staff/update_payment.php"
                    class="quick-card"
                >
                    Payment Status
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>