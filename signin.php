<!-- ================= SIGN IN PAGE ================= -->
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign In</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background: #f2f2f2;
    }

    .login-card {
        width: 400px;
        margin: auto;
        margin-top: 100px;
        padding: 30px;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    .title {
        text-align: center;
        margin-bottom: 20px;
        font-weight: bold;
    }

    /* Eye toggle button style */
    .toggle-btn {
        cursor: pointer;
        border: 1px solid #ced4da;
        border-left: none;
        background: #fff;
    }
</style>
</head>

<body>

<!-- Login Card Container -->
<div class="login-card">

    <h3 class="title">Sign In</h3>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form action="auth/login.php" method="POST">

        <!-- Email Input -->
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" placeholder="Enter email" required>
        </div>

        <!-- Password Input with Show/Hide -->
        <div class="mb-3">
            <label class="form-label">Password</label>
            <div class="input-group">
                <input type="password" id="password" class="form-control" name="password" placeholder="Enter password" required>
                <span class="input-group-text toggle-btn" onclick="togglePassword()">
                    👁️
                </span>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-dark w-100">Sign In</button>

        <!-- Forgot Password Link -->
        <div class="text-end mt-2">
            <a href="forgotpassword.html" style="font-size: 14px;">Forgot Password?</a>
        </div>

        <hr>

        <!-- Register Link -->
        <p class="text-center" style="font-size: 14px;">
            Don't have an account?
            <a href="register.php">Register</a>
        </p>

    </form>

</div>

<!-- JavaScript: Toggle Password -->
<script>
function togglePassword() {
    const password = document.getElementById("password");
    const icon = event.target;
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