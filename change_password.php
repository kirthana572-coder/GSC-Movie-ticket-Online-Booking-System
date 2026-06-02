<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/db.php';

switch ($_SESSION['role']) {

    case 'customer':
        $profileUrl = BASE_URL . '/customer/profile.php';
        break;

    case 'staff':
        $profileUrl = BASE_URL . '/staff/profile.php';
        break;

    case 'admin':
        $profileUrl = BASE_URL . '/admin/profile.php';
        break;

    default:
        $profileUrl = BASE_URL . '/index.php';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/gsc-style.css">
    <style>
        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;

            background:
            linear-gradient(
                180deg,
                #faf8f2,
                #f3ede0
            );

            min-height:100vh;
        }
        .main-container{
            max-width:500px;
            margin:90px auto;
            padding:0 15px;
            justify-content: center;
            align-items: center;
        }
        
        .change-pwd-card{
            transform: scale(1.15);
            transform-origin: center;
            border:none;

            border-radius:24px;

            background:white;

            padding:35px;

            box-shadow:
            0 10px 30px rgba(0,0,0,.08);
        }

        .card-title{
            color: #222;
            font-weight: 700;
            font-size: 30px;
        }
        .card-subtitle{
            color: #777;
            font-size: 15px;
        }
        label{
            font-weight: 500;
            margin-bottom: 6px;
            color: #686666;
        }       
        .form-control{
            background: #fff !important;
            color: #000 !important;
            border-radius: 12px !important;
            padding: 12px !important;
            border: 1px solid #ddd;
        }
        .form-control:focus{
            background: #fff !important;
            color: #000 !important;
            border-color: #f5c518;
            box-shadow: 0 0 0 0.2rem rgba(245,197,24,0.25);
        }
        .input-group-text{
            background: #fff !important;
            color: #000 !important;
            border: 1px solid #ddd;
            border-left: none;
        }      
        .toggle-btn{
            cursor: pointer;
            background: #fff;
            border-radius: 0 12px 12px 0;
        }
        .btn-warning{
            background-color: #f5c518;
            border: none;
            border-radius: 30px;
            padding: 12px;
            font-size: 18px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-warning:hover{
            background-color: #e0b400;
            transform: scale(1.03);
        }
        a{
            text-decoration: none;
            color: #666;
        }
        a:hover{
            color: #000;
        }

        ::-ms-reveal{
            display:none;
        }
    
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-container">
        <div class="change-pwd-card card shadow p-4">
            <h3 class="text-center mb-4">
                Change Password
            </h3>

            <p class="text-center card-subtitle mb-4">Update your account password securely</p>

            <!-- ERROR -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- SUCCESS -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- FORM -->
            <form action="auth/change_password.php" method="POST">
                <div class="mb-3">
                    <label>New Password</label>
                    <div class="input-group">
                        <input type="password" name="new_password" id="newPassword" class="form-control" required>
                        <span class="input-group-text toggle-btn" onclick="togglePassword('newPassword', this)">👁</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Confirm Password</label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                        <span class="input-group-text toggle-btn" onclick="togglePassword('confirmPassword', this)">👁</span>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning w-100">Update Password</button>
                <div class="text-center mt-3">
                    <a href="<?= $profileUrl ?>">
                        Back to Profile
                    </a>
                </div>
            </form>
        </div>
</div>
<script>
function togglePassword(fieldId, btn) {
    let input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "🙈";
    } else {
        input.type = "password";
        btn.textContent = "👁";
    }
}
</script>

<script src="<?= BASE_URL ?>/notification.js"></script>
</body>
</html>