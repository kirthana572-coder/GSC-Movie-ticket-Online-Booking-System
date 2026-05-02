<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Change Password - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4" style="width: 400px;">
        <h4 class="text-center mb-3">Change Password</h4>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form action="auth/change_password.php" method="POST">
            <!-- New Password -->
            <div class="mb-3">
                <label>New Password</label>
                <div class="input-group">
                    <input type="password" id="newPassword" name="new_password" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword', this)">👁</button>
                </div>
            </div>
            <!-- Confirm Password -->
            <div class="mb-3">
                <label>Confirm Password</label>
                <div class="input-group">
                    <input type="password" id="confirmPassword" name="confirm_password" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword', this)">👁</button>
                </div>
            </div>
            <button type="submit" class="btn btn-dark w-100">Update Password</button>
            <div class="text-center mt-3">
                <a href="index.php">Back to Home</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId, btn) {
    let input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "🙈";
    } else {
        input.type = "password";
        btn.textContent = "👁";
    }
}
</script>

</body>
</html>