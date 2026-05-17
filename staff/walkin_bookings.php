<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// if ($_SESSION['role'] !== 'staff') {
//     die("Access denied.");
// }


if(isset($_GET['delete'])){

    $id = $_GET['delete'];

    $sql = "
        DELETE FROM walkin_bookings
        WHERE booking_code = '$id'
    ";

    if($conn->query($sql)){

        echo "
        <script>
            alert('Booking deleted successfully!');
            window.location.href='walkin_bookings.php';
        </script>
        ";

        exit();
    }
}

$walkinBookings = $conn->query("
    SELECT 
        wb.*,
        m.title AS movie_title,
        s.show_date,
        s.show_time
    FROM walkin_bookings wb
    JOIN showtimes s ON wb.showtime_id = s.id
    JOIN movies m ON s.movie_id = m.id
    ORDER BY wb.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Walk-in Booking - GSC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background:
            linear-gradient(
                rgba(245,242,234,0.92),
                rgba(255,220,164,0.92)
            );

            min-height: 100vh;
        }

        .page-container{
            padding: 40px;
        }

        .top-bar{
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 35px;
        }

        .page-title{
            font-size: 40px;

            font-weight: 700;

            color: #f5c518;

            margin: 0;
        }

        .back-btn{
            text-decoration: none;

            background: #2f2f2f;

            color: white;

            padding: 10px 20px;

            border-radius: 12px;

            font-weight: 600;

            transition: 0.25s;
        }

        .back-btn:hover{
            background: #f5c518;

            color: #111;
        }

        .add-btn{
            text-decoration: none;

            background: #ffd332;

            color: #111;

            padding: 10px 22px;

            border-radius: 12px;

            font-weight: 700;

            transition: 0.25s;
        }

        .add-btn:hover{
            background: #ffdc5f;

            color: #111;
        }

        .table-card{
            background: rgba(255,255,255,0.8);

            border-radius: 24px;

            padding: 25px;

            box-shadow:
            0 10px 30px rgba(0,0,0,0.12);
        }

        .table{
            margin-bottom: 0;
        }

        .table thead th{
            background: #ffd53b !important;

            color: #111;

            border: none;

            padding: 16px;
        }

        .table tbody td{
            padding: 16px;

            vertical-align: middle;

            background: rgba(255,255,255,0.65);

            border-color: rgba(0,0,0,0.06);
        }

        .table tbody tr:hover td{
            background: rgba(245,197,24,0.12);
        }

        .badge{
            padding: 8px 14px;

            border-radius: 20px;

            font-size: 14px;
        }

        .action-group{
            display: flex;

            gap: 8px;

            flex-wrap: wrap;
        }

        .btn-action{
            text-decoration: none;

            padding: 7px 14px;

            border-radius: 8px;

            font-size: 14px;

            font-weight: 600;

            transition: 0.25s;
        }

        .btn-view{
            background: #ffe082;

            color: #111;
        }

        .btn-edit{
            background: #fff3cd;

            color: #111;
        }

        .btn-delete{
            background: #f8d7da;

            color: #842029;
        }

        .btn-view:hover,
        .btn-edit:hover,
        .btn-delete:hover{
            transform: scale(1.04);
        }

    </style>
</head>

<body>

<div class="page-container">

    <div class="top-bar">

        <a href="staff_dashboard.php" class="back-btn">
            ← Back Dashboard
        </a>

        <h1 class="page-title">
            Walk-in Bookings
        </h1>

        <a href="add_walkin_booking.php" class="add-btn">
            + Add Booking
        </a>

    </div>

    <div class="table-card">

        <table class="table table-bordered align-middle">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th width="280">Action</th>
                </tr>
            </thead>

            <tbody>

                <?php
                $no = 1;
                while($booking = $walkinBookings->fetch_assoc()):
                ?>

                <?php
                    $statusClass = 'bg-warning text-dark';

                    if($booking['payment_status'] == 'Paid'){
                        $statusClass = 'bg-success';
                    }

                    if($booking['payment_status'] == 'Cancelled'){
                        $statusClass = 'bg-danger';
                    }
                ?>

                <tr>

                    <td>
                        <?= $no++ ?>
                    </td>

                    <td>
                        <?= $booking['booking_code'] ?>
                    </td>

                    <td>
                        <div>
                            <strong><?= $booking['customer_name'] ?></strong><br>

                            <span class="text-muted">
                                <?= $booking['movie_title'] ?>
                            </span><br>

                            <span class="text-muted">
                                <?= date('d M Y', strtotime($booking['show_date'])) ?>
                                <?= date('h:i A', strtotime($booking['show_time'])) ?>
                            </span>
                        </div>
                    </td>

                    <td>
                        <?= date('d M Y', strtotime($booking['show_date'])) ?>
                    </td>

                    <td>
                        <span class="badge <?= $statusClass ?>">
                            <?= $booking['payment_status'] ?>
                        </span>
                    </td>

                    <td>

                        <div class="action-group">

                            <a href="view_walkin_booking.php?id=<?= $booking['id'] ?>"
                               class="btn-action btn-view">
                                View
                            </a>

                            <a href="edit_walkin_booking.php?id=<?= $booking['id'] ?>"
                               class="btn-action btn-edit">
                                Edit
                            </a>

                            <a href="walkin_bookings.php?delete=<?= $booking['booking_code'] ?>"
                               class="btn-action btn-delete"
                               onclick="return confirm('Delete this booking?')">
                                Delete
                            </a>

                        </div>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>