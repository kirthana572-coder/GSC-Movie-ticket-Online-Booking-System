<?php 
require_once 'config/db.php';

// Now Showing（有showtime）
$nowShowing = $conn->query("

    SELECT DISTINCT m.*
    FROM movies m
    JOIN showtimes s
        ON s.movie_id = m.id
    WHERE m.status = 'active'
    AND TIMESTAMP(
        s.show_date,
        s.show_time
    ) > NOW()
    ORDER BY m.id DESC

");

// Upcoming（没有showtime）
$upcomingMovies = $conn->query("

    SELECT m.*
    FROM movies m
    WHERE m.status = 'active'
    AND NOT EXISTS (
        SELECT 1
        FROM showtimes s
        WHERE s.movie_id = m.id
        AND TIMESTAMP(
            s.show_date,
            s.show_time
        ) > NOW()
    )
    ORDER BY m.id DESC

");
?>

<!DOCTYPE html>
<html>

<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GSC Movie Ticket Booking System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">

    <style>

        /* =========================
           GLOBAL
        ========================= */

        body {
            background:url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba') 
            no-repeat center center/cover fixed;

            min-height:85vh;

            overflow-y:auto;
            animation:none;
            background-size:cover;
        }


        .overlay::before{

            content:'';

            position:absolute;

            top:-200px;
            left:50%;

            width:800px;
            height:800px;

            transform:translateX(-50%);

            background:
            radial-gradient(
                circle,
                rgba(245,197,24,.12),
                transparent 70%
            );

            pointer-events:none;

            opacity:.4;

            mix-blend-mode:screen;
        }

        .overlay{
            min-height:100vh;

            display:flex;
            flex-direction:column;

            justify-content:center;
            align-items:center;

            background:
                linear-gradient(
                    180deg,
                    rgba(0,0,0,.42) 0%,
                    rgba(0,0,0,.68) 100%
                ),
                radial-gradient(
                    circle at center top,
                    rgba(245,197,24,.18),
                    transparent 55%
                );

            padding:40px 40px 80px;

            position:relative;

            overflow:hidden;
        }

        .overlay::after{

            content:'';

            position:absolute;

            left:0;
            right:0;
            bottom:0;

            height:250px;

            background:
            linear-gradient(
                to bottom,
                transparent,
                rgba(0,0,0,.45)
            );

            pointer-events:none;
        }

        .hero-box{

            max-width:1000px;

            text-align:center;

            color:#fff;

            margin-bottom:50px;

            position:relative;
            z-index:2;
        }

        .hero-badge{

            display:inline-block;

            background:#f5c518;

            color:#111;

            font-weight:700;

            padding:8px 18px;

            border-radius:999px;

            margin-bottom:25px;
        }

        .hero-box h1{

            font-size:clamp(52px,6vw,82px);

            font-weight:900;

            letter-spacing:-2px;

            line-height:1.05;

            margin-bottom:25px;

            background:
                linear-gradient(
                    180deg,
                    #ffffff,
                    #f5f5f5,
                    #d9d9d9
                );

                -webkit-background-clip:text;
                -webkit-text-fill-color:transparent;
        }

        .hero-box p{

            font-size:20px;

            line-height:1.8;

            color:rgba(255,255,255,.85);

            max-width:750px;

            margin:auto;
        }

        .hero-actions{

            margin-top:35px;

            display:flex;

            justify-content:center;

            gap:15px;
        }

        .hero-actions .btn{

            min-width:180px;

            height:56px;

            display:flex;
            align-items:center;
            justify-content:center;

            font-weight:700;
        }

        .hero-actions .btn-warning{

            min-width:220px;

            box-shadow:
                0 10px 25px rgba(245,197,24,.35);
        }

        .hero-actions .btn-outline-light{

            min-width:180px;

            border:2px solid rgba(255,255,255,.6);

            backdrop-filter:blur(10px);
        }

        .hero-actions .btn-outline-light:hover{

            background:white;
            color:#111;
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

        .feature-section{

            width:100%;

            max-width:1200px;

            position:relative;

            z-index:2;

            margin-top:40px;
        }

        .feature-card{

            background:
            rgba(255,255,255,.08);

            backdrop-filter:blur(14px);

            border:
            1px solid rgba(255,255,255,.15);

            color:white;

            border-radius:28px;

            padding:40px 30px;

            text-align:center;

            height:100%;

            box-shadow:
                0 15px 35px rgba(0,0,0,.15);

            transition:.3s;
        }

        .feature-card:hover{

            transform:
            translateY(-10px);

            box-shadow:
                0 20px 40px rgba(0,0,0,.25),
                0 0 30px rgba(245,197,24,.15);
        }

        .feature-card h5{

            color:#fff;

            margin-top:15px;

            font-weight:700;
        }

        .feature-card p{

            color:rgba(255,255,255,.75);

            margin-bottom:0;
        }

        .feature-icon{

            font-size:48px;

            color:#f5c518;

            display:block;

            margin-bottom:18px;
        }

        .hero-brand{
            width:420px;
            height:100px;

            overflow:hidden;

            display:flex;
            justify-content:center;
            align-items:center;

            margin:0 auto 20px;
        }

        .hero-brand img{

            width:380px;

            filter:
                drop-shadow(0 0 15px rgba(245,197,24,.55))
                drop-shadow(0 0 50px rgba(245,197,24,.18));

            animation:
                floatLogo 3s ease-in-out infinite alternate;
        }

        .hero-tagline{

            color:rgba(255,255,255,.75);

            font-size:14px;

            letter-spacing:2px;

            text-transform:uppercase;

            margin-top:-10px;

            margin-bottom:30px;
        }

        .btn-warning{

            position:relative;
            overflow:hidden;
        }

        .btn-warning::before{

            content:'';

            position:absolute;

            top:0;
            left:-120%;

            width:60%;
            height:100%;

            background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255,255,255,.4),
                transparent
            );

            transition:.7s;
        }

        .btn-warning:hover::before{

            left:140%;
        }

        .preview-section{

            position:relative;

            width:100%;

            padding:60px 0 80px;

            background:
            linear-gradient(
                180deg,
                #f5f3ee,
                #faf8f4
            );
        }

        .preview-section::before{

            content:'';

            position:absolute;

            top:0;
            left:0;
            right:0;

            height:80px;

            background:
            linear-gradient(
                to bottom,
                rgba(0, 0, 0, 0.30),
                transparent
            );
        }

        .section-title{

            text-align:center;

            margin-bottom:50px;
        }

        .section-title h2{

            color:#212529;

            font-size:54px;

            font-weight:900;
        }

        .section-title p{

            font-size:17px;

            color:#6c757d;
        }

        .preview-card{

            position:relative;

            overflow:hidden;

            border-radius:24px;

            height:500px;

            cursor:pointer;

            background:#fff;

            box-shadow:
            0 12px 30px rgba(0,0,0,.12);

            transition:.35s;
        }

        .preview-card:hover{
            transform:translateY(-10px);
        }

        .preview-card img{

            width:100%;
            height:100%;

            object-fit:cover;

            transition:.5s;
        }

        .preview-card:hover img{

            transform:scale(1.08);
        }

        .preview-overlay{

            position:absolute;

            inset:0;

            display:flex;

            flex-direction:column;

            justify-content:flex-end;

            padding:30px;

            background:
            linear-gradient(
                transparent,
                rgba(0,0,0,.9)
            );
        }

        .preview-overlay h5{

            color:white;

            font-size:24px;

            font-weight:700;

            margin-bottom:15px;
        }

        .preview-overlay .btn{
            width:fit-content;
        }

        .preview-btn{

            background:#f5c518;

            color:#111;

            border:none;

            border-radius:12px;

            padding:10px 18px;

            font-size:13px;

            font-weight:700;

            letter-spacing:.3px;

            box-shadow:
                0 6px 16px rgba(245,197,24,.25);

            transition:.25s ease;
        }

        .preview-btn:hover{

            background:#ffd84d;

            color:#111;

            transform:translateY(-2px);

            box-shadow:
                0 0 20px rgba(245,197,24,.5),
                0 10px 30px rgba(245,197,24,.35);
        }

        .footer{
            width:100%;

            text-align:center;

            padding:55px 20px;

            background:
            linear-gradient(
                180deg,
                #2b2b2b,
                #1f1f1f
            );

            border-top:
            1px solid rgba(245,197,24,.15);

            backdrop-filter:blur(12px);
        }

        .footer p{

            color:white;

            margin-bottom:5px;

            font-weight:600;
        }

        .footer small{

            color:
                rgba(255,255,255,.6);
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
           User dashbroad HERO SECTION
        ========================= */

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
            transform:
                translateY(-2px)
                scale(1.03);

            box-shadow:
                0 0 20px rgba(245,197,24,.5),
                0 10px 30px rgba(245,197,24,.35);
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

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
</head>

<body>

<?php include 'includes/navbar.php'; ?>


<?php if (!isset($_SESSION['user_id'])): ?>

    <!-- =========================
         GUEST SECTION
    ========================= -->

    <div class="overlay">

        <div class="hero-box">

            <div class="hero-content">

                <div class="hero-brand">

                    <img src="<?= BASE_URL ?>/assets/logo.png"
                        alt="GSC Logo">

                </div>

                <div class="hero-tagline">

                    Malaysia's Leading Cinema Experience

                </div>

                <br><br>
                <h1>
                    Experience Movies Like Never Before
                </h1>

                <p>
                    Discover the latest blockbusters, choose your seats,
                    and enjoy a seamless cinema experience with GSC.
                </p>

                <div class="hero-actions">

                    <a href="<?= BASE_URL ?>/register.php"
                        class="btn btn-warning btn-lg">
                        Create Account
                    </a>

                    <a href="<?= BASE_URL ?>/login.php"
                        class="btn btn-outline-light btn-lg">
                        Sign In
                    </a>

                </div>

            </div>

        </div>

        <div class="container feature-section">

            <div class="row g-4">

                <div class="col-md-3">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-ticket-perforated-fill feature-icon"></i>
                        </div>

                        <h5>Easy Booking</h5>
                        <p>Book tickets in seconds.</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                            <i class="bi bi-grid-3x3-gap-fill feature-icon"></i>
                        <h5>Seat Selection</h5>
                        <p>Choose your favourite seats.</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                            <i class="bi bi-phone-fill feature-icon"></i>

                        <h5>E-Tickets</h5>
                        <p>Access tickets anytime.</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="feature-card">
                            <i class="bi bi-film feature-icon"></i>

                        <h5>Latest Movies</h5>
                        <p>Discover new releases.</p>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <section class="preview-section">

        <div class="container">

            <div class="section-title">
                <h2>Now Showing</h2>
                <p>Experience the latest blockbusters in cinemas.</p>
            </div>

            <div class="row g-4">

                <?php
                $previewMovies = $conn->query("
                    SELECT
                        id,
                        title,
                        poster_image
                    FROM movies
                    ORDER BY created_at DESC
                    LIMIT 3
                ");

                while($movie = $previewMovies->fetch_assoc()):
                ?>

                <div class="col-md-4">

                    <div class="preview-card">

                        <img
                            src="<?= BASE_URL ?>/uploads/posters/<?= htmlspecialchars($movie['poster_image']) ?>"
                            alt="<?= htmlspecialchars($movie['title']) ?>"
                        >

                        <div class="preview-overlay">

                            <h5>
                                <?= htmlspecialchars($movie['title']) ?>
                            </h5>

                            <a
                                href="<?= BASE_URL ?>/login.php?id=<?= $movie['id'] ?>"
                                class="btn preview-btn"
                            >
                                View Details
                            </a>

                        </div>

                    </div>

                </div>

                <?php endwhile; ?>

            </div>

        </div>

    </section>

    <footer class="footer">

        <div class="container">

            <p>
                © <?= date('Y') ?> GSC Movie Ticket Booking System
            </p>

            <small>
                Book Movies • Select Seats • Enjoy The Show
            </small>

        </div>

    </footer>

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

                <div class="section-header">

                    <h2>Quick Actions</h2>

                </div>

                <div class="row quick-actions-row">

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

                        <div class="col-6 col-md-4 mb-3">

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

                        <div class="col-6 col-md-4 mb-3">

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