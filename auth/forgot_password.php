<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /GSC-Movie-ticket-Online-Booking-System/forgotpassword.php");
    exit();
}

$email = trim($_POST['email'] ?? '');

//Valid email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please enter a valid email.";
    header("Location: /GSC-Movie-ticket-Online-Booking-System/forgotpassword.php");
    exit();
}

// 检查邮箱是否存在
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['error'] = "No account found with this email.";
    $stmt->close();
    header("Location: /GSC-Movie-ticket-Online-Booking-System/forgotpassword.php");
    exit();
}
$stmt->close();

// 生成随机令牌
$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

// 保存令牌和过期时间
$stmt = $conn->prepare("
    UPDATE users 
    SET reset_token = ?, reset_token_expiry = ? 
    WHERE email = ?
");
$stmt->bind_param("sss", $token, $expiry, $email);
$stmt->execute();
$stmt->close();

// 本地开发环境：直接显示重置链接（模拟发送邮件）
$reset_link = "http://localhost/GSC-Movie-ticket-Online-Booking-System/reset_password.php?token=" . $token;

$_SESSION['success'] = "Reset link generated! <br><a href='$reset_link'>Click here to reset your password</a> (For demo purposes).";
header("Location: /GSC-Movie-ticket-Online-Booking-System/forgotpassword.php");
exit();