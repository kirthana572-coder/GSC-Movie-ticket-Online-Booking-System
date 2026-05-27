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