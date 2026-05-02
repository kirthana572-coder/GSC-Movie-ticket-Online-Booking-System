<!-- ================= REGISTER PAGE ================= -->
<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - GSC</title>

    <!-- Bootstrap CSS -->
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

<!-- Page Container -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <!-- Register Card -->
            <div class="card p-4 shadow">

                <h3 class="text-center mb-3">Register</h3>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <form action="auth/register.php" method="POST">

                    <!-- Full Name -->
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>

                    <!-- Password with toggle -->
                    <div class="mb-3">
                        <label>Password</label>
                        <div class="input-group">
                            <input type="password" id="password" class="form-control" name="password" required>
                            <span class="input-group-text toggle-btn"
                                  onclick="togglePassword('password', this)">
                                👁️
                            </span>
                        </div>
                    </div>

                    <!-- Confirm Password with toggle -->
                    <div class="mb-3">
                        <label>Confirm Password</label>
                        <div class="input-group">
                            <input type="password" id="confirmPassword" class="form-control" name="confirm_password" required>
                            <span class="input-group-text toggle-btn"
                                  onclick="togglePassword('confirmPassword', this)">
                                👁️
                            </span>
                        </div>
                    </div>

                    <!-- Register Button -->
                    <button type="submit" class="btn btn-warning w-100">Register</button>

                </form>

                <!-- Link to Sign In -->
                <div class="text-center mt-3">
                    <a href="signin.php">Already have account? Sign In</a>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- JavaScript: Toggle Password Function -->
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