<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/db.php';
$token = $_GET['token'] ?? '';
$error = '';
$success = '';
if(empty($token)) die("Invalid reset link.");
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if(!$user) die("Reset link is invalid or expired.");
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if(strlen($password) < 6) $error = "Password must be at least 6 characters.";
    elseif($password !== $confirm) $error = "Passwords do not match.";
    else{
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $stmt->bind_param("si", $hash, $user['id']);
        $stmt->execute();
        $success = "Password updated successfully! <a href='".BASE_URL."/login.php'>Click here to sign in</a>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{ background:linear-gradient(135deg,#f4edd9,#f9d59f); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card{ max-width:450px; border-radius:24px; background:rgba(255,255,255,0.95); padding:30px; }
        .btn-warning{ background:#f5c518; border-radius:40px; }
    </style>
</head>
<body>
<div class="card">
    <h3 class="text-center">Reset Password</h3>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php else: ?>
        <form method="POST">
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="New password" required></div>
            <div class="mb-3"><input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required></div>
            <button type="submit" class="btn btn-warning w-100">Update Password</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>