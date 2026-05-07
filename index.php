<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>GSC Movie Ticket Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Navbar Background */
        .custom-navbar {
            background: linear-gradient(to right, #000000, #000000);
            padding: 15px 0;

            margin: 10px;
            border-radius: 10px;

            position: relative;
            z-index: 1000; 
        }

        /* Logo */
        .navbar-brand {
            color: white;
            font-size: 20px;
            font-weight: 500;
        }

        /* Buttons */
        .btn-outline-light {
            border-radius: 0px;
            padding: 5px 15px;
        }

        .btn-warning {
            background-color: #f5c518;
            border: none;
            border-radius: 50px;
            padding: 10px 22px;
        }

        .btn-warning:hover {
            background-color: #e0b400;
        }
    
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
            linear-gradient(
                rgba(0, 0, 0, 0.5),
                rgba(0, 0, 0, 0.8)
            ),
    
            radial-gradient(circle at top,
                rgba(238, 226, 184, 0.8),
                rgba(255,140,0,0.2),
                transparent 65%
            );
        }

        /* Light effect */
        .overlay::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;

            width: 200%;
            height: 200%;

            background: radial-gradient(circle,
                rgba(238, 234, 217, 0.25),
                rgba(255,140,0,0.15),
                transparent 70%
            );

            animation: moveLight 7s ease-in-out infinite;
                z-index: 1;
            }

        /* Center content box */
        .hero-box {
            position: relative;
            z-index: 2;

            color: white;
            text-align: center;
            padding: 50px;
            border-radius: 15px;

            backdrop-filter: blur(10px);
        }

        /* zoom animation */
        @keyframes zoomBg {
            from {
                background-size: 100%;
            }
            to {
                background-size: 125%;
            }

        }


        /* Light moving animation */
        @keyframes moveLight {
            0% {
                transform: translate(-30%, -30%) scale(1);
            }
            50% {
                transform: translate(0%, 0%) scale(1.4);
            }
            100% {
                transform: translate(-30%, -30%) scale(1);
            }
        }

        /* Button stye */
        .btn-custom {
            padding: 12px 30px;
            font-size: 18px;
            border-radius: 30px;
            transition: 0.3s;
        }

        .btn-custom:hover {
            transform: scale(1.08);
        }

        /* Button */
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
            pointer-events: auto; /* 只让内容可以点 */
        }

       
    </style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<?php if (!isset($_SESSION['user_id'])): ?>

<!-- 未登录 -->
<div class="overlay d-flex justify-content-center align-items-center">

    <div class="hero-box text-center">
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

<?php else: ?>

<!-- 已登录 -->
<div class="container text-center mt-5">
    <h1>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>

    <p class="lead mt-3">
        You are logged in as <strong><?= ucfirst($_SESSION['role']) ?></strong>.
    </p>

    <div class="mt-4">
        <a href="customer/movies.php" class="btn btn-warning btn-lg me-3">Browse Movies</a>
        <a href="customer/history.php" class="btn btn-outline-dark btn-lg">My Bookings</a>
    </div>
</div>

<?php endif; ?>