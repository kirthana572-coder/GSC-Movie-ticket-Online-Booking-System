<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// 获取筛选参数
$branch_id = $_GET['branch_id'] ?? '';
$date = $_GET['date'] ?? '';

if (empty($date)) {

    $firstDate = $conn->query("
        SELECT MIN(show_date) AS first_date
        FROM showtimes
        WHERE show_date >= CURDATE()
    ");

    $date = $firstDate->fetch_assoc()['first_date'] ?? date('Y-m-d');
}

$search = $_GET['search'] ?? '';

// 获取所有分店
$branches = $conn->query("SELECT * FROM branches");

$availableDates = $conn->query("
    SELECT DISTINCT show_date
    FROM showtimes
    WHERE show_date >= CURDATE()
    ORDER BY show_date ASC
");

// 查询电影及场次
$sql = "
SELECT m.id AS movie_id, m.poster_image, m.title, m.genre, m.duration, m.description,
       s.id AS showtime_id, s.show_date, s.show_time,
       b.name AS branch_name, b.id AS branch_id
FROM showtimes s
JOIN movies m ON s.movie_id = m.id
JOIN branches b ON s.branch_id = b.id
WHERE 1=1
";

// 搜索条件
if (!empty($branch_id)) {
    $sql .= " AND b.id = " . intval($branch_id);
}
$sql .= " AND s.show_date = '" . $conn->real_escape_string($date) . "'";
if (!empty($search)) {
    $sql .= " AND m.title LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$sql .= " ORDER BY s.show_time ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Browse Movies - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">

    <style>
        /* ===== Page Background ===== */
    body{

        margin:0;
        
        background:
        linear-gradient(
            180deg,
            #faf8f2,
            #f3ede0
        );

        min-height:100vh;

        font-family:'Segoe UI',sans-serif;
    }

     body::before{
        content: "";

        position: fixed;

        top: 0;
        left: 0;

        width: 100%;
        height: 100%;

        backdrop-filter: blur(1px);

        z-index: -1;
    }   

    /* ===== Page Container ===== */
    .movies-container{
        padding-top: 30px;
        padding-bottom: 50px;
    }

    /* ===== Title ===== */
    .page-title{
        font-size: 38px;
        font-weight: 700;

        color: #222;

        margin-bottom: 25px;
    }

    /* ===== Filter Box ===== */
    .filter-box{

        background:white;

        border-radius:24px;

        padding:25px;

        box-shadow:
        0 10px 30px rgba(0,0,0,.08);

        border:none;
    }

    /* ===== Inputs ===== */
    .form-control,
    .form-select{
        background:#fff !important;
        color: #000 !important;

        border-radius: 12px !important;

        padding: 12px !important;

        border: 1px solid #ddd !important;
    }

    .form-control:focus,
    .form-select:focus{
        border-color: #f5c518 !important;

        box-shadow:
        0 0 0 0.2rem rgba(245,197,24,0.25) !important;
    }

    /* ===== Movie Card ===== */
    .movie-card{
        border: none;

        border-radius: 22px;

        overflow: hidden;

        background: rgba(246, 246, 246, 0.96);

        transition: 0.3s;

        box-shadow:
        0 8px 24px rgba(0,0,0,0.08);

        height: 100%;
    }

    .movie-card:hover{
        transform: translateY(-6px);

        box-shadow:
        0 12px 28px rgba(0,0,0,0.14);
    }

    /* ===== Card Body ===== */
    .movie-card .card-body{
        padding: 24px;
    }

    /* ===== Movie Title ===== */
    .movie-title{
        font-size: 24px;
        font-weight: 700;

        color: #222;

        margin-bottom: 12px;
    }

    .movie-title a{
        text-decoration: none;
        color: #222;
    }

    .movie-title a:hover{
        color: #f5c518;
    }

    /* ===== Movie Text ===== */
    .movie-info{
        color: #666;

        line-height: 1.8;
    }

    /* ===== Buttons ===== */
    .btn-warning{
        background-color: #ecc843 !important;
        border: none;

        border-radius: 30px;

        padding: 10px 22px;

        font-weight: 600;

        transition: 0.3s;
    }

    .btn-warning:hover{
        background-color: #e0b400;

        transform: scale(1.03);
    }

    /* ===== Alert ===== */
    .alert{
        border-radius: 16px;
    } 

    .page-header{

        text-align:center;

        margin-bottom:35px;
    }

    .page-header h1{

        font-size:48px;

        font-weight:900;

        color:#222;
    }

    .page-header p{

        color:#666;

        font-size:17px;
    }

    .movie-poster{

        height:400px;

        width:100%;

        object-fit:cover;
    }

    .empty-state{

        background:white;

        padding:60px;

        border-radius:24px;

        text-align:center;

        box-shadow:
        0 10px 25px rgba(0,0,0,.08);
    }

    .empty-icon{

        font-size:70px;

        margin-bottom:15px;
    }

    </style>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">

</head>
<body class="browse-movies-page">

<?php include '../includes/navbar.php'; ?>

<div class="container movies-container">

    <div class="page-header">

        <h1>

            Now Showing

        </h1>

        <p>

            Explore available movies and choose your preferred showtime.

        </p>

    </div>

    <br>

    <!-- 筛选表单 -->
    <form method="GET" class="row g-2 mb-4 filter-box">
        <div class="col-md-3">
            <select name="branch_id" class="form-select">
                <option value="">All Branches</option>
                <?php while($b = $branches->fetch_assoc()): ?>
                    <option value="<?= $b['id'] ?>" <?= ($branch_id == $b['id']) ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">

            <select name="date" class="form-select">

                <?php while($d = $availableDates->fetch_assoc()): ?>

                    <option
                        value="<?= $d['show_date'] ?>"
                        <?= $date == $d['show_date'] ? 'selected' : '' ?>
                    >

                        <?= date('d M Y (D)', strtotime($d['show_date'])) ?>

                    </option>

                <?php endwhile; ?>

            </select>

        </div>
        <div class="col-md-2">
            <input type="text" name="search" class="form-control" placeholder="Search movie..."
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2"><button type="submit" class="btn btn-warning w-100">Filter</button></div>
    </form>

    <br><br>
    <!-- 电影卡片列表 -->
    <div class="row movies-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4 movie-col">
                    <div class="card movie-card">

                        <img
                            src="<?= BASE_URL ?>/uploads/posters/<?= $row['poster_image'] ?>"
                            class="movie-poster"
                            alt="<?= htmlspecialchars($row['title']) ?>">

                        <div class="card-body">
                            <h5 class="movie-title"><a href="<?= BASE_URL ?>/customer/movie_detail.php?movie_id=<?= $row['movie_id'] ?>"><?= htmlspecialchars($row['title']) ?></a></h5>
                            <p class="movie-info"><strong><?= htmlspecialchars($row['genre']) ?></strong><br><?= $row['duration'] ?> mins</p>
                            <p><small><?= htmlspecialchars($row['branch_name']) ?></small></p>
                            <p><strong>Date:</strong> <?= date('d M Y', strtotime($row['show_date'])) ?></p>
                            <p><strong>Time:</strong> <?= date('h:i A', strtotime($row['show_time'])) ?></p>
                            <a href="<?= BASE_URL ?>/customer/select_seat.php?showtime_id=<?= $row['showtime_id'] ?>" class="btn btn-warning w-100 mt-3">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>

            <div class="col-12">
                <div class="empty-state">

                    <div class="empty-icon">
                        🍿
                    </div>

                    <h3>
                        No Movies Available
                    </h3>

                    <p>
                        There are currently no movies scheduled for the selected filters.
                    </p>

                </div>
            </div>

        <?php endif; ?>
    </div>
</div>
<script src="<?= BASE_URL ?>/notification.js"></script>
</body>
</html>