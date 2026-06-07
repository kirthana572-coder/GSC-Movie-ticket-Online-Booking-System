<?php

require_once '../includes/admin_auth.php';
require_once '../config/db.php';


// Get Current Admin Info

$stmt = $conn->prepare("
    SELECT full_name, email
    FROM users
    WHERE id = ?
");

$stmt->bind_param(
    "i",
    $_SESSION['user_id']
);

$stmt->execute();

$user = $stmt
    ->get_result()
    ->fetch_assoc();


// Update Profile

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_name = trim($_POST['full_name'] ?? '');


    if (
        $new_name &&
        $new_name !== $user['full_name']
    ) {

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


        $_SESSION['full_name'] = $new_name;

        $user['full_name'] = $new_name;


        $msg = '
            <div class="alert alert-success">
                Profile updated successfully.
            </div>
        ';
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Profile - GSC</title>

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
                #e2e8f0
            );

            min-height:100vh;
        }

        /* MAIN CONTENT */

        .main{
            margin-left:220px;

            min-height:100vh;

            display:flex;

            justify-content:center;

            align-items:center;

            padding:40px;
        }

        .profile-card{
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

        .profile-avatar{
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

            font-size:52px;
            font-weight:700;

            color:#111;

            box-shadow:
            0 10px 25px rgba(245,197,24,0.35);

            margin-bottom:25px;
        }

        .profile-title{
            text-align:center;

            font-size:38px;
            font-weight:700;

            color:#111827;

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

        .btn-password{
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

        .btn-password:hover{
            background:#111827;
            color:white;
        }

        .btn-save:disabled{

            background:#d1d5db !important;

            color:#6b7280 !important;

            cursor:not-allowed;

            transform:none;

            box-shadow:none;
        }

    </style>

</head>

<body class="admin-page admin-profile-page">

<?php include '../includes/admin_sidebar.php'; ?>

<!-- MAIN -->

<div class="main">

    <div class="profile-card">

        <!-- Avatar -->

        <div class="profile-avatar">

            <?= strtoupper(substr($user['full_name'],0,1)) ?>

        </div>


        <!-- Title -->

        <div class="profile-title">

            Admin Profile

        </div>


        <!-- Success Message -->

        <?= $msg ?? '' ?>


        <!-- Form -->

        <form method="POST">

            <!-- Full Name -->

            <div class="mb-4">

                <label class="form-label">
                    Full Name
                </label>

                <input 
                    type="text"
                    name="full_name"
                    class="form-control"

                    value="<?= htmlspecialchars($user['full_name']) ?>"

                    required
                >

            </div>


            <!-- Email -->

            <div class="mb-4">

                <label class="form-label">
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

            <div class="d-grid gap-3">

                <button
                    type="submit"
                    class="btn btn-save"
                    id="updateBtn"
                    disabled
                >

                    Update Profile

                </button>

                <a 
                    href="change_password.php"
                    class="btn-password"
                >
                    Change Password
                </a>

            </div>

        </form>

    </div>

</div>

<script>

const nameInput =
    document.querySelector(
        'input[name="full_name"]'
    );

const updateBtn =
    document.getElementById(
        'updateBtn'
    );

const originalName =
    nameInput.value;

nameInput.addEventListener(
    'input',
    function(){

        updateBtn.disabled =
            this.value.trim() === originalName;
    }
);

</script>

</body>
</html>