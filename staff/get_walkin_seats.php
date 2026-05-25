<?php
require_once '../config/db.php';
$movie_id = $_GET['movie_id'] ?? 0;
$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
if (!$movie_id || !$date || !$time) exit;
// 获取 showtime_id
$stmt = $conn->prepare("SELECT id FROM showtimes WHERE movie_id = ? AND show_date = ? AND show_time = ?");
$stmt->bind_param("iss", $movie_id, $date, $time);
$stmt->execute();
$showtime = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$showtime) exit;
$showtime_id = $showtime['id'];
// 已预订座位（在线）
$bookedSeats = [];
$stmt = $conn->prepare("SELECT bs.seat_id FROM booking_seats bs JOIN bookings b ON bs.booking_id = b.id WHERE b.showtime_id = ?");
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $bookedSeats[] = $row['seat_id'];
$stmt->close();
// 已预订座位（walk-in）
$stmt = $conn->prepare("SELECT seat_id FROM walkin_booking_seats wbs JOIN walkin_bookings wb ON wbs.walkin_booking_id = wb.id WHERE wb.showtime_id = ?");
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $bookedSeats[] = $row['seat_id'];
$stmt->close();
// 获取所有座位
$stmt = $conn->prepare("SELECT * FROM seats WHERE showtime_id = ? ORDER BY seat_number");
$stmt->bind_param("i", $showtime_id);
$stmt->execute();
$seats = $stmt->get_result();
while ($seat = $seats->fetch_assoc()) {
    $disabled = in_array($seat['id'], $bookedSeats);
    ?>
    <label class="btn <?= $disabled ? 'btn-secondary' : 'btn-outline-warning' ?>" style="min-width:70px">
        <input type="checkbox" name="seats[]" value="<?= $seat['id'] ?>" autocomplete="off" <?= $disabled ? 'disabled' : '' ?>>
        <?= htmlspecialchars($seat['seat_number']) ?>
    </label>
    <?php
}
$stmt->close();
?>