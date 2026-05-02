<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="width: 400px;">
        <h4 class="text-center mb-3">Forgot Password</h4>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <p class="text-muted text-center">Enter your email to receive a reset link</p>

        <form action="auth/forgot_password.php" method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn btn-dark w-100">Send Reset Link</button>
            <div class="text-center mt-3">
                <a href="signin.php">Back to Sign In</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>