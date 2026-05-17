<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GSC-Movie-ticket-Online-Booking-System/frontend/gsc-style.css">
    <style>
        body{
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background: linear-gradient(135deg, #f4edd9, #f9d59f) !important;

            min-height: 100vh;

            animation: fadeBg 2s ease;
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
            min-height: calc(100vh - 70px);

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .change-pwd-card{
            max-width: 430px;
            width: 100%;

            padding: 35px;

            border-radius: 24px;
            border: none;

            margin-bottom:-100px;

            background: rgba(251, 251, 248, 0.95);

            backdrop-filter: blur(12px);

            box-shadow:
            0 10px 30px rgba(0,0,0,0.12);
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

            box-shadow:
            0 0 0 0.2rem rgba(245,197,24,0.25);
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

        .icon-circle{
            width: 75px;
            height: 75px;

            margin: auto;

            border-radius: 50%;

            background:
            linear-gradient(135deg, #f7efc7, #ffffff);

            color: #f5c518;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 34px;

            box-shadow:
            0 8px 20px rgba(0,0,0,0.2);

            margin-bottom: 18px;
        }

    </style>
</head>


<body>

    <div class="main-container">
        <div class="change-pwd-card card shadow p-4">

        <div class="icon-circle">
            🔒
        </div>

        <h4 class="text-center mb-2 card-title">
            Change Password
                </h4>

        <p class="text-center card-subtitle mb-4">
            Update your staff account password securely
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
            <div class="mb-3">
                <label>New Password</label>
                <div class="input-group">
                    <input type="password" name="new_password" id="newPassword" class="form-control" required>
                    <span class="input-group-text toggle-btn" onclick="togglePassword('newPassword', this)">👁</span>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3">
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
                <a href="/GSC-Movie-ticket-Online-Booking-System/staff/profile.php">Back to Profile</a>
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