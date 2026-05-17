<?php

//先关掉因为还没有staff login fucntion
require_once '../includes/staff_auth.php';

require_once '../config/db.php';

//if ($_SESSION['role'] !== 'staff') {
    //die("Access denied.");
//}

// 可能要改
// Example statistics
$totalBookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];

$pendingPayments = $conn->query("
    SELECT COUNT(*) as total 
    FROM bookings 
    WHERE payment_status = 'Pending'
")->fetch_assoc()['total'];

$confirmedTickets = $conn->query("
    SELECT COUNT(*) as total 
    FROM bookings 
    WHERE payment_status = 'Paid'
")->fetch_assoc()['total'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard - GSC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background:
            linear-gradient(
                rgba(245, 242, 234, 0.9),
                rgba(255, 230, 191, 0.81)
            ),
            url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=1974&auto=format&fit=crop')
            center center / cover no-repeat fixed;

            min-height: 100vh;

        }

        .dashboard-container{
            display: flex;

            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar{
            width: 270px;

            background: rgba(255, 255, 255, 0.6);

            backdrop-filter: blur(10px);

            padding: 30px 20px;

            border-right:
            1px solid rgba(255,255,255,0.08);
        }

        .sidebar h2{
            color: #f2c112;

            font-weight: 400;

            margin-bottom: 40px;

            text-align: center;
        }

        .sidebar a{
            display: block;

            text-decoration: none;

            color: #222;

            padding: 14px 18px;

            border-radius: 14px;

            margin-bottom: 12px;

            transition: 0.25s;

            font-weight: 500;
        }

        .sidebar a:hover{
            background: #ffe896;

            color: #111;

            transform: translateX(4px);
        }

        .sidebar .active{
            background: #ffde65;

            color: #111;
        }

        /* Main Content */
        .main-content{
            flex: 1;

            padding: 40px;
        }

        .welcome-box h1{
            font-size: 42px;

            font-weight: 700;

            color: #ffc800;
        }

        .welcome-box p{
            color: #878787;

            font-size: 18px;
        }

        /* Cards */
        .stats-grid{
            display: grid;

            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));

            gap: 25px;
        }

        .stats-card{
            background: rgba(255,255,255,0.72);

            border:
            1px solid rgba(0,0,0,0.06);

            border-radius: 24px;

            padding: 30px;

            backdrop-filter: blur(10px);

            transition: 0.3s;

            box-shadow:
            0 8px 24px rgba(0,0,0,0.25);
        }

        .stats-card:hover{
            transform: translateY(-6px);

            box-shadow:
            0 14px 32px rgba(0,0,0,0.35);
        }

        .stats-icon{
            font-size: 42px;

            margin-bottom: 18px;
        }

        .stats-title{
            font-size: 18px;

            color: #666;

            margin-bottom: 10px;
        }

        .stats-number{
            font-size: 40px;

            font-weight: 700;

            color: #f5c518;
        }

        /* Quick Actions */
        .quick-actions{
            margin-top: 50px;
        }

        .quick-actions h3{
            margin-bottom: 20px;

            color: #212120;
        }

        .action-buttons{
            display: flex;

            gap: 18px;

            flex-wrap: wrap;
        }

        .action-buttons a{
            text-decoration: none;

            background: #ffd53d;

            color: #373737;

            padding: 14px 24px;

            border-radius: 30px;

            font-weight: 700;

            transition: 0.25s;
        }

        .action-buttons a:hover{
            background: #ffedaa;

            transform: scale(1.04);
        }

        /* Hamburger Menu */
        .menu-wrapper{
            position: relative;
        }

        .menu-btn{
            border: none;

            background: rgba(255,255,255,0.8);

            width: 52px;
            height: 52px;

            border-radius: 14px;

            font-size: 28px;

            cursor: pointer;

            transition: 0.25s;

            box-shadow:
            0 6px 16px rgba(0,0,0,0.1);
        }

        .menu-btn:hover{
            background: #f5c518;

            transform: scale(1.05);
        }

        .dropdown-menu-custom{
            position: absolute;

            right: 0;
            top: 65px;

            width: 220px;

            background: rgba(255,255,255,0.92);

            border-radius: 18px;

            overflow: hidden;

            box-shadow:
            0 10px 24px rgba(0,0,0,0.15);

            display: none;

            z-index: 999;
        }

        .dropdown-menu-custom a{
            display: block;

            padding: 16px 20px;

            text-decoration: none;

            color: #222;

            font-weight: 600;

            transition: 0.2s;
        }

        .dropdown-menu-custom a:hover{
            background: #f5c518;
        }

        /* Top Right Menu */
        .top-bar{
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 35px;
        }

    </style>
</head>

<body>

<div class="dashboard-container">

    <!-- Sidebar -->
    <div class="sidebar">

        <br><br>
        <h2> Staff Panel</h2>

        <a href="staff_dashboard.php" class="active">
            Dashboard
        </a>

        <a href="customer_bookings.php">
            View Customer Bookings
        </a>

        <a href="update_payment.php">
            Update Payment Status
        </a>

        <a href="scan_qr.php">
            Scan QR Ticket
        </a>

        <a href="walkin_bookings.php">
            Walk-in Bookings
        </a>

    </div>

    <!-- Main Content -->
    <div class="main-content">

    <div class="top-bar">

    <div class="welcome-box">

        <h1>
            Welcome, Staff 👋
        </h1>

        <p>
            Manage cinema bookings, payments, tickets, and walk-in customers.
        </p>

    </div>

    <div class="menu-wrapper">

        <button class="menu-btn" onclick="toggleMenu()">
            ☰
        </button>

        <div class="dropdown-menu-custom" id="dropdownMenu">

            <a href="profile.php">
                My Profile
            </a>

            <a href="../logout.php">
                Sign Out
            </a>

        </div>

    </div>

</div>


        <!-- Stats Cards -->
        <div class="stats-grid">

            <div class="stats-card">

                <div class="stats-icon">
                    🎟️
                </div>

                <div class="stats-title">
                    Total Bookings
                </div>

                <div class="stats-number">
                    <?= $totalBookings ?>
                </div>

            </div>

            <div class="stats-card">

                <div class="stats-icon">
                    ⏳
                </div>

                <div class="stats-title">
                    Pending Payments
                </div>

                <div class="stats-number">
                    <?= $pendingPayments ?>
                </div>

            </div>

            <div class="stats-card">

                <div class="stats-icon">
                    ✅
                </div>

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

            <h3>
                Quick Actions
            </h3>

            <div class="action-buttons">

                <a href="customer_bookings.php">
                    View Customer Bookings
                </a>

                <a href="scan_qr.php">
                    Scan QR
                </a>

            </div>

        </div>

    </div>

</div>

<script>
function toggleMenu() {

    const menu = document.getElementById("dropdownMenu");

    if(menu.style.display === "block"){
        menu.style.display = "none";
    } else {
        menu.style.display = "block";
    }
}

window.onclick = function(event){

    if(!event.target.matches('.menu-btn')){

        const menu = document.getElementById("dropdownMenu");

        if(menu.style.display === "block"){
            menu.style.display = "none";
        }
    }
}
</script>
</body>
</html>