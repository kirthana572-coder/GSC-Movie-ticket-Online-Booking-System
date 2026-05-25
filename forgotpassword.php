<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ margin:0; font-family:'Segoe UI',sans-serif; background:linear-gradient(135deg,#f4edd9,#f9d59f); min-height:100vh; }
        .main-container{ min-height:calc(100vh - 90px); display:flex; justify-content:center; align-items:center; padding:30px; }
        .forgot-card{ width:100%; max-width:420px; background:rgba(255,255,255,0.95); border-radius:22px; padding:35px; box-shadow:0 10px 30px rgba(0,0,0,0.12); backdrop-filter:blur(10px); }
        .page-title{ text-align:center; font-size:32px; font-weight:700; color:#333; margin-bottom:10px; }
        .page-subtitle{ text-align:center; color:#777; margin-bottom:30px; font-size:15px; }
        .form-label{ font-weight:600; color:#444; margin-bottom:8px; }
        .form-control{ border-radius:12px; padding:12px; border:1px solid #ddd; }
        .form-control:focus{ border-color:#f5c518; box-shadow:0 0 0 0.2rem rgba(245,197,24,0.25); }
        .btn-warning{ background-color:#f5c518; border:none; border-radius:30px; padding:12px; font-size:17px; font-weight:600; transition:0.3s; }
        .btn-warning:hover{ background-color:#e0b400; transform:scale(1.03); }
        .back-link{ text-align:center; margin-top:18px; }
        .back-link a{ text-decoration:none; color:#666; transition:0.3s; }
        .back-link a:hover{ color:#000; }
        .alert{ border-radius:12px; }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-container">
    <div class="forgot-card">
        <h2 class="page-title">Forgot Password</h2>
        <div class="page-subtitle">Enter your email to receive a reset link</div>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <form action="<?= BASE_URL ?>/auth/forgot_password.php" method="POST">
            <div class="mb-3"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" placeholder="Enter your email" required></div>
            <button type="submit" class="btn btn-warning w-100">Send Reset Link</button>
        </form>
        <div class="back-link"><a href="<?= BASE_URL ?>/login.php">Back to Sign In</a></div>
    </div>
</div>
</body>
</html>