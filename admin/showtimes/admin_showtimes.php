<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';


/* SEARCH */

$search = trim($_GET['search'] ?? '');


$sql = "

    SELECT
        s.*,
        m.title,
        b.name AS branch_name

    FROM showtimes s

    JOIN movies m
    ON s.movie_id = m.id

    JOIN branches b
    ON s.branch_id = b.id

";


if($search != ''){

    $search = $conn->real_escape_string($search);

    $sql .= "

        WHERE
            m.title LIKE '%$search%'
            OR
            b.name LIKE '%$search%'

    ";
}


$sql .= "

    ORDER BY
    s.show_date DESC,
    s.show_time DESC

";


$showtimes = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

    <title>
        Admin Showtimes - GSC
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

        .movie-title{
            font-weight:700;
            color:#111827;
        }

        .branch{
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

    </style>

</head>

<body>

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

    <!-- TOP -->

    <div class="top-bar">

        <div class="page-title">
            Showtimes Management
        </div>

        <a
            href="add_showtime.php"
            class="add-btn"
        >
            + Add Showtime
        </a>

    </div>


    <!-- SEARCH -->

    <div class="search-card">

        <form method="GET" class="d-flex gap-3">

            <input
                type="text"
                name="search"
                class="form-control search-input"

                placeholder="Search movie or branch..."

                value="<?= htmlspecialchars($search) ?>"
            >

            <button class="btn btn-dark px-4">

                Search

            </button>

            <?php if($search != ''): ?>

                <a
                    href="admin_showtimes.php"
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
                        Movie
                    </th>

                    <th>
                        Branch
                    </th>

                    <th>
                        Date
                    </th>

                    <th>
                        Time
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

                <?php if($showtimes->num_rows > 0): ?>

                    <?php while($s = $showtimes->fetch_assoc()): ?>

                        <tr>

                            <!-- MOVIE -->

                            <td>

                                <div class="movie-title">

                                    <?= htmlspecialchars($s['title']) ?>

                                </div>

                            </td>


                            <!-- BRANCH -->

                            <td>

                                <div class="branch">

                                    <?= htmlspecialchars($s['branch_name']) ?>

                                </div>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?= date('d M Y', strtotime($s['show_date'])) ?>

                            </td>


                            <!-- TIME -->

                            <td>

                                <?= date('h:i A', strtotime($s['show_time'])) ?>

                            </td>


                            <!-- CREATED -->

                            <td>

                                <?= date('d M Y', strtotime($s['created_at'])) ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <div class="d-flex gap-2">

                                    <a
                                        href="view_showtime.php?id=<?= $s['id'] ?>"
                                        class="action-btn btn-view"
                                    >
                                        View
                                    </a>

                                    <a
                                        href="edit_showtime.php?id=<?= $s['id'] ?>"
                                        class="action-btn btn-edit"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="delete_showtime.php?id=<?= $s['id'] ?>"
                                        class="action-btn btn-delete"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="6">

                            <div class="empty-text">

                                No showtimes found.

                            </div>

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>