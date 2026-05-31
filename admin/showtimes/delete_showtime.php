<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';


$id = $_GET['id'] ?? 0;


/* CHECK SHOWTIME */

$stmt = $conn->prepare("

    SELECT id
    FROM showtimes
    WHERE id = ?

");

$stmt->bind_param("i", $id);

$stmt->execute();

$showtime = $stmt
    ->get_result()
    ->fetch_assoc();


if(!$showtime){

    die("Showtime not found.");
}

$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM bookings
    WHERE showtime_id = ?
    AND payment_status IN ('pending', 'paid')
");
$stmt->bind_param("i", $id);
$stmt->execute();
$online = $stmt->get_result()->fetch_assoc()['total'];

$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM walkin_bookings
    WHERE showtime_id = ?
    AND payment_status = 'Paid'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$walkin = $stmt->get_result()->fetch_assoc()['total'];

if (($online + $walkin) > 0) {

    echo "<script>
        alert('Cannot delete: this showtime has existing bookings (online or walk-in).');
        window.location.href='admin_showtimes.php';
    </script>";
    exit();
}

/* DELETE SHOWTIME */

$stmt = $conn->prepare("

    DELETE FROM showtimes
    WHERE id = ?

");

$stmt->bind_param("i", $id);

$stmt->execute();


/* REDIRECT */

header(

    "Location: " .

    BASE_URL .

    "/admin/showtimes/admin_showtimes.php?success=deleted"

);

exit();
?>