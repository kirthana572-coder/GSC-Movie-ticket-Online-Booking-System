<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Staff Login - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f4edd9, #f9d59f); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { max-width: 400px; border-radius: 20px; background: rgba(255,255,255,0.95); padding: 30px; }
        .btn-warning { background: #f5c518; border-radius: 30px; }
    </style>
</head>
<body>
<div class="card">
    <h3 class="text-center">Staff Sign In</h3>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <form action="../auth/staff_login.php" method="POST">
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-warning w-100">Sign In</button>
    </form>
</div>
</body>
</html>