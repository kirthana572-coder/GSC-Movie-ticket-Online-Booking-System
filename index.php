<?php
session_start();
require_once 'config/db.php';

// 查询热门电影（这里简单取所有电影，你可以以后改成热门）
$hotMovies = $conn->query("SELECT id, title, genre, poster_image FROM movies LIMIT 10");
?>
<!DOCTYPE html>
<html>
<head>
    <title>GSC Movie Ticket Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GSC-Movie-ticket-Online-Booking-System/frontend/gsc-style.css">
    <style>
        /* 保留原来的背景图样式，但调整覆盖层 */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #0a0a0a;
            color: #e0e0e0;
        }
        .hero-section {
            background: url('https://images.unsplash.com/photo-1524985069026-dd778a71c7b4') no-repeat center center/cover;
            height: 100vh;
            position: relative;
        }
        .hero-overlay {
            background: rgba(0,0,0,0.8);
            height: 100%;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .btn-custom {
            padding: 12px 30px;
            font-size: 18px;
            border-radius: 30px;
            font-weight: bold;
        }
        .btn-warning {
            background-color: #f5c518;
            border: none;
            color: #000;
        }
        .btn-warning:hover {
            background-color: #d4a800;
        }
        /* 横向滚动电影区 */
        .movie-scroll {
            display: flex;
            overflow-x: auto;
            gap: 15px;
            padding: 20px 0;
            scrollbar-width: thin;
        }
        .movie-scroll .card {
            min-width: 200px;
            background: #1c1c1c;
            border: 1px solid #333;
            color: #e0e0e0;
        }
        .movie-scroll .card-title {
            color: #f5c518;
        }
        .movie-scroll::-webkit-scrollbar {
            height: 6px;
        }
        .movie-scroll::-webkit-scrollbar-thumb {
            background: #f5c518;
            border-radius: 3px;
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!-- 未登录状态：显示欢迎英雄区 -->
<?php if (!isset($_SESSION['user_id'])): ?>
<div class="hero-section">
    <div class="hero-overlay">
        <h1 class="display-3 fw-bold">Welcome to GSC Booking</h1>
        <p class="lead mt-3">Book your favorite movies anytime, anywhere.</p>
        <p>Discover latest movies, choose your seats, and enjoy the show!</p>
        <div class="mt-4">
            <a href="register.php" class="btn btn-warning btn-lg btn-custom me-3">Register</a>
            <a href="login.php" class="btn btn-outline-light btn-lg btn-custom">Sign In</a>
        </div>
    </div>
</div>
<?php else: ?>
<!-- 已登录状态：欢迎信息 + 横向滚动电影 -->
<div class="container mt-4">
    <h1>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
    <p class="lead">You are logged in as <strong><?= ucfirst($_SESSION['role']) ?></strong>.</p>
</div>
<?php endif; ?>

<!-- 横向滚动热门电影（无论是否登录都显示） -->
<div class="container mt-5">
    <h3 class="mb-3"><span style="border-left: 4px solid #f5c518; padding-left: 10px;">Now Showing</span></h3>
    <?php if ($hotMovies->num_rows > 0): ?>
    <div class="movie-scroll">
        <?php while($movie = $hotMovies->fetch_assoc()): ?>
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?></h5>
                <p class="card-text"><small><?= htmlspecialchars($movie['genre']) ?></small></p>
                <a href="customer/movie_detail.php?movie_id=<?= $movie['id'] ?>" class="btn btn-sm btn-outline-warning">View Details</a>
                <a href="customer/movies.php?search=<?= urlencode($movie['title']) ?>" class="btn btn-sm btn-warning mt-1">Book Now</a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <p class="text-muted">No movies available at the moment.</p>
    <?php endif; ?>
</div>

<!-- 登录后的快捷入口（已登录时显示） -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="container mt-4">
    <h3 class="mb-3"><span style="border-left: 4px solid #f5c518; padding-left: 10px;">Quick Actions</span></h3>
    <div class="row">
        <div class="col-md-3 mb-3">
            <a href="customer/movies.php" class="btn btn-outline-light w-100 py-3">🎬 Browse Movies</a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="customer/history.php" class="btn btn-outline-light w-100 py-3">📋 My Bookings</a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="customer/profile.php" class="btn btn-outline-light w-100 py-3">👤 My Profile</a>
        </div>
        <div class="col-md-3 mb-3">
            <a href="customer/movies.php" class="btn btn-warning w-100 py-3">🎟️ Get Tickets</a>
        </div>
    </div>
</div>
<?php endif; ?>

</body>
</html>