<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: #f2f2f2;
}

.movie-card {
    transition: 0.2s;
    border-radius: 10px;
}

.movie-card:hover {
    transform: scale(1.02);
}
</style>
</head>

<body>

<!--NAVBAR-->
<nav class="navbar navbar-dark bg-dark px-3">

    <span class="navbar-brand">🎬 Movie Booking</span>

    <!-- MENU BUTTON -->
    <button class="btn btn-outline-light"
        data-bs-toggle="offcanvas"
        data-bs-target="#profileMenu">
        ☰
    </button>

</nav>

<!--MOVIE LIST -->
<div class="container mt-4">

    <input type="text" class="form-control mb-4" placeholder="Search movies...">

    <h5>Movies</h5>

    <div class="card movie-card p-3 mb-3 shadow-sm">
        <div class="d-flex justify-content-between">
            <div>
                <h6>Avengers: Endgame</h6>
                <small class="text-muted">Action / Sci-Fi</small>
            </div>
            <a href="showtime.php?movie=1" class="btn btn-primary btn-sm">View Showtimes</a>
        </div>
    </div>

    <div class="card movie-card p-3 mb-3 shadow-sm">
        <div class="d-flex justify-content-between">
            <div>
                <h6>Spider-Man</h6>
                <small class="text-muted">Action</small>
            </div>
            <a href="showtime.php?movie=2" class="btn btn-primary btn-sm">View Showtimes</a>
        </div>
    </div>

</div>

<!--OFFCANVAS PROFILE -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="profileMenu">

    <div class="offcanvas-header">
        <h5 class="offcanvas-title">My Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body text-center">

        <!-- dynamic user data -->
        <h5><?= $_SESSION['full_name']; ?></h5>
        <p class="text-muted"><?= $_SESSION['email']; ?></p>

        <hr>

        <a href="profile.php" class="btn btn-light w-100 mb-2">View Profile</a>

        <a href="change_password.php" class="btn btn-light w-100 mb-2">Change Password</a>

        <a href="booking_history.php" class="btn btn-light w-100 mb-2">Booking History</a>

        <hr>

        <a href="auth/logout.php" class="btn btn-danger w-100">Sign Out</a>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>