<?php
session_start();
// 如果没登录，跳转到登录页
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GSC Movie Ticket Booking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Header -->
<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <span class="navbar-brand">Golden Screen Cinema (GSC)</span>
    <div class="text-white">
        Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?> 
        (<?= ucfirst($_SESSION['role']) ?>)
        <a href="auth/logout.php" class="btn btn-sm btn-outline-light ms-3">Logout</a>
    </div>
  </div>
</nav>

<!-- Welcome Section -->
<div class="container text-center mt-5">
    <h1>Movie Ticket Booking System</h1>
    <p class="lead mt-3">
        You are logged in as <strong><?= htmlspecialchars($_SESSION['full_name']) ?></strong>.
        <br>Choose an option below to get started.
    </p>

    <div class="mt-4">
        <a href="#" class="btn btn-warning btn-lg me-3">Browse Movies</a>
        <a href="#" class="btn btn-outline-dark btn-lg">My Bookings</a>
    </div>
</div>

</body>
</html>