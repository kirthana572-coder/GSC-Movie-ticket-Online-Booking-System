<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GSC-Movie-ticket-Online-Booking-System/frontend/gsc-style.css">
</head>

<body class="bg-light">
<?php include 'includes/navbar.php'; ?>
<div class="container d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow p-4" style="width: 400px;">

        <h4 class="text-center mb-3">Forgot Password</h4>

        <p class="text-muted text-center">
            Enter your email to receive reset link
        </p>

        <!-- ERROR -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- SUCCESS -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- FORM -->
        <form action="auth/forgot_password.php" method="POST">

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <button type="submit" class="btn btn-dark w-100">
                Send Reset Link
            </button>

            <div class="text-center mt-3">
                <a href="login.php">Back to Sign In</a>
            </div>

        </form>

    </div>

</div>

</body>
</html>