<?php

// Include database connection
require_once '../config/db.php';


// Set content type
header('Content-Type: text/html; charset=utf-8');


// Get data from URL
$movie_id = $_GET['movie_id'] ?? '';

$date = $_GET['date'] ?? '';


// Check missing data
if (!$movie_id || !$date) {

    echo '<option disabled selected>Missing data</option>';

    exit;
}


$stmt = $conn->prepare("
    SELECT s.show_time

    FROM showtimes s

    JOIN movies m
    ON s.movie_id = m.id

    WHERE s.movie_id = ?
    AND s.show_date = ?
    AND m.status = 'active'
    AND TIMESTAMP(s.show_date, s.show_time) > NOW()

    ORDER BY s.show_time ASC
");

$stmt->bind_param(
    "is",
    $movie_id,
    $date
);

$stmt->execute();

$result = $stmt->get_result();


// Check if no showtime found
if ($result->num_rows == 0) {

    echo '<option disabled selected>No Time Available</option>';

    exit;
}


// Default option
echo '<option value="">Select Time</option>';


// Display showtimes
while ($row = $result->fetch_assoc()) {

    // Format time
    $time = date(
        'h:i A',
        strtotime($row['show_time'])
    );

    echo '
        <option value="' . htmlspecialchars($row['show_time']) . '">
            ' . htmlspecialchars($time) . '
        </option>
    ';
}


// Close statement
$stmt->close();

?>