<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

$staff_id = intval($_GET['id'] ?? 0);

if(!$staff_id){

    die("Invalid Staff.");
}


/* GET STAFF */

$stmt = $conn->prepare("

    SELECT
        id,
        full_name,
        email,
        role,
        status,
        created_at

    FROM users

    WHERE id = ?
    AND role IN ('staff', 'admin')

");

$stmt->bind_param(
    "i",
    $staff_id
);

$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows === 0){

    die("Staff not found.");
}

$staff = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Staff Details - GSC
    </title>

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

        .info-row{

            padding:16px 0;

            border-bottom:
            1px solid #e5e7eb;
        }

        .label{

            color:#6b7280;

            font-size:14px;

            font-weight:600;
        }

        .value{

            font-size:18px;

            font-weight:600;

            color:#111827;
        }

        .status-active{

            background:#dcfce7;
            color:#166534;

            padding:6px 12px;

            border-radius:999px;

            font-size:14px;

            font-weight:600;
        }

        .status-inactive{

            background:#e5e7eb;
            color:#374151;

            padding:6px 12px;

            border-radius:999px;

            font-size:14px;

            font-weight:600;
        }

        .btn-edit{

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

            text-decoration:none;

            transition:0.25s;

            box-shadow:
            0 10px 20px rgba(245,197,24,0.25);
        }

        .btn-edit:hover{

            text-decoration:none;

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

<body class="admin-page admin-view-staff-page">

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

    <?php if(isset($_GET['updated'])): ?>

    <div
        id="successToast"
        class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-4 show"
        style="z-index:9999;"
    >
        <div class="d-flex">

            <div class="toast-body">

                Updated successfully.

            </div>

        </div>
    </div>

    <?php endif; ?>


    <div class="card-box">

        <div class="page-title">

            <?= ucfirst($staff['role']) ?> Details

        </div>

        <div class="info-row">

            <div class="label">

                Full Name

            </div>

            <div class="value">

                <?= htmlspecialchars($staff['full_name']) ?>

            </div>

        </div>

        <div class="info-row">

            <div class="label">

                Email

            </div>

            <div class="value">

                <?= htmlspecialchars($staff['email']) ?>

            </div>

        </div>

        <div class="info-row">

            <div class="label">

                Role

            </div>

            <div class="value">

                <?= ucfirst($staff['role']) ?>

            </div>

        </div>

        <div class="info-row">

            <div class="label">

                Status

            </div>

            <div class="value">

                <?php if($staff['status'] === 'active'): ?>

                    <span class="status-active">

                        Active

                    </span>

                <?php else: ?>

                    <span class="status-inactive">

                        Inactive

                    </span>

                <?php endif; ?>

            </div>

        </div>

        <div class="info-row">

            <div class="label">

                Created At

            </div>

            <div class="value">

                <?= date('d M Y h:i A', strtotime($staff['created_at'])) ?>

            </div>

        </div>

        <div class="d-flex gap-3 mt-4 justify-content-center">

            <a
                href="edit_staff.php?id=<?= $staff['id'] ?>&from=details"
                class="btn-edit"
            >
                Edit Staff
            </a>

            <a
                href="staffs.php"
                class="back-btn"
            >
                Back
            </a>

        </div>

    </div>

</div>

</body>
</html>