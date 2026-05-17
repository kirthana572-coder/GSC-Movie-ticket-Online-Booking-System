<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';
$id = intval($_GET['id']);
$conn->query("DELETE FROM walkin_bookings WHERE id = $id");
header("Location: walkin_bookings.php");
exit;
?>