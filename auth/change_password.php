<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /GSC-Movie-ticket-Online-Booking-System/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /GSC-Movie-ticket-Online-Booking-System/change_password.php");
    exit();
}

$new_password = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (strlen($new_password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters.";
    header("Location: /GSC-Movie-ticket-Online-Booking-System/change_password.php");
    exit();
}
if ($new_password !== $confirm) {   // ✅ 改为 $confirm
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: /GSC-Movie-ticket-Online-Booking-System/change_password.php");
    exit();
}

$hash = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$stmt->bind_param("si", $hash, $_SESSION['user_id']);
$stmt->execute();

$_SESSION['success'] = "Password changed successfully.";
header("Location: /GSC-Movie-ticket-Online-Booking-System/change_password.php");
exit();