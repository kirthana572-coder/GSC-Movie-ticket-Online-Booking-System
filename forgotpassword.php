<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        #particles-js { position: fixed; top:0; left:0; width:100%; height:100%; z-index:-1; background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); }
        .glass-card { background: rgba(255,255,255,0.1); backdrop-filter: blur(12px); border-radius: 32px; padding: 40px 35px; max-width: 460px; width: 100%; border:1px solid rgba(255,255,255,0.2); animation: fadeUp 0.8s; transition:0.3s; }
        .glass-card:hover { transform: translateY(-5px); }
        @keyframes fadeUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        h2 { color:#f5c518; text-align:center; margin-bottom:15px; }
        .sub { color:#ddd; text-align:center; margin-bottom:30px; font-size:14px; }
        .form-control { border-radius: 40px; padding: 12px 20px; background: rgba(255,255,255,0.9); border:none; }
        .btn-submit { background:#f5c518; border-radius:40px; padding:12px; font-weight:700; width:100%; transition:0.3s; }
        .btn-submit:hover { background:#ffd43b; transform:scale(1.02); }
        .back-link { text-align:center; margin-top:20px; }
        .back-link a { color:#f5c518; text-decoration:none; }
        .alert { border-radius:40px; background:rgba(0,0,0,0.7); color:#fff; }
    </style>
</head>
<body>
<div id="particles-js"></div>
<div class="glass-card">
    <h2><i class="fas fa-key"></i> Forgot Password</h2>
    <div class="sub">Enter your email to receive a reset link</div>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <form action="auth/forgot_password.php" method="POST">
        <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Your email address" required></div>
        <button type="submit" class="btn-submit">Send Reset Link</button>
    </form>
    <div class="back-link"><a href="login.php"><i class="fas fa-arrow-left"></i> Back to Sign In</a></div>
</div>
</body>
</html>