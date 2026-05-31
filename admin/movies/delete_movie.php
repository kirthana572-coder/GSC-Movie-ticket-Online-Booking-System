<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

$id = $_GET['id'] ?? 0;


/* GET POSTER */

$stmt = $conn->prepare("
    SELECT poster_image
    FROM movies
    WHERE id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$movie = $stmt
    ->get_result()
    ->fetch_assoc();


if(!$movie){

    die("Movie not found.");
}

$stmt = $conn->prepare("
    SELECT COUNT(*) as total
    FROM bookings b
    JOIN showtimes s ON b.showtime_id = s.id
    WHERE s.movie_id = ?
    AND b.payment_status IN ('pending', 'paid')
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result()->fetch_assoc();

if ($result['total'] > 0) {

    echo "<script>
        alert('Cannot delete: this movie has active bookings.');
        window.location.href='admin_movies.php';
    </script>";
    exit();
}


/* DELETE POSTER */

if($movie['poster_image']){

    $path =
        '../../uploads/posters/'
        . $movie['poster_image'];

    if(file_exists($path)){

        unlink($path);
    }
}


/* DELETE MOVIE */

$stmt = $conn->prepare("
    DELETE FROM movies
    WHERE id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();


header(
    "Location: " .
    BASE_URL .
    "/admin/movies/admin_movies.php?success=deleted"
);

exit();