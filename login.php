<?php session_start(); ?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign In - GSC</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>

        body{
            margin: 0;
            font-family: 'Segoe UI', sans-serif;

            background:
            linear-gradient(
                135deg,
                #f4edd9,
                #f9d59f
            );

            min-height: 100vh;
        }

        body{
            animation: fadeBg 1.5s ease;
        }

        @keyframes fadeBg{
            from{ opacity:0; }
            to{ opacity:1; }
        }

        .main-container{

            min-height: calc(100vh - 70px);

            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-card{

            max-width: 420px;
            width: 100%;

            padding: 32px;

            border-radius: 20px;
            border: none;

            background: rgba(255,255,255,0.95);

            box-shadow:
            0 10px 30px rgba(0,0,0,0.1);

            backdrop-filter: blur(10px);
        }

        h3{
            font-weight: bold;
            color: #333;
        }

        .form-control{

            border-radius: 10px;

            padding: 12px;

            border: 1px solid #ddd;
        }

        .form-control:focus{

            border-color: #f5c518;

            box-shadow:
            0 0 0 0.2rem rgba(245,197,24,0.25);
        }

        .toggle-btn{

            cursor: pointer;

            background: #fff;

            border-radius: 0 10px 10px 0;
        }

        .btn-warning{

            background-color: #f5c518;

            border: none;

            border-radius: 30px;

            padding: 12px;

            font-size: 18px;

            transition: 0.3s;
        }

        .btn-warning:hover{

            background-color: #e0b400;

            transform: scale(1.03);
        }

        a{

            text-decoration: none;

            color: #555;
        }

        a:hover{
            color: #000;
        }

    </style>
</head>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="main-container">

    <div class="card login-card">

        <h3 class="text-center mb-3">
            Sign In
        </h3>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['success']); ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <form action="auth/login.php" method="POST">

            <!-- Email -->
            <div class="mb-3">

                <label class="form-label">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    required
                >

            </div>

            <!-- Password -->
            <div class="mb-3">

                <label class="form-label">
                    Password
                </label>

                <div class="input-group">

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        required
                    >

                    <span
                        class="input-group-text toggle-btn"
                        onclick="togglePassword()"
                    >
                        👁️
                    </span>

                </div>

            </div>

            <div class="text-end mb-3">

                <a href="forgotpassword.php">
                    Forgot password?
                </a>

            </div>

            <button type="submit" class="btn btn-warning w-100">

                Sign In

            </button>

        </form>

        <div class="text-center mt-3">

            <a href="register.php">
                Don't have an account? Register
            </a>

        </div>

    </div>

</div>

<script>

function togglePassword(){

    const field =
        document.getElementById('password');

    if(field.type === "password"){

        field.type = "text";

    }else{

        field.type = "password";
    }
}

</script>

</body>
</html>