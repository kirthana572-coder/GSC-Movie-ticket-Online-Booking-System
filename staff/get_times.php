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


// Get available showtimes
$stmt = $conn->prepare("
    SELECT DISTINCT show_time
    FROM showtimes
    WHERE movie_id = ?
    AND show_date = ?
    ORDER BY show_time ASC
");

$stmt->bind_param("is", $movie_id, $date);

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