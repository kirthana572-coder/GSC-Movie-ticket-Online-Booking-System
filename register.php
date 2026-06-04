<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:#f6f7fb;
            min-height:100vh;
            animation:fadeBg 2s ease; 
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
        
        .register-card{
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

        .register-card:hover{
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

        .form-control{ border-radius:10px; padding:12px; border:1px solid #ddd; }
        
        .form-control:focus{ border-color:#f5c518; box-shadow:0 0 0 0.2rem rgba(245,197,24,0.25); }
        
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

        .auth-btn:focus{
            box-shadow:none;
        }

        .login-link{
            color:#6c757d;
            text-decoration:none;
            font-weight:500;
        }

        .login-link:hover{
            color:#212529;
        }

        ::-ms-reveal{
            display:none;
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-container">
    <div class="register-card">
        <div class="text-center mb-4">

            <h3>Create Account</h3>

            <p class="page-subtitle">
                Create a GSC account to book movies and manage tickets.
            </p>

        </div>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <form action="<?= BASE_URL ?>/auth/register.php" method="POST">
            
        <div class="mb-3">

            <label class="form-label fw-semibold">
                Full Name
            </label>

            <input
                type="text"
                name="full_name"
                class="form-control"
                required
            >

        </div>

            <div class="mb-3">

                <label class="form-label fw-semibold">
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    required
                >

            </div>
            
            <div class="mb-3">

            <label class="form-label fw-semibold">
                Password
            </label>

            <div class="input-group">

                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    required
                >

                <span
                    class="input-group-text toggle-btn"
                    onclick="togglePassword('password', this)"
                >
                    👁️
                </span>

            </div>

        </div>
            <div class="mb-4">

                <label class="form-label fw-semibold">
                    Confirm Password
                </label>

                <div class="input-group">
                    <input type="password" id="confirmPassword" name="confirm_password" class="form-control" required>
                    <span class="input-group-text toggle-btn" onclick="togglePassword('confirmPassword', this)">👁️</span>
                </div>
            </div>

            <button type="submit" class="btn auth-btn w-100">Register</button>
        </form>

        <div class="text-center mt-4">

            <a
                href="<?= BASE_URL ?>/login.php"
                class="login-link"
            >
                Already have an account? Sign In
            </a>

        </div>
    </div>
</div>
<script>function togglePassword(fieldId,icon){ var field=document.getElementById(fieldId); if(field.type==="password"){ field.type="text"; icon.textContent="🙈"; }else{ field.type="password"; icon.textContent="👁️"; } }</script>
</body>
</html>