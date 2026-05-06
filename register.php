<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - GSC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/GSC-Movie-ticket-Online-Booking-System/frontend/gsc-style.css">


    <style>
        /* Page Background (clean & bright) */
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background: linear-gradient(135deg, #f4edd9, #f9d59f);
            min-height: 100vh;
        }

        body {
            animation: fadeBg 2s ease;
        }

@keyframes fadeBg {
    from { opacity: 0; }
    to { opacity: 1; }
}

        /* Center Layout (fix navbar issue) */
        .main-container {
            min-height: calc(100vh - 70px); /* avoid navbar */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Register Card */
        .register-card {
            max-width: 420px;
            width: 100%;
            margin-top: -20px;

            padding: 30px;
            border-radius: 20px;
            border: none;

            background: rgba(255, 255, 255, 0.95);

            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }

        /* ===== Title ===== */
        h3 {
            font-weight: bold;
            color: #333;
        }

        /* ===== Input Fields ===== */
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
        }

        /* focus effect */
        .form-control:focus {
            border-color: #f5c518;
            box-shadow: 0 0 0 0.2rem rgba(245,197,24,0.25);
        }

        /* ===== Eye Button ===== */
       .toggle-btn {
            cursor: pointer;
            background: #fff;
            border-radius: 0 10px 10px 0;
        } 

        /* ===== Button ===== */
        .btn-warning {
            background-color: #f5c518;
            border: none;
            border-radius: 30px;
            padding: 12px;
            font-size: 18px;
            transition: 0.3s;
        }

        .btn-warning:hover {
            background-color: #e0b400;
            transform: scale(1.05);
        }

        /* ===== Link ===== */
        a {
            text-decoration: none;
            color: #555;
        }

        a:hover {
            color: #000;
        }
    </style>
</head>

<body>

<?php include 'includes/navbar.php'; ?>
<!-- Page Container -->
<div class="main-container">    

    <!-- Register Card -->
            <div class="card register-card">

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
                        <label class="form-label">Full Name</label>
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

            