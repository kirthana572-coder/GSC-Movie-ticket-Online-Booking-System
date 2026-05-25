<?php
require_once '../config/db.php';
header('Content-Type: text/html; charset=utf-8');
$movie_id = $_GET['movie_id'] ?? '';
$date = $_GET['date'] ?? '';
if (!$movie_id || !$date) {
    echo '<option disabled selected>Missing data</option>';
    exit;
}
$stmt = $conn->prepare("SELECT DISTINCT show_time FROM showtimes WHERE movie_id = ? AND show_date = ? ORDER BY show_time ASC");
$stmt->bind_param("is", $movie_id, $date);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo '<option disabled selected>No Time Available</option>';
    exit;
}
echo '<option value="">Select Time</option>';
while ($row = $result->fetch_assoc()) {
    $time = date('h:i A', strtotime($row['show_time']));
    echo '<option value="' . htmlspecialchars($row['show_time']) . '">' . htmlspecialchars($time) . '</option>';
}
$stmt->close();
?>