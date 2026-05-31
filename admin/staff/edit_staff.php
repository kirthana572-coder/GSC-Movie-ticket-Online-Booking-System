<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

$staff_id = intval($_GET['id'] ?? 0);

$from = $_GET['from'] ?? '';

if(!$staff_id){

    die("Invalid Staff.");
}

if(!$staff_id){

    die("Invalid Staff.");
}


/* GET STAFF */

$stmt = $conn->prepare("

    SELECT
        id,
        full_name,
        email,
        status

    FROM users

    WHERE id = ?
    AND role = 'staff'

");

$stmt->bind_param("i", $staff_id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows === 0){

    die("Staff not found.");
}

$staff = $result->fetch_assoc();


/* UPDATE */

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $password  = trim($_POST['password']);
    $status    = $_POST['status'];

    if(
        empty($full_name) ||
        empty($email)
    ){

        $error = "Full name and email are required.";
    }

    else{

        $check = $conn->prepare("

            SELECT id

            FROM users

            WHERE email = ?
            AND id != ?

        ");

        $check->bind_param(
            "si",
            $email,
            $staff_id
        );

        $check->execute();

        if($check->get_result()->num_rows > 0){

            $error = "Email already exists.";
        }

        else{

            if($password != ''){

                $password_hash =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                $update = $conn->prepare("

                    UPDATE users

                    SET
                        full_name = ?,
                        email = ?,
                        password_hash = ?,
                        status = ?

                    WHERE id = ?

                ");

                $update->bind_param(
                    "ssssi",
                    $full_name,
                    $email,
                    $password_hash,
                    $status,
                    $staff_id
                );

            }else{

                $update = $conn->prepare("

                    UPDATE users

                    SET
                        full_name = ?,
                        email = ?,
                        status = ?

                    WHERE id = ?

                ");

                $update->bind_param(
                    "sssi",
                    $full_name,
                    $email,
                    $status,
                    $staff_id
                );
            }

            $update->execute();

            if($from == 'details'){

                header(
                    "Location: view_staff.php?id=$staff_id&updated=1"
                );

            }else{

                header(
                    "Location: staffs.php?updated=1"
                );
            }

            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Staff - GSC</title>

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

            max-width:700px;
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

            font-size:14px;

            font-weight:700;

            color:#374151;

            margin-bottom:8px;

            display:block;

            letter-spacing:.3px;
        }

        .form-control,
        .form-select{

            height:52px;

            border-radius:14px;

            border:1px solid #dbe2ea;

            padding:12px 16px;

            font-size:15px;

            transition:.25s;
        }

        .form-control:focus,
        .form-select:focus{

            border-color:#f5c518;

            box-shadow:
            0 0 0 4px rgba(245,197,24,.18);
        }

        .form-section{

            margin-bottom:24px;
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

        .btn-save:disabled{

            background:#d1d5db;

            color:#6b7280;

            cursor:not-allowed;

            box-shadow:none;

            transform:none;
        }

    </style>

</head>

<body>

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

    <div class="card-box">

        <div class="page-title">

            Edit Staff

        </div>

        <?php if($error): ?>

            <div class="alert alert-danger">

                <?= $error ?>

            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-section">

                <label class="form-label">

                    Staff Full Name

                </label>

                <input
                    type="text"
                    name="full_name"
                    class="form-control"
                    value="<?= htmlspecialchars($staff['full_name']) ?>"
                    required
                >

            </div>

            <div class="form-section">

                <label class="form-label">

                    Email Address

                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($staff['email']) ?>"
                    required
                >

            </div>

            <div class="form-section">

                <label class="form-label">

                    New Password

                </label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                >

                <small class="text-muted">

                    Leave blank to keep current password.

                </small>

            </div>

            <div class="form-section">

                <label class="form-label">

                    Status

                </label>

                <select
                    name="status"
                    class="form-select"
                >

                    <option
                        value="active"
                        <?= $staff['status']=='active' ? 'selected' : '' ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?= $staff['status']=='inactive' ? 'selected' : '' ?>
                    >
                        Inactive
                    </option>

                </select>

            </div>

            <div class="d-flex gap-3 mt-4 justify-content-center">

                <button
                    class="btn btn-save"
                    id="updateBtn"
                    disabled
                >
                    Update Staff
                </button>


                <?php if($from == 'details'): ?>
                    <a
                        href="view_staff.php?id=<?= $staff['id'] ?>"
                        class="back-btn"
                    >
                        Back
                    </a>
                <?php else: ?>

                    <a
                        href="staffs.php"
                        class="back-btn"
                    >
                        Back
                    </a>

                <?php endif; ?>

            </div>

        </form>

    </div>

</div>

<script>

const form =
    document.querySelector('form');

const updateBtn =
    document.getElementById('updateBtn');

const originalData =
    new FormData(form);

form.addEventListener('input', () => {

    const currentData =
        new FormData(form);

    let changed = false;

    for (let [key, value] of currentData.entries()) {

        if (value !== originalData.get(key)) {

            changed = true;
            break;
        }
    }

    updateBtn.disabled = !changed;

});

</script>

</body>
</html>