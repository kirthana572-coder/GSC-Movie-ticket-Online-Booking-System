<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>GSC Movie Ticket Booking System</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<!-- Header -->
<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <span class="navbar-brand">Golden Screen Cinema (GSC)</span>
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
<div class="container text-center mt-5">

    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- 已登录状态 -->
        <h1>Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
        <p class="lead mt-3">
            You are logged in as <strong><?= ucfirst($_SESSION['role']) ?></strong>.
            <br>Choose an option below to get started.
        </p>

        <div class="mt-4">
            <a href="customer/movies.php" class="btn btn-warning btn-lg me-3">Browse Movies</a>
            <a href="customer/history.php" class="btn btn-outline-dark btn-lg">My Bookings</a>
        </div>

    <?php else: ?>
        <!-- 未登录状态（基于 Joyce 的设计） -->
        <h1>Movie Ticket Booking System</h1>

        <p class="lead mt-3">
            This system allows customers to register, login, select movies, choose seats, and make bookings easily.
        </p>

        <p class="mt-3">
            New user? Please register first before signing in.
        </p>

        <div class="mt-4">
            <a href="register.php" class="btn btn-warning btn-lg me-3">Register</a>
            <a href="signin.php" class="btn btn-outline-dark btn-lg">Sign In</a>
        </div>
    <?php endif; ?>

</div>

</body>
</html>