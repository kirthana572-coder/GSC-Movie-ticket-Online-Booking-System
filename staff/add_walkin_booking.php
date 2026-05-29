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


    // =========================
    // Check seat availability
    // =========================
    foreach ($selectedSeats as $seat_id) {

        $seat_id = intval($seat_id);


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
            payment_status,
            created_at
        )

        VALUES
        (
            ?, ?, ?, ?, 'Paid', NOW()
        )

    ");

    $stmt->bind_param(
        "ssid",
        $booking_code,
        $customer_name,
        $showtime_id,
        $totalPrice
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
    <title>Add Walk-in Booking - GSC</title>

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
            min-height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 40px;
        }

        .booking-card{
            width: 100%;
            max-width: 700px;

            background: rgba(255,255,255,0.82);

            border-radius: 28px;

            padding: 45px;

            box-shadow:
            0 10px 30px rgba(0,0,0,0.15);
        }

        .page-title{
            text-align: center;

            font-size: 38px;

            font-weight: 700;

            color: #f5c518;

           margin-bottom: 10px;
        }

        .page-subtitle{
            text-align: center;

            color: #777;

            margin-bottom: 35px;
        }

        .form-label{
            font-weight: 600;

            color: #333;

            margin-bottom: 10px;
        }

        .form-control,
        .form-select{
            border-radius: 14px;

            padding: 14px;

            border: 1px solid rgba(0,0,0,0.1);

            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus{
            border-color: #f5c518;

            box-shadow:
            0 0 0 0.15rem rgba(245,197,24,0.25) !important;
        }

        .ticket-box{
            background: rgba(255,255,255,0.7);

            border-radius: 18px;

            padding: 20px;

            margin-bottom: 20px;
        }

        .ticket-title{
            font-size: 18px;

            font-weight: 700;

            margin-bottom: 15px;

            color: #444;
        }

        .price-tag{
            color: #f5c518;

            font-weight: 700;
        }

        .total-box{
            background: rgba(245,197,24,0.15);

            border-radius: 18px;

            padding: 18px;

            text-align: center;

            margin-top: 25px;
        }

        .total-title{
            font-size: 18px;

            color: #555;
        }

        .total-price{
            font-size: 32px;

            font-weight: 700;

            color: #f5c518;
        }

        .btn-book{
            width: 100%;

            background: #ffd53b;

            color: #111;

            border: none;

            border-radius: 16px;

            padding: 15px;

            font-size: 18px;

            font-weight: 700;

            margin-top: 25px;

            transition: 0.25s;
        }

        .btn-book:hover{
            background: #ffdc5f;

            transform: scale(1.02);
        }

    </style>
</head>

<body>

<div class="page-container">

    <div class="booking-card">

        <h1 class="page-title">
            Walk-in Booking
        </h1>

        <p class="page-subtitle">
            Create a new walk-in customer booking
        </p>

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

                <div id="seatContainer"
                    class="d-flex flex-wrap gap-2">
                </div>

            </div>


            <div class="ticket-box">

                <div class="ticket-title">
                    Ticket Selection
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

            <div class="total-box">

                <div class="total-title">
                    Total Price
                </div>

                <div class="total-price" id="totalPrice">
                    RM 0.00
                </div>

            </div>

            <button type="submit" class="btn-book">
                Add Booking
            </button>

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