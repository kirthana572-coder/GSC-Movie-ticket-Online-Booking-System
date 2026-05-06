<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GSC-Movie-ticket-Online-Booking-System/frontend/gsc-style.css">
    <style>
        /* 只保留布局相关，颜色交给 gsc-style.css */
        .change-pwd-card {
            width: 400px;
            margin: auto;
            margin-top: 100px;
            padding: 30px;
            border-radius: 10px;
        }
        .change-pwd-card .card-title {
            color: #f5c518;
            font-weight: bold;
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

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="change-pwd-card card shadow p-4">

        <h4 class="text-center mb-3 card-title">Change Password</h4>

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
        <form action="auth/change_password.php" method="POST">

            <!-- New Password -->
            <div class="mb-3">
                <label>New Password</label>
                <div class="input-group">
                    <input type="password" name="new_password" id="newPassword" class="form-control" required>
                    <span class="input-group-text toggle-btn" onclick="togglePassword('newPassword', this)">👁</span>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3">
                <label>Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control" required>
                    <span class="input-group-text toggle-btn" onclick="togglePassword('confirmPassword', this)">👁</span>
                </div>
            </div>

            <button type="submit" class="btn btn-warning w-100">
                Update Password
            </button>

            <div class="text-center mt-3">
                <a href="customer/profile.php">Back to Profile</a>
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