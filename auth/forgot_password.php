<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../src/Exception.php';
require_once '../src/PHPMailer.php';
require_once '../src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/forgotpassword.php");
    exit();
}
$email = trim($_POST['email'] ?? '');
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please enter a valid email.";
    header("Location: " . BASE_URL . "/forgotpassword.php");
    exit();
}
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $_SESSION['success'] = "If your email is registered, you will receive a reset link.";
    $stmt->close();
    header("Location: " . BASE_URL . "/forgotpassword.php");
    exit();
}
$stmt->close();
$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
$stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
$stmt->bind_param("sss", $token, $expiry, $email);
$stmt->execute();
$stmt->close();

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'gscmovieticketonlinebookingsys@gmail.com';
    $mail->Password   = 'dfdajqrfbilgylme';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->setFrom('gscmovieticketonlinebookingsys@gmail.com', 'GSC Cinema');
    $mail->addAddress($email);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $domain = $_SERVER['HTTP_HOST'];
    $reset_link = $protocol . $domain . BASE_URL . "/reset_password.php?token=" . $token;
    $mail->isHTML(true);
    $mail->Subject = 'Reset Your GSC Cinema Password';
    $mail->Body    = "<h2>Password Reset Request</h2><p>Click the link below to reset your password (valid for 1 hour):</p><p><a href='$reset_link'>$reset_link</a></p><p>If you didn't request this, please ignore this email.</p><p>GSC Cinema Team</p>";
    $mail->AltBody = "Reset your password: $reset_link";
    $mail->send();
    $_SESSION['success'] = "Reset link sent to your email. Please check inbox/spam.";
} catch (Exception $e) {
    $_SESSION['error'] = "Failed to send email. Error: " . $mail->ErrorInfo;
}
header("Location: " . BASE_URL . "/forgotpassword.php");
exit();
?>