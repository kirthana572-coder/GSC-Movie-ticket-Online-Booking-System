<?php
session_start();
session_destroy();
header("Location: /GSC-Movie-ticket-Online-Booking-System/signin.php");
exit();