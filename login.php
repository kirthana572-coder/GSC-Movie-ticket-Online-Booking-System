<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sign In - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
   
    <style>
        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:#f6f7fb;
            min-height:100vh;
            animation:fadeBg 1.5s ease;
        }
        @keyframes fadeBg{ from{opacity:0;} to{opacity:1;} }

        .main-container{
            min-height:100vh;

            display:flex;
            justify-content:center;
            align-items:flex-start;

            padding-top:120px;
            padding-bottom:60px;
        }

        .login-card{
            max-width:520px;
            width:100%;

            padding:48px;

            border-radius:22px;

            background:#fff;

            border:1px solid rgba(0,0,0,.05);

            box-shadow:
                0 10px 25px rgba(0,0,0,.08);

            transition:.25s;
        }

        .login-card:hover{
            transform:translateY(-2px);

            box-shadow:
                0 14px 35px rgba(0,0,0,.10);
        }

        .page-subtitle{
            color:#6c757d;
            font-size:15px;
            margin:0;
        }

        h3{
            font-size:32px;
            font-weight:700;
            color:#212529;
            letter-spacing:-0.5px;
            margin-bottom:8px;
        }

        .form-control{
            height:50px;

            border-radius:12px;

            border:1px solid #dee2e6;

            padding:0 15px;

            font-size:15px;
        }
        
        .form-control:focus{ border-color:#f5c518; box-shadow:0 0 0 0.2rem rgba(245,197,24,0.25); }
        
        .form-label{
            color:#495057;
            font-size:14px;
            font-weight:600;
            margin-bottom:8px;
        }

        .toggle-btn{
            cursor:pointer;
            background:#fff;

            border-left:none;

            border-radius:0 10px 10px 0;

            user-select:none;
        }
        
        .auth-btn{
            background:#f5c518;
            color:#111;

            border:none;
            border-radius:12px;

            height:50px;

            font-size:16px;
            font-weight:700;

            transition:.25s;
        }

        .auth-btn:hover{
            background:#ffd028;
            color:#111;

            transform:translateY(-2px);
        }
        .auth-link{
            color:#6c757d;
            text-decoration:none;
            font-weight:500;
        }

        .auth-link:hover{
            color:#212529;
        }

        ::-ms-reveal{
            display:none;
        }

    </style>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">

</head>
<body class="auth-page">
<?php include 'includes/navbar.php'; ?>
<div class="main-container">
    <div class="auth-card">
        <div class="text-center mb-4">

            <h3>Welcome Back</h3>

            <p class="page-subtitle">
                Sign in to continue managing your bookings and tickets.
            </p>

        </div>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <form action="<?= BASE_URL ?>/auth/login.php" method="POST">
            <div class="mb-4"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" required>
                    <span class="input-group-text toggle-btn" onclick="togglePassword()">👁️</span>
                </div>
            </div>

            <div class="text-end mb-5">
                <a href="<?= BASE_URL ?>/forgotpassword.php" class="auth-link">
                    Forgot Password?
                </a>
            </div>

            <button type="submit" class="btn auth-btn w-100">Sign In</button>
        </form>

        <div class="text-center mt-2">
            <a href="<?= BASE_URL ?>/register.php" class="auth-link">
            Don't have an account? Register
            </a>
        </div>

    </div>
</div>
<script>function togglePassword(){ var field=document.getElementById('password'); field.type=field.type==="password"?"text":"password"; }</script>
</body>
</html>