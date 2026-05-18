<?php
session_start();
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
        $success = "Password updated successfully! <a href='login.php'>Click here to sign in</a>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        #particles-js { position: fixed; top:0; left:0; width:100%; height:100%; z-index:-1; background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); }
        .glass-card { background: rgba(255,255,255,0.1); backdrop-filter: blur(12px); border-radius: 32px; padding: 40px 35px; max-width: 460px; width:100%; border:1px solid rgba(255,255,255,0.2); animation: fadeUp 0.8s; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        h3 { color:#f5c518; text-align:center; margin-bottom:25px; }
        .form-control { border-radius: 40px; padding:12px 20px; background:rgba(255,255,255,0.9); border:none; }
        .btn-reset { background:#f5c518; border-radius:40px; padding:12px; font-weight:700; width:100%; transition:0.3s; }
        .btn-reset:hover { background:#ffd43b; transform:scale(1.02); }
        .alert { border-radius:40px; background:rgba(0,0,0,0.7); color:#fff; }
    </style>
</head>
<body>
<div id="particles-js"></div>
<div class="glass-card">
    <h3><i class="fas fa-lock"></i> Reset Password</h3>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php else: ?>
        <form method="POST">
            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="New password" required></div>
            <div class="mb-3"><input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required></div>
            <button type="submit" class="btn-reset">Update Password <i class="fas fa-check-circle"></i></button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>