<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

// 获取筛选参数
$branch_id = $_GET['branch_id'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d', strtotime('+1 day'));
$search = $_GET['search'] ?? '';

// 获取所有分店
$branches = $conn->query("SELECT * FROM branches");

// 查询电影及场次
$sql = "
SELECT m.id AS movie_id, m.title, m.genre, m.duration, m.description,
       s.id AS showtime_id, s.show_date, s.show_time,
       b.name AS branch_name, b.id AS branch_id
FROM showtimes s
JOIN movies m ON s.movie_id = m.id
JOIN branches b ON s.branch_id = b.id
WHERE 1=1
";
if (!empty($branch_id)) {
    $sql .= " AND b.id = " . intval($branch_id);
}
$sql .= " AND s.show_date = '" . $conn->real_escape_string($date) . "'";

// 搜索条件
if (!empty($search)) {
    $sql .= " AND m.title LIKE '%" . $conn->real_escape_string($search) . "%'";
}

$sql .= " ORDER BY s.show_time ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Browse Movies - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    /* ===== Page Background ===== */
    body{
        margin: 0;
        font-family: 'Segoe UI', sans-serif;

        background:
        linear-gradient(
      rgba(244,237,217,0.80),
      rgba(249,213,159,0.8)
        ),

        url('https://images.unsplash.com/photo-1513106580091-1d82408b8cd6?q=80&w=1974&auto=format&fit=crop')
        center center / cover no-repeat fixed;        
        min-height: 100vh;
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
        background: rgba(250, 249, 239, 0.30);

        padding: 20px;

        border: 1px solid rgba(255,255,255,0.35);

        backdrop-filter: blur(10px);

        box-shadow:
        0 6px 18px rgba(0,0,0,0.05);

        margin-bottom: 30px;
    }

    /* ===== Inputs ===== */
    .form-control,
    .form-select{
        background: rgb(251, 253, 213)f !important;
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

<body>

<?php include '../includes/navbar.php'; ?>

<div class="container movies-container">
    
    <h2 class="page-title">Now Showing</h2>

    <!-- 筛选表单 -->
    <form method="GET" class="row g-3 mb-4 filter-box">
        <div class="col-md-3">
            <select name="branch_id" class="form-select">
                <option value="">All Branches</option>
                <?php while($b = $branches->fetch_assoc()): ?>
                    <option value="<?= $b['id'] ?>" <?= ($branch_id == $b['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>">
        </div>
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Search movie..."
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-warning w-100">Filter</button>
        </div>
    </form>

    <!-- 电影卡片列表 -->
    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card movie-card">
                        <div class="card-body">
                            <h5 class="movie-title">
                                <a href="movie_detail.php?movie_id=<?= $row['movie_id'] ?>" class="text-decoration-none text-dark">
                                    <?= htmlspecialchars($row['title']) ?>
                                </a>
                            </h5>
                            <p class="movie-info">
                                <strong><?= htmlspecialchars($row['genre']) ?></strong><br>
                                <?= htmlspecialchars($row['duration']) ?> mins
                            </p>
                            <p><small><?= htmlspecialchars($row['branch_name']) ?></small></p>
                            <p><strong>Date:</strong> <?= htmlspecialchars(date('d M Y', strtotime($row['show_date']))) ?></p>
                            <p><strong>Time:</strong> <?= htmlspecialchars(date('h:i A', strtotime($row['show_time']))) ?></p>
                            <a href="select_seat.php?showtime_id=<?= $row['showtime_id'] ?>" class="btn btn-warning">Book Now</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">No showtimes found for selected filters.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
<script src="/GSC-Movie-ticket-Online-Booking-System/notification.js"></script>
</body>
</html>