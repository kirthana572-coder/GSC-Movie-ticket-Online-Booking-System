<?php
session_start();
?>


<!DOCTYPE html>
<html>
<head>
    <title>GSC Movie Ticket Booking System</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

     <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;

            /* Background*/
            background: url('https://images.unsplash.com/photo-1524985069026-dd778a71c7b4') no-repeat center center/cover;
            height: 100vh;
        }
        
        .overlay {
            background: rgba(0,0,0,0.7);
            height: 100vh;
            width: 100%;
        }

        .hero-box {
            color: white;
            text-align: center;
            padding: 50px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        .btn-custom {
            padding: 12px 30px;
            font-size: 18px;
            border-radius: 30px;
        }

        .btn-warning {
            background-color: #f5c518;
            border: none;
        }

        .btn-warning:hover {
            background-color: #e0b400;
        }
    </style>
</head>
</head>


<body>

<!-- Header -->
<nav class="navbar navbar-dark bg-dark">
  <div class="container">

    <span class="navbar-brand">
        Golden Screen Cinema (GSC)
    </span>

    <div class="text-white">
        <?php if (isset($_SESSION['user_id'])): ?>
            Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?> 
            (<?= ucfirst($_SESSION['role']) ?>)
            <a href="auth/logout.php" class="btn btn-sm btn-outline-light ms-3">Logout</a>
        <?php else: ?>
            <span class="text-white-50">Not logged in</span>
        <?php endif; ?>
    </div>
  </div>
</nav>


<!-- Welcome Section -->
<?php if (!isset($_SESSION['user_id'])): ?>
 <!-- 未登录状态（基于 Joyce 的设计） -->
<div class="overlay d-flex justify-content-center align-items-center">

    <div class="hero-box">
        <h1 class="display-4 fw-bold">Welcome to GSC Booking</h1>

        <p class="lead mt-3">
            Book your favorite movies anytime, anywhere.
        </p>

        <p>
            Discover latest movies, choose your seats, and enjoy the show!
        </p>

        <div class="mt-4">
            <a href="register.php" class="btn btn-warning btn-lg btn-custom me-3">Register</a>
            <a href="login.php" class="btn btn-outline-light btn-lg btn-custom">Sign In</a>
        </div>
    </div>

</div>
<?php endif; ?>



<?php if (isset($_SESSION['user_id'])): ?>
<!-- 已登录状态 -->
<div class="container text-center mt-5">
    <h1>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
        
    <p class="lead mt-3">
        You are logged in as <strong><?= ucfirst($_SESSION['role']) ?></strong>.
        <br>Choose an option below to get started.
    </p>

    <div class="mt-4">
        <a href="customer/movies.php" class="btn btn-warning btn-lg me-3">Browse Movies</a>
        <a href="customer/history.php" class="btn btn-outline-dark btn-lg">My Bookings</a>
    </div>

</div>
<?php endif; ?>

</body>
</html>