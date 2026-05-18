<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - GSC Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }
        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
        }
        .login-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(12px);
            border-radius: 32px;
            padding: 40px 35px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.2);
            transition: transform 0.3s ease;
            animation: fadeUp 0.8s;
        }
        .login-card:hover { transform: translateY(-5px); }
        @keyframes fadeUp { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
        .logo { text-align:center; font-size: 48px; font-weight:800; color:#f5c518; margin-bottom:10px; letter-spacing:2px; text-shadow:0 2px 5px rgba(0,0,0,0.2); }
        .tagline { text-align:center; color:#ddd; margin-bottom:30px; font-size:14px; }
        .form-control { border-radius: 40px; padding: 12px 20px; background: rgba(255,255,255,0.9); border: none; }
        .form-control:focus { border-color: #f5c518; box-shadow: 0 0 0 0.2rem rgba(245,197,24,0.3); background: #fff; }
        .input-group-text { background: rgba(255,255,255,0.9); border-left: none; border-radius: 0 40px 40px 0; cursor: pointer; }
        .btn-signin { background: #f5c518; border: none; border-radius: 40px; padding: 12px; font-weight:700; font-size:18px; transition:0.3s; width:100%; color:#1a1a1a; }
        .btn-signin:hover { background: #ffd43b; transform: scale(1.02); }
        .forgot-link { text-align:right; margin-top:10px; }
        .forgot-link a, .register-link a { color: #f5c518; text-decoration: none; font-size:14px; }
        .forgot-link a:hover, .register-link a:hover { text-decoration: underline; }
        .register-link { text-align:center; margin-top:20px; border-top:1px solid rgba(255,255,255,0.3); padding-top:20px; color:#ddd; }
        .alert { border-radius: 40px; background: rgba(0,0,0,0.7); color:#fff; border: none; }
    </style>
</head>
<body>
<div id="particles-js"></div>
<div class="login-card">
    <div class="logo">🎬 GSC</div>
    <div class="tagline">Your ultimate movie experience</div>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <form action="auth/login.php" method="POST">
        <div class="mb-3"><label class="form-label text-white">Email address</label><input type="email" name="email" class="form-control" required autofocus></div>
        <div class="mb-3"><label class="form-label text-white">Password</label><div class="input-group"><input type="password" name="password" id="password" class="form-control" required><span class="input-group-text" onclick="togglePassword()"><i class="far fa-eye-slash" id="toggleIcon"></i></span></div></div>
        <button type="submit" class="btn-signin">Sign In <i class="fas fa-arrow-right ms-2"></i></button>
        <div class="forgot-link"><a href="forgotpassword.php">Forgot password?</a></div>
        <div class="register-link">New user? <a href="register.php">Create an account</a></div>
    </form>
</div>
<script>function togglePassword(){const pwd=document.getElementById('password'),icon=document.getElementById('toggleIcon');if(pwd.type==='password'){pwd.type='text';icon.classList.remove('fa-eye-slash');icon.classList.add('fa-eye');}else{pwd.type='password';icon.classList.remove('fa-eye');icon.classList.add('fa-eye-slash');}}</script>
</body>
</html>