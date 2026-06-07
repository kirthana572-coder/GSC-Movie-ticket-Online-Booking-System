<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';


$id = $_GET['id'] ?? 0;


/* GET SHOWTIME */

$stmt = $conn->prepare("

    SELECT
        s.*,
        m.title,
        m.duration,
        m.poster_image,
        b.name AS branch_name

    FROM showtimes s

    JOIN movies m
    ON s.movie_id = m.id

    JOIN branches b
    ON s.branch_id = b.id

    WHERE s.id = ?

");

$stmt->bind_param("i", $id);

$stmt->execute();

$showtime = $stmt
    ->get_result()
    ->fetch_assoc();


if(!$showtime){

    die("Showtime not found.");
}

$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

?>

<!DOCTYPE html>
<html>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Showtime Details
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

        .page-title{
            font-size:42px;
            font-weight:700;

            color:#111827;

            margin-bottom:35px;
        }

        .details-card{
            background:white;

            border-radius:30px;

            overflow:hidden;

            box-shadow:
            0 15px 35px rgba(0,0,0,0.08);

            display:grid;

            grid-template-columns:
            350px 1fr;
        }

        .poster-side{
            background:#111827;

            display:flex;
            align-items:center;
            justify-content:center;

            padding:30px;
        }

        .poster-img{
            width:100%;

            border-radius:24px;

            box-shadow:
            0 15px 35px rgba(0,0,0,0.4);
        }

        .info-side{
            padding:45px;
        }

        .movie-title{
            font-size:48px;
            font-weight:800;

            color:#111827;

            letter-spacing:-1px;
            margin-bottom:20px;
        }

        .info-badge{
            display:inline-block;

            background:#f3f4f6;

            padding:10px 16px;

            border-radius:14px;

            margin-right:10px;
            margin-bottom:15px;

            font-weight:600;

            color:#374151;
        }

        .description{
            margin-top:25px;

            line-height:1.9;

            color:#555;

            font-size:16px;
        }

        .created{
            margin-top:30px;

            color:#888;

            font-size:14px;
        }

        .button-group{
            display:flex;
            gap:18px;

            margin-top:35px;
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

            text-decoration:none;

            padding:14px 24px;

            border-radius:14px;

            font-weight:700;

            transition:0.25s;

            box-shadow:
            0 10px 20px rgba(245,197,24,0.25);
        }

        .btn-edit:hover{
            transform:translateY(-2px);

            box-shadow:
            0 15px 28px rgba(245,197,24,0.35);
        }

        .btn-back{
            background:#e5e7eb;

            color:#111;

            text-decoration:none;

            padding:14px 24px;

            border-radius:14px;

            font-weight:600;

            transition:0.25s;
        }

        .btn-back:hover{
            background:#d1d5db;
            color:#111827;
            transform:translateY(-2px);
        }

        .toast-msg{
            position:fixed;

            top:30px;
            right:35px;

            z-index:9999;

            padding:16px 24px;

            border-radius:16px;

            font-weight:600;

            color:white;

            backdrop-filter: blur(10px);

            border:1px solid rgba(255,255,255,0.2);

            box-shadow:
            0 10px 25px rgba(0,0,0,0.15);

            animation:
            slideIn 0.35s ease,
            fadeOut 0.4s ease 3s forwards;
        }

        .success-toast{
            background:
            linear-gradient(
                135deg,
                #2ac563,
                #16a34a
            );
        }

        @keyframes slideIn{

            from{
                opacity:0;
                transform:translateX(40px);
            }

            to{
                opacity:1;
                transform:translateX(0);
            }
        }

        @keyframes fadeOut{

            to{
                opacity:0;
                transform:translateY(-10px);
            }
        }

    </style>

</head>

<body class="admin-page admin-view-showtime-page">

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

    <div class="page-title">

        Showtime Details

    </div>


    <div class="details-card">

    <!-- LEFT SIDE -->

    <!-- POSTER -->
        <div class="poster-side">

            <?php if($showtime['poster_image']): ?>

                <img
                    src="<?= BASE_URL ?>/uploads/posters/<?= $showtime['poster_image'] ?>"
                    class="poster-img"
                >

            <?php else: ?>

                <div style="color:white;">
                    No Poster
                </div>

            <?php endif; ?>

        </div>


    <!-- RIGHT SIDE -->

    <div class="info-side">

        <div class="movie-title">

            <?= htmlspecialchars($showtime['title']) ?>

        </div>


        <div class="info-badge">

            🎬 <?= htmlspecialchars($showtime['branch_name']) ?>

        </div>

        <div class="info-badge">

            ⏱ <?= $showtime['duration'] ?> mins

        </div>


        <div class="description">

            <p>
                <b>Show Date:</b>
                <?= date(
                    'd M Y',
                    strtotime($showtime['show_date'])
                ) ?>
            </p>

            <p>
                <b>Show Time:</b>
                <?= date(
                    'h:i A',
                    strtotime($showtime['show_time'])
                ) ?>
            </p>

        </div>


        <div class="created">

            Created At:
            <?= date(
                'd M Y h:i A',
                strtotime($showtime['created_at'])
            ) ?>

        </div>


        <!-- BUTTONS -->

        <div class="button-group">

            <a
                href="edit_showtime.php?id=<?= $showtime['id'] ?>&from=details"
                class="btn-edit"
            >
                Edit Showtime
            </a>

            <a
                href="admin_showtimes.php"
                class="btn-back"
            >
                Back
            </a>

        </div>

    </div>

    <?php if($success): ?>

        <div class="toast-msg success-toast">

            <?= $success ?>

        </div>

    <?php endif; ?>

</div>

<script>

setTimeout(() => {

    const toast = document.querySelector('.toast-msg');

    if(toast){

        toast.remove();

    }

}, 3500);

</script>

</body>
</html>