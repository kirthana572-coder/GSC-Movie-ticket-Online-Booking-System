<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';


/* AUTOCOMPLETE */

if(isset($_GET['term'])){

    $term = trim($_GET['term']);

    $data = [];

    // MOVIES
    $stmt = $conn->prepare("

        SELECT DISTINCT title

        FROM movies

        WHERE title LIKE CONCAT('%', ?, '%')

        LIMIT 5

    ");

    $stmt->bind_param(
        "s",
        $term
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){

        $data[] = [
            'type' => 'movie',
            'value' => $row['title']
        ];
    }


    // BRANCHES
    $stmt = $conn->prepare("

        SELECT DISTINCT name

        FROM branches

        WHERE name LIKE CONCAT('%', ?, '%')

        LIMIT 5

    ");

    $stmt->bind_param(
        "s",
        $term
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){

        $data[] = [
            'type' => 'branch',
            'value' => $row['name']
        ];
    }


    // SHOWTIME ID
    $stmt = $conn->prepare("

        SELECT id

        FROM showtimes

        WHERE id LIKE CONCAT('%', ?, '%')

        LIMIT 5

    ");

    $stmt->bind_param(
        "s",
        $term
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){

        $data[] = [
            'type' => 'showtime',
            'value' => $row['id']
        ];
    }

    header('Content-Type: application/json');

    echo json_encode($data);

    exit();
}

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

        #suggestions{

            position:absolute;

            top:100%;

            left:0;

            right:0;

            background:white;

            border-radius:12px;

            margin-top:5px;

            overflow:hidden;

            z-index:999;

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);

            display:none;
        }

        .suggestion-item{

            padding:12px 16px;

            cursor:pointer;

            transition:.2s;
        }

        .suggestion-item:hover{

            background:#f3f4f6;
        }

        .suggestion-item.active{

            background:#e0e7ff;
        }

    </style>

</head>

<body>

<?php include '../../includes/admin_sidebar.php'; ?>

<div class="main">

    <div class="top-bar">

        <div class="page-title">

            Seats Management

        </div>

    </div>

    <!-- SEARCH -->

    <div class="search-card position-relative">

    <form method="GET" class="d-flex gap-3">

        <div class="position-relative flex-grow-1">

            <input
                type="text"
                id="searchInput"
                name="search"
                class="form-control search-input"
                placeholder="Search movie, branch or showtime ID..."
                autocomplete="off"
                value="<?= htmlspecialchars($search) ?>"
            >

            <div id="suggestions"></div>

        </div>

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

<script>

const searchInput =
    document.getElementById('searchInput');

const suggestions =
    document.getElementById('suggestions');

let currentIndex = -1;

searchInput.addEventListener('input', () => {

    const keyword =
        searchInput.value.trim();

    currentIndex = -1;

    if(keyword.length < 1){

        suggestions.style.display = 'none';
        return;
    }

    fetch(
        'admin_seats.php?term=' +
        encodeURIComponent(keyword)
    )
    .then(res => res.json())
    .then(data => {

        suggestions.innerHTML = '';

        if(data.length === 0){

            suggestions.style.display = 'none';
            return;
        }

        data.forEach(item => {

            const div =
                document.createElement('div');

            div.className =
                'suggestion-item';

            let icon = '';

            if(item.type === 'movie'){

                icon = '🎬 ';
            }
            else if(item.type === 'branch'){

                icon = '🏢 ';
            }
            else{

                icon = '🆔 ';
            }

            div.textContent =
                icon + item.value;

            div.dataset.value =
                item.value;

            div.onclick = () => {

                searchInput.value =
                    item.value;

                suggestions.style.display =
                    'none';

                searchInput.form.submit();
            };

            suggestions.appendChild(div);
        });

        suggestions.style.display =
            'block';
    });

});

document.addEventListener('click', e => {

    if(
        !searchInput.contains(e.target)
        &&
        !suggestions.contains(e.target)
    ){

        suggestions.style.display =
            'none';
    }

});

searchInput.addEventListener('keydown', e => {

    const items =
        document.querySelectorAll(
            '.suggestion-item'
        );

    if(!items.length) return;

    if(e.key === 'ArrowDown'){

        e.preventDefault();

        currentIndex++;

        if(currentIndex >= items.length){

            currentIndex = 0;
        }

        updateSelection(items);
    }

    if(e.key === 'ArrowUp'){

        e.preventDefault();

        currentIndex--;

        if(currentIndex < 0){

            currentIndex =
                items.length - 1;
        }

        updateSelection(items);
    }

    if(e.key === 'Enter'){

        if(currentIndex >= 0){

            e.preventDefault();

            items[currentIndex].click();
        }
    }

});

function updateSelection(items){

    items.forEach(item =>
        item.classList.remove('active')
    );

    items[currentIndex]
        .classList.add('active');
}

</script>
</body>
</html>