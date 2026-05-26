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