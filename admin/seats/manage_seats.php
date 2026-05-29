<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';


$showtime_id = $_GET['showtime_id'] ?? 0;

if(!$showtime_id){

    die("Invalid showtime.");
}


/* GET SHOWTIME */

$stmt = $conn->prepare("

    SELECT
        s.*,
        m.title,
        b.name AS branch_name

    FROM showtimes s

    JOIN movies m
    ON s.movie_id = m.id

    JOIN branches b
    ON s.branch_id = b.id

    WHERE s.id = ?

");

$stmt->bind_param("i", $showtime_id);

$stmt->execute();

$showtime = $stmt
    ->get_result()
    ->fetch_assoc();


if(!$showtime){

    die("Showtime not found.");
}


/* UPDATE SEAT STATUS */

if(isset($_POST['seat_id'])){

    $seat_id = $_POST['seat_id'];
    $new_status = trim($_POST['status']);

    // GET CURRENT STATUS
    $seatStmt = $conn->prepare("

        SELECT status
        FROM seats
        WHERE id = ?

    ");

    $seatStmt->bind_param(
        "i",
        $seat_id
    );

    $seatStmt->execute();

    $seat = $seatStmt
        ->get_result()
        ->fetch_assoc();

    $old_status = trim($seat['status']);


    /* ALLOWED ADMIN ACTIONS

        pending  -> available
        blocked  -> available
        available -> blocked

        NOT ALLOWED:
        booked -> anything
        available -> pending
        pending -> blocked
        blocked -> pending

    */

    $allowed = false;

    if(
        $old_status === 'pending'
        &&
        $new_status === 'available'
    ){
        $allowed = true;
    }

    if(
        $old_status === 'blocked'
        &&
        $new_status === 'available'
    ){
        $allowed = true;
    }

    if(
        $old_status === 'available'
        &&
        $new_status === 'blocked'
    ){
        $allowed = true;
    }


    // INVALID ACTION
    if(!$allowed){

        $_SESSION['success'] =
            "Invalid seat status action.";

        header(
            "Location: manage_seats.php?showtime_id=" . $showtime_id
        );

        exit();
    }



    /* PENDING -> AVAILABLE
       CANCEL USER BOOKING */

    if(
        $old_status === 'pending'
        &&
        $new_status === 'available'
    ){

        // FIND BOOKING
        $bookingStmt = $conn->prepare("

            SELECT
                b.id,
                b.user_id

            FROM bookings b

            JOIN booking_seats bs
            ON b.id = bs.booking_id

            WHERE bs.seat_id = ?
            AND b.showtime_id = ?
            AND b.payment_status = 'Pending'

            LIMIT 1

        ");

        $bookingStmt->bind_param(
            "ii",
            $seat_id,
            $showtime_id
        );

        $bookingStmt->execute();

        $booking = $bookingStmt
            ->get_result()
            ->fetch_assoc();


        if($booking){

            $booking_id = $booking['id'];
            $user_id    = $booking['user_id'];


            // CANCEL BOOKING
            $cancelReason =
                "Cancelled by admin because selected seat became unavailable.";

            $cancelledBy =
                "admin";

            $cancelStmt = $conn->prepare("

                UPDATE bookings

                SET
                    payment_status = 'Cancelled',
                    cancel_reason = ?,
                    cancelled_by = ?,
                    cancelled_at = NOW()

                WHERE id = ?

            ");

            $cancelStmt->bind_param(
                "ssi",
                $cancelReason,
                $cancelledBy,
                $booking_id
            );

            $cancelStmt->execute();


            // RELEASE ALL SEATS
            $releaseStmt = $conn->prepare("

                UPDATE seats s

                JOIN booking_seats bs
                ON s.id = bs.seat_id

                SET s.status = 'available'

                WHERE bs.booking_id = ?

            ");

            $releaseStmt->bind_param(
                "i",
                $booking_id
            );

            $releaseStmt->execute();


            // NOTIFICATION
            $message =
                "Your booking #$booking_id was cancelled by admin because the selected seat became unavailable.";

            $notifStmt = $conn->prepare("

                INSERT INTO notifications
                (
                    user_id,
                    message
                )

                VALUES
                (
                    ?, ?
                )

            ");

            $notifStmt->bind_param(
                "is",
                $user_id,
                $message
            );

            $notifStmt->execute();

    
        }

        /* FIND WALKIN BOOKING */

        $walkinStmt = $conn->prepare("

            SELECT wb.id

            FROM walkin_bookings wb

            JOIN walkin_booking_seats wbs
            ON wb.id = wbs.walkin_booking_id

            WHERE wbs.seat_id = ?
            AND wb.showtime_id = ?
            AND wb.payment_status = 'Pending'

            LIMIT 1

        ");

        $walkinStmt->bind_param(
            "ii",
            $seat_id,
            $showtime_id
        );

        $walkinStmt->execute();

        $walkinBooking =
            $walkinStmt
            ->get_result()
            ->fetch_assoc();


        /* CANCEL WALKIN */

        if($walkinBooking){

            $walkin_booking_id =
                $walkinBooking['id'];


            // CANCEL BOOKING

            $cancelStmt = $conn->prepare("

                UPDATE walkin_bookings

                SET
                    payment_status = 'Cancelled',
                    cancel_reason = 'Cancelled by admin because selected seat became unavailable.',
                    cancelled_by = 'admin',
                    cancelled_at = NOW()

                WHERE id = ?

            ");

            $cancelStmt->bind_param(
                "i",
                $walkin_booking_id
            );

            $cancelStmt->execute();


            // RELEASE ALL SEATS

            $releaseStmt = $conn->prepare("

                UPDATE seats s

                JOIN walkin_booking_seats wbs
                ON s.id = wbs.seat_id

                SET s.status = 'available'

                WHERE wbs.walkin_booking_id = ?

            ");

            $releaseStmt->bind_param(
                "i",
                $walkin_booking_id
            );

            $releaseStmt->execute();
        }
    }

    // 防止 blocked seat 被自动释放
if(
    $old_status === 'blocked'
    &&
    $new_status === 'available'
){

    // 检查有没有 booking 还绑着这个 seat
    $checkStmt = $conn->prepare("

        SELECT COUNT(*) AS total

        FROM booking_seats bs

        JOIN bookings b
        ON bs.booking_id = b.id

        WHERE bs.seat_id = ?
        AND b.payment_status IN ('Pending','Paid')

    ");

    $checkStmt->bind_param(
        "i",
        $seat_id
    );

    $checkStmt->execute();

    $check =
        $checkStmt
        ->get_result()
        ->fetch_assoc();

    if($check['total'] > 0){

        $_SESSION['success'] =
            "Cannot unblock seat because it still belongs to a booking.";

        header(
            "Location: manage_seats.php?showtime_id=" . $showtime_id
        );

        exit();
    }
}
    /* UPDATE SEAT */

    $stmt = $conn->prepare("

        UPDATE seats
        SET status = ?
        WHERE id = ?

    ");

    $stmt->bind_param(
        "si",
        $new_status,
        $seat_id
    );

    $stmt->execute();


    $_SESSION['success'] =
        "Seat updated successfully.";

    header(
        "Location: manage_seats.php?showtime_id=" . $showtime_id
    );

    exit();
}


/* RESET PENDING */

if(isset($_POST['reset_pending'])){

    $stmt = $conn->prepare("

        UPDATE seats
        SET status = 'available'
        WHERE showtime_id = ?
        AND status = 'pending'

    ");

    $stmt->bind_param(
        "i",
        $showtime_id
    );

    $stmt->execute();

    $_SESSION['success'] =
        "Pending seats reset.";

    header(
        "Location: manage_seats.php?showtime_id=" . $showtime_id
    );

    exit();

}

/* ADD SEATS */

if(isset($_POST['add_seats'])){

    $rowLetter =
        strtoupper(trim($_POST['row_letter']));

    $seatCount =
        intval($_POST['seat_count']);

    $applyAll =
        isset($_POST['apply_all']);

    // GET LAST ROW
    $lastRowStmt = $conn->prepare("

        SELECT LEFT(seat_number,1) AS row_letter

        FROM seats

        WHERE showtime_id = ?

        ORDER BY row_letter DESC

        LIMIT 1

    ");

    $lastRowStmt->bind_param(
        "i",
        $showtime_id
    );

    $lastRowStmt->execute();

    $lastRow =
        $lastRowStmt
        ->get_result()
        ->fetch_assoc();


    // CHECK NEXT ROW
    if($lastRow){

        $expectedRow =
            chr(ord($lastRow['row_letter']) + 1);

        if($rowLetter !== $expectedRow){

            $_SESSION['success'] =
                "Next row must be $expectedRow";

            header(
                "Location: manage_seats.php?showtime_id=" . $showtime_id
            );

            exit();
        }
    }
    else{

        // FIRST ROW MUST BE A
        if($rowLetter !== 'A'){

            $_SESSION['success'] =
                "First row must be A";

            header(
                "Location: manage_seats.php?showtime_id=" . $showtime_id
            );

            exit();
        }
    }


    if($seatCount <= 0){

        $_SESSION['success'] =
            "Invalid seat count.";

        header(
            "Location: manage_seats.php?showtime_id=" . $showtime_id
        );

        exit();
    }


    // DEFAULT CURRENT SHOWTIME
    $showtimeIds = [$showtime_id];


    // APPLY ALL SHOWTIMES
    if($applyAll){

        $allShowtimes = $conn->query("

            SELECT id
            FROM showtimes

        ");

        $showtimeIds = [];

        while($row = $allShowtimes->fetch_assoc()){

            $showtimeIds[] = $row['id'];
        }
    }


    foreach($showtimeIds as $sid){

        // FIND LAST NUMBER OF ROW
        $stmt = $conn->prepare("

            SELECT seat_number

            FROM seats

            WHERE showtime_id = ?
            AND seat_number LIKE CONCAT(?, '%')

            ORDER BY
                CAST(SUBSTRING(seat_number,2) AS UNSIGNED) DESC

            LIMIT 1

        ");

        $stmt->bind_param(
            "is",
            $sid,
            $rowLetter
        );

        $stmt->execute();

        $lastSeat =
            $stmt->get_result()->fetch_assoc();


        // DEFAULT START
        $startNo = 1;


        // CONTINUE LAST NUMBER
        if($lastSeat){

            $lastNumber =
                intval(
                    substr($lastSeat['seat_number'],1)
                );

            $startNo = $lastNumber + 1;
        }


        // INSERT NEW SEATS
        for($i = 0; $i < $seatCount; $i++){

            $seatNumber =
                $rowLetter . ($startNo + $i);


            $insert = $conn->prepare("

                INSERT INTO seats
                (
                    showtime_id,
                    seat_number,
                    status
                )

                VALUES
                (
                    ?, ?, 'available'
                )

            ");

            $insert->bind_param(
                "is",
                $sid,
                $seatNumber
            );

            $insert->execute();
        }
    }


    $_SESSION['success'] =
        "Seats added successfully.";

    header(
        "Location: manage_seats.php?showtime_id=" . $showtime_id
    );

    exit();
}

/* DELETE SEATS */

if(isset($_POST['delete_seats'])){

    $rowLetter =
        strtoupper(trim($_POST['row_letter']));

    $seatCount =
        intval($_POST['seat_count']);

    $applyAll =
        isset($_POST['apply_all']);


    if($seatCount <= 0){

        $_SESSION['success'] =
            "Invalid seat count.";

        header(
            "Location: manage_seats.php?showtime_id=" . $showtime_id
        );

        exit();
    }


    // DEFAULT CURRENT SHOWTIME
    $showtimeIds = [$showtime_id];


    // APPLY ALL SHOWTIMES
    if($applyAll){

        $allShowtimes = $conn->query("

            SELECT id
            FROM showtimes

        ");

        $showtimeIds = [];

        while($row = $allShowtimes->fetch_assoc()){

            $showtimeIds[] = $row['id'];
        }
    }


    foreach($showtimeIds as $sid){

        // GET LAST SEATS OF ROW
        $stmt = $conn->prepare("

            SELECT
                id,
                seat_number,
                status

            FROM seats

            WHERE showtime_id = ?
            AND seat_number LIKE CONCAT(?, '%')

            ORDER BY
                CAST(SUBSTRING(seat_number,2) AS UNSIGNED) DESC

            LIMIT ?

        ");

        $stmt->bind_param(
            "isi",
            $sid,
            $rowLetter,
            $seatCount
        );

        $stmt->execute();

        $result =
            $stmt->get_result();


        $seatsToDelete = [];

        while($seat = $result->fetch_assoc()){

            $seatsToDelete[] = $seat;
        }


        // REVERSE TO CHECK ORDER
        $seatsToDelete =
            array_reverse($seatsToDelete);


        $expectedNo = null;

        $valid = true;


        foreach($seatsToDelete as $seat){

            $seatNo =
                intval(
                    substr($seat['seat_number'],1)
                );


            // FIRST NUMBER
            if($expectedNo === null){

                $expectedNo = $seatNo;
            }

            // MUST BE CONTINUOUS
            else{

                if($seatNo != ($expectedNo + 1)){

                    $valid = false;
                    break;
                }

                $expectedNo = $seatNo;
            }


            // CANNOT DELETE BOOKED/PENDING
            if(
                $seat['status'] === 'booked'
                ||
                $seat['status'] === 'pending'
            ){

                $valid = false;
                break;
            }
        }


        if(!$valid){

            $_SESSION['success'] =
                "Only latest continuous available/blocked seats can be deleted.";

            header(
                "Location: manage_seats.php?showtime_id=" . $showtime_id
            );

            exit();
        }


        // DELETE SEATS
        foreach($seatsToDelete as $seat){

            $delete = $conn->prepare("

                DELETE FROM seats
                WHERE id = ?

            ");

            $delete->bind_param(
                "i",
                $seat['id']
            );

            $delete->execute();
        }
    }


    $_SESSION['success'] =
        "Seats deleted successfully.";

    header(
        "Location: manage_seats.php?showtime_id=" . $showtime_id
    );

    exit();
}

/* GET SEATS */

$seats = $conn->query("

    SELECT

        s.*,

        CONCAT('#', MAX(b.id)) AS booking_code,

        MAX(u.full_name) AS customer_name,

        MAX(b.payment_status) AS payment_status,

        CONCAT('#', MAX(wb.id)) AS walkin_code,

        MAX(wb.customer_name) AS walkin_customer,

        MAX(wb.payment_status) AS walkin_payment

    FROM seats s

    LEFT JOIN booking_seats bs
    ON s.id = bs.seat_id

    LEFT JOIN bookings b
    ON bs.booking_id = b.id

    LEFT JOIN users u
    ON b.user_id = u.id

    LEFT JOIN walkin_booking_seats wbs
    ON s.id = wbs.seat_id

    LEFT JOIN walkin_bookings wb
    ON wbs.walkin_booking_id = wb.id

    WHERE s.showtime_id = $showtime_id

    GROUP BY s.id

    ORDER BY

        LEFT(s.seat_number,1),

        CAST(SUBSTRING(s.seat_number,2) AS UNSIGNED)

");


/* STATS */

$stats = $conn->query("

    SELECT

        COUNT(*) AS total,

        SUM(
            CASE
                WHEN status = 'available'
                THEN 1
                ELSE 0
            END
        ) AS available_count,

        SUM(
            CASE
                WHEN status = 'pending'
                THEN 1
                ELSE 0
            END
        ) AS pending_count,

        SUM(
            CASE
                WHEN status = 'booked'
                THEN 1
                ELSE 0
            END
        ) AS booked_count,

        SUM(
            CASE
                WHEN status = 'blocked'
                THEN 1
                ELSE 0
            END
        ) AS blocked_count

    FROM seats

    WHERE showtime_id = $showtime_id

")->fetch_assoc();


$success = $_SESSION['success'] ?? '';

unset($_SESSION['success']);

?>

<!DOCTYPE html>
<html>

<head>

    <title>
        Manage Seats
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

            font-size:42px;

            font-weight:800;

            color:#111827;

            margin-bottom:10px;
        }

        .movie-info{

            color:#6b7280;

            margin-bottom:35px;
        }

        .stats-grid{

            display:grid;

            grid-template-columns:
            repeat(5, 1fr);

            gap:20px;

            margin-bottom:35px;
        }

        .stat-card{

            background:white;

            padding:22px;

            border-radius:22px;

            text-align:center;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);
        }

        .stat-number{

            font-size:34px;

            font-weight:800;

            color:#111827;
        }

        .stat-label{

            color:#6b7280;

            margin-top:6px;
        }

        .seat-card{

            background:white;

            border-radius:28px;

            padding:40px;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);
        }

        .screen{

            width:80%;

            margin:auto;

            height:65px;

            background:
            linear-gradient(
                to bottom,
                #f8f8f8,
                #cfcfcf
            );

            border-radius:
            16px 16px 50px 50px;

            text-align:center;

            line-height:65px;

            font-weight:800;

            letter-spacing:6px;

            margin-bottom:50px;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.2);
        }

        .seat-layout{

            display:flex;

            flex-wrap:wrap;

            justify-content:center;

            gap:14px;

            max-width:980px;

            margin:auto;
        }

        .seat-btn{

            width:65px;
            height:65px;

            border:none;

            border-radius:18px;

            font-weight:700;

            color:white;

            transition:0.25s;
        }

        .seat-btn:hover{

            transform:scale(1.08);
        }

        .available{

            background:#22c55e;
        }

        .pending{

            background:#f59e0b;
        }

        .booked{

            background:#ef4444;
        }

        .blocked{

            background:#6b7280;
        }

        .aisle{

            width:70px;
        }

        .row-break{

            flex-basis:100%;
            height:0;
        }

        .action-bar{

            margin-top:40px;

            display:flex;

            gap:18px;

            justify-content:center;
        }

        .action-btn{

            border:none;

            padding:14px 22px;

            border-radius:16px;

            font-weight:700;
        }

        .reset-pending{

            background:#fbbf24;
        }

        .back-btn{

            background:#111827;

            color:white;
        }

        .toast-msg{

            position:fixed;

            top:30px;
            right:35px;

            z-index:9999;

            background:#16a34a;

            color:white;

            padding:16px 22px;

            border-radius:16px;

            font-weight:700;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.2);
        }

        .add-seat-btn{

            display:flex;

            align-items:center;

            gap:10px;

            background:
            linear-gradient(
                135deg,
                #2563eb,
                #1d4ed8
            );

            color:white;

            border:none;

            padding:14px 24px;

            border-radius:18px;

            font-weight:700;

            font-size:15px;

            box-shadow:
            0 10px 20px rgba(37,99,235,0.25);

            transition:0.25s;
        }

        .add-seat-btn:hover{

            transform:
            translateY(-2px)
            scale(1.03);

            box-shadow:
            0 14px 28px rgba(37,99,235,0.35);
        }

        .plus-icon{

            width:28px;
            height:28px;

            border-radius:50%;

            background:
            rgba(255,255,255,0.18);

            display:flex;

            align-items:center;

            justify-content:center;

            font-size:20px;

            font-weight:800;
        }

        .delete-seat-btn{

            display:flex;

            align-items:center;

            gap:10px;

            background:
            linear-gradient(
                135deg,
                #dc2626,
                #b91c1c
            );

            color:white;

            border:none;

            padding:14px 24px;

            border-radius:18px;

            font-weight:700;

            font-size:15px;

            box-shadow:
            0 10px 20px rgba(220,38,38,0.25);

            transition:0.25s;
        }

        .delete-seat-btn:hover{

            transform:
            translateY(-2px)
            scale(1.03);

            box-shadow:
            0 14px 28px rgba(220,38,38,0.35);
        }

        .minus-icon{

            width:28px;
            height:28px;

            border-radius:50%;

            background:
            rgba(255,255,255,0.18);

            display:flex;

            align-items:center;

            justify-content:center;

            font-size:20px;

            font-weight:800;
        }

    </style>

</head>

<body>

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

    <div class="page-title">

        Manage Seats

    </div>

    <div class="movie-info">

        <?= htmlspecialchars($showtime['title']) ?>

        •

        <?= htmlspecialchars($showtime['branch_name']) ?>

        •

        <?= date(
            'd M Y',
            strtotime($showtime['show_date'])
        ) ?>

        •

        <?= date(
            'h:i A',
            strtotime($showtime['show_time'])
        ) ?>

    </div>


    <!-- STATS -->

    <div class="stats-grid">

        <div class="stat-card">

            <div class="stat-number">

                <?= $stats['total'] ?>

            </div>

            <div class="stat-label">

                Total

            </div>

        </div>

        <div class="stat-card">

            <div class="stat-number text-success">

                <?= $stats['available_count'] ?>

            </div>

            <div class="stat-label">

                Available

            </div>

        </div>

        <div class="stat-card">

            <div class="stat-number text-warning">

                <?= $stats['pending_count'] ?>

            </div>

            <div class="stat-label">

                Pending

            </div>

        </div>

        <div class="stat-card">

            <div class="stat-number text-danger">

                <?= $stats['booked_count'] ?>

            </div>

            <div class="stat-label">

                Booked

            </div>

        </div>

        <div class="stat-card">

            <div class="stat-number text-secondary">

                <?= $stats['blocked_count'] ?>

            </div>

            <div class="stat-label">

                Blocked

            </div>

        </div>

    </div>


    <!-- SEATS -->

    <div class="seat-card">

        <div class="screen">

            SCREEN

        </div>


        <div class="seat-layout">

            <?php

                $currentRow = '';
                $seatInRow = 0;

                while($seat = $seats->fetch_assoc()):

                $rowLetter =
                    substr($seat['seat_number'], 0, 1);


                // NEW ROW
                if($currentRow != '' && $currentRow !== $rowLetter){

                    echo '<div class="row-break"></div>';

                    $seatInRow = 0;
                }

                $currentRow = $rowLetter;


                // AISLE AFTER 5 SEATS
                if($seatInRow == 5){

                    echo '<div class="aisle"></div>';
                }

                ?>

                <button

                    type="button"

                    class="seat-btn <?= $seat['status'] ?>"

                    data-bs-toggle="modal"

                    data-bs-target="#seatModal"

                    data-seat-id="<?= $seat['id'] ?>"

                    data-seat-number="<?= $seat['seat_number'] ?>"

                    data-seat-status="<?= $seat['status'] ?>"
                    
                    data-booking-code="<?= $seat['booking_code'] ?: $seat['walkin_code'] ?>"

                    data-customer-name="<?= htmlspecialchars(
                        $seat['customer_name'] ?: $seat['walkin_customer']
                    ) ?>"

                    data-payment-status="<?= $seat['payment_status'] ?: $seat['walkin_payment'] ?>"

                >

                    <?= $seat['seat_number'] ?>

                </button>

                <?php $seatInRow++; ?>

            <?php endwhile; ?>

        </div>


        <!-- ACTIONS -->

        <div class="action-bar">

            <form method="POST">

                <button
                    name="reset_pending"
                    class="action-btn reset-pending"
                >

                    Reset Pending

                </button>

            </form>

            <button
                type="button"
                class="add-seat-btn"
                data-bs-toggle="modal"
                data-bs-target="#addSeatModal"
            >

                <span class="plus-icon">+</span>

                Add Seats

            </button>


            <button
                type="button"
                class="delete-seat-btn"
                data-bs-toggle="modal"
                data-bs-target="#deleteSeatModal"
            >

                <span class="minus-icon">−</span>

                Delete Seats

            </button>

            <a
                href="<?= BASE_URL ?>/admin/seats/admin_seats.php"
                class="action-btn back-btn text-decoration-none d-flex align-items-center justify-content-center"
            >

                Back

            </a>

        </div>

    </div>

</div>


<!-- MODAL -->

<div
    class="modal fade"
    id="seatModal"
    tabindex="-1"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content rounded-4 border-0">

            <form method="POST">

                <div class="modal-body p-4">

                    <h3
                        class="fw-bold mb-3"
                        id="seatTitle"
                    >
                    </h3>

                    <input
                        type="hidden"
                        name="seat_id"
                        id="seatIdInput"
                    >

                    <div
                        id="bookingInfo"
                        class="mb-3 p-3 rounded bg-light"
                        style="display:none;"
                    >
                    </div>

                    <label class="fw-semibold mb-2">

                        Seat Status

                    </label>

                    <select
                        name="status"
                        id="seatStatusInput"
                        class="form-select mb-4"
                    >

                        <option value="available">

                            Available

                        </option>


                        <option value="blocked">

                            Blocked

                        </option>

                    </select>

                    <div class="d-flex gap-3 justify-content-end">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >

                            Cancel

                        </button>

                        <button
                            class="btn btn-dark"
                        >

                            Save Changes

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- ADD SEATS MODAL -->

<div
    class="modal fade"
    id="addSeatModal"
    tabindex="-1"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content rounded-4 border-0">

            <form method="POST">

                <div class="modal-body p-4">

                    <h3 class="fw-bold mb-4">
                        Add Seats
                    </h3>

                    <label class="fw-semibold mb-2">
                        Row Letter
                    </label>

                    <input
                        type="text"
                        name="row_letter"
                        class="form-control mb-3"
                        placeholder="Example: A"
                        maxlength="1"
                        required
                    >

                    <label class="fw-semibold mb-2">
                        Number Of Seats
                    </label>

                    <input
                        type="number"
                        name="seat_count"
                        class="form-control mb-4"
                        placeholder="Example: 5"
                        required
                    >


                    <div class="form-check mb-4">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="apply_all"
                            id="applyAll"
                        >

                        <label
                            class="form-check-label"
                            for="pplyAll"
                        >
                            Apply to all showtimes
                        </label>

                    </div>

                    <div class="d-flex gap-3 justify-content-end">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            name="add_seats"
                            class="btn btn-primary"
                        >
                            Add Seats
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- DELETE SEATS MODAL -->

<div
    class="modal fade"
    id="deleteSeatModal"
    tabindex="-1"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content rounded-4 border-0">

            <form method="POST">

                <div class="modal-body p-4">

                    <h3 class="fw-bold mb-4 text-danger">
                        Delete Seats
                    </h3>

                    <label class="fw-semibold mb-2">
                        Row Letter
                    </label>

                    <input
                        type="text"
                        name="row_letter"
                        class="form-control mb-3"
                        placeholder="Example: A"
                        maxlength="1"
                        required
                    >

                    <label class="fw-semibold mb-2">
                        Number Of Seats To Delete
                    </label>

                    <input
                        type="number"
                        name="seat_count"
                        class="form-control mb-4"
                        placeholder="Example: 2"
                        required
                    >

                    <div class="form-check mb-4">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="apply_all"
                            id="deleteApplyAll"
                        >

                        <label
                            class="form-check-label"
                            for="deleteApplyAll"
                        >
                            Apply to all showtimes
                        </label>

                    </div>

                    <div class="d-flex gap-3 justify-content-end">

                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            name="delete_seats"
                            class="btn btn-danger"
                        >
                            Delete Seats
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>


<?php if($success): ?>

    <div class="toast-msg">

        <?= $success ?>

    </div>

<?php endif; ?>


<script>

document.querySelectorAll('.seat-btn').forEach(btn => {

    btn.addEventListener('click', function(){

        const currentStatus =
            this.dataset.seatStatus.trim();

        
        const bookingCode =
            this.dataset.bookingCode;

        const customerName =
            this.dataset.customerName;

        const paymentStatus =
            this.dataset.paymentStatus;

        const bookingInfo =
            document.getElementById(
                'bookingInfo'
            );

        const select =
            document.getElementById(
                'seatStatusInput'
            );

        document.getElementById(
            'seatIdInput'
        ).value =
            this.dataset.seatId;

        document.getElementById(
            'seatTitle'
        ).innerHTML =
            'Seat ' +
            this.dataset.seatNumber +
            '<br><small class="text-muted">Current Status: ' +
            currentStatus.charAt(0).toUpperCase() +
            currentStatus.slice(1) +
            '</small>';


        if(bookingCode){

            bookingInfo.style.display = 'block';

            bookingInfo.innerHTML = `
                <strong>Booking:</strong> ${bookingCode}<br>
                <strong>Customer:</strong> ${customerName}<br>
                <strong>Payment:</strong> ${paymentStatus}
            `;
        }
        else{

            bookingInfo.style.display = 'none';

            bookingInfo.innerHTML = '';
        }


        // RESET OPTIONS
        select.innerHTML = '';


        // PENDING -> AVAILABLE
        if(currentStatus === 'pending'){

            select.innerHTML = `
                <option value="available" selected>
                    Available
                </option>
            `;
        }


        // BLOCKED -> AVAILABLE
        else if(currentStatus === 'blocked'){

            select.innerHTML = `
                <option value="available" selected>
                    Available
                </option>
            `;
        }


        // AVAILABLE -> BLOCKED
        else if(currentStatus === 'available' ){

            select.innerHTML = `
                <option value="blocked" selected>
                    Blocked
                </option>
            `;
        }


        // BOOKED -> NO ACTION
        else if(currentStatus === 'booked'){

            select.innerHTML = `
                <option disabled>
                    Booked seat cannot be modified
                </option>
            `;
        }

    });

});

</script>


<script>

setTimeout(() => {

    const toast =
        document.querySelector('.toast-msg');

    if(toast){

        toast.remove();

    }

}, 3000);

</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>