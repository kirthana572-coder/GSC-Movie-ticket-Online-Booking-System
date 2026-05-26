<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';


// ADD MOVIE

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['title'] ?? '');
    $genre       = trim($_POST['genre'] ?? '');
    $duration    = trim($_POST['duration'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $posterName = null;


    // VALIDATION

    if (
        empty($title) ||
        empty($genre) ||
        empty($duration) ||
        empty($description)
    ) {

        $error = "Please fill in all fields.";
    }


    // IMAGE UPLOAD

    if (!isset($error) && isset($_FILES['poster'])) {

        if ($_FILES['poster']['error'] === 0) {

            $allowed = [
                'jpg',
                'jpeg',
                'png',
                'webp',
                'avif'
            ];

            $fileName = $_FILES['poster']['name'];

            $tmpName  = $_FILES['poster']['tmp_name'];

            $fileSize = $_FILES['poster']['size'];

            $ext = strtolower(
                pathinfo($fileName, PATHINFO_EXTENSION)
            );


            // VALIDATE IMAGE TYPE

            if (!in_array($ext, $allowed)) {

                $error = "Only JPG, PNG, WEBP, avif allowed.";
            }


            // VALIDATE FILE SIZE

            elseif ($fileSize > 5 * 1024 * 1024) {

                $error = "Image size must be below 5MB.";
            }


            // SAVE IMAGE

            else {

                $posterName =
                    time() . '_' . uniqid() . '.' . $ext;

                $uploadPath =
                    '../../uploads/posters/' . $posterName;

                move_uploaded_file(
                    $tmpName,
                    $uploadPath
                );
            }
        }
    }


    // INSERT MOVIE

    if (!isset($error)) {

        $stmt = $conn->prepare("
            INSERT INTO movies
            (
                title,
                genre,
                duration,
                description,
                poster_image
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");

        $stmt->bind_param(
            "ssiss",
            $title,
            $genre,
            $duration,
            $description,
            $posterName
        );

        $stmt->execute();


        $_SESSION['success'] =
            "Movie added successfully.";

        header("
            Location:
            " . BASE_URL . "/admin/movies/admin_movies.php
        ");

        exit();
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Add Movie</title>

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
            font-size:42px;
            font-weight:700;
            color:#111827;

            text-align:center;

            margin-bottom:40px;
        }

        .form-card{
            background:white;

            border-radius:26px;

            padding:35px;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);

            max-width:850px;
            width:100%;
            margin: 0 auto;   /* 加这个就会水平居中 */
            
        }

        .form-label{
            font-weight:600;
            margin-bottom:8px;
        }

        .form-control,
        textarea{
            border-radius:14px !important;
            padding:12px !important;
        }

        .form-control:focus,
        textarea:focus{

            border-color:#f5c518 !important;

            box-shadow:
            0 0 0 0.2rem rgba(245,197,24,0.25) !important;
        }

        .btn-save{
            background:#f5c518 !important;
            border:none !important;

            color:#111 !important;

            font-weight:700;

            padding:12px 25px;

            border-radius:14px;

            transition:0.25s;
        }

        .btn-save:hover{
            background:#ffd43b !important;
            transform:scale(1.02);
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
        }

    </style>

</head>

<body>

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

    <h1 class="page-title">
        Add Movie
    </h1>


    <div class="form-card">

        <?php if(isset($error)): ?>

            <div class="alert alert-danger">
                <?= $error ?>
            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <!-- TITLE -->

            <div class="mb-4">

                <label class="form-label">
                    Movie Title
                </label>

                <input
                    type="text"
                    name="title"
                    class="form-control"
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
                    placeholder="Action, Adventure"
                    required
                >

            </div>


            <!-- DURATION -->

            <div class="mb-4">

                <label class="form-label">
                    Duration (Minutes)
                </label>

                <input
                    type="number"
                    name="duration"
                    class="form-control"
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
                ></textarea>

            </div>


            <!-- POSTER -->

            <div class="mb-4">

                <label class="form-label">
                    Poster Image
                </label>

                <input
                    type="file"
                    name="poster"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.webp,.avif"
                >

            </div>


            <!-- BUTTONS -->

            <div class="d-flex gap-3">

                <button class="btn btn-save">

                    Add Movie

                </button>

                <a
                    href="<?= BASE_URL ?>/admin/movies/admin_movies.php"
                    class="btn-back"
                >
                    Back
                </a>

            </div>

        </form>

    </div>

</div>

</body>
</html>