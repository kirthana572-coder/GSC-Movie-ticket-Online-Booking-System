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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($movie['title']) ?> - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/frontend/gsc-style.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/global.css">

    <style>

        body{

            background:
            linear-gradient(
                135deg,
                #faf8f2,
                #f3ede0
            );
        }

        .movie-hero{

            background:
            linear-gradient(
                135deg,
                #ffffff,
                #faf7ef
            );

            border-radius:30px;

            padding:45px;

            box-shadow:
            0 15px 40px rgba(0,0,0,.08);
        }

        .hero-poster{

            width:100%;

            height:550px;

            object-fit:cover;

            border-radius:20px;

            box-shadow:
            0 15px 35px rgba(0,0,0,.2);
        }

        .movie-title{

            font-size:52px;

            font-weight:900;

            color:#111;

            margin-bottom:20px;
        }

        .movie-meta{

            display:flex;

            gap:20px;

            margin-bottom:20px;

            color:#666;

            font-weight:600;
        }

        .movie-description{

            font-size:17px;

            line-height:1.8;

            color:#555;
        }

        .showtime-card{

            background:white;

            border-radius:20px;

            padding:25px;

            display:flex;

            justify-content:space-between;

            align-items:center;

            box-shadow:
            0 8px 24px rgba(0,0,0,.08);

            transition:.3s;
        }

        .showtime-card:hover{

            transform:translateY(-4px);

            box-shadow:
            0 15px 30px rgba(0,0,0,.12);
        }

        .showtime-card h5{

            font-weight:800;

            margin-bottom:5px;
        }

        .showtime-date{

            font-size:20px;

            font-weight:800;

            color:#111;
        }

        .showtime-time{

            font-size:16px;

            color:#f5c518;

            font-weight:700;
        }

        .showtime-branch{

            color:#666;
        }

        .section-title{

            margin-bottom:25px;
        }

        .section-title h2{

            font-size:34px;

            font-weight:800;

            color:#222;
        }

        .meta-pill{

            background:#f8f9fa;

            padding:10px 18px;

            border-radius:50px;

            font-size:14px;

            font-weight:600;

            border:1px solid #e5e5e5;
        }

        .btn-book{

            background:#f5c518;

            border:none;

            color:#000;

            font-weight:700;

            padding:12px 24px;

            border-radius:12px;

            transition:.3s;
        }

        .btn-book:hover{

            background:#e6b800;

            transform:translateY(-2px);
        }

        .empty-showtime{

            background:white;

            border-radius:25px;

            padding:60px 40px;

            text-align:center;

            box-shadow:
            0 10px 25px rgba(0,0,0,.08);
        }

        .empty-icon{

            font-size:70px;

            margin-bottom:20px;
        }

        .empty-showtime h4{

            font-size:28px;

            font-weight:700;

            color:#222;

            margin-bottom:15px;
        }

        .empty-showtime p{

            color:#6c757d;

            max-width:500px;

            margin:0 auto;

            line-height:1.7;
        }

    </style>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">

</head>
<body class="movie-details-page">

<?php include '../includes/navbar.php'; ?>

<div class="container py-5">
    <div class="movie-hero mb-5">

    <div class="row align-items-center">

        <div class="col-md-4">

            <img
                src="<?= BASE_URL ?>/uploads/posters/<?= $movie['poster_image'] ?>"
                class="hero-poster"
                alt="<?= htmlspecialchars($movie['title']) ?>"
            >

        </div>

        <div class="col-md-8">


            <h1 class="movie-title">
                <?= htmlspecialchars($movie['title']) ?>
            </h1>

            <div class="movie-meta">

                <div class="meta-pill">
                    🎬 <?= htmlspecialchars($movie['genre']) ?>
                </div>

                <div class="meta-pill">
                    ⏱ <?= $movie['duration'] ?> Minutes
                </div>

            </div>

            <p class="movie-description">

                <?= nl2br(htmlspecialchars($movie['description'])) ?>

            </p>

        </div>

    </div>

</div>

<div class="section-title">

    <h2>
        Available Showtimes
    </h2>

</div>


<?php if ($showtimes->num_rows > 0): ?>

    <div class="row">

        <?php while($s = $showtimes->fetch_assoc()): ?>

            <div class="col-md-6 mb-4">

                <div class="showtime-card">

                    <div class="showtime-info">

                        <div class="showtime-date">
                            <?= date('d M Y', strtotime($s['show_date'])) ?>
                        </div>

                        <div class="showtime-time">
                            <?= date('h:i A', strtotime($s['show_time'])) ?>
                        </div>

                        <div class="showtime-branch">
                            📍 <?= htmlspecialchars($s['branch_name']) ?>
                        </div>

                    </div>

                    <a
                        href="<?= BASE_URL ?>/customer/select_seat.php?showtime_id=<?= $s['showtime_id'] ?>"
                        class="btn btn-book"
                    >
                        Book Now
                    </a>

                </div>

            </div>

        <?php endwhile; ?>

    </div>
<?php else: ?>

    <div class="empty-showtime">

        <div class="empty-icon">
            🎭
        </div>

        <h4>
            No Showtimes Available
        </h4>

        <p>
            There are currently no scheduled showtimes for this movie.
            Please check back later for upcoming screenings.
        </p>

        <a href="<?= BASE_URL ?>/customer/movies.php"
        class="btn btn-warning mt-3">
            Browse Other Movies
        </a>

    </div>
<?php endif; ?>


<div class="text-center mt-5">

    <a
        href="<?= BASE_URL ?>/index.php"
        class="btn btn-outline-dark btn-lg px-5"
    >

        Back

    </a>

</div>


</div>
<script src="<?= BASE_URL ?>/notification.js"></script>
</body>
</html>