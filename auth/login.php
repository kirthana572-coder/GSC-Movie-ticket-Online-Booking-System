<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Email and password required.";
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

session_regenerate_id(true);
$_SESSION['user_id']   = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email']     = $user['email'];
$_SESSION['role']      = $user['role'];

// 根据角色跳转，使用 BASE_URL 绝对路径
if ($user['role'] === 'staff') {
    header("Location: " . BASE_URL . "/staff/staff_dashboard.php");
} elseif ($user['role'] === 'admin') {
    header("Location: " . BASE_URL . "/admin/admin_dashboard.php");
} else {
    header("Location: " . BASE_URL . "/index.php");
}
exit();
?>