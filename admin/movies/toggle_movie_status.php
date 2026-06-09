<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

$id = intval($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

if(
    !in_array(
        $status,
        ['active', 'inactive']
    )
){
    exit('Invalid status');
}

$stmt = $conn->prepare("
    UPDATE movies
    SET status = ?
    WHERE id = ?
");

$stmt->bind_param(
    "si",
    $status,
    $id
);

$stmt->execute();

header(
    "Location: admin_movies.php?success=status"
);

exit();