<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Sign In - GSC Cinema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- 引入 particles.js -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', 'Poppins', sans-serif;
            min-height: 100vh;
            background: #0a0f1e;
            overflow: hidden;
            position: relative;
        }
        /* 粒子背景容器 */
        #particles-js {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            background: linear-gradient(135deg, #0a0f1e 0%, #141b2b 100%);
        }
        /* 主内容区浮层 */
        .login-wrapper {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
            padding: 20px;
        }
        .login-card {
            background: rgba(20, 25, 40, 0.75);
            backdrop-filter: blur(12px);
            border-radius: 48px;
            padding: 45px 40px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 45px rgba(0,0,0,0.3), 0 0 0 1px rgba(255,255,255,0.1);
            transition: transform 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            animation: floatIn 0.8s ease-out;
        }
        .login-card:hover {
            transform: translateY(-8px);
        }
        @keyframes floatIn {
            0% {
                opacity: 0;
                transform: translateY(40px) scale(0.96);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .logo {
            text-align: center;
            font-size: 52px;
            font-weight: 800;
            background: linear-gradient(135deg, #f5c518, #ffdd66);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .tagline {
            text-align: center;
            color: #ccddee;
            margin-bottom: 32px;
            font-size: 14px;
            font-weight: 300;
            letter-spacing: 1px;
        }
        .form-label {
            color: #eef4ff;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-control {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 40px;
            padding: 12px 20px;
            color: #fff;
            font-size: 15px;
            transition: all 0.3s;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.18);
            border-color: #f5c518;
            box-shadow: 0 0 0 3px rgba(245,197,24,0.3);
            color: #fff;
        }
        .form-control::placeholder {
            color: rgba(255,255,255,0.5);
        }
        .input-group-text {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.25);
            border-left: none;
            border-radius: 0 40px 40px 0;
            color: #fff;
            cursor: pointer;
            transition: 0.2s;
        }
        .input-group-text:hover {
            background: rgba(245,197,24,0.3);
        }
        .btn-signin {
            background: linear-gradient(90deg, #f5c518, #ffde6e);
            border: none;
            border-radius: 40px;
            padding: 12px;
            font-weight: 700;
            font-size: 18px;
            width: 100%;
            color: #1a1a2e;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(245,197,24,0.3);
        }
        .btn-signin:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(245,197,24,0.5);
            filter: brightness(1.05);
        }
        .forgot-link {
            text-align: right;
            margin-top: 12px;
        }
        .forgot-link a {
            color: #bbccff;
            text-decoration: none;
            font-size: 13px;
            transition: 0.2s;
        }
        .forgot-link a:hover {
            color: #f5c518;
        }
        .register-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.15);
        }
        .register-link a {
            color: #f5c518;
            text-decoration: none;
            font-weight: 600;
        }
        .alert {
            border-radius: 40px;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            border: none;
            color: #fff;
            font-size: 13px;
        }
        .alert-danger {
            border-left: 4px solid #ff6b6b;
        }
        .alert-success {
            border-left: 4px solid #4cd964;
        }
        /* 响应式 */
        @media (max-width: 500px) {
            .login-card {
                padding: 35px 25px;
                margin: 20px;
            }
            .logo {
                font-size: 42px;
            }
        }
    </style>
</head>
<body>

<div id="particles-js"></div>

<div class="login-wrapper">
    <div class="login-card">
        <div class="logo">
            🎬 GSC CINEMA
        </div>
        <div class="tagline">
            Experience the magic of movies
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form action="auth/login.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="your@email.com" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <span class="input-group-text" onclick="togglePassword()">
                        <i class="far fa-eye-slash" id="toggleIcon"></i>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn-signin">
                Sign In <i class="fas fa-arrow-right ms-2"></i>
            </button>
            <div class="forgot-link">
                <a href="forgotpassword.php">Forgot password?</a>
            </div>
            <div class="register-link">
                New here? <a href="register.php">Create an account</a>
            </div>
        </form>
    </div>
</div>

<script>
    // 粒子配置
    particlesJS("particles-js", {
        "particles": {
            "number": {
                "value": 120,
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#f5c518"
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                },
                "polygon": {
                    "nb_sides": 5
                }
            },
            "opacity": {
                "value": 0.4,
                "random": true,
                "anim": {
                    "enable": true,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 3,
                "random": true,
                "anim": {
                    "enable": false,
                    "speed": 40,
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#f5c518",
                "opacity": 0.2,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 3,
                "direction": "none",
                "random": false,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                    "enable": false,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "grab"
                },
                "onclick": {
                    "enable": true,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 140,
                    "line_linked": {
                        "opacity": 0.8
                    }
                },
                "push": {
                    "particles_nb": 4
                }
            }
        },
        "retina_detect": true
    });

    function togglePassword() {
        const pwd = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (pwd.type === 'password') {
            pwd.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            pwd.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
</script>
</body>
</html>