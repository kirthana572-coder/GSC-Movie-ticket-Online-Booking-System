<?php
require_once '../config/db.php';

$movie_id = $_GET['movie_id'] ?? '';

if (!$movie_id) {
    exit;
}

$result = $conn->query("
    SELECT DISTINCT show_date
    FROM showtimes
    WHERE movie_id = '$movie_id'
    ORDER BY show_date ASC
");

echo '<option value="">Select Date</option>';

while($row = $result->fetch_assoc()) {
    echo '<option value="'.$row['show_date'].'">'
        .$row['show_date'].
    '</option>';
}
?>