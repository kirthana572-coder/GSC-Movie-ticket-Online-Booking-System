<?php

// Include authentication and database
require_once '../includes/staff_auth.php';

require_once '../config/db.php';


// Get current user information
$stmt = $conn->prepare("
    SELECT full_name, email
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $_SESSION['user_id']);

$stmt->execute();

$user = $stmt
    ->get_result()
    ->fetch_assoc();


// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get new full name
    $new_name = trim($_POST['full_name'] ?? '');


    // Check if name is not empty
    if ($new_name) {

        // Update database
        $stmt = $conn->prepare("
            UPDATE users
            SET full_name = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "si",
            $new_name,
            $_SESSION['user_id']
        );

        $stmt->execute();


        // Update session
        $_SESSION['full_name'] = $new_name;


        // Update current user data
        $user['full_name'] = $new_name;


        // Success message
        $msg = '
            <div class="alert alert-success">
                Updated.
            </div>
        ';
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Staff Profile - GSC</title>

    <!-- Bootstrap CSS -->
    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <style>

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:#f6f7fb;
            min-height:100vh;

        }

        @keyframes fadeBg{

            from{
                opacity:0;
            }

            to{
                opacity:1;
            }
        }

        .main-container{
            min-height:100vh;

            display:flex;
            justify-content:center;
            align-items:center;

            margin-left:280px;

            padding:40px;
            transform:translateY(-20px);
        }

        .profile-card{
            width:100%;
            max-width:620px;

            background:#fff;

            border-radius:22px;

            padding:45px;

            border:none;

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);
        }

        .profile-avatar{

            width:95px;
            height:95px;

            border-radius:50%;

            background:
                linear-gradient(
                    135deg,
                    #f7cf5b,
                    #f5c518
                );

            color:#1f1f1f;

            font-size:34px;

            font-weight:800;

            display:flex;
            align-items:center;
            justify-content:center;

            margin:auto;

            border:none;

            box-shadow:none;
        }

        form{
            margin-top:10px;
        }

        .form-control{
            border-radius:12px;

            padding:12px 14px;

            border:1px solid #e9ecef;

            box-shadow:none;
        }

        .form-control:focus{
            border-color:#f5c518;

            box-shadow:
            0 0 0 .15rem rgba(245,197,24,.25);
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

        .btn-update,
        .btn-password{

            width:100%;
            height:52px;

            display:flex;
            align-items:center;
            justify-content:center;

            border-radius:12px;

            font-weight:700;

            text-decoration:none;
        }

        .btn-update{

            background:#f7cf5b;
            color:#1f1f1f;
            border:none;
        }

        .btn-update:hover{

            background:#f5c518;
            transform:translateY(-2px);
        }

        .btn-password{

            background:#2f2f2f;
            color:#fff;

            margin-top:12px;
        }

        .button-group{

            margin-top:60px;
        }

        .btn-password:hover{

            background:#1f1f1f;
            color:#fff;

            transform:translateY(-2px);
        }

        label{
            font-size:13px;
            font-weight:600;
            color:#495057;
            margin-bottom:8px;
        }

        .alert{
            border:none;
            border-radius:14px;
            font-weight:600;
        }

        .alert-success{
            background:#e7f8ee;
            color:#1e7e34;
            text-align:center;
        }

        .btn-update:disabled{
            background:#e9ecef;
            color:#adb5bd;
            cursor:not-allowed;
        }

    </style>

</head>

<body>

<?php include '../includes/staff_sidebar.php'; ?>

    <!-- Main content -->
    <div class="main-container">

        <div class="card profile-card">

            <!-- Avatar -->
            <div class="text-center mb-3">

                <div class="profile-avatar mx-auto mb-3">

                    <?= strtoupper(substr($user['full_name'], 0, 1)) ?>

                </div>

            </div>


            <!-- Title -->
            <h1 class="page-title">
                Staff Profile
            </h1>

            <p class="page-subtitle">
                Manage your account information and security settings
            </p>


            <!-- Success message -->
            <?= $msg ?? '' ?>


            <!-- Profile form -->
            <form method="POST">

                <!-- Full name -->
                <div class="mb-3">

                    <label>
                        Full Name
                    </label>

                    <input 
                        id="fullName"
                        type="text"
                        name="full_name"
                        class="form-control"

                        value="<?= htmlspecialchars($user['full_name']) ?>"

                        required
                    >

                </div>


                <!-- Email -->
                <div class="mb-5">

                    <label>
                        Email
                    </label>

                    <input 
                        type="email"
                        class="form-control"

                        value="<?= htmlspecialchars($user['email']) ?>"

                        disabled
                    >

                </div>


                <!-- Buttons -->
                <div class="button-group">

                    <button
                        class="btn-update"
                        id="updateBtn"
                        disabled
                    >
                        Update Profile
                    </button>

                    <a 
                        href="<?= BASE_URL ?>/staff/change_password.php"
                        class="btn-password"
                    >
                        Change Password
                    </a>

                </div>

            </form>

        </div>

    </div>

    <script>

    const originalName =
        document.getElementById('fullName').value;

    const fullName =
        document.getElementById('fullName');

    const updateBtn =
        document.getElementById('updateBtn');

    function validateProfile(){

        updateBtn.disabled =
            fullName.value.trim() === '' ||
            fullName.value.trim() === originalName;
    }

    fullName.addEventListener(
        'input',
        validateProfile
    );

    validateProfile();

    </script>


    <!-- Notification JS -->
    <script src="<?= BASE_URL ?>/notification.js"></script>

</body>
</html>