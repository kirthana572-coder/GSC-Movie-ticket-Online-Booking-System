<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $status = $_POST['status'] ?? 'active';

    if (
        empty($full_name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "All fields are required.";
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Invalid email address.";
    }

    elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";
    }

    elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";
    }

    else {

        $check = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
        ");

        $check->bind_param("s", $email);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {

            $error = "Email already exists.";
        }

        else {

            $password_hash =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

            $stmt = $conn->prepare("

                INSERT INTO users
                (
                    full_name,
                    email,
                    password_hash,
                    role,
                    status
                )

                VALUES
                (
                    ?,
                    ?,
                    ?,
                    'staff',
                    ?
                )

            ");

            $stmt->bind_param(
                "ssss",
                $full_name,
                $email,
                $password_hash,
                $status
            );

            if ($stmt->execute()) {

                header("Location: staffs.php?added=1");
                exit();
            }

            else {

                $error = "Failed to add staff.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Add Staff - GSC</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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

        .main{

            margin-left:260px;
            padding:40px;
        }


        .card-box{

            background:
            linear-gradient(
                180deg,
                #ffffff,
                #fcfcfc
            );

            border-radius:24px;

            padding:35px;

            max-width:740px;
            margin:0 auto;

            border:1px solid #eef2f7;

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);
        }

        .page-title{

            font-size:48px;
            font-weight:800;

            color:#111827;

            letter-spacing:-1px;

            text-align:center;

            margin-bottom:35px;
        }

        .form-label{

            font-weight:500;
        }

        .btn-save{

            background:
            linear-gradient(
                135deg,
                #f5c518,
                #ffd43b
            );

            border:none;

            color:#111827;

            font-weight:700;

            padding:15px 34px;

            border-radius:18px;

            transition:0.25s;

            box-shadow:
            0 10px 20px rgba(245,197,24,0.25);
        }

        .btn-save:hover{

            transform:
            translateY(-2px);

            box-shadow:
            0 15px 28px rgba(245,197,24,0.35);
        }

        .back-btn{

            background:#f3f4f6;

            color:#374151;

            font-weight:600;

            padding:15px 30px;

            border-radius:18px;

            text-decoration:none;

            transition:0.25s;

            border:1px solid #e5e7eb;
        }

        .back-btn:hover{

            background:#e5e7eb;

            color:#111827;

            transform:translateY(-2px);
        }

    </style>

</head>

<body>

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">


    <div class="card-box">

        <div class="page-title">

            Add Staff

        </div>

        <?php if($error): ?>

            <div class="alert alert-danger">

                <?= htmlspecialchars($error) ?>

            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">

                <label class="form-label">

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

                <label class="form-label">

                    Email

                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    required
                >

            </div>

            <div class="mb-3">

                <label class="form-label">

                    Password

                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    required
                >

            </div>

            <div class="mb-3">

                <label class="form-label">

                    Confirm Password

                </label>

                <input
                    type="password"
                    name="confirm_password"
                    class="form-control"
                    required
                >

            </div>

            <div class="mb-4">

                <label class="form-label">

                    Status

                </label>

                <select
                    name="status"
                    class="form-select"
                >

                    <option value="active">

                        Active

                    </option>

                    <option value="inactive">

                        Inactive

                    </option>

                </select>

            </div>

            <div class="d-flex gap-3 mt-4 justify-content-center">

                <button class="btn btn-save">

                    Add

                </button>

                <a
                    href="<?= BASE_URL ?>/admin/staff/staffs.php"
                    class="back-btn"
                >

                    Back

                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>