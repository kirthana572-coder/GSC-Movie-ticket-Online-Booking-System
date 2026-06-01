<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';   // 确保 BASE_URL 可用

$stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['full_name'] ?? '');
    if (
        $new_name &&
        $new_name !== $user['full_name']
    ) {
        $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $_SESSION['user_id']);
        $stmt->execute();
        $_SESSION['full_name'] = $new_name;
        $user['full_name'] = $new_name;
        $msg = '
            <div class="alert alert-success">
                Profile updated successfully.
            </div>
            ';
    } else{

    $msg = '
    <div class="alert alert-info">
        No changes detected.
    </div>
    ';
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
            margin:0;
            font-family:'Segoe UI',sans-serif;
            background:
            linear-gradient(
                180deg,
                #faf8f2,
                #f3ede0
            );
            min-height:100vh;
        }
        
        .main-container{
            max-width:600px;
            margin:60px auto;
            padding:0 15px;
        }

        .profile-card{

            border:none;

            border-radius:24px;

            background:white;

            padding:35px;

            box-shadow:
            0 10px 30px rgba(0,0,0,.08);
        }

        .profile-avatar-large{

            width:100px;
            height:100px;

            border-radius:50%;

            background:#111827;

            color:white;

            font-size:38px;
            font-weight:700;

            margin:auto;

            display:flex;
            align-items:center;
            justify-content:center;
        }

        .profile-avatar-large:hover{ transform: scale(1.05); }
        h3{ font-weight:bold; color:#333; }
        .form-control{ border-radius: 12px; padding: 12px; border:1px solid #ddd; }
        .form-control:focus{ border-color:#f5c518; box-shadow: 0 0 0 0.2rem rgba(245,197,24,0.25); }
        .btn-warning{ background-color:#f5c518; border:none; border-radius:30px; padding:12px !important; font-size:17px; font-weight:700; transition:0.3s; width:100%;}
        .btn-warning:hover{ background-color:#e0b400; transform:scale(1.03); }
        .btn-outline-dark{ border-radius:30px; padding:12px; font-weight:700; text-align:center; display:block; text-decoration:none; color:#222; border:1px solid #222; transition:0.3s;}
        .btn-outline-dark:hover { background:#222; color:#fff; }
        label{ font-weight:500; margin-bottom:6px; }

        .btn-warning:disabled{

            background:#d1d5db !important;

            color:#6b7280 !important;

            cursor:not-allowed;

            opacity:1;

            transform:none;
        }
    </style>
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="main-container">
    <div class="card profile-card">
        <div class="text-center mb-3">
            <div class="profile-avatar-large mb-3">
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
                <button
                    type="submit"
                    class="btn btn-warning"
                    id="saveBtn"
                    disabled>

                    Save Changes

                </button>
                <a href="<?= BASE_URL ?>/change_password.php" class="btn btn-outline-dark">Change Password</a>
            </div>
        </form>
    </div>
</div>

<script>

const nameInput = document.querySelector('input[name="full_name"]');
const saveBtn = document.getElementById('saveBtn');

const originalName = nameInput.value.trim();

nameInput.addEventListener('input', () => {

    const currentName = nameInput.value.trim();

    saveBtn.disabled = (currentName === originalName);

});

</script>

<script src="<?= BASE_URL ?>/notification.js"></script>
</body>
</html>