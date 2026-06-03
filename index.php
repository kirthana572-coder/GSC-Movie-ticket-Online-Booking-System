<?php 
require_once 'config/db.php';

// Now Showing（有showtime）
$nowShowing = $conn->query("
    SELECT DISTINCT
        m.id,
        m.title,
        m.genre,
        m.poster_image
    FROM movies m
    INNER JOIN showtimes s
        ON m.id = s.movie_id
    ORDER BY m.id DESC
    LIMIT 6
");

// Upcoming（没有showtime）
$upcomingMovies = $conn->query("
    SELECT
        m.id,
        m.title,
        m.genre,
        m.poster_image
    FROM movies m
    WHERE NOT EXISTS (
        SELECT 1
        FROM showtimes s
        WHERE s.movie_id = m.id
    )
    ORDER BY m.id DESC
    LIMIT 6
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>GSC Movie Ticket Booking System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>

        /* =========================
           GLOBAL
        ========================= */

        body {
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:url('https://images.unsplash.com/photo-1524985069026-dd778a71c7b4') 
            no-repeat center center/cover fixed;

            min-height:100vh;

            overflow-x:hidden;
            overflow-y:auto;
            animation:none;
            background-size:cover;
        }

        .overlay {
            position:relative;
            height:100vh;
            width:100%;
            background:
                linear-gradient(
                    rgba(0,0,0,0.5),
                    rgba(0, 0, 0, 0.64)
                ),
                radial-gradient(
                    circle at top,
                    rgba(238,226,184,0.8),
                    rgba(255,140,0,0.2),
                    transparent 65%
                );

            pointer-events:none;
        }

        .hero-box {
            position:relative;
            z-index:2;
            color:white;
            text-align:center;
            padding:50px;
            border-radius:15px;
            backdrop-filter:blur(10px);
            pointer-events:auto;
        }

        .dashboard-body {
            background:
            linear-gradient(
                180deg,
                #faf8f2,
                #f3ede0
            );
        }

        .dashboard-container {
            padding-top:90px;
            padding-bottom:50px;
        }

        /* =========================
           ANIMATION
        ========================= */

        @keyframes floatLogo {
            from {
                transform:translateY(0px);
            }

            to {
                transform:translateY(-6px);
            }
        }

        @keyframes zoomBg {
            from {
                background-size:100%;
            }

            to {
                background-size:130%;
            }
        }

        @keyframes slideInRight {
            from {
                transform:translateX(100%);
                opacity:0;
            }

            to {
                transform:translateX(0);
                opacity:1;
            }
        }

        /* =========================
           HERO SECTION
        ========================= */

        .hero-logo img {
            width:600px;
            margin-bottom:35px;
            margin-top:-300px;
            filter:drop-shadow(0 0 30px rgba(245,197,24,0.45));
            animation:floatLogo 3s infinite alternate;
        }

        .hero-banner{

            min-height:320px;

            border-radius:30px;

            background:
            linear-gradient(
                90deg,
                rgba(0,0,0,.85) 0%,
                rgba(0,0,0,.55) 45%,
                rgba(0,0,0,.2) 100%
            ),
            url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?q=80&w=2000')
            center center/cover;

            display:flex;
            align-items:center;

            padding:35px;
            overflow:hidden;

            margin-top:-50px;

            box-shadow:
            0 20px 40px rgba(0,0,0,.15);
        }

        .hero-content{

            color:white;
        }

        .hero-content h1{

            font-size:58px;
            font-weight:900;
            line-height:1.1;
            margin-bottom:15px;
        }

        .hero-content h3{
            font-size:22px;
            font-weight:600;
            opacity:.9;
        }

        .hero-content p{
            font-size:18px;
            opacity:.9;
        }

        .badge-now-showing{

            background:#f5c518;
            color:#000;

            padding:8px 15px;

            border-radius:30px;

            font-weight:700;
        }


        .action-card{

            background:white;

            border-radius:20px;

            padding:30px;

            text-align:center;

            font-size:30px;

            cursor:pointer;

            box-shadow:
            0 8px 24px rgba(0,0,0,.08);

            transition:.3s;

            height:180px;

            display:flex;

            flex-direction:column;

            justify-content:center;
        }

        .action-card h5{

            margin-top:15px;

            font-size:18px;

            color:#222;

            font-weight:700;
        }

        .action-card:hover{

            transform:translateY(-6px);

            box-shadow:
            0 12px 28px rgba(0,0,0,.15);
        }

        /* =========================
           BUTTON
        ========================= */

        .btn-custom {
            padding:12px 30px;
            font-size:18px;
            border-radius:30px;
            transition:0.3s;
        }

        .btn-custom:hover {
            transform:scale(1.08);
        }

        .btn-warning {
            background-color:#f5c518;
            border:none;
            border-radius:30px;
            padding:12px;
            font-size:18px;
            transition:0.3s;
        }

        .btn-warning:hover {
            background-color:#e0b400;
            transform:scale(1.05);
        }

        .quick-actions .btn {
            border-radius:30px;
            padding:14px 20px;
            font-weight:600;
            transition:0.3s;
        }

        .quick-actions .btn:hover {
            transform:scale(1.03);
        }

        /* =========================
           SEARCH BAR
        ========================= */

        .search-bar{
            max-width:700px;
            margin:40px auto;
        }

        .search-wrapper{
            position:relative;
        }

        .search-input{
            width:100%;

            height:68px;

            border:none;

            border-radius:50px;

            padding:0 70px 0 28px;

            font-size:18px;

            background:#fff;

            box-shadow:
            0 8px 24px rgba(0,0,0,.08);

            transition:.3s;
        }

        .search-input:focus{
            outline:none;

            box-shadow:
            0 10px 30px rgba(245,197,24,.25);

            border:2px solid #ffda56;
        }


        .search-btn{
            position:absolute;

            right:8px;
            top:8px;

            width:52px;
            height:52px;

            border:none;

            border-radius:50%;

            background:#ffda56;

            color:#111;

            font-size:20px;

            transition:.3s;
        }

        .search-btn:hover{
            background:#e0b400;

            transform:scale(1.05);
        }

        /* =========================
           MOVIE CARD
        ========================= */

        .movie-card {
            background:rgba(255,255,255,0.95);
            border:none;
            border-radius:20px;
            overflow:hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,.08);
            transition: transform .25s ease, box-shadow .25s ease;
            height:100%;
            will-change: transform;
        }

        .movie-card:hover {
            transform:
            translateY(-8px);

            box-shadow:
            0 20px 40px rgba(0,0,0,.18);
        }

        .movie-card .card-body {
            padding:20px;
        }

        .movie-card .card-title {
            font-size:22px;
            font-weight:700;
            color:#222;
        }

        .movie-card .card-title a {
            color:#222;
            text-decoration:none;
        }

        .movie-card .card-title a:hover {
            color:#f5c518;
        }

        /* =========================
           TOAST NOTIFICATION
        ========================= */

        .toast-container {
            position:fixed;
            top:20px;
            right:20px;
            z-index:9999;
            display:flex;
            flex-direction:column;
            gap:10px;
            pointer-events:none;
        }

        .toast {
            background:#ff6b6b;
            color:white;
            padding:12px 20px;
            border-radius:8px;
            box-shadow:0 4px 12px rgba(0,0,0,0.15);
            font-size:14px;
            font-weight:bold;
            min-width:250px;
            max-width:350px;
            word-wrap:break-word;
            pointer-events:auto;
            cursor:pointer;
            transition:0.3s;
            animation:slideInRight 0.3s ease;
        }

        .toast:hover {
            transform:scale(1.02);
            background:#ff5252;
        }

        .toast.fade-out {
            opacity:0;
            transform:translateX(100%);
            transition:opacity 0.3s, transform 0.3s;
        }

        .movie-poster {
            height:420px;
            object-fit:cover;
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        .movie-card:hover .movie-poster{

            transform:scale(1.05);
        }

        .section-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:25px;
        }

        .section-header h2{
            font-size:32px;
            font-weight:800;
            color:#222;
        }

        .coming-soon-btn {
            position: relative;
            overflow: hidden;
            font-weight: 700;
            border-radius: 30px;
            transition: 0.3s;
            color: #666;
        }

        /* 默认 hover 卡片时动画 */
        .movie-card:hover .coming-soon-btn {
            border-color: #f5c518;
            color: #000;
        }

        /* hover 时覆盖效果 */
        .coming-soon-btn::after {
            content: "Coming Soon";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;

            background: #f5c518;
            color: #000;

            display: flex;
            align-items: center;
            justify-content: center;

            transform: translateY(100%);
            transition: 0.3s ease;

            font-weight: 800;
        }

        .movie-card:hover .coming-soon-btn::after {
            transform: translateY(0);
        }


    </style>
</head>

<body>

<?php include 'includes/navbar.php'; ?>


<?php if (!isset($_SESSION['user_id'])): ?>

    <!-- =========================
         GUEST SECTION
    ========================= -->

    <div class="overlay d-flex justify-content-center align-items-center">

        <div class="hero-box text-center">

            <div class="hero-logo">
                <img src="<?= BASE_URL ?>/assets/logo.png" alt="GSC Logo">
            </div>

            <h1 class="display-4 fw-bold hero-title">
                Welcome to GSC Booking
            </h1>

            <p class="lead mt-3">
                Book your favorite movies anytime, anywhere.
            </p>

            <p>
                Discover latest movies, choose your seats, and enjoy the show!
            </p>

            <div class="mt-4">

                <a href="<?= BASE_URL ?>/register.php"
                   class="btn btn-warning btn-lg btn-custom me-3">
                    Register
                </a>

                <a href="<?= BASE_URL ?>/login.php"
                   class="btn btn-outline-light btn-lg btn-custom">
                    Sign In
                </a>

            </div>

        </div>

    </div>

<?php else: ?>

    <!-- =========================
         USER DASHBOARD
    ========================= -->

    <div class="dashboard-body">

        <div class="container dashboard-container">

            <div class="hero-banner">

                <div class="hero-content">

                    <h3>
                        Hello,
                        <?= htmlspecialchars($_SESSION['full_name']) ?>
                    </h3>

                    <h1 class="mt-3">
                        Book Your Next Movie Night
                    </h1>

                    <p>
                        Browse the latest movies and reserve your favourite seats.
                    </p>


                    <!-- Search -->

                    <div class="search-bar">

                        <form action="<?= BASE_URL ?>/customer/movies.php"
                            method="GET">

                            <div class="search-wrapper">

                                <input type="text"
                                    name="search"
                                    class="search-input"
                                    placeholder="Search movie title, genre or keyword...">

                                <button type="submit"
                                        class="search-btn">

                                    🔍

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>


            <br><br><br>

            <!-- Quick Actions -->

            <div class="quick-actions text-center">

                <div class="row">

                    <div class="section-header">

                        <h2>Quick Actions</h2>

                    </div>

                    <div class="col-md-4 mb-4">

                        <a href="<?= BASE_URL ?>/customer/profile.php"
                        class="text-decoration-none">

                            <div class="action-card">

                                <i class="bi bi-person-circle"></i>

                                <h5>Profile</h5>

                            </div>

                        </a>

                    </div>

                    <div class="col-md-4 mb-4">

                        <a href="<?= BASE_URL ?>/customer/history.php"
                        class="text-decoration-none">

                            <div class="action-card">

                                <i class="bi bi-ticket-perforated"></i>

                                <h5>My Bookings</h5>

                            </div>

                        </a>

                    </div>

                    <div class="col-md-4 mb-4">

                        <a href="<?= BASE_URL ?>/customer/movies.php"
                        class="text-decoration-none">

                            <div class="action-card">

                                <i class="bi bi-film"></i>

                                <h5>Movies</h5>

                            </div>

                        </a>

                    </div>

                </div>

            </div>

            <br><br><br>
            <!-- Movie Section -->

            <div class="section-header">


                <h2>Now Showing</h2>

            </div>

            <div class="row mb-4">

                <?php if ($nowShowing && $nowShowing->num_rows > 0): ?>

                    <?php while($movie = $nowShowing->fetch_assoc()): ?>

                        <div class="col-md-4 mb-4">

                            <div class="card movie-card">

                                <img src="<?= BASE_URL ?>/uploads/posters/<?= $movie['poster_image'] ?>"
                                    class="movie-poster"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low"
                                    alt="<?= htmlspecialchars($movie['title']) ?>">

                                <div class="card-body">

                                    <h5 class="card-title">
                                        <?= htmlspecialchars($movie['title']) ?>
                                    </h5>

                                    <p class="text-muted">
                                        <?= htmlspecialchars($movie['genre']) ?>
                                    </p>

                                    <a href="<?= BASE_URL ?>/customer/movie_detail.php?movie_id=<?= $movie['id'] ?>"
                                    class="btn btn-warning w-100">

                                        Book Now

                                    </a>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <p class="text-muted">
                        No movies available yet.
                    </p>

                <?php endif; ?>

            </div>

            <br><br>

            <div class="section-header">

                <h2>Upcoming Movies</h2>

            </div>

            <div class="row mb-4">

                <?php if ($upcomingMovies && $upcomingMovies->num_rows > 0): ?>

                    <?php while($movie = $upcomingMovies->fetch_assoc()): ?>

                        <div class="col-md-4 mb-4">

                            <div class="card movie-card">

                                <img src="<?= BASE_URL ?>/uploads/posters/<?= $movie['poster_image'] ?>"
                                    class="movie-poster"
                                    alt="<?= htmlspecialchars($movie['title']) ?>">

                                <div class="card-body">

                                    <h5 class="card-title">
                                        <?= htmlspecialchars($movie['title']) ?>
                                    </h5>

                                    <p class="text-muted">
                                        <?= htmlspecialchars($movie['genre']) ?>
                                    </p>

                                    <button class="btn btn-outline-secondary w-100 coming-soon-btn" disabled>
                                        Coming Soon
                                    </button>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <p class="text-muted">
                        No upcoming movies.
                    </p>

                <?php endif; ?>

            </div>

        </div>

    </div>

<?php endif; ?>


<script src="<?= BASE_URL ?>/notification.js"></script>

</body>
</html>