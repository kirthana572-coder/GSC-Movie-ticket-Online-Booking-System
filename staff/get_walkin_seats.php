<?php
require_once '../config/db.php';

$movie_id = $_GET['movie_id'];
$date = $_GET['date'];
$time = $_GET['time'];

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

$showtime_id = $showtime['id'] ?? 0;

if(!$showtime_id){
    exit();
}

$bookedSeats = [];

/* ONLINE BOOKINGS */
$result1 = $conn->query("
    SELECT bs.seat_id
    FROM booking_seats bs
    JOIN bookings b
    ON bs.booking_id = b.id
    WHERE b.showtime_id = '$showtime_id'
");

while($row = $result1->fetch_assoc()){
    $bookedSeats[] = $row['seat_id'];
}

/* WALK-IN BOOKINGS */
$walkinSeats = $conn->query("
    SELECT seat_id
    FROM walkin_booking_seats wbs
    JOIN walkin_bookings wb
    ON wbs.walkin_booking_id = wb.id
    WHERE wb.showtime_id = '$showtime_id'
");

while($row = $walkinSeats->fetch_assoc()){

    $bookedSeats[] = $row['seat_id'];
}


/* ALL SEATS */
$seats = $conn->query("
    SELECT *
    FROM seats
    WHERE showtime_id = '$showtime_id'
    ORDER BY seat_number
    ");

while($seat = $seats->fetch_assoc()){

    $disabled =
        in_array($seat['id'], $bookedSeats);

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
        >

        <?= $seat['seat_number'] ?>

    </label>

<?php } ?>