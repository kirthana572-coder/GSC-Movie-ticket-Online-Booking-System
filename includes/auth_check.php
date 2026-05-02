<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /GSC-Movie-ticket-Online-Booking-System/signin.php");
    exit();
}

if ($_SESSION['role'] !== 'customer') {

    header("Location: /GSC-Movie-ticket-Online-Booking-System/index.php");
    exit();
}
?>