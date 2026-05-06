<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background: 
                radial-gradient(circle at top, rgba(245,197,24,0.08), transparent 60%),
                linear-gradient(135deg, #f7f2e4, #fcebd3);

            min-height: 100vh;
        }

        body {
            animation: fadeBg 3s ease;
        }

        @keyframes fadeBg {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Center layout */
        .main-container {
            min-height: calc(100vh - 70px);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Card */
        .forgot-card {
            max-width: 380px;   /* 比 register 小一点 */
            width: 100%;

            padding: 35px 30px;
            border-radius: 20px;
            border: none;

            background: rgba(248, 247, 242, 0.9);

            box-shadow: 0 8px 25px rgba(0,0,0,0.08);

            backdrop-filter: blur(12px);
  
            animation: fadeIn 0.5s ease;
        }

        /* Title */
        h4 {
            font-weight: bold;
            color: #333;
        }

        /* Input */
        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: #f5c518;
            box-shadow: 0 0 0 0.2rem rgba(245,197,24,0.25);
        }

        /* Button */
        .btn-warning {
            background-color: #f5c518;
            border-radius: 30px;
            font-weight: 500;
        }

        .btn-warning:hover {
            background-color: #e0b400;
            transform: scale(1.05);
        }

        /* Link */
        a {
            text-decoration: none;
            color: #555;
         }

         a:hover {
            color: #000;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            margin: auto;

            background: #f5c518;
            color: #000;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 26px;

            box-shadow: 0 5px 15px rgba(245,197,24,0.4);
        }

        .custom-navbar {
            background: linear-gradient(to right, #1f2a33, #2c3e50);

            margin: 15px;              
            padding: 12px 20px;

            border-radius: 12px;      
    
            box-shadow: 0 8px 20px rgba(0,0,0,0.25); 
        }

        /* Button */
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

    </style>
</head>


<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-container">

    <div class="card forgot-card">

        <div class="text-center mb-3">
        <div class="icon-circle">🔐</div>
    </div>

        <h4 class="text-center mb-2">Forgot Password</h4>

        <p class="text-muted text-center mb-3">
            Enter your email and we’ll send you a reset link.
        </p>

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
        <form action="auth/forgot_password.php" method="POST">

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>

            <button type="submit" class="btn btn-warning w-100">
                Send Reset Link
            </button>

            <div class="text-center mt-3">
                <a href="login.php">Back to Sign In</a>
            </div>

        </form>

    </div>

</div>

</body>
</html>