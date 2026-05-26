<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

// Update payment status
if(isset($_POST['update_status'])){

    $booking_id = intval($_POST['booking_id']);
    $new_status = $_POST['payment_status'];

    $allowed = ['Pending', 'Paid', 'Cancelled', 'Expired'];

    if(in_array($new_status, $allowed)){

        $stmt = $conn->prepare("
            UPDATE bookings
            SET payment_status = ?
            WHERE id = ?
        ");

        $stmt->bind_param("si", $new_status, $booking_id);
        $stmt->execute();
        $stmt->close();

        header("Location: customer_bookings.php");
        exit();
    }
}

// Get search input
$search = $_GET['search'] ?? '';


// SQL query
$sql = "
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

    JOIN users u
    ON b.user_id = u.id

    JOIN showtimes s
    ON b.showtime_id = s.id

    JOIN movies m
    ON s.movie_id = m.id

    JOIN branches br
    ON s.branch_id = br.id
";


// Search by booking ID
if (!empty($search)) {

    $search = $conn->real_escape_string($search);

    $sql .= "
        WHERE b.id = '$search'
    ";
}


// Order latest booking first
$sql .= "
    ORDER BY b.booking_date DESC
";


// Execute query
$bookings = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Customer Bookings</title>

    <!-- Bootstrap CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <style>

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:linear-gradient(
                rgba(245,242,234,0.92),
                rgba(255,220,164,0.92)
            );
            min-height:100vh;
        }

        .container-box{
            padding:40px;
        }

        .page-title{
            margin-top:40px !important;
            margin-bottom:50px !important;
            position:absolute;
            left:50%;
            top:30px;
            transform:translateX(-50%);
            font-size:55px;
            font-weight:700;
            color:#f5c518;
            margin:0;
        }

        .search-bar{
            display:flex;
            justify-content:center;
            align-items:center;
            gap:12px;
            margin-top:80px;
            margin-bottom:50px;
        }

        .search-bar input{
            max-width:450px !important;
            height:46px;
        }

        .table{
            background:rgba(255,255,255,0.77) !important;
            border-radius:20px;
            overflow:hidden;
        }

        .table thead th{
            background:#ffd748 !important;
            color:#111;
            border:none;
            padding:18px;
        }

        .table tbody td{
            background:rgba(255,255,255,0.72) !important;
            border-color:rgba(0,0,0,0.08);
            padding:18px;
        }

        .table tbody tr:hover td{
            background:rgba(245,197,24,0.15) !important;
        }

        .badge{
            padding:8px 14px;
            border-radius:20px;
        }

        .btn-details{
            background:#ffdc5f !important;
            border-radius:4px !important;
            padding:7px 15px !important;
            font-weight:500 !important;
            transition:0.25s;
        }

        .btn-details:hover{
            background:#ffd028 !important;
            transform:scale(1.05);
        }

        .top-bar{
            margin-bottom:28px;
        }

        .back-btn{
            display:inline-block;
            text-decoration:none;
            background:#323232;
            color:white;
            padding:8px 18px;
            border-radius:10px;
            font-weight:400;
            transition:0.25s;
        }

        .back-btn:hover{
            background:#f5c518;
            transform:translateY(-2px);
        }

        .btn-ticket{
            background:#95db84 !important;
            color:#111 !important;
            border:none !important;
            padding:7px 15px !important;
            font-weight:600 !important;
            transition:0.25s;
        }

        .btn-ticket:hover{
            background:#27b220 !important;
            transform:scale(1.05);
        }

    </style>

</head>

<body>

    <div class="container-box">

        <!-- Back button -->
        <div class="top-bar">

            <a 
                href="<?= BASE_URL ?>/staff/staff_dashboard.php" 
                class="back-btn"
            >
                ← Back Dashboard
            </a>

        </div>


        <!-- Page title -->
        <h1 class="page-title">
            Customer Bookings
        </h1>


        <!-- Search form -->
        <form 
            method="GET" 
            class="search-bar"
        >

            <input 
                type="text"
                name="search"
                class="form-control"
                placeholder="Search Booking ID"
                value="<?= htmlspecialchars($search) ?>"
                style="max-width:300px; height:46px;"
            >

            <button 
                type="submit"
                class="btn btn-warning fw-bold px-4"
            >
                Search
            </button>


            <!-- Reset button -->
            <?php if ($search != ''): ?>

                <a 
                    href="<?= BASE_URL ?>/staff/customer_bookings.php"
                    class="btn btn-sm btn-primary fw-bold"
                >
                    Reset
                </a>

            <?php endif; ?>

        </form>


        <!-- Booking table -->
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

                <?php while ($b = $bookings->fetch_assoc()): ?>

                    <?php

                    // Status badge color
                    $statusClass = 'bg-warning text-dark';

                    if ($b['payment_status'] == 'Paid') {
                        $statusClass = 'bg-success';
                    }

                    if (
                        in_array(
                            $b['payment_status'],
                            ['Cancelled', 'Expired']
                        )
                    ) {
                        $statusClass = 'bg-danger';
                    }

                    ?>

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

                            <form method="POST" class="d-flex gap-2 align-items-center">

                                <input 
                                    type="hidden" 
                                    name="booking_id" 
                                    value="<?= $b['id'] ?>"
                                >

                                <select 
                                    name="payment_status"
                                    class="form-select form-select-sm"
                                    style="min-width:120px;"
                                >

                                    <option value="Pending"
                                        <?= $b['payment_status'] == 'Pending' ? 'selected' : '' ?>>
                                        Pending
                                    </option>

                                    <option value="Paid"
                                        <?= $b['payment_status'] == 'Paid' ? 'selected' : '' ?>>
                                        Paid
                                    </option>

                                    <option value="Cancelled"
                                        <?= $b['payment_status'] == 'Cancelled' ? 'selected' : '' ?>>
                                        Cancelled
                                    </option>

                                    <option value="Expired"
                                        <?= $b['payment_status'] == 'Expired' ? 'selected' : '' ?>>
                                        Expired
                                    </option>

                                </select>

                                <button 
                                    type="submit"
                                    name="update_status"
                                    class="btn btn-sm btn-dark fw-bold"
                                >
                                    Update
                                </button>

                            </form>

                        </td>

                        <td>

                            <!-- View details button -->
                            <a 
                                href="<?= BASE_URL ?>/staff/booking_details.php?booking_id=<?= $b['id'] ?>"
                                class="btn btn-details"
                            >
                                View Details
                            </a>


                            <!-- QR button -->
                            <?php if ($b['payment_status'] == 'Paid'): ?>

                                <br>

                                <a 
                                    href="<?= BASE_URL ?>/staff/generate_ticket.php?booking_id=<?= $b['id'] ?>"
                                    class="btn btn-ticket mt-1"
                                >
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