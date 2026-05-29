<?php

// Include database connection
require_once '../config/db.php';


// Get data from URL
$movie_id = $_GET['movie_id'] ?? 0;
$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$booking_id = intval($_GET['booking_id'] ?? 0);


// Stop if missing data
if (!$movie_id || !$date || !$time || !$booking_id) {
    exit;
}


// Get showtime ID
$stmt = $conn->prepare("
    SELECT id
    FROM showtimes
    WHERE movie_id = ?
    AND show_date = ?
    AND show_time = ?
");

$stmt->bind_param("iss", $movie_id, $date, $time);

$stmt->execute();

$showtime = $stmt->get_result()->fetch_assoc();

$stmt->close();


// Stop if showtime not found
if (!$showtime) {
    exit;
}

$showtime_id = $showtime['id'];


// Store booked seats
$bookedSeats = [];


// Get booked online seats
$stmt = $conn->prepare("
    SELECT seat_id
    FROM booking_seats bs

    JOIN bookings b
    ON bs.booking_id = b.id

    WHERE b.showtime_id = ?
");

$stmt->bind_param("i", $showtime_id);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $bookedSeats[] = $row['seat_id'];
}

$stmt->close();


// Get booked walk-in seats
// Exclude current booking
$stmt = $conn->prepare("
    SELECT seat_id
    FROM walkin_booking_seats wbs

    JOIN walkin_bookings wb
    ON wbs.walkin_booking_id = wb.id

    WHERE wb.showtime_id = ?
    AND wb.id != ?
");

$stmt->bind_param("ii", $showtime_id, $booking_id);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $bookedSeats[] = $row['seat_id'];
}

$stmt->close();


// Get current selected seats
$currentSeats = [];

$stmt = $conn->prepare("
    SELECT seat_id
    FROM walkin_booking_seats
    WHERE walkin_booking_id = ?
");

$stmt->bind_param("i", $booking_id);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $currentSeats[] = $row['seat_id'];
}

$stmt->close();


// Get all seats
$stmt = $conn->prepare("
    SELECT *
    FROM seats
    WHERE showtime_id = ?
    ORDER BY 
    LEFT(seats.seat_number,1),
    CAST(SUBSTRING(seats.seat_number,2) AS UNSIGNED)
");

$stmt->bind_param("i", $showtime_id);

$stmt->execute();

$seats = $stmt->get_result();


// Display seats
while ($seat = $seats->fetch_assoc()) {

    // Check seat status
    $disabled = in_array($seat['id'], $bookedSeats);

    $checked = in_array($seat['id'], $currentSeats);

    ?>

    <label 
        class="btn <?= $disabled ? 'btn-secondary' : 'btn-outline-warning' ?>" 
        style="min-width:70px"
    >

        <input 
            type="checkbox"
            name="seats[]"
            value="<?= $seat['id'] ?>"
            autocomplete="off"

            <?= $disabled ? 'disabled' : '' ?>

            <?= $checked ? 'checked' : '' ?>
        >

        <?= htmlspecialchars($seat['seat_number']) ?>

    </label>

    <?php
}

$stmt->close();

?>