<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

$user_id = intval($_GET['id'] ?? 0);

if(!$user_id){

    die("Invalid User.");
}


/* USER INFO */

$stmt = $conn->prepare("

    SELECT
        id,
        full_name,
        email,
        role,
        created_at

    FROM users

    WHERE id = ?
    AND role = 'customer'

");

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if(!$user){

    die("Customer not found.");
}


/* BOOKING HISTORY */

$bookings = $conn->query("

    SELECT

        b.id,
        b.payment_status,
        b.booking_date,

        m.title,

        br.name AS branch_name,

        s.show_date,
        s.show_time,

        SUM(
            CASE bs.ticket_type
                WHEN 'Adult' THEN 12
                WHEN 'Senior' THEN 8
                WHEN 'Student' THEN 10
                WHEN 'Children' THEN 6
                ELSE 12
            END
        ) AS total_price

    FROM bookings b

    JOIN showtimes s
    ON b.showtime_id = s.id

    JOIN movies m
    ON s.movie_id = m.id

    JOIN branches br
    ON s.branch_id = br.id

    LEFT JOIN booking_seats bs
    ON b.id = bs.booking_id

    WHERE b.user_id = {$user_id}

    GROUP BY b.id

    ORDER BY b.id DESC

");

?>
<!DOCTYPE html>
<html>

<head>

<title>
    View User - GSC
</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<style>

body{
    margin:0;
    font-family:'Segoe UI',sans-serif;

    background:
    linear-gradient(
        135deg,
        #f8fafc,
        #eef2ff
    );

    min-height:100vh;
}

.main{
    margin-left:260px;
    padding:40px;
}

.page-title{
    font-size:48px;
    font-weight:800;

    color:#111827;

    letter-spacing:-1px;

    text-align:center;

    margin-bottom:35px;
}

.card-box{

    background:white;

    border-radius:24px;

    padding:30px;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.08);

    margin-bottom:25px;
}

.section-title{

    font-size:22px;

    font-weight:700;

    margin-bottom:20px;

    color:#111827;
}

.info-row{

    display:flex;

    justify-content:space-between;

    padding:14px 0;

    border-bottom:
    1px solid #f1f5f9;
}

.info-label{

    color:#64748b;
}

.info-value{

    font-weight:600;
}

.table{
    margin-bottom:0;
}

.table thead th{

    background:#f8fafc;

    border:none;

    padding:16px;

    font-weight:700;
}

.table tbody td{

    padding:16px;
}

.status{

    padding:6px 12px;

    border-radius:999px;

    font-size:13px;

    font-weight:700;
}

.paid{
    background:#dcfce7;
    color:#166534;
}

.pending{
    background:#fef3c7;
    color:#92400e;
}

.cancelled{
    background:#fee2e2;
    color:#991b1b;
}

.action-btn{

    background:#dbeafe;

    color:#1d4ed8;

    padding:8px 14px;

    border-radius:12px;

    text-decoration:none;

    font-weight:600;
}

.action-btn:hover{

    transform:scale(1.05);
}

.back-btn{

    background:#f3f4f6;

    color:#111827;

    font-weight:600;

    padding:15px 30px;

    border-radius:18px;

    text-decoration:none;

    transition:0.25s;

    border:1px solid #e5e7eb;

    margin-top: 30px;

    display: inline-block;
}

.back-btn:hover{

    background:#e5e7eb;

    color:#111827;

    transform:translateY(-2px);
}

</style>

</head>

<body>

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

<h1 class="page-title">
    User Details
</h1>

<div class="card-box">

<h3 class="section-title">
    Customer Information
</h3>

<div class="info-row">
    <span class="info-label">Full Name</span>
    <span class="info-value">
        <?= htmlspecialchars($user['full_name']) ?>
    </span>
</div>

<div class="info-row">
    <span class="info-label">Email</span>
    <span class="info-value">
        <?= htmlspecialchars($user['email']) ?>
    </span>
</div>

<div class="info-row">
    <span class="info-label">Role</span>
    <span class="info-value">
        <?= ucfirst($user['role']) ?>
    </span>
</div>

<div class="info-row">
    <span class="info-label">Registered</span>
    <span class="info-value">
        <?= date('d M Y h:i A', strtotime($user['created_at'])) ?>
    </span>
</div>

</div>

<div class="card-box">

    <h3 class="section-title">
        Booking History
    </h3>

    <table class="table align-middle">

        <thead>

            <tr>

                <th>
                    Booking ID
                </th>

                <th>
                    Movie
                </th>

                <th>
                    Branch
                </th>

                <th>
                    Show Date
                </th>

                <th>
                    Status
                </th>

                <th>
                    Total
                </th>

                <th>
                    Action
                </th>

            </tr>

        </thead>

        <tbody>

        <?php if($bookings->num_rows > 0): ?>

            <?php while($b = $bookings->fetch_assoc()): ?>

                <?php

                $statusClass = 'pending';

                if(
                    strtolower($b['payment_status']) == 'paid'
                ){
                    $statusClass = 'paid';
                }

                if(
                    strtolower($b['payment_status']) == 'cancelled'
                ){
                    $statusClass = 'cancelled';
                }

                ?>

                <tr>

                    <td>

                        #<?= $b['id'] ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($b['title']) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($b['branch_name']) ?>

                    </td>

                    <td>

                        <?= date(
                            'd M Y',
                            strtotime($b['show_date'])
                        ) ?>

                        <br>

                        <small class="text-muted">

                            <?= date(
                                'h:i A',
                                strtotime($b['show_time'])
                            ) ?>

                        </small>

                    </td>

                    <td>

                        <span class="status <?= $statusClass ?>">

                            <?= htmlspecialchars($b['payment_status']) ?>

                        </span>

                    </td>

                    <td>

                        RM <?= number_format($b['total_price'], 2) ?>

                    </td>

                    <td>

                        <a
                            href="view_booking.php?id=<?= $b['id'] ?>"
                            class="action-btn"
                        >
                            View
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

        <?php else: ?>

            <tr>

                <td colspan="7">

                    <div class="text-center py-4 text-muted">

                        No booking history found.

                    </div>

                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

    <div class="text-center mt-4">

        <a
            href="users.php"
            class="back-btn"
        >
            Back
        </a>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>