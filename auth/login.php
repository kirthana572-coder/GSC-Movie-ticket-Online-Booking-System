<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Email and password required.";
    header("Location: ../login.php");
    exit();
}

// 查询用户（不限制角色，让数据库的 role 字段决定）
$stmt = $conn->prepare("
    SELECT id, full_name, email, password_hash, role 
    FROM users 
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit();
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit();
}

session_regenerate_id(true);

// 存储基本信息
$_SESSION['user_id']   = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email']     = $user['email'];
$_SESSION['role']      = $user['role'];

// 根据角色跳转
if ($user['role'] === 'staff') {
    header("Location: ../staff/staff_dashboard.php");
} elseif ($user['role'] === 'admin') {
    header("Location: ../admin/dashboard.php");   // 如果你之后有 admin 模块
} else {
    header("Location: ../index.php");             // 默认普通用户
}
exit();
?>