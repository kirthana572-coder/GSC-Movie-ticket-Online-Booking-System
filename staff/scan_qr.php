<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

$error = '';
$success = '';
$booking_id = '';

// 处理 POST 提交（手动输入或扫码传回的 ID）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = intval($_POST['booking_id']);
    if ($booking_id) {
        $result = $conn->query("SELECT qr_used, payment_status FROM bookings WHERE id = $booking_id");
        if ($row = $result->fetch_assoc()) {
            if ($row['payment_status'] !== 'Paid') {
                $error = "Ticket not paid.";
            } elseif ($row['qr_used'] == 1) {
                $error = "Ticket already used.";
            } else {
                $conn->query("UPDATE bookings SET qr_used = 1 WHERE id = $booking_id");
                $success = "Ticket validated. Entry allowed.";
                // 可选：插入通知
            }
        } else {
            $error = "Invalid booking ID.";
        }
    } else {
        $error = "Booking ID required.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Scan QR Ticket - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { background: linear-gradient(135deg, #f4edd9, #f9d59f); min-height: 100vh; }
        .container-custom { max-width: 600px; margin: 50px auto; }
        .card { border-radius: 24px; background: rgba(255,255,255,0.95); padding: 25px; }
        #reader { width: 100%; margin-bottom: 20px; }
        .btn-warning { background: #f5c518; border-radius: 30px; }
    </style>
</head>
<body>
<div class="container-custom">
    <div class="card">
        <h3 class="text-center">Scan QR Ticket</h3>
        <p class="text-muted text-center">Use camera to scan the QR code on ticket</p>

        <!-- 二维码扫描区域 -->
        <div id="reader"></div>

        <hr>
        <p class="text-center"><strong>Or enter Booking ID manually:</strong></p>
        <form method="POST">
            <div class="input-group mb-3">
                <input type="text" name="booking_id" class="form-control" placeholder="Booking ID" value="<?= htmlspecialchars($booking_id) ?>" required>
                <button type="submit" class="btn btn-warning">Validate</button>
            </div>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-danger mt-3"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success mt-3"><?= $success ?></div>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="staff_dashboard.php" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>

<script>
// 初始化扫码器
const html5QrCode = new Html5Qrcode("reader");
const qrCodeSuccessCallback = (decodedText, decodedResult) => {
    // 假设二维码内容就是 booking_id 或包含 booking_id（例如 "BOOKING:123"）
    let bookingId = decodedText;
    // 如果内容是 "GSC Ticket #123 ..." 格式，提取数字
    const match = decodedText.match(/#(\d+)/);
    if (match) {
        bookingId = match[1];
    }
    // 自动提交表单进行验证
    const formData = new FormData();
    formData.append('booking_id', bookingId);
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    }).then(response => response.text())
      .then(html => {
          // 刷新页面显示结果（简单粗暴但有效）
          document.open();
          document.write(html);
          document.close();
      }).catch(err => console.error(err));
};
const config = { fps: 10, qrbox: { width: 250, height: 250 } };
html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback)
    .catch(err => console.log("Unable to start scanning", err));
</script>
</body>
</html>