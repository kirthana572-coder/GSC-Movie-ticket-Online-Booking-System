<?php
require_once '../includes/staff_auth.php';
require_once '../config/db.php';

$stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['full_name'] ?? '');
    if ($new_name) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $_SESSION['user_id']);
        $stmt->execute();
        $_SESSION['full_name'] = $new_name;
        $user['full_name'] = $new_name;
        $msg = '<div class="alert alert-success">Updated.</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Profile - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body{
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(rgba(245,242,234,0.92), rgba(255,220,164,0.92));
        min-height: 100vh;
        animation: fadeBg 2s ease;
    }
    @keyframes fadeBg {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .main-container{
        min-height: calc(100vh - 70px);
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .profile-card{
        max-width: 500px;
        width: 100%;
        padding: 40px;
        border-radius: 28px;
        border: none;
        margin-bottom: -150px;
        background: rgba(255,255,255,0.78);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .profile-avatar{
        width: 120px !important;
        height: 120px !important;
        border-radius: 50% !important;
        background: linear-gradient(135deg, #fceaa8, #ffffff) !important;
        color: #000000 !important;
        font-size: 48px !important;
        font-weight: bold !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: auto !important;
        border: 4px solid rgb(247, 235, 192) !important;
        box-shadow: 0 5px 20px rgba(0,0,0,0.35) !important;
        transition: 0.25s;
    }
    .profile-avatar:hover{ transform: scale(1.05); }
    h3{ font-weight:bold; color:#333; }
    .form-control{ border-radius: 12px; padding: 12px; border:1px solid #ddd; }
    .form-control:focus{ border-color:#f5c518; box-shadow: 0 0 0 0.2rem rgba(245,197,24,0.25); }
    .btn-warning{
        background: #ffd53b !important;
        border: none !important;
        color: #111 !important;
        border-radius: 14px;
        padding: 14px;
        font-size: 17px;
        font-weight: 700;
        transition: 0.25s;
    }
    .btn-warning:hover{ background: #ffdc5f !important; transform: scale(1.02); }
    .btn-outline-dark{ border-radius:14px; padding:14px; font-weight: 700; text-align:center; text-decoration:none; color:#222; border:1px solid #222; transition:0.3s; display:block; }
    .btn-outline-dark:hover{ background:#222; color:#fff; }
    label{ font-weight:500; margin-bottom:6px; }
    .top-bar{ position: absolute; top: 50px; left: 500px; }
    .back-btn{ display: inline-block; text-decoration: none; background: #ffd500; color: #353535; padding: 8px 16px; border-radius: 12px; font-weight: 600; transition: 0.25s; }
    .back-btn:hover{ background: #f5c518; color: #111; transform: translateY(-2px); }
    </style>
</head>
<body>

<div class="top-bar">
    <a href="staff_dashboard.php" class="back-btn">← Back Dashboard</a>
</div>

<div class="main-container">
    <div class="card profile-card">
        <div class="text-center mb-3">
            <div class="profile-avatar mx-auto mb-3">
                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
            </div>
        </div>
        <h3 class="text-center mb-4">Staff Profile</h3>
        <?= $msg ?? '' ?>
        
        <form method="POST">
            <div class="mb-3">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            <div class="mb-4">
                <label>Email</label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
            </div>
            <div class="d-grid gap-2">
                <button class="btn btn-warning">Update Profile</button>
                <a href="<?= BASE_URL ?>/staff/change_password.php" class="btn-outline-dark">Change Password</a>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/notification.js"></script>
</body>
</html>