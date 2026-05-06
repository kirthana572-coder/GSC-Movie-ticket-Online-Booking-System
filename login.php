<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign In</title>

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
.toggle-btn {
    cursor: pointer;
    border: 1px solid #ced4da;
    border-left: none;
    background: #fff;
}
</style>
</head>

<body>
<?php include 'includes/navbar.php'; ?>
<div class="login-card">

    <h3 class="title">Sign In</h3>

    <!-- ERROR -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- SUCCESS -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; ?>
        </div>
        <?php unset($_SESSION['success']); ?>
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
        <button type="submit" class="btn btn-dark w-100">Sign In</button>

        <div class="text-end mt-2">
            <a href="forgot_password.php">Forgot Password?</a>
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