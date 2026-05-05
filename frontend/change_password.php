<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow p-4" style="width: 400px;">
        <h4 class="text-center mb-3">Change Password</h4>

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
        <form action="auth/changepassword.php" method="POST">

            <!-- New Password -->
            <div class="mb-3">
                <label>New Password</label>
                <div class="input-group">
                    <input type="password" name="new_password" id="newPassword" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('newPassword', this)">👁</button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3">
                <label>Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPassword', this)">👁</button>
                </div>
            </div>

            <button type="submit" class="btn btn-dark w-100">
                Update Password
            </button>

            <div class="text-center mt-3">
                <a href="profile.php">Back to Profile</a>
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