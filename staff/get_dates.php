<?php

// Include database connection
require_once '../config/db.php';


// Get movie ID
$movie_id = $_GET['movie_id'] ?? '';


// Stop if no movie ID
if (!$movie_id) {
    exit;
}


// Get available show dates
$stmt = $conn->prepare("
    SELECT DISTINCT s.show_date

    FROM showtimes s

    JOIN movies m
    ON s.movie_id = m.id

    WHERE s.movie_id = ?
    AND m.status = 'active'
    AND TIMESTAMP(s.show_date, s.show_time) > NOW()

    ORDER BY s.show_date ASC
");

$stmt->bind_param("i", $movie_id);

$stmt->execute();

$result = $stmt->get_result();


// Default option
echo '<option value="">Select Date</option>';


// Display dates
while ($row = $result->fetch_assoc()) {

    echo '
        <option value="' . htmlspecialchars($row['show_date']) . '">
            ' . htmlspecialchars($row['show_date']) . '
        </option>
    ';
}


// Close statement
$stmt->close();

?>