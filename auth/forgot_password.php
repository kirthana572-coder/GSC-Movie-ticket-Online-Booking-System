<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
require_once '../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../src/Exception.php';
require_once '../src/PHPMailer.php';
require_once '../src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../forgotpassword.php");
    exit();
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please enter a valid email.";
    header("Location: ../forgotpassword.php");
    exit();
}

// 检查邮箱是否存在
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $_SESSION['success'] = "If your email is registered, you will receive a reset link.";
    $stmt->close();
    header("Location: ../forgotpassword.php");
    exit();
}
$stmt->close();

// 生成 token
$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

$stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
$stmt->bind_param("sss", $token, $expiry, $email);
$stmt->execute();
$stmt->close();

// ---------- 发送邮件 ----------
$mail = new PHPMailer(true);
try {
    // SMTP 配置（可根据需要修改）
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'gscmovieticketonlinebookingsys@gmail.com';
    $mail->Password   = 'dfdajqrfbilgylme';        // 请使用你自己的授权码
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // 发件人和收件人
    $mail->setFrom('gscmovieticketonlinebookingsys@gmail.com', 'GSC Cinema');
    $mail->addAddress($email);

    // ----- 动态生成重置链接（关键修改）-----
    // 自动检测协议（http / https）
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    // 当前域名（如 localhost 或线上域名）
    $domain = $_SERVER['HTTP_HOST'];
    // 组合完整链接
    $reset_link = $protocol . $domain . BASE_URL . "/reset_password.php?token=" . $token;

    $mail->isHTML(true);
    $mail->Subject = 'Reset Your GSC Cinema Password';
    $mail->Body    = "
        <h2>Password Reset Request</h2>
        <p>Click the link below to reset your password (valid for 1 hour):</p>
        <p><a href='$reset_link'>$reset_link</a></p>
        <p>If you didn't request this, please ignore this email.</p>
        <p>GSC Cinema Team</p>
    ";
    $mail->AltBody = "Reset your password: $reset_link";

    $mail->send();
    $_SESSION['success'] = "Reset link sent to your email. Please check inbox/spam.";
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to send email. Error: " . $mail->ErrorInfo;
}

header("Location: ../forgotpassword.php");
exit();
?>