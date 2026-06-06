<?php
/**
 * 发送邮件
 */
function sendMail($to, $subject, $body) {
    require_once __DIR__ . '/../src/PHPMailer.php';
    require_once __DIR__ . '/../src/SMTP.php';
    require_once __DIR__ . '/../src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("邮件发送失败: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * 发送站内消息（初始 is_popup_shown = 0）
 */
function sendStationNotification($user_id, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, is_popup_shown, created_at) VALUES (?, ?, 0, 0, NOW())");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
}
?>