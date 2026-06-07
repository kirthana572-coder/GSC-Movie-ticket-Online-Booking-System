<?php

require_once '../includes/admin_auth.php';
require_once '../config/db.php';

?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Change Password</title>

    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
    <style>

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;

            background:
            linear-gradient(
                135deg,
                #f8fafc,
                #eef2ff
            );

            min-height:100vh;
        }

        /* MAIN */

        .main{
            margin-left:220px;

            min-height:100vh;

            display:flex;

            justify-content:center;

            align-items:center;

            padding:40px;
        }

        .password-card{
            width:100%;
            max-width:600px;

            background:white;

            border-radius:32px;

            padding:45px;

            box-shadow:
            0 15px 35px rgba(0,0,0,0.12);

            animation:fadeUp 0.3s ease;
        }

        @keyframes fadeUp{

            from{
                opacity:0;
                transform:translateY(25px);
            }

            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        .icon-circle{
            width:130px;
            height:130px;

            border-radius:50%;

            background:
            linear-gradient(
                135deg,
                #f5c518,
                #ffe27a
            );

            display:flex;

            align-items:center;

            justify-content:center;

            margin:auto;

            font-size:50px;
            font-weight:700;

            box-shadow:
            0 10px 25px rgba(245,197,24,0.35);

            margin-bottom:25px;
        }

        .page-title{
            text-align:center;

            font-size:36px;
            font-weight:700;

            color:#111827;

            margin-bottom:10px;
        }

        .page-subtitle{
            text-align:center;

            color:#666;

            margin-bottom:35px;
        }

        .form-label{
            font-weight:600;
            color:#444;
        }

        .form-control{
            border-radius:14px;

            padding:14px;

            border:1px solid #ddd;
        }

        .form-control:focus{
            border-color:#f5c518;

            box-shadow:
            0 0 0 0.2rem rgba(245,197,24,0.25);
        }

        .input-group-text{
            background:white;
            cursor:pointer;
        }

        .btn-save{
            background:#f5c518 !important;

            border:none !important;

            color:#111 !important;

            font-weight:700;

            padding:14px;

            border-radius:14px;

            transition:0.25s;
        }

        .btn-save:hover{
            background:#ffd43b !important;

            transform:scale(1.02);
        }

        .btn-back{
            border-radius:14px;

            padding:14px;

            font-weight:700;

            text-decoration:none;

            border:2px solid #111827;

            color:#111827;

            transition:0.25s;

            display:block;

            text-align:center;
        }

        .btn-back:hover{
            background:#111827;
            color:white;
        }

    </style>

</head>

<body class="admin-page admin-change-password-page">

<?php include '../includes/admin_sidebar.php'; ?>


<div class="main">

    <div class="password-card">

        <!-- ICON -->

        <div class="icon-circle">
            🔒
        </div>


        <!-- TITLE -->

        <div class="page-title">
            Change Password
        </div>

        <div class="page-subtitle">
            Secure your admin account with a new password
        </div>


        <!-- ERROR -->

        <?php if(isset($_SESSION['error'])): ?>

            <div class="alert alert-danger">

                <?= $_SESSION['error']; ?>

            </div>

            <?php unset($_SESSION['error']); ?>

        <?php endif; ?>


        <!-- SUCCESS -->

        <?php if(isset($_SESSION['success'])): ?>

            <div class="alert alert-success">

                <?= $_SESSION['success']; ?>

            </div>

            <?php unset($_SESSION['success']); ?>

        <?php endif; ?>


        <!-- FORM -->

        <form action="../auth/change_password.php" method="POST">

            <!-- NEW PASSWORD -->

            <div class="mb-4">

                <label class="form-label">
                    New Password
                </label>

                <div class="input-group">

                    <input 
                        type="password"
                        name="new_password"
                        id="newPassword"
                        class="form-control"
                        required
                    >

                </div>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="mb-4">

                <label class="form-label">
                    Confirm Password
                </label>

                <div class="input-group">

                    <input 
                        type="password"
                        name="confirm_password"
                        id="confirmPassword"
                        class="form-control"
                        required
                    >

                </div>

            </div>


            <!-- BUTTONS -->

            <div class="d-grid gap-3">

                <button class="btn btn-save">

                    Update Password

                </button>

                <a 
                    href="admin_profile.php"
                    class="btn-back"
                >
                    Back to Profile
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>