<?php
require_once '../includes/auth_check.php';
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
    <title>My Profile - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body{
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: radial-gradient(circle at top, rgba(245,197,24,0.08), transparent 60%), linear-gradient(135deg, #f7f2e4, #fcebd3);
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
        max-width: 420px;
        width: 100%;
        padding: 35px;
        border-radius: 22px;
        border: none;
        background: rgba(253, 253, 246, 0.92);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .profile-avatar{
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #fceaa8, #ffffff);
        color: #000;
        font-size: 48px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: auto;
        border: 4px solid rgb(247, 235, 192);
        box-shadow: 0 5px 20px rgba(0,0,0,0.35);
        transition: 0.25s;
    }
    .profile-avatar:hover{ transform: scale(1.05); }
    h3{ font-weight:bold; color:#333; }
    .form-control{ border-radius: 12px; padding: 12px; border:1px solid #ddd; }
    .form-control:focus{ border-color:#f5c518; box-shadow: 0 0 0 0.2rem rgba(245,197,24,0.25); }
    .btn-warning{ background-color:#f5c518; border:none; border-radius:30px; padding:12px; font-size:17px; font-weight:700; transition:0.3s; width:100%;}
    .btn-warning:hover{ background-color:#e0b400; transform:scale(1.03); }
    .btn-outline-dark{ border-radius:30px; padding:12px; font-weight:700; text-align:center; display:block; text-decoration:none; color:#222; border:1px solid #222; transition:0.3s;}
    .btn-outline-dark:hover { background:#222; color:#fff; }
    label{ font-weight:500; margin-bottom:6px; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="main-container">
    <div class="card profile-card">
        <div class="text-center mb-3">
            <div class="profile-avatar mb-3">
                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
            </div>
        </div>
        <h3 class="text-center mb-4">My Profile</h3>
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
                <button type="submit" class="btn btn-warning">Update Profile</button>
                
                <a href="<?= BASE_URL ?>/change_password.php" class="btn btn-outline-dark">Change Password</a>
            </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/notification.js"></script>
</body>
</html>