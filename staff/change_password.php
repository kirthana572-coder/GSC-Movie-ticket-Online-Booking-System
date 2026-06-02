<?php

require_once '../includes/staff_auth.php';
require_once '../config/db.php';

?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GSC-Movie-ticket-Online-Booking-System/frontend/gsc-style.css">
    <style>
        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:#f6f7fb;
            min-height:100vh;
        }

        @keyframes fadeBg{
            from{
                opacity: 0;
            }
            to{
                opacity: 1;
            }
        }

        .main-container{
            margin-left:280px;

            padding:50px;

            display:flex;
            justify-content:center;
            align-items:center;
            transform:translateY(30px);
        }

        .change-pwd-card{
            width:100%;
            max-width:620px;

            background:#fff;

            border-radius:22px;

            padding:45px;

            border:none;

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);
        }

        .page-title{
            font-size:28px;
            font-weight:800;
            color:#1f1f1f;
            text-align:center;
            margin-bottom:10px;
        }

        .page-subtitle{
            text-align:center;
            color:#6c757d;

            margin-bottom:30px;

            position:relative;
            padding-bottom:18px;
        }

        .page-subtitle::after{
            content:"";

            position:absolute;

            left:50%;
            bottom:0;

            transform:translateX(-50%);

            width:70px;
            height:2px;

            background:#dee2e6;

            border-radius:999px;
        }

        label{
            font-size:13px;
            font-weight:600;
            color:#495057;
            margin-bottom:8px;
            text-transform:uppercase;
            letter-spacing:.5px;
        }     

        .form-control{
            background:#fff !important;
            color:#212529 !important;

            border-radius:12px !important;

            padding:12px 14px !important;

            border:1px solid #e9ecef !important;

            box-shadow:none !important;
        }

        .form-control:focus{
            background:#fff !important;
            color:#212529 !important;

            border-color:#f5c518 !important;

            box-shadow:
            0 0 0 .15rem rgba(245,197,24,.25)
            !important;
        }

        .input-group-text{
            width:48px;

            color:#8c8c8c;

            transition:.2s;
        }

        .input-group-text:hover{
            color:#f5c518;
        }

        .toggle-btn{
            cursor: pointer;

            background: #fff;

            border-radius: 0 12px 12px 0;
        }

        .btn-warning{

            width:100%;
            height:52px;

            background:#f7cf5b !important;

            color:#1f1f1f !important;

            border:none !important;

            margin-top:40px !important;

            border-radius:12px !important;

            font-weight:700 !important;

            transition:.2s;
        }

        .btn-warning:hover{

            background:#f5c518 !important;

            transform:translateY(-2px);
        }

        a{
            text-decoration: none;
            color: #666;
        }

        a:hover{
            color: #000;
        }

        .btn-back{

            width:100%;
            height:52px;

            display:flex;
            align-items:center;
            justify-content:center;

            margin-top:20px;

            background:#2f2f2f;

            color:#fff;

            border-radius:12px;

            text-decoration:none;

            font-weight:700;

            transition:.2s;
        }

        .btn-back:hover{

            background:#1f1f1f;

            color:#fff;

            transform:translateY(-2px);
        }

        .alert{
            border:none;
            border-radius:14px;
            font-weight:600;
            text-align:center;
             margin-bottom:25px;
        }

        .alert-success{
            background:#e7f8ee;
            color:#1e7e34;
        }

        .alert-danger{
            background:#fde8e8;
            color:#c92a2a;
        }

        .btn-warning{
            margin-top:10px;
        }

        .btn-back{
            margin-top:14px;
        }

        ::-ms-reveal{
            display:none;
        }
    </style>
</head>


<body>

<?php include '../includes/staff_sidebar.php'; ?>
    <div class="main-container">
        <div class="change-pwd-card">

        <h1 class="page-title">
            Change Password
        </h1>

        <p class="page-subtitle">
            Update your account password securely
        </p>


        <!-- ERROR -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- SUCCESS -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- FORM -->
        <form action="auth/change_password.php" method="POST">

            <!-- New Password -->
            <div class="mb-4">
                <label>New Password</label>
                <div class="input-group">
                    <input type="password" name="new_password" id="newPassword" class="form-control" required>
                    <span class="input-group-text toggle-btn" onclick="togglePassword('newPassword', this)">👁</span>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label>Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                    <span class="input-group-text toggle-btn" onclick="togglePassword('confirmPassword', this)">👁</span>
                </div>
            </div>

            <button type="submit" class="btn btn-warning w-100">
                Update Password
            </button>

            <div class="text-center mt-3">
                <a
                    href="/GSC-Movie-ticket-Online-Booking-System/staff/profile.php"
                    class="btn-back"
                >
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
<script src="/GSC-Movie-ticket-Online-Booking-System/notification.js"></script>
</body>
</html>