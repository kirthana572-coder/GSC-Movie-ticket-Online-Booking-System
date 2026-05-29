<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';


/* SEARCH */

$search = trim($_GET['search'] ?? '');


$sql = "

    SELECT
        s.id AS showtime_id,
        s.show_date,
        s.show_time,

        m.title,
        b.name AS branch_name,

        COUNT(se.id) AS total_seats,

        SUM(
            CASE
                WHEN se.status = 'available'
                THEN 1
                ELSE 0
            END
        ) AS available_count,

        SUM(
            CASE
                WHEN se.status = 'pending'
                THEN 1
                ELSE 0
            END
        ) AS pending_count,

        SUM(
            CASE
                WHEN se.status = 'booked'
                THEN 1
                ELSE 0
            END
        ) AS booked_count

    FROM showtimes s

    JOIN movies m
    ON s.movie_id = m.id

    JOIN branches b
    ON s.branch_id = b.id

    LEFT JOIN seats se
    ON se.showtime_id = s.id

";


if($search != ''){

    $search = $conn->real_escape_string($search);

    $sql .= "

        WHERE
            m.title LIKE '%$search%'
            OR b.name LIKE '%$search%'
            OR s.id LIKE '%$search%'

    ";
}


$sql .= "

    GROUP BY s.id

    ORDER BY s.id DESC

";


$showtimes = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

    <title>
        Admin Seats - GSC
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

            font-size:42px;

            font-weight:800;

            color:#111827;

            margin-bottom:30px;
        }

        .search-card{

            background:white;

            border-radius:24px;

            padding:25px;

            margin-bottom:25px;

            box-shadow:
            0 10px 25px rgba(0,0,0,0.08);
        }

        .search-input{

            height:54px;

            border-radius:16px;
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

            background:#f8fafc;

            border:none;

            padding:18px;

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

            font-size:17px;
        }

        .branch{

            color:#6b7280;

            font-size:14px;
        }

        .seat-badge{

            display:inline-block;

            padding:8px 14px;

            border-radius:12px;

            font-size:13px;

            font-weight:700;

            margin-right:8px;
        }

        .available{

            background:#dcfce7;

            color:#166534;
        }

        .pending{

            background:#fef3c7;

            color:#92400e;
        }

        .booked{

            background:#fee2e2;

            color:#991b1b;
        }

        .manage-btn{

            background:
            linear-gradient(
                135deg,
                #ffd640,
                #ffdd64
            );

            color:#111827;

            text-decoration:none;

            padding:12px 18px;

            border-radius:14px;

            font-weight:700;

            transition:0.25s;

            display:inline-block;
        }

        .manage-btn:hover{

            transform:translateY(-2px);

            color:#111827;

            box-shadow:
            0 10px 20px rgba(245,197,24,0.3);
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

    <div class="page-title">

        Seats Management

    </div>


    <!-- SEARCH -->

    <div class="search-card">

        <form method="GET" class="d-flex gap-3">

            <input
                type="text"
                name="search"
                class="form-control search-input"

                placeholder="Search movie, branch or showtime ID..."

                value="<?= htmlspecialchars($search) ?>"
            >

            <button class="btn btn-dark px-4">

                Search

            </button>

            <?php if($search != ''): ?>

                <a
                    href="admin_seats.php"
                    class="btn btn-secondary d-flex align-items-center"
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

                        Showtime

                    </th>

                    <th>

                        Date & Time

                    </th>

                    <th>

                        Seat Summary

                    </th>

                    <th width="220">

                        Action

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

                                <div class="branch">

                                    <?= htmlspecialchars($s['branch_name']) ?>

                                    • Showtime ID:
                                    <?= $s['showtime_id'] ?>

                                </div>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?= date(
                                    'd M Y',
                                    strtotime($s['show_date'])
                                ) ?>

                                <br>

                                <span class="text-muted">

                                    <?= date(
                                        'h:i A',
                                        strtotime($s['show_time'])
                                    ) ?>

                                </span>

                            </td>


                            <!-- SEATS -->

                            <td>

                                <span class="seat-badge available">

                                    Available:
                                    <?= $s['available_count'] ?>

                                </span>

                                <span class="seat-badge pending">

                                    Pending:
                                    <?= $s['pending_count'] ?>

                                </span>

                                <span class="seat-badge booked">

                                    Booked:
                                    <?= $s['booked_count'] ?>

                                </span>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <a
                                    href="manage_seats.php?showtime_id=<?= $s['showtime_id'] ?>"
                                    class="manage-btn"
                                >

                                    Manage Seats

                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="4">

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