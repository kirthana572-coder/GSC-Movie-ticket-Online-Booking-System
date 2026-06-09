<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';


// Check form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // =========================
    // Get form data
    // =========================
    $customer_name = trim($_POST['customer_name']);

    $movie_id = $_POST['movie_id'];

    $show_date = $_POST['show_date'];

    $show_time = $_POST['show_time'];


    // Get selected seats
    $selectedSeats = $_POST['seats'] ?? [];


    // =========================
    // Get showtime ID
    // =========================
    $stmt = $conn->prepare("
        SELECT id 
        FROM showtimes 
        WHERE movie_id = ? 
        AND show_date = ? 
        AND show_time = ?
    ");

    $stmt->bind_param(
        "iss",
        $movie_id,
        $show_date,
        $show_time
    );

    $stmt->execute();

    $showtime = $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();


    // Show error if showtime not found
    if (!$showtime) {

        echo "
            <script>
                alert('Showtime not found!');
                history.back();
            </script>
        ";

        exit();
    }

    $showtime_id = $showtime['id'];

    $checkShowtime = $conn->prepare("

        SELECT id

        FROM showtimes

        WHERE id = ?
        AND TIMESTAMP(show_date, show_time) > NOW()

    ");

    $checkShowtime->bind_param(
        "i",
        $showtime_id
    );

    $checkShowtime->execute();

    if(
        $checkShowtime
        ->get_result()
        ->num_rows === 0
    ){

        echo "

        <script>

            alert('This showtime has already started or ended.');

            history.back();

        </script>

        ";

        exit();
    }

    $checkShowtime->close();


    // =========================
    // Check seat availability
    // =========================
    foreach ($selectedSeats as $seat_id) {

        $seat_id = intval($seat_id);

        // 检查 seat 当前状态

        $seatCheck = $conn->prepare("

            SELECT status

            FROM seats

            WHERE id = ?
            AND showtime_id = ?

        ");

        $seatCheck->bind_param(
            "ii",
            $seat_id,
            $showtime_id
        );

        $seatCheck->execute();

        $seatStatus =
            $seatCheck
            ->get_result()
            ->fetch_assoc();

        $seatCheck->close();

        if(
            !$seatStatus
            ||
            $seatStatus['status'] !== 'available'
        ){

            echo "

            <script>

                alert('One or more selected seats are not available.');

                history.back();

            </script>

            ";

            exit();
        }


        // -------------------------
        // Check online bookings
        // -------------------------
        $stmt = $conn->prepare("
            SELECT *
            FROM booking_seats bs

            JOIN bookings b 
            ON bs.booking_id = b.id

            WHERE b.showtime_id = ?
            AND bs.seat_id = ?
            AND b.payment_status IN ('Pending','Paid')
        ");

        $stmt->bind_param(
            "ii",
            $showtime_id,
            $seat_id
        );

        $stmt->execute();

        $checkOnline = $stmt->get_result();

        $stmt->close();


        // -------------------------
        // Check walk-in bookings
        // -------------------------
        $stmt2 = $conn->prepare("
            SELECT *
            FROM walkin_booking_seats wbs

            JOIN walkin_bookings wb
            ON wbs.walkin_booking_id = wb.id

            WHERE wb.showtime_id = ?
            AND wbs.seat_id = ?
            AND wb.payment_status IN ('Pending','Paid')
        ");

        $stmt2->bind_param(
            "ii",
            $showtime_id,
            $seat_id
        );

        $stmt2->execute();

        $checkWalkin = $stmt2->get_result();

        $stmt2->close();


        // Stop if seat already booked
        if (
            $checkOnline->num_rows > 0 ||
            $checkWalkin->num_rows > 0
        ) {

            echo "
                <script>
                    alert('One or more seats already booked.');
                    history.back();
                </script>
            ";

            exit();
        }
    }


    // =========================
    // Get ticket quantities
    // =========================
    $adult_qty = intval($_POST['adult_qty'] ?? 0);

    $senior_qty = intval($_POST['senior_qty'] ?? 0);

    $student_qty = intval($_POST['student_qty'] ?? 0);

    $children_qty = intval($_POST['children_qty'] ?? 0);


    // =========================
    // Calculate total tickets
    // =========================
    $totalTickets =
        $adult_qty +
        $senior_qty +
        $student_qty +
        $children_qty;


    // =========================
    // Validation
    // =========================

    // Check ticket quantity
    if ($totalTickets <= 0) {

        echo "
            <script>
                alert('Please select at least one ticket.');
                history.back();
            </script>
        ";

        exit();
    }


    // Check seat selection
    if (count($selectedSeats) == 0) {

        echo "
            <script>
                alert('Please select at least one seat.');
                history.back();
            </script>
        ";

        exit();
    }


    // Check seat count matches ticket count
    if (count($selectedSeats) != $totalTickets) {

        echo "
            <script>
                alert('Selected seats must equal total tickets.');
                history.back();
            </script>
        ";

        exit();
    }

    // =========================
    // Calculate total price
    // =========================

    $totalPrice =
        ($adult_qty * 12) +
        ($senior_qty * 8) +
        ($student_qty * 10) +
        ($children_qty * 6);


    // =========================
    // Generate booking code
    // =========================

    $booking_code =
        'W' . time();


    // =========================
    // Insert walkin booking
    // =========================

    $stmt = $conn->prepare("

        INSERT INTO walkin_bookings
        (
            booking_code,
            customer_name,
            showtime_id,
            total_price,

            adult_qty,
            senior_qty,
            student_qty,
            children_qty,

            payment_status,
            created_at
        )

        VALUES
        (
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            'Paid',
            NOW()
        )

    ");

    $stmt->bind_param(
        "ssidiiii",
        $booking_code,
        $customer_name,
        $showtime_id,
        $totalPrice,

        $adult_qty,
        $senior_qty,
        $student_qty,
        $children_qty
    );

    $stmt->execute();

    $walkin_booking_id =
        $stmt->insert_id;

    $stmt->close();


    // =========================
    // Insert seats
    // =========================

    foreach($selectedSeats as $seat_id){

        // insert relation
        $seatStmt = $conn->prepare("

            INSERT INTO walkin_booking_seats
            (
                walkin_booking_id,
                seat_id
            )

            VALUES
            (
                ?, ?
            )

        ");

        $seatStmt->bind_param(
            "ii",
            $walkin_booking_id,
            $seat_id
        );

        $seatStmt->execute();


        // update seat status
        $updateSeat = $conn->prepare("

            UPDATE seats
            SET status = 'booked'
            WHERE id = ?

        ");

        $updateSeat->bind_param(
            "i",
            $seat_id
        );

        $updateSeat->execute();
    }


    // =========================
    // Success
    // =========================

    echo "

    <script>

        alert('Walk-in booking created successfully!');

        window.location.href =
            '" . BASE_URL . "/staff/walkin_bookings.php';

    </script>

    ";

    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Walk-in Booking - GSC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">

    <style>

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:#f6f7fb;
            min-height:100vh;
        }

        .page-container{
            margin-left:280px;
            width:calc(100% - 280px);

            min-height:100vh;

            display:flex;
            justify-content:center;
            align-items:center;

            padding:40px;
            box-sizing:border-box;
        }

        .booking-card{
            width:100%;
            max-width:850px;

            background:#fff;

            border-radius:18px;

            padding:32px;

            border:1px solid #eef0f3;

            box-shadow:
            0 8px 24px rgba(0,0,0,.08);
        }

        .page-title{
            font-size:30px;
            font-weight:800;
            color:#212529;

            letter-spacing:-0.5px;
            text-align:center;
            margin-bottom:6px;
            
        }

        .page-subtitle{
            color:#6c757d;
            font-size:14px;
            margin-bottom:28px;
            text-align:center;
            
        }

        .form-label{
            font-weight: 600;

            color: #333;

            margin-bottom: 10px;
        }

        .form-control,
        .form-select{

            height:48px;

            border-radius:10px;

            border:1px solid #dee2e6;

            font-size:14px;

            box-shadow:none;
        }

        .form-control:focus,
        .form-select:focus{

            border-color:#f5c518;

            box-shadow:
            0 0 0 3px rgba(245,197,24,.15);
        }

        .ticket-box{

            background:#fff;

            border:1px solid rgba(0,0,0,.06);

            border-radius:16px;

            padding:24px;

            margin-top:30px;

            box-shadow:
            0 4px 12px rgba(0,0,0,.04);
        }

        .price-tag{

            color:#6c757d;

            font-size:13px;
        }

        .total-box{

            margin-top:24px;
            margin-bottom:24px;

            padding:32px;

            border-radius:18px;

            background:
            linear-gradient(
                135deg,
                #1f1f1f,
                #343a40
            );
        }

        .total-title{
            color:rgba(255,255,255,.7);
            font-weight:600;
        }

        .total-price{
            color:#f5c518;
            font-size:42px;
            font-weight:800;
            margin-top:8px;
        }

        .checkout-box{

            margin-top:30px;

            padding:32px;

            border-radius:20px;

            background:
            linear-gradient(
                135deg,
                #1f1f1f,
                #343a40
            );

            text-align:center;

            box-shadow:
            0 12px 30px rgba(0,0,0,.18);
        }

        .btn-book{

            display:inline-flex;

            align-items:center;
            justify-content:center;

            min-width:240px;

            height:56px;

            margin-top:28px;

            padding:0 36px;

            border:none;

            border-radius:14px;

            background:
            linear-gradient(
                135deg,
                #f8d45a,
                #f5c518
            );

            color:#1f1f1f;

            font-size:16px;
            font-weight:700;

            letter-spacing:.4px;

            box-shadow:
            0 8px 18px rgba(245,197,24,.35);

            transition:.25s ease;
        }

        .btn-book:hover{

            transform:translateY(-3px);

            box-shadow:
            0 14px 28px rgba(245,197,24,.45);
        }

        .btn-book:active{

            transform:translateY(0);
        }

        .seat-card{

            background:#fff;

            border-radius:16px;

            border:1px solid #eef0f3;

            padding:20px;

            box-shadow:
            0 4px 12px rgba(0,0,0,.04);
        }

        .section-header{

            font-size:16px;

            font-weight:700;

            color:#212529;

            margin-bottom:16px;
        }

        .header-top{
            display:flex;
            justify-content:space-between;
            align-items:flex-start;
            margin-bottom:30px;
        }

        .btn-back{
            background:#fff;
            border:1px solid #dee2e6;
            border-radius:10px;
            padding:10px 18px;
            color:#495057;
            text-decoration:none;
            font-weight:600;
            transition:.2s;
        }

        .btn-back:hover{
            background:#f8f9fa;
            color:#212529;
        }

    </style>
</head>

<body class="staff-page add-walkin-booking-page">

<?php include '../includes/staff_sidebar.php'; ?>


<div class="page-container">

    <div class="booking-card">

        <div class="header-top">

            <div>
                <h1 class="page-title">
                    Add Walk-In Booking
                </h1>

                <p class="page-subtitle">
                    Create a new customer booking and assign seats.
                </p>
            </div>

        </div>

        <form method="POST">

            <div class="mb-4">

                <label class="form-label">
                    Customer Name
                </label>

                <input type="text"
                       name="customer_name"
                       class="form-control"
                       placeholder="Enter customer name"
                       required>

            </div>

            <!-- Movie -->
             
            <div class="mb-4">

                <label class="form-label">
                    Select Movie
              </label>

                <select name="movie_id" id="movie_id" class="form-select" required>

                    <option selected disabled>
                        Select Movie
                    </option>

                    <?php
                    $movies = $conn->query("
                        SELECT *
                        FROM movies
                        WHERE status = 'active'
                        ORDER BY title ASC
                    ");

                    while($movie = $movies->fetch_assoc()):
                    ?>

                    <option value="<?= $movie['id'] ?>">
                        <?= $movie['title'] ?>
                    </option>

                    <?php endwhile; ?>

                </select>

            </div>

            <!-- Date -->
            <div class="mb-4">

                <label class="form-label">Select Date</label>

                <select name="show_date" id="show_date" class="form-select" required>
                    <option disabled selected>Select Date</option>
                </select>

            </div>

            <!-- Time -->
            <div class="mb-4">

                <label class="form-label">Select Time</label>

                <select name="show_time" id="show_time" class="form-select" required>
                    <option disabled selected>Select Time</option>
                </select>

            </div>

            <!-- Seats -->
            <div class="mb-4">

                <label class="form-label">
                    Select Seats
                </label>

                <div class="seat-card">

                    <div class="section-header">
                        🎟 Seat Selection
                    </div>

                    <div id="seatContainer"></div>

                </div>

            </div>


            <div class="ticket-box">

                <div class="section-header">
                    🎟 Ticket Selection
                </div>
                <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Adult Ticket
                    </label>

                    <input type="number"
                        name="adult_qty"
                        id="adultQty"
                        class="form-control"
                        value="0"
                        min="0">

                    <small class="price-tag">
                        🧑 Adult - RM12.00
                    </small>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Senior Ticket
                    </label>

                    <input type="number"
                        name="senior_qty"
                        id="seniorQty"
                        class="form-control"
                        value="0"
                        min="0">

                    <small class="price-tag">
                        👴 Senior - RM8.00
                    </small>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Student Ticket
                    </label>

                    <input type="number"
                        name="student_qty"
                        id="studentQty"
                        class="form-control"
                        value="0"
                        min="0">

                    <small class="price-tag">
                        🎓 Student - RM10.00
                    </small>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Children Ticket
                    </label>

                    <input type="number"
                        name="children_qty"
                        id="childrenQty"
                        class="form-control"
                        value="0"
                        min="0">

                    <small class="price-tag">
                        👶 Children - RM6.00
                    </small>

                </div>

            </div>

            </div>

            <div class="checkout-box">

                <div class="total-title">
                    Total Price
                </div>

                <div class="total-price" id="totalPrice">
                    RM 0.00
                </div>


                <button type="submit" class="btn-book">
                    Confirm & Create Booking
                </button>

            </div>

        </form>

    </div>

</div>

<script>

const adultQty = document.getElementById('adultQty');
const seniorQty = document.getElementById('seniorQty');
const studentQty = document.getElementById('studentQty');
const childrenQty = document.getElementById('childrenQty');

const totalPrice = document.getElementById('totalPrice');

function updateTotal(){

    let adult = parseInt(adultQty.value) || 0;
    let senior = parseInt(seniorQty.value) || 0;
    let student = parseInt(studentQty.value) || 0;
    let children = parseInt(childrenQty.value) || 0;

    let total =
        (adult * 12) +
        (senior * 8) +
        (student * 10) +
        (children * 6);

    totalPrice.textContent = 'RM ' + total.toFixed(2);
}

adultQty.addEventListener('input', updateTotal);
seniorQty.addEventListener('input', updateTotal);
studentQty.addEventListener('input', updateTotal);
childrenQty.addEventListener('input', updateTotal);

updateTotal();

// MOVIE → DATE

document.getElementById('movie_id').addEventListener('change', async function () {

    const movieId = this.value;

    const res = await fetch('get_dates.php?movie_id=' + movieId);
    const data = await res.text();

    const dateSelect = document.getElementById('show_date');
    const timeSelect = document.getElementById('show_time');

    dateSelect.innerHTML = data;

    // 强制 reset time
    timeSelect.innerHTML = '<option value="">Select Time</option>';
});


// DATE → TIME  
document.addEventListener('change', function(e) {

    if (e.target && e.target.id === 'show_date') {

        const movieId = document.getElementById('movie_id').value;
        const date = e.target.value;

        if (!date) return; // 防止空值

        fetch('get_times.php?movie_id=' + movieId + '&date=' + date)
            .then(res => res.text())
            .then(data => {
                document.getElementById('show_time').innerHTML = data;
            });
    }
});

// TIME → SEATS
document.addEventListener('change', function(e) {

    if (e.target && e.target.id === 'show_time') {

        const movieId = document.getElementById('movie_id').value;
        const date = document.getElementById('show_date').value;
        const time = e.target.value;

        fetch(
            'get_walkin_seats.php?movie_id=' +
            movieId +
            '&date=' +
            date +
            '&time=' +
            time
        )
        .then(res => res.text())
        .then(data => {

            document.getElementById('seatContainer').innerHTML = data;

        });
    }
});


function validateTicketCount(){

    let selectedSeats =
        document.querySelectorAll(
            'input[name="seats[]"]:checked'
        ).length;

    let totalTickets =
        (parseInt(adultQty.value) || 0) +
        (parseInt(seniorQty.value) || 0) +
        (parseInt(studentQty.value) || 0) +
        (parseInt(childrenQty.value) || 0);

    if(totalTickets > selectedSeats){

        alert(
            'Total tickets cannot exceed selected seats.'
        );

        return false;
    }

    return true;
}

document.querySelector('form').addEventListener('submit', function(e){

    let selectedSeats =
        document.querySelectorAll(
            'input[name="seats[]"]:checked'
        ).length;

    let totalTickets =
        (parseInt(adultQty.value) || 0) +
        (parseInt(seniorQty.value) || 0) +
        (parseInt(studentQty.value) || 0) +
        (parseInt(childrenQty.value) || 0);

    if(selectedSeats !== totalTickets){

        e.preventDefault();

        alert(
            'Selected seats must equal total tickets.'
        );
    }
});

</script>

</body>
</html>