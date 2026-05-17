<?php
session_start();
require_once '../config/db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT id, full_name, password_hash, role FROM users WHERE email = ? AND role = 'staff'");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = 'staff';
    header("Location: ../staff/staff_dashboard.php");
} else {
    $_SESSION['error'] = "Invalid staff credentials.";
    header("Location: ../staff/login.php");
}
exit;
?>