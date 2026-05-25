<?php
require_once '../config/db.php';
$movie_id = $_GET['movie_id'] ?? '';
if (!$movie_id) exit;
$stmt = $conn->prepare("SELECT DISTINCT show_date FROM showtimes WHERE movie_id = ? ORDER BY show_date ASC");
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
echo '<option value="">Select Date</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . htmlspecialchars($row['show_date']) . '">' . htmlspecialchars($row['show_date']) . '</option>';
}
$stmt->close();
?>