<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

$from = $_GET['from'] ?? 'movies';
$id = $_GET['id'] ?? 0;


/* GET MOVIE */

$stmt = $conn->prepare("
    SELECT *
    FROM movies
    WHERE id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$movie = $stmt
    ->get_result()
    ->fetch_assoc();


if (!$movie) {
    die("Movie not found.");
}


/* UPDATE MOVIE */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['title'] ?? '');
    $genre       = trim($_POST['genre'] ?? '');
    $duration    = trim($_POST['duration'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $poster = $movie['poster_image'];


    /*  UPLOAD NEW POSTER */

    if (!empty($_FILES['poster']['name'])) {

        $allowed = [
            'jpg',
            'jpeg',
            'png',
            'webp',
            'avif'
        ];

        $fileName = $_FILES['poster']['name'];

        $tmpName  = $_FILES['poster']['tmp_name'];

        $ext = strtolower(
            pathinfo($fileName, PATHINFO_EXTENSION)
        );

        if (in_array($ext, $allowed)) {

            $newName =
                time()
                . '_'
                . uniqid()
                . '.'
                . $ext;

            $uploadPath =
                '../../uploads/posters/'
                . $newName;

            move_uploaded_file(
                $tmpName,
                $uploadPath
            );

            $poster = $newName;
        }
    }


    /* UPDATE DATABASE */

    $stmt = $conn->prepare("
        UPDATE movies
        SET
            title = ?,
            genre = ?,
            duration = ?,
            description = ?,
            poster_image = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "sssssi",
        $title,
        $genre,
        $duration,
        $description,
        $poster,
        $id
    );

    $stmt->execute();

    $_SESSION['success'] =
    "Movie updated successfully.";

        if($from == 'details'){

            header(
                "Location:view_movie.php?id=$id&updated=1"
            );

        }else{

            header(
                "Location:admin_movies.php?updated=1"
            );
        }

        exit();
            }


?>

<!DOCTYPE html>
<html>

<head>

    <title>Edit Movie</title>

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
        }

        .main{
            margin-left:260px;
            padding:40px;
        }

        .page-title{
            font-size:48px;
            font-weight:800;

            margin-bottom:35px;

            text-align:center;

            color:#111827;

            letter-spacing:-1px;
        }

        .form-card{
            background:white;

            padding:35px;

            border-radius:24px;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);

            max-width:900px;
            width:100%;
            margin: 0 auto;
        }

        .form-label{
            font-weight:600;
        }

        .form-control{
            border-radius:14px;
            padding:12px;
        }

        .form-control:focus{
            border-color:#f5c518;

            box-shadow:
            0 0 0 0.2rem rgba(245,197,24,0.25);
        }

        .poster-preview{
            width:180px;

            border-radius:18px;

            box-shadow:
            0 10px 20px rgba(0,0,0,0.15);
        }

        .save-btn{
            background:
            linear-gradient(
                135deg,
                #f5c518,
                #ffd43b
            );

            border:none;

            color:#111827;

            padding:12px 25px;

            border-radius:14px;

            font-weight:700;

            transition:0.25s;

            box-shadow:
            0 10px 20px rgba(245,197,24,0.25);
        }

        .save-btn:hover{
            transform:
            translateY(-2px);

            box-shadow:
            0 15px 28px rgba(245,197,24,0.35);
        }

        .btn-back{
            background:#e5e7eb;
            color:#111;
            text-decoration:none;

            padding:12px 22px;

            border-radius:14px;

            font-weight:600;

            transition:0.25s;
        }

        .btn-back:hover{
            background:#d1d5db;
            color:#111827;
            transform:translateY(-2px);
        }

        .save-btn:disabled{

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

    <div class="page-title">

        Edit Movie

    </div>


    <div class="form-card">

        <form method="POST" enctype="multipart/form-data">

            <!-- TITLE -->

            <div class="mb-4">

                <label class="form-label">

                    Movie Title

                </label>

                <input
                    type="text"
                    name="title"
                    class="form-control"

                    value="<?= htmlspecialchars($movie['title']) ?>"

                    required
                >

            </div>


            <!-- GENRE -->

            <div class="mb-4">

                <label class="form-label">

                    Genre

                </label>

                <input
                    type="text"
                    name="genre"
                    class="form-control"

                    value="<?= htmlspecialchars($movie['genre']) ?>"

                    required
                >

            </div>


            <!-- DURATION -->

            <div class="mb-4">

                <label class="form-label">

                    Duration

                </label>

                <input
                    type="number"
                    name="duration"
                    class="form-control"

                    value="<?= htmlspecialchars($movie['duration']) ?>"

                    required
                >

            </div>


            <!-- DESCRIPTION -->

            <div class="mb-4">

                <label class="form-label">

                    Description

                </label>

                <textarea
                    name="description"
                    class="form-control"
                    rows="5"
                    required
                ><?= htmlspecialchars($movie['description']) ?></textarea>

            </div>


            <!-- CURRENT POSTER -->

            <div class="mb-4">

                <label class="form-label">

                    Current Poster

                </label>

                <br>

                <?php if($movie['poster_image']): ?>

                    <img
                        src="<?= BASE_URL ?>/uploads/posters/<?= $movie['poster_image'] ?>"
                        class="poster-preview"
                    >

                <?php else: ?>

                    No Poster

                <?php endif; ?>

            </div>


            <!-- NEW POSTER -->

            <div class="mb-4">

                <label class="form-label">

                    Replace Poster

                </label>

                <input
                    type="file"
                    name="poster"
                    class="form-control"

                    accept=".jpg,.jpeg,.png,.webp,.avif"
                >

            </div>


            <!-- BUTTON -->

            <div class="d-flex gap-3">
                <button
                    type="submit"
                    class="save-btn"
                    id="updateBtn"
                    disabled
                >
                    Save Changes
                </button>

                <?php if($from == 'details'): ?>

                    <a
                        href="view_movie.php?id=<?= $movie['id'] ?>"
                        class="btn-back"
                    >
                        Back
                    </a>

                <?php else: ?>

                    <a
                        href="admin_movies.php"
                        class="btn-back"
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

const initialData =
    new FormData(form);

form.addEventListener('input', checkChanges);
form.addEventListener('change', checkChanges);

function checkChanges(){

    const currentData =
        new FormData(form);

    let changed = false;

    for(const [key,value] of currentData.entries()){

        if(value !== initialData.get(key)){

            changed = true;
            break;
        }
    }

    updateBtn.disabled = !changed;
}

</script>
</body>
</html>