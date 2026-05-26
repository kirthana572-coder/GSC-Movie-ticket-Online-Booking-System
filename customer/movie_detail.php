<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$movie_id = $_GET['movie_id'] ?? 0;
if (!$movie_id) die("Movie not found.");

// 获取电影详情
$movie = $conn->query("SELECT * FROM movies WHERE id = " . intval($movie_id))->fetch_assoc();
if (!$movie) die("Movie not found.");

// 获取该电影的未来场次
$showtimes = $conn->query("
    SELECT s.id AS showtime_id, s.show_date, s.show_time,
           b.name AS branch_name, b.id AS branch_id
    FROM showtimes s
    JOIN branches b ON s.branch_id = b.id
    WHERE s.movie_id = " . intval($movie_id) . "
        AND s.show_date >= CURDATE()
    ORDER BY s.show_date, s.show_time
");
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($movie['title']) ?> - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/gsc-style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?= htmlspecialchars($movie['title']) ?></h3>
                    <p><strong>Genre:</strong> <?= htmlspecialchars($movie['genre']) ?></p>
                    <p><strong>Duration:</strong> <?= $movie['duration'] ?> mins</p>
                    <p><?= nl2br(htmlspecialchars($movie['description'])) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <h4>Upcoming Showtimes</h4>
            <?php if ($showtimes->num_rows > 0): ?>
                <table class="table table-bordered">
                    <thead><tr><th>Date</th><th>Time</th><th>Branch</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php while($s = $showtimes->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($s['show_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($s['show_time'])) ?></td>
                            <td><?= htmlspecialchars($s['branch_name']) ?></td>
                            <td><a href="<?= BASE_URL ?>/customer/select_seat.php?showtime_id=<?= $s['showtime_id'] ?>" class="btn btn-sm btn-warning">Book Now</a></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>


            <?php else: ?>
                <div class="alert alert-info">No upcoming showtimes for this movie.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="<?= BASE_URL ?>/notification.js"></script>
</body>
</html>