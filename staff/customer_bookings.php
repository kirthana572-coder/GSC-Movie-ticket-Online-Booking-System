<?php
require_once '../config/db.php';
require_once '../includes/staff_auth.php';
//if ($_SESSION['role'] !== 'staff') {
    //die("Access denied.");
//}

$bookings = $conn->query("
    SELECT 
        b.id,
        b.payment_status,
        b.booking_date,

        u.full_name,

        m.title,

        s.show_date,
        s.show_time,

        br.name AS branch_name

    FROM bookings b

    JOIN users u ON b.user_id = u.id
    JOIN showtimes s ON b.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    JOIN branches br ON s.branch_id = br.id

    ORDER BY b.booking_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Bookings</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background:
            linear-gradient(
                rgba(245,242,234,0.92),
                rgba(255, 220, 164, 0.92)
            );

            min-height: 100vh;
        }

        .container-box{
            padding: 40px;
        }

        .page-title{
            font-size: 50px;
            font-weight: 700;

            color: #f5c518;

            margin-top: 30px;

            margin-bottom: 25px;
        }

        /*table*/
        .table{
            background: rgba(255, 255, 255, 0.77) !important;

            border-radius: 20px;

            overflow: hidden;
        }

        .table thead th{
            background: #ffd748 !important;

            color: #111;

            border: none;

            padding: 18px;
        }

        .table tbody td{
            background: rgba(255,255,255,0.72) !important;

            border-color: rgba(0,0,0,0.08);

            padding: 18px;
        }

        .table tbody tr:hover td{
            background: rgba(245,197,24,0.15) !important;
        }

        .badge{
            padding: 8px 14px;

            border-radius: 20px;
        }

        /*Details Button*/
        .btn-details{

            background: #ffdc5f !important;

            border-radius: 4px !important;

            padding: 7px 15px !important;

            font-weight: 500 !important;

            transition: 0.25s;
        }

        .btn-details:hover{
            background: #ffd028 !important;

            transform: scale(1.05);
        }

        /*Backbutton*/
        .top-bar{
            margin-bottom: 28px;
        }

        .back-btn{
            display: inline-block;

            text-decoration: none;

            background: #323232;

            color: white;

            padding: 8px 18px;

            border-radius: 10px;

            font-weight: 400;

            transition: 0.25s;
        }

        .back-btn:hover{
            background: #f5c518;


            transform: translateY(-2px);
        }

        /*Print Ticket Button*/
        .btn-ticket{

            background: #95db84 !important;

            color: #111 !important;

            border: none !important;

            padding: 7px 15px !important;

            font-weight: 600 !important;

            transition: 0.25s;
        }

        .btn-ticket:hover{

            background: #27b220 !important;

            transform: scale(1.05);
        }
            </style>
</head>

<body>

<div class="container-box">

    <div class="top-bar">

        <a href="staff_dashboard.php" class="back-btn">
            ← Back Dashboard
        </a>

    </div>

    <h1 class="page-title">
        Customer Bookings
    </h1>

    <table class="table table-bordered">

        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Customer</th>
                <th>Movie</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

        <?php while($b = $bookings->fetch_assoc()): ?>

            <tr>

                <td>
                    #<?= $b['id'] ?>
                </td>

                <td>
                    <?= htmlspecialchars($b['full_name']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($b['title']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($b['branch_name']) ?>
                </td>

                <td>
                    <?= date('d M Y', strtotime($b['show_date'])) ?>
                </td>

                <td>
                    <?= date('h:i A', strtotime($b['show_time'])) ?>
                </td>

                <td>

                    <?php
                    $statusClass = 'bg-warning text-dark';

                    if ($b['payment_status'] == 'Paid'){
                        $statusClass = 'bg-success';
                    }

                    if (
                        $b['payment_status'] == 'Cancelled' ||
                        $b['payment_status'] == 'Expired'
                    ){
                        $statusClass = 'bg-danger';
                    }
                    ?>

                    <span class="badge <?= $statusClass ?>">
                        <?= $b['payment_status'] ?>
                    </span>

                </td>

                <td>

                    <a href="booking_details.php?booking_id=<?= $b['id'] ?>"
                        class="btn btn-details">
                        View Details
                    </a>
                    <?php if ($b['payment_status'] == 'Paid'): ?>
                <br>
                    <a href="generate_ticket.php?booking_id=<?= $b['id'] ?>" 
                        class="btn btn-ticket mt-1">
                        View QR
                    </a>
                    <?php endif; ?>
                </td>

            </tr>

        <?php endwhile; ?>

        </tbody>

    </table>

</div>

</body>
</html>