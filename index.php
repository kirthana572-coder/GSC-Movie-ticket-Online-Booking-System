<?php
session_start();
require_once 'config/db.php';

// 获取热门电影（登录后仪表盘用）
$hotMovies = $conn->query("SELECT id, title, genre FROM movies LIMIT 6");
?>
<!DOCTYPE html>
<html>
<head>
    <title>GSC Movie Ticket Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ===== 保留你原有的首页背景设计 ===== */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: url('https://images.unsplash.com/photo-1524985069026-dd778a71c7b4') no-repeat center center/cover;
            height: 100vh;
            overflow: hidden;
            animation: zoomBg 20s ease-in-out infinite alternate;
        }

        .overlay {
            position: relative;
            height: 100vh;
            width: 100%;
            background: 
                linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.8)),
                radial-gradient(circle at top, rgba(238, 226, 184, 0.8), rgba(255,140,0,0.2), transparent 65%);
        }

        .overlay::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(238, 234, 217, 0.25), rgba(255,140,0,0.15), transparent 70%);
            animation: moveLight 7s ease-in-out infinite;
            z-index: 1;
        }

        .hero-box {
            position: relative;
            z-index: 2;
            color: white;
            text-align: center;
            padding: 50px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            
        }

        .hero-title{
            margin-top: -100px;
        }

        @keyframes zoomBg {
            from { background-size: 100%; }
            to { background-size: 130%; }
        }

        @keyframes moveLight {
            0% { transform: translate(-30%, -30%) scale(1); }
            50% { transform: translate(0%, 0%) scale(1.4); }
            100% { transform: translate(-30%, -30%) scale(1.2); }
        }

        .btn-custom {
            padding: 12px 30px;
            font-size: 18px;
            border-radius: 30px;
            transition: 0.3s;
        }

        .btn-custom:hover {
            transform: scale(1.08);
        }

        .btn-warning {
            background-color: #f5c518;
            border: none;
            border-radius: 30px;
            padding: 12px;
            font-size: 18px;
            transition: 0.3s;
        }

        .btn-warning:hover {
            background-color: #e0b400;
            transform: scale(1.05);
        }

        .overlay {
            pointer-events: none;
        }

        .hero-box {
            pointer-events: auto;
        }

        .hero-logo img{
            width: 600px;

            margin-bottom: 35px;

            margin-top: -300px;

            filter:
            drop-shadow(0 0 30px rgba(245,197,24,0.45));

            animation: floatLogo 3s ease-in-out infinite alternate;
        }

        @keyframes floatLogo{
            from{
                transform: translateY(0px);
            }

            to{
                transform: translateY(-6px);
            }
        }

        /*登录后*/
        .dashboard-body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f2e9, #f7d7a8);
            min-height: 100vh;
            overflow-y: auto;
        }

        .dashboard-container {
            padding-top: 30px;
            padding-bottom: 50px;
        }

        .search-bar {
            max-width: 600px;
            margin: 0 auto 30px auto;
        }

        .search-bar input {
            border-radius: 30px;
            padding: 14px 20px;
            border: 2px solid #f5c518;
            background: white;
            font-size: 16px;
        }

        .movie-card {
            background: rgba(255,255,255,0.95);
            border: none;
            border-radius: 20px;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            height: 100%;
        }

        .movie-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.14);
        }

        .movie-card .card-body {
            padding: 24px;
        }

        .movie-card .card-title {
            font-size: 22px;
            font-weight: 700;
            color: #222;
        }

        .movie-card .card-title a {
            color: #222;
            text-decoration: none;
        }

        .movie-card .card-title a:hover {
            color: #f5c518;
        }

        .quick-actions .btn {
            border-radius: 30px;
            padding: 14px 20px;
            font-weight: 600;
            transition: 0.3s;
        }

        .quick-actions .btn:hover {
            transform: scale(1.03);
        }
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<!--未登录 -->
<?php if (!isset($_SESSION['user_id'])): ?>
<div class="overlay d-flex justify-content-center align-items-center">
    <div class="hero-box text-center">

        <div class="hero-logo">
            <img src="assets/logo.png" alt="GSC Logo">
        </div>

        <h1 class="display-4 fw-bold hero-title">Welcome to GSC Booking</h1>
        <p class="lead mt-3">Book your favorite movies anytime, anywhere.</p>
        <p>Discover latest movies, choose your seats, and enjoy the show!</p>
        <div class="mt-4">
            <a href="register.php" class="btn btn-warning btn-lg btn-custom me-3">Register</a>
            <a href="login.php" class="btn btn-outline-light btn-lg btn-custom">Sign In</a>
        </div>
    </div>
</div>

<?php else: ?>
<!--已登录：Dashboard -->
<div class="dashboard-body">
    <div class="container dashboard-container">

        <!-- 欢迎信息 -->
        <h2 class="text-center mb-2" style="font-weight: 700; color: #222;">
            Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!
        </h2>
        <p class="text-center text-muted mb-4">
            You are logged in as <strong><?= ucfirst($_SESSION['role']) ?></strong>.
        </p>

        <!-- 搜索栏 -->
        <div class="search-bar">
            <form action="customer/movies.php" method="GET">
                <input type="text" name="search" class="form-control" placeholder="Search movies...">
            </form>
        </div>

        <!-- 电影列表标题 -->
        <h4 class="mb-3" style="font-weight: 600; color: #333;">
            🎬 Movies
        </h4>

        <!-- 电影卡片列表 -->
        <div class="row mb-4">
            <?php if ($hotMovies && $hotMovies->num_rows > 0): ?>
                <?php while($movie = $hotMovies->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card movie-card">
                            <div class="card-body text-center">
                                <h5 class="card-title">
                                    <a href="customer/movie_detail.php?movie_id=<?= $movie['id'] ?>">
                                        <?= htmlspecialchars($movie['title']) ?>
                                    </a>
                                </h5>
                                <p class="text-muted"><?= htmlspecialchars($movie['genre']) ?></p>
                                <a href="customer/movies.php?search=<?= urlencode($movie['title']) ?>" class="btn btn-warning btn-sm mt-2">View Showtimes</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No movies available yet.</p>
            <?php endif; ?>
        </div>

        <!-- 快捷操作（对应 Storyboard #4 的 Profile 菜单入口） -->
        <div class="quick-actions text-center">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <a href="customer/profile.php" class="btn btn-outline-dark w-100">👤 View Profile</a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="customer/history.php" class="btn btn-outline-dark w-100">📋 Booking History</a>
                </div>
                <div class="col-md-4 mb-3">
                    <a href="customer/movies.php" class="btn btn-warning w-100">🎟️ Browse All Movies</a>
                </div>
            </div>
        </div>

    </div>
</div>
<?php endif; ?>
<!-- 浮动提示条样式（如果全局 CSS 已包含就不需要重复） -->
<style>
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    pointer-events: none;
}
.toast {
    background: #ff6b6b;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    font-size: 14px;
    font-weight: bold;
    min-width: 250px;
    max-width: 350px;
    word-wrap: break-word;
    pointer-events: auto;
    cursor: pointer;
    transition: all 0.3s ease;
    animation: slideInRight 0.3s ease;
}
.toast:hover {
    transform: scale(1.02);
    background: #ff5252;
}
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
.toast.fade-out {
    opacity: 0;
    transform: translateX(100%);
    transition: opacity 0.3s, transform 0.3s;
}
</style>

<script src="/GSC-Movie-ticket-Online-Booking-System/notification.js"></script>
</script>
</body>
</html>