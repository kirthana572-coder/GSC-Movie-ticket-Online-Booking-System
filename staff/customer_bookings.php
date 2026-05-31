<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

// Update payment status
if(isset($_POST['update_status'])){

    $booking_id   = intval($_POST['booking_id']);
    $new_status   = $_POST['payment_status'];

    $current = $conn->query("
        SELECT payment_status, cancel_reason, cancelled_by 
        FROM bookings 
        WHERE id = $booking_id
    ")->fetch_assoc();

    if ($current['cancelled_by'] === 'admin') {
        $_SESSION['success'] = "This booking was cancelled by admin and cannot be modified.";
        header("Location: customer_bookings.php");
        exit();
    }

    if ($current['payment_status'] === 'Cancelled') {
        $_SESSION['success'] = "This booking is already cancelled and cannot be modified.";
        header("Location: customer_bookings.php");
        exit();
    }

    $new_status = $_POST['payment_status'];

    $cancel_reason = $_POST['cancel_reason'] ?? $current['cancel_reason'];

    $cancelled_by  = $current['cancelled_by'];

    if ($new_status === 'Cancelled') {

        $cancel_reason = $cancel_reason ?: "Cancelled by staff";
        $cancelled_by  = $cancelled_by ?: "staff";
    }


    $allowed = ['Pending', 'Paid', 'Cancelled', 'Expired'];


    if (!in_array($current['payment_status'], ['Pending', 'Paid'])) {
        $_SESSION['success'] = "This booking cannot be modified.";
        header("Location: customer_bookings.php");
        exit();
    }

    if(in_array($new_status, $allowed)){

        // 如果 staff cancel
        if($new_status === 'Cancelled'){

            $stmt = $conn->prepare("
                UPDATE bookings
                SET
                    payment_status = ?,
                    cancel_reason = ?,
                    cancelled_by = ?,
                    cancelled_at = NOW()
                WHERE id = ?
            ");

            $stmt->bind_param(
                "sssi",
                $new_status,
                $cancel_reason,
                $cancelled_by,
                $booking_id
            );

        } else {

            $stmt = $conn->prepare("

                UPDATE bookings

                SET payment_status = ?

                WHERE id = ?

            ");

            $stmt->bind_param(
                "si",
                $new_status,
                $booking_id
            );
        }

        $stmt->execute();

        /* PAID -> BOOKED */

        if($new_status === 'Paid'){

            $seatStmt = $conn->prepare("

                UPDATE seats s

                JOIN booking_seats bs
                ON s.id = bs.seat_id

                SET s.status = 'booked'

                WHERE bs.booking_id = ?

            ");

            $seatStmt->bind_param(
                "i",
                $booking_id
            );

            $seatStmt->execute();

            $seatStmt->close();
        }

        /* 如果是 Cancelled，要 release seats */
        if ($new_status === 'Cancelled') {

            $release = $conn->prepare("
                UPDATE seats s
                JOIN booking_seats bs ON s.id = bs.seat_id
                SET s.status = 'available'
                WHERE bs.booking_id = ?
            ");

            $release->bind_param("i", $booking_id);
            $release->execute();
            $release->close();
        }
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
        b.cancel_reason,
        b.cancelled_by,
        b.cancelled_at,
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

                            <form method="POST" class="d-flex flex-column gap-2">

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

                                <?php if($b['cancelled_by'] === 'admin'): ?>

                                    <option selected disabled>
                                        Cancelled by Admin
                                    </option>

                                <?php elseif($b['payment_status'] === 'Pending'): ?>

                                    <option value="Pending" selected>
                                        Pending
                                    </option>

                                    <option value="Paid">
                                        Paid
                                    </option>

                                    <option value="Cancelled">
                                        Cancelled
                                    </option>

                                <?php elseif($b['payment_status'] === 'Paid'): ?>

                                    <option value="Paid" selected>
                                        Paid
                                    </option>

                                <?php elseif($b['payment_status'] === 'Cancelled'): ?>

                                    <option value="Cancelled" selected>
                                        Cancelled
                                    </option>

                                <?php elseif($b['payment_status'] === 'Expired'): ?>

                                    <option value="Expired" selected>
                                        Expired
                                    </option>

                                <?php endif; ?>

                                </select>

                                <input
                                    type="text"
                                    name="cancel_reason"
                                    id="cancelReasonInput"
                                    class="form-control form-control-sm"
                                    placeholder="Cancel reason"
                                    style="display:none;"
                                />

                                <button 
                                    type="submit"
                                    name="update_status"
                                    class="btn btn-sm btn-dark fw-bold"

                                    <?= (
                                        $b['payment_status'] !== 'Pending'
                                        || $b['cancelled_by'] === 'admin'
                                    ) ? 'disabled' : '' ?>
                                >
                                    Update
                                </button>

                            </form>

                            <?php if ($b['payment_status'] === 'Cancelled'): ?>

                                <div class="small text-danger mt-1">
                                    <?= htmlspecialchars($b['cancel_reason'] ?? '') ?>
                                </div>

                            <?php endif; ?>

                        </td>

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

<script>

document.querySelectorAll('select[name="payment_status"]').forEach(select => {

    const form = select.closest('form');
    const reasonInput = form.querySelector('input[name="cancel_reason"]');

    // 初始化隐藏
    reasonInput.style.display = 'none';

    select.addEventListener('change', function () {

        if (this.value === 'Cancelled') {
            reasonInput.style.display = 'block';
        } else {
            reasonInput.style.display = 'none';
            reasonInput.value = '';
        }

    });

});

</script>

</body>
</html>