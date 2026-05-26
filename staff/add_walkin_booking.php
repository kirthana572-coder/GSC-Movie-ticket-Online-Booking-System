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
}
?>