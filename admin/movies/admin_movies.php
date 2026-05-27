<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';


// SEARCH

$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT *
    FROM movies
";

if ($search != '') {

    $search = $conn->real_escape_string($search);

    $sql .= "
        WHERE title LIKE '%$search%'
    ";
}

$sql .= "
    ORDER BY id DESC
";

$movies = $conn->query($sql);
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

?>

<!DOCTYPE html>
<html>

<head>

    <title>
        Admin Movies - GSC
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

        .top-bar{
            display:flex;
            justify-content:space-between;
            align-items:center;

            margin-bottom:30px;
        }

        .page-title{
            font-size:42px;
            font-weight:700;
            color:#111827;
        }

        .add-btn{
            background:#f5c518;
            color:#111;
            text-decoration:none;

            padding:12px 20px;

            border-radius:16px;

            font-weight:700;

            transition:0.25s;
        }

        .add-btn:hover{
            background:#ffd93d;
            transform:translateY(-2px);
        }

        .search-card{
            margin-bottom:25px;

        }

        .search-input{
            max-width:1000px;
            height:50px;
        }

        .table-card{
            background:white;

            border-radius:24px;

            padding:25px;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);
        }

        .table{
            margin-bottom:0;
        }

        .table thead th{
            border:none;

            padding:18px;

            background:#f8fafc;

            color:#374151;

            font-weight:700;
        }

        .table tbody td{
            padding:18px;
            vertical-align:middle;
        }

        .table tbody tr{
            transition:0.2s;
        }

        .table tbody tr:hover{
            background:#f9fafb;
        }

        .poster{
            width:70px;
            height:90px;

            border-radius:12px;

            object-fit:cover;

            background:#eee;
        }

        .movie-title{
            font-weight:700;
            color:#111827;
        }

        .genre{
            color:#666;
            font-size:14px;
        }

        .action-btn{
            border:none;

            padding:9px 14px;

            border-radius:12px;

            font-size:14px;

            font-weight:600;

            text-decoration:none;

            transition:0.2s;
        }

        .btn-view{
            background:#dbeafe;
            color:#1d4ed8;
        }

        .btn-edit{
            background:#fef3c7;
            color:#92400e;
        }

        .btn-delete{
            background:#fee2e2;
            color:#b91c1c;
        }

        .action-btn:hover{
            transform:scale(1.05);
        }

        .empty-text{
            text-align:center;
            padding:40px;
            color:#777;
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

<body>

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

    <!-- TOP -->

    <div class="top-bar">

        <div class="page-title">
            Movies Management
        </div>

        <a 
            href="add_movie.php"
            class="add-btn"
        >
            + Add Movie
        </a>

    </div>


    <!-- SEARCH -->

    <div class="search-card">

        <form method="GET" class="d-flex gap-3">

            <input 
                type="text"
                name="search"
                class="form-control search-input"

                placeholder="Search movie title..."

                value="<?= htmlspecialchars($search) ?>"
            >

            <button class="btn btn-dark px-4">

                Search

            </button>

            <?php if($search != ''): ?>

                <a 
                    href="admin_movies.php"
                    class="btn btn-secondary d-flex align-items-center justify-content-center"
                >
                    Reset
                </a>

            <?php endif; ?>

        </form>

    </div>


    <!-- TABLE -->

    <div class="table-card">

        <table class="table align-middle">

            <thead>

                <tr>

                    <th>
                        Poster
                    </th>

                    <th>
                        Movie
                    </th>

                    <th>
                        Duration
                    </th>

                    <th>
                        Created
                    </th>

                    <th width="280">
                        Actions
                    </th>

                </tr>

            </thead>

            <tbody>

                <?php if($movies->num_rows > 0): ?>

                    <?php while($m = $movies->fetch_assoc()): ?>

                        <tr>

                            <!-- POSTER -->

                            <td>
                                <?php if($m['poster_image']): ?>

                                    <img
                                        src="<?= BASE_URL ?>/uploads/posters/<?= $m['poster_image'] ?>"

                                        width="70"

                                        style="
                                            border-radius:10px;
                                            cursor:pointer;
                                            transition:0.25s;
                                        "

                                        onmouseover="this.style.transform='scale(1.05)'"
                                        onmouseout="this.style.transform='scale(1)'"

                                        data-bs-toggle="modal"

                                        data-bs-target="#posterModal"

                                        onclick="
                                            showPoster(
                                                '<?= BASE_URL ?>/uploads/posters/<?= $m['poster_image'] ?>'
                                            )
                                        "
                                    >

                                <?php else: ?>

                                    No Poster

                                <?php endif; ?>
                            </td>


                            <!-- MOVIE INFO -->

                            <td>

                                <div class="movie-title">

                                    <?= htmlspecialchars($m['title']) ?>

                                </div>

                                <div class="genre">

                                    <?= htmlspecialchars($m['genre']) ?>

                                </div>

                            </td>


                            <!-- DURATION -->

                            <td>

                                <?= $m['duration'] ?> mins

                            </td>


                            <!-- CREATED -->

                            <td>

                                <?= date('d M Y', strtotime($m['created_at'])) ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <div class="d-flex gap-2">

                                    <a 
                                        href="view_movie.php?id=<?= $m['id'] ?>"
                                        class="action-btn btn-view"
                                    >
                                        View
                                    </a>

                                    <a 
                                        href="edit_movie.php?id=<?= $m['id'] ?>&from=movies"
                                        class="action-btn btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <button
                                        type="button"
                                        class="action-btn btn-delete"

                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteModal"

                                        data-id="<?= $m['id'] ?>"
                                        data-title="<?= htmlspecialchars($m['title']) ?>"
                                    >
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="5">

                            <div class="empty-text">

                                No movies found.

                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <?php if($success == 'added'): ?>

        <div class="toast-msg success-toast">

            Movie added successfully

        </div>

    <?php endif; ?>


    <?php if($success): ?>

        <div class="toast-msg success-toast">

            <?= $success ?>

        </div>

    <?php endif; ?>


    <?php if($success == 'deleted'): ?>

        <div class="toast-msg success-toast">

            Movie deleted successfully

        </div>

    <?php endif; ?>

</div>

<!-- POSTER MODAL -->

<div
    class="modal fade"
    id="posterModal"
    tabindex="-1"
>

    <div
        class="modal-dialog modal-dialog-centered modal-lg"
        style="max-width:460px;"
    >

        <div
            class="modal-content"
            style="
                background:transparent;
                border:none;
            "
        >

            <img
                id="modalPoster"
                src=""
                style="
                    width:100%;
                    border-radius:20px;
                    box-shadow:
                    0 10px 30px rgba(0,0,0,0.5);
                "
            >

        </div>

    </div>

</div>

<!-- DELETE MODAL -->
<div
    class="modal fade"
    id="deleteModal"
    tabindex="-1"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 rounded-4">

            <div class="modal-body p-4 text-center">

                <h3 class="fw-bold mb-3">

                    Delete Movie?

                </h3>

                <p
                    class="text-muted mb-4"
                    id="deleteMovieText"
                >
                </p>

                <div class="d-flex gap-3 justify-content-center">

                    <button
                        class="btn btn-secondary px-4 py-2 rounded-3"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        id="confirmDeleteBtn"
                        class="btn btn-danger px-4 py-2 rounded-3"
                    >
                        Delete
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<script>

function showPoster(src){

    document.getElementById(
        'modalPoster'
    ).src = src;

}

</script>

<script>

setTimeout(() => {

    const toast = document.querySelector('.toast-msg');

    if(toast){

        toast.remove();

    }

}, 3500);

</script>


<script>

document.querySelectorAll('.btn-delete').forEach(btn => {

    btn.addEventListener('click', function(){

        const id = this.dataset.id;
        const title = this.dataset.title;

        document.getElementById(
            'deleteMovieText'
        ).innerHTML =
            'Are you sure you want to delete <b>'
            + title +
            '</b>?';

        document.getElementById(
            'confirmDeleteBtn'
        ).onclick = function(){

            window.location.href =
                'delete_movie.php?id=' + id;
        };

    });

});

</script>

<script>

setTimeout(() => {

    const toast = document.querySelector('.toast-msg');

    if(toast){

        toast.remove();

    }

}, 3500);

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>