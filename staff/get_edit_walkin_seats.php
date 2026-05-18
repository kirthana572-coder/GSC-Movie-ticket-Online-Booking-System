<?php
require_once '../config/db.php';

$movie_id = $_GET['movie_id'];
$date = $_GET['date'];
$time = $_GET['time'];
$booking_id = $_GET['booking_id'];

$showtime = $conn->query("
    SELECT id
    FROM showtimes
    WHERE movie_id = '$movie_id'
    AND show_date = '$date'
    AND show_time = '$time'
")->fetch_assoc();

if(!$showtime){
    exit();
}

$showtime_id = $showtime['id'];

$bookedSeats = [];

/* ONLINE BOOKINGS */
$result = $conn->query("
    SELECT seat_id
    FROM booking_seats bs
    JOIN bookings b
    ON bs.booking_id = b.id
    WHERE b.showtime_id = '$showtime_id'
");

while($row = $result->fetch_assoc()){
    $bookedSeats[] = $row['seat_id'];
}

/* OTHER WALKIN BOOKINGS */
$result2 = $conn->query("
    SELECT seat_id
    FROM walkin_booking_seats wbs
    JOIN walkin_bookings wb
    ON wbs.walkin_booking_id = wb.id
    WHERE wb.showtime_id = '$showtime_id'
    AND wb.id != '$booking_id'
");

while($row = $result2->fetch_assoc()){
    $bookedSeats[] = $row['seat_id'];
}

/* CURRENT BOOKING SEATS */
$currentSeats = [];

$current = $conn->query("
    SELECT seat_id
    FROM walkin_booking_seats
    WHERE walkin_booking_id = '$booking_id'
");

while($row = $current->fetch_assoc()){
    $currentSeats[] = $row['seat_id'];
}

/* GET ALL SEATS */
$seats = $conn->query("
    SELECT *
    FROM seats
    WHERE showtime_id = '$showtime_id'
    ORDER BY seat_number
");

while($seat = $seats->fetch_assoc()){

    $disabled =
        in_array($seat['id'], $bookedSeats);

    $checked =
        in_array($seat['id'], $currentSeats);

    ?>

    <label
        class="btn <?= $disabled ? 'btn-secondary' : 'btn-outline-warning' ?>"
        style="min-width:70px">

        <input
            type="checkbox"
            name="seats[]"
            value="<?= $seat['id'] ?>"
            autocomplete="off"

            <?= $disabled ? 'disabled' : '' ?>

            <?= $checked ? 'checked' : '' ?>
        >

        <?= $seat['seat_number'] ?>

    </label>

<?php } ?>