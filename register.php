<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - GSC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
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

<?php include 'includes/navbar.php'; ?>
<!-- Page Container -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <!-- Register Card -->
            <div class="card p-4 shadow">

                <h3 class="text-center mb-3">Register</h3>

        
                <!-- SHOW ERROR -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?php echo $_SESSION['error']; ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- SHOW SUCCESS -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>


                <!-- connect to PHP -->
                <form action="auth/register.php" method="POST">

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>

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
                            <span class="input-group-text toggle-btn" onclick="togglePassword('password', this)">👁️</span>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label>Confirm Password</label>
                        <div class="input-group">
                            <input type="password" id="confirmPassword" name="confirm_password" class="form-control" required>
                            <span class="input-group-text toggle-btn" onclick="togglePassword('confirmPassword', this)">👁️</span>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-warning w-100">
                        Register
                    </button>

                </form>

                <div class="text-center mt-3">
                    <a href="login.php">Already have account? Sign In</a>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- Toggle Password -->
<script>
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);

    if (field.type === "password") {
        field.type = "text";
        icon.textContent = "🙈";
    } else {
        field.type = "password";
        icon.textContent = "👁️";
    }
}
</script>

</body>
</html>