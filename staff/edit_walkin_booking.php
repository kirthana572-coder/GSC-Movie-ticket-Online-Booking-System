<?php

// Include authentication and database
require_once '../includes/staff_auth.php';
require_once '../config/db.php';


// Get booking ID
$id = intval($_GET['id'] ?? 0);

// Check booking ID
if ($id <= 0) {
    die("Invalid booking ID.");
}


// Get current booking information
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

    WHERE walkin_bookings.id = $id
");

$booking = $result->fetch_assoc();


// Show error if booking not found
if (!$booking) {
    die("Booking not found.");
}


// Get current selected seats
$currentSeats = [];

$getSeats = $conn->query("
    SELECT seat_id 
    FROM walkin_booking_seats 
    WHERE walkin_booking_id = $id
");

while ($seat = $getSeats->fetch_assoc()) {
    $currentSeats[] = $seat['seat_id'];
}


// Form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data
    $customer_name = $conn->real_escape_string(trim($_POST['customer_name']));
    $movie_id = intval($_POST['movie_id']);
    $show_date = $conn->real_escape_string($_POST['show_date']);
    $show_time = $conn->real_escape_string($_POST['show_time']);

    // Payment status
    $payment_status = $conn->real_escape_string($_POST['payment_status']);

    // Selected seats
    $selectedSeats = $_POST['seats'] ?? [];


    // Get showtime ID
    $stmt = $conn->prepare("
        SELECT id 
        FROM showtimes 
        WHERE movie_id = ? 
        AND show_date = ? 
        AND show_time = ?
    ");

    $stmt->bind_param("iss", $movie_id, $show_date, $show_time);
    $stmt->execute();

    $showtime = $stmt->get_result()->fetch_assoc();

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


    // Check seat conflict
    foreach ($selectedSeats as $seat_id) {

        $seat_id = intval($seat_id);

        // Check online booking seats
        $stmt = $conn->prepare("
            SELECT *
            FROM booking_seats bs

            JOIN bookings b
            ON bs.booking_id = b.id

            WHERE b.showtime_id = ?
            AND bs.seat_id = ?
        ");

        $stmt->bind_param("ii", $showtime_id, $seat_id);
        $stmt->execute();

        $checkOnline = $stmt->get_result();

        $stmt->close();


        // Check walk-in booking seats
        $stmt2 = $conn->prepare("
            SELECT *
            FROM walkin_booking_seats wbs

            JOIN walkin_bookings wb
            ON wbs.walkin_booking_id = wb.id

            WHERE wb.showtime_id = ?
            AND wbs.seat_id = ?
            AND wb.id != ?
        ");

        $stmt2->bind_param("iii", $showtime_id, $seat_id, $id);
        $stmt2->execute();

        $checkWalkin = $stmt2->get_result();

        $stmt2->close();


        // Stop if seat already booked
        if ($checkOnline->num_rows > 0 || $checkWalkin->num_rows > 0) {

            echo "
                <script>
                    alert('One or more seats already booked.');
                    history.back();
                </script>
            ";

            exit();
        }
    }


    // Get ticket quantities
    $adult_qty = intval($_POST['adult_qty']);
    $senior_qty = intval($_POST['senior_qty']);
    $student_qty = intval($_POST['student_qty']);
    $children_qty = intval($_POST['children_qty']);


    // Calculate total tickets
    $totalTickets =
        $adult_qty +
        $senior_qty +
        $student_qty +
        $children_qty;


    // Validation
    if ($totalTickets <= 0) {

        echo "
            <script>
                alert('Please select at least one ticket.');
                history.back();
            </script>
        ";

        exit();
    }

    if (count($selectedSeats) == 0) {

        echo "
            <script>
                alert('Please select at least one seat.');
                history.back();
            </script>
        ";

        exit();
    }

    if (count($selectedSeats) != $totalTickets) {

        echo "
            <script>
                alert('Selected seats must equal total tickets.');
                history.back();
            </script>
        ";

        exit();
    }


    // Calculate total price
    $total =
        ($adult_qty * 12) +
        ($senior_qty * 8) +
        ($student_qty * 10) +
        ($children_qty * 6);


    // Update booking
    $sql = "
        UPDATE walkin_bookings 

        SET 
            customer_name = '$customer_name',
            showtime_id = $showtime_id,
            adult_qty = $adult_qty,
            senior_qty = $senior_qty,
            student_qty = $student_qty,
            children_qty = $children_qty,
            total_price = $total,
            payment_status = '$payment_status'

        WHERE id = $id
    ";


    // Execute update
    if ($conn->query($sql) === TRUE) {

        // Delete old seats
        $conn->query("
            DELETE FROM walkin_booking_seats 
            WHERE walkin_booking_id = $id
        ");


        // Build ticket queue
        $ticketQueue = [];

        for ($i = 0; $i < $adult_qty; $i++) {
            $ticketQueue[] = 'Adult';
        }

        for ($i = 0; $i < $senior_qty; $i++) {
            $ticketQueue[] = 'Senior';
        }

        for ($i = 0; $i < $student_qty; $i++) {
            $ticketQueue[] = 'Student';
        }

        for ($i = 0; $i < $children_qty; $i++) {
            $ticketQueue[] = 'Children';
        }


        // Reinsert selected seats
        $selectedSeats = array_values($selectedSeats);

        $stmt2 = $conn->prepare("
            INSERT INTO walkin_booking_seats
            (walkin_booking_id, seat_id, ticket_type)
            VALUES (?, ?, ?)
        ");

        for ($i = 0; $i < count($selectedSeats); $i++) {

            $seat_id = intval($selectedSeats[$i]);
            $ticket_type = $ticketQueue[$i];

            $stmt2->bind_param("iis", $id, $seat_id, $ticket_type);
            $stmt2->execute();
        }

        $stmt2->close();


        // Success message
        echo "
            <script>
                alert('Booking updated successfully!');
                window.location.href='" . BASE_URL . "/staff/walkin_bookings.php';
            </script>
        ";

        exit();

    } else {

        // Failed message
        echo "
            <script>
                alert('Update failed: " . $conn->error . "');
                history.back();
            </script>
        ";

        exit();
    }
}

?>