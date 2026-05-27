<?php 

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

$from = $_GET['from'] ?? 'showtimes';
$id = $_GET['id'] ?? 0;


/* GET SHOWTIME */

$stmt = $conn->prepare("

    SELECT *
    FROM showtimes
    WHERE id = ?

");

$stmt->bind_param("i", $id);

$stmt->execute();

$showtime = $stmt
    ->get_result()
    ->fetch_assoc();


if(!$showtime){

    die("Showtime not found.");
}


/* GET MOVIES */

$movies = $conn->query("

    SELECT id, title
    FROM movies
    ORDER BY title ASC

");


/* GET BRANCHES */

$branches = $conn->query("

    SELECT id, name
    FROM branches
    ORDER BY name ASC

");


/* UPDATE SHOWTIME */

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $movie_id  = $_POST['movie_id'] ?? '';
    $branch_id = $_POST['branch_id'] ?? '';
    $show_date = $_POST['show_date'] ?? '';
    $show_time = $_POST['show_time'] ?? '';


    $stmt = $conn->prepare("

        UPDATE showtimes

        SET
            movie_id = ?,
            branch_id = ?,
            show_date = ?,
            show_time = ?

        WHERE id = ?

    ");

    $stmt->bind_param(
        "iissi",
        $movie_id,
        $branch_id,
        $show_date,
        $show_time,
        $id
    );

    $stmt->execute();

    if($stmt->affected_rows > 0){

        if($from == 'details'){

            $_SESSION['success'] =
                "Showtime updated successfully.";

            header(
                "Location: " .
                BASE_URL .
                "/admin/showtimes/view_showtime.php?id=$id"
            );

        }else{

            $_SESSION['success'] =
                "Showtime updated successfully.";

            header(
                "Location: " .
                BASE_URL .
                "/admin/showtimes/admin_showtimes.php"
            );
        }

        exit();
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>
        Edit Showtime - GSC
    </title>

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

        .page-title{

            font-size:48px;
            font-weight:800;

            color:#111827;

            letter-spacing:-1px;

            text-align:center;

            margin-bottom:35px;
        }

        .form-card{

            background:white;

            border-radius:28px;

            padding:40px;

            max-width:760px;
            width:100%;
            margin:0 auto;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);
        }

        .form-label{

            font-weight:700;

            color:#374151;

            margin-bottom:12px;

            font-size:15px;
        }

        .form-control,
        .form-select{

            height:58px;

            border-radius:18px;

            border:1px solid #e5e7eb;

            background:#f9fafb;

            padding:0 18px;

            font-size:15px;

            transition:0.25s;

            box-shadow:none;
        }

        .form-control:focus,
        .form-select:focus{

            border-color:#f5c518;

            background:white;

            box-shadow:
            0 0 0 4px rgba(245,197,24,0.15);
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

            transform:translateY(-2px);

            box-shadow:
            0 15px 28px rgba(245,197,24,0.35);
        }

        .btn-back{

            background:#f3f4f6;

            color:#374151;

            font-weight:600;

            padding:15px 30px;

            border-radius:18px;

            text-decoration:none;

            transition:0.25s;

            border:1px solid #e5e7eb;
        }

        .btn-back:hover{

            background:#e5e7eb;

            color:#111827;

            transform:translateY(-2px);
        }

    </style>

</head>

<body>

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

    <div class="page-title">

        Edit Showtime

    </div>


    <div class="form-card">

        <form method="POST">

            <!-- MOVIE -->

            <div class="mb-4">

                <label class="form-label">

                    Movie

                </label>

                <select
                    name="movie_id"
                    class="form-select"
                    required
                >

                    <?php while($m = $movies->fetch_assoc()): ?>

                        <option
                            value="<?= $m['id'] ?>"

                            <?= $showtime['movie_id'] == $m['id'] ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars($m['title']) ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- BRANCH -->

            <div class="mb-4">

                <label class="form-label">

                    Branch

                </label>

                <select
                    name="branch_id"
                    class="form-select"
                    required
                >

                    <?php while($b = $branches->fetch_assoc()): ?>

                        <option
                            value="<?= $b['id'] ?>"

                            <?= $showtime['branch_id'] == $b['id'] ? 'selected' : '' ?>
                        >

                            <?= htmlspecialchars($b['name']) ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- DATE -->

            <div class="mb-4">

                <label class="form-label">

                    Show Date

                </label>

                <input
                    type="date"
                    name="show_date"
                    class="form-control"

                    value="<?= $showtime['show_date'] ?>"

                    required
                >

            </div>


            <!-- TIME -->

            <div class="mb-4">

                <label class="form-label">

                    Show Time

                </label>

                <input
                    type="time"
                    name="show_time"
                    class="form-control"

                    value="<?= substr($showtime['show_time'],0,5) ?>"

                    required
                >

            </div>


            <!-- BUTTONS -->

            <div class="d-flex gap-3 mt-4 justify-content-center">

                <button
                    type="submit"
                    class="btn-save"
                >

                    Update Showtime

                </button>

                <?php if($from == 'details'): ?>

                    <a
                        href="view_showtime.php?id=<?= $showtime['id'] ?>"
                        class="btn-back"
                    >
                        Back
                    </a>

                <?php else: ?>

                    <a
                        href="admin_showtimes.php"
                        class="btn-back"
                    >

                        Back

                    </a>
                <?php endif; ?>

            </div>

        </form>

    </div>

</div>

</body>
</html>