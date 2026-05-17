<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// if ($_SESSION['role'] !== 'staff') {
//     die("Access denied.");
// }

$id = $_GET['id'];

$result = $conn->query("
    SELECT
        walkin_bookings.*,
        movies.id AS movie_id,
        movies.title,
        showtimes.show_date,
        showtimes.show_time
    FROM walkin_bookings

    JOIN showtimes
    ON walkin_bookings.showtime_id = showtimes.id

    JOIN movies
    ON showtimes.movie_id = movies.id

    WHERE walkin_bookings.id = '$id'
");

$booking = $result->fetch_assoc();

if(!$booking){
    die("Booking not found.");
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $customer_name = $_POST['customer_name'];
    $movie_id = $_POST['movie_id'];
    $show_date = $_POST['show_date'];
    $show_time = $_POST['show_time'];
    $payment_status = $_POST['payment_status'];

    $getShowtime = $conn->query("
    SELECT id
    FROM showtimes
    WHERE movie_id = '$movie_id'
    AND show_date = '$show_date'
    AND show_time = '$show_time'
    ");

    $showtime = $getShowtime->fetch_assoc();

    if(!$showtime){
        echo "<script>alert('Showtime not found!'); history.back();</script>";
        exit();
    }

    $showtime_id = $showtime['id'];

    $adult_qty = intval($_POST['adult_qty']);
    $senior_qty = intval($_POST['senior_qty']);
    $student_qty = intval($_POST['student_qty']);
    $children_qty = intval($_POST['children_qty']);

    $total =
        ($adult_qty * 12) +
        ($senior_qty * 8) +
        ($student_qty * 10) +
        ($children_qty * 6);

    $sql = "
        UPDATE walkin_bookings
        SET
            customer_name = '$customer_name',
            showtime_id = '$showtime_id',
            adult_qty = '$adult_qty',
            senior_qty = '$senior_qty',
            student_qty = '$student_qty',
            children_qty = '$children_qty',
            total_price = '$total',
            payment_status = '$payment_status'
        WHERE id = '$id'
    ";

    if($conn->query($sql)){

        echo "
        <script>
            alert('Booking updated successfully!');
            window.location.href='walkin_bookings.php';
        </script>
        ";

        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Walk-in Booking - GSC</title>

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

        .btn-save{
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

        .btn-save:hover{
            background: #ffdc5f;

            transform: scale(1.02);
        }

        .back-btn{
            width: 100%;

            display: block;

            text-align: center;

            text-decoration: none;

            background: #2f2f2f;

            color: white;

            border-radius: 16px;

            padding: 15px;

            font-size: 18px;

            font-weight: 700;

            margin-top: 15px;

            transition: 0.25s;
        }

        .back-btn:hover{
            background: #f5c518;

            color: #111;

            transform: scale(1.02);
        }

    </style>
</head>

<body>

<div class="page-container">

    <div class="booking-card">

        <h1 class="page-title">
            Edit Walk-in Booking
        </h1>

        <p class="page-subtitle">
            Update booking information
        </p>

        <form method="POST">

            <div class="mb-4">

                <label class="form-label">
                    Customer Name
                </label>

                <input type="text"
                       name="customer_name"
                       class="form-control"
                       value="<?= $booking['customer_name'] ?>"
                       required>

            </div>

            <!-- Movie -->
            <div class="mb-4">

                <label class="form-label">
                    Select Movie
                </label>

                <select name="movie_id" id="movie_id" class="form-select" required>

                    <?php
                    $movies = $conn->query("
                        SELECT *
                        FROM movies
                    ");

                    while($movie = $movies->fetch_assoc()):
                    ?>

                    <option
                        value="<?= $movie['id'] ?>"
                        <?= ($booking['movie_id'] == $movie['id']) ? 'selected' : '' ?>
                    >
                        <?= $movie['title'] ?>
                    </option>

                    <?php endwhile; ?>

                </select>

            </div>

            <!-- Date -->
            <div class="mb-4">

                <label class="form-label">
                    Select Date
                </label>

                <select name="show_date" id="show_date" class="form-select" required>

                    <option disabled selected>Select Date</option>

                </select>
            </div>

            <!-- Time -->
            <div class="mb-4">

                <label class="form-label">
                    Select Time
                </label>

                <select name="show_time" id="show_time" class="form-select" required>

                    <option disabled selected>Select Time</option>
                </select>
            </div>

            <div class="ticket-box">

                <div class="ticket-title">
                    Edit Ticket Quantity
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
                               value="<?= $booking['adult_qty'] ?>"
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
                               value="<?= $booking['senior_qty'] ?>"
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
                               value="<?= $booking['student_qty'] ?>"
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
                               value="<?= $booking['children_qty'] ?>"
                               min="0">

                        <small class="price-tag">
                            👶 Children - RM6.00
                        </small>

                    </div>

                </div>

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Payment Status
                </label>

                <select name="payment_status" class="form-select">

                    <option value="Pending"
                        <?= ($booking['payment_status'] == 'Pending') ? 'selected' : '' ?>>
                        Pending
                    </option>

                    <option value="Paid"
                        <?= ($booking['payment_status'] == 'Paid') ? 'selected' : '' ?>>
                        Paid
                    </option>

                    <option value="Cancelled"
                        <?= ($booking['payment_status'] == 'Cancelled') ? 'selected' : '' ?>>
                        Cancelled
                    </option>

                </select>

            </div>

            <div class="total-box">

                <div class="total-title">
                    Updated Total Price
                </div>

                <div class="total-price" id="totalPrice">
                    RM <?= number_format($booking['total_price'], 2) ?>
                </div>

            </div>

            <button type="submit" class="btn-save">
                Save Changes
            </button>

            <a href="walkin_bookings.php" class="back-btn">
                Back
            </a>

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

    totalPrice.textContent =
        'RM ' + total.toFixed(2);
}

adultQty.addEventListener('input', updateTotal);
seniorQty.addEventListener('input', updateTotal);
studentQty.addEventListener('input', updateTotal);
childrenQty.addEventListener('input', updateTotal);

updateTotal();

/* load dropdown chain */
document.addEventListener('DOMContentLoaded', async function(){

const movieId=document.getElementById('movie_id').value;
const selectedDate="<?= $booking['show_date'] ?>";
const selectedTime="<?= $booking['show_time'] ?>";

const dateSelect=document.getElementById('show_date');
const timeSelect=document.getElementById('show_time');

let res1=await fetch('get_dates.php?movie_id='+movieId);
dateSelect.innerHTML = await res1.text();

setTimeout(() => {
    dateSelect.value = selectedDate;
}, 0);

let res2=await fetch('get_times.php?movie_id='+movieId+'&date='+selectedDate);
timeSelect.innerHTML = await res2.text();

setTimeout(() => {
    timeSelect.value = selectedTime;
}, 0);

});

/* change events */
document.getElementById('movie_id').addEventListener('change',function(){
fetch('get_dates.php?movie_id='+this.value)
.then(r=>r.text())
.then(d=>{
document.getElementById('show_date').innerHTML=d;
document.getElementById('show_time').innerHTML='<option>Select Time</option>';
});
});

document.getElementById('show_date').addEventListener('change',function(){
const movieId=document.getElementById('movie_id').value;
fetch('get_times.php?movie_id='+movieId+'&date='+this.value)
.then(r=>r.text())
.then(d=>{
document.getElementById('show_time').innerHTML=d;
});
});
</script>

</body>
</html>