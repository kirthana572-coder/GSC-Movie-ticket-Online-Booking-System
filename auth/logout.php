<?php
session_start();
session_destroy();
header("Location: /GSC-Movie-ticket-Online-Booking-System/index.php");
exit();