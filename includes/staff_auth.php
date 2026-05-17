<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: /GSC-Movie-ticket-Online-Booking-System/staff/login.php");
    exit;
}
?>