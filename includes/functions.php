<?php

/**
 * 发送邮件（使用 PHPMailer）
 */
function sendMail($to, $subject, $body) {
    // 加载 PHPMailer 类文件
    require_once __DIR__ . '/../src/Exception.php';
    require_once __DIR__ . '/../src/PHPMailer.php';
    require_once __DIR__ . '/../src/SMTP.php';

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
        error_log("Email sent successfully to $to");
        return true;
    } catch (\Exception $e) {
        error_log("邮件发送失败: " . $mail->ErrorInfo);
        error_log("Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * 发送站内消息
 */
function sendStationNotification($user_id, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, is_popup_shown, created_at) VALUES (?, ?, 0, 0, NOW())");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
}
?>