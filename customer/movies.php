<?php
require_once '../includes/auth_check.php'; // 权限验证
require_once '../config/db.php';

// 获取分店、日期筛选参数
$branch_id = $_GET['branch_id'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d', strtotime('+1 day'));

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
$sql .= " ORDER BY s.show_time ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Browse Movies - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- 简单导航栏 -->
<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="/GSC-Movie-ticket-Online-Booking-System/index.php">GSC Cinema</a>
    <div class="text-white">
        <?= htmlspecialchars($_SESSION['full_name']) ?> 
        <a href="/GSC-Movie-ticket-Online-Booking-System/auth/logout.php" class="btn btn-sm btn-outline-light ms-3">Logout</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h2>Now Showing</h2>
    <!-- 筛选表单 -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <select name="branch_id" class="form-select">
                <option value="">All Branches</option>
                <?php while($b = $branches->fetch_assoc()): ?>
                    <option value="<?= $b['id'] ?>" <?= ($branch_id == $b['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($date) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-warning">Filter</button>
        </div>
    </form>

    <!-- 电影卡片列表 -->
    <div class="row">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                            <p class="card-text">
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
                <div class="alert alert-info">No showtimes found for selected date and branch.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>