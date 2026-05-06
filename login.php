<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign In - GSC</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/GSC-Movie-ticket-Online-Booking-System/frontend/gsc-style.css">

<style>
    /* 只保留布局相关的样式，颜色交给 gsc-style.css */
    .login-card {
        width: 400px;
        margin: auto;
        margin-top: 100px;
        padding: 30px;
        border-radius: 10px;
    }
    .title {
        text-align: center;
        margin-bottom: 20px;
        font-weight: bold;
        color: #f5c518;
    }
    .toggle-btn {
        cursor: pointer;
        border: 1px solid #444;
        border-left: none;
        background: #2a2a2a;
        color: #e0e0e0;
    }
</style>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="login-card card shadow">

    <h3 class="title">Sign In</h3>

    <!-- ERROR -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- SUCCESS -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <!-- FORM -->
    <form action="auth/login.php" method="POST">

        <!-- Email -->
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label>Password</label>
            <div class="input-group">
                <input type="password" id="password" name="password" class="form-control" required>
                <span class="input-group-text toggle-btn" onclick="togglePassword(this)">👁️</span>
            </div>
        </div>

        <!-- Button -->
        <button type="submit" class="btn btn-warning w-100">Sign In</button>

        <div class="text-end mt-2">
            <a href="forgotpassword.php">Forgot Password?</a>
        </div>

        <hr>

        <p class="text-center">
            Don't have an account?
            <a href="register.php">Register</a>
        </p>

    </form>

</div>

<script>
function togglePassword(icon) {
    const password = document.getElementById("password");
    if (password.type === "password") {
        password.type = "text";
        icon.textContent = "🙈";
    } else {
        password.type = "password";
        icon.textContent = "👁️";
    }
}
</script>

</body>
</html>