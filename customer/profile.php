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
    <title>Profile - GSC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../includes/navbar.php'; ?>
<div class="container mt-4">
    <h2>My Profile</h2>
    <?= $msg ?? '' ?>
    <form method="POST">
        <input type="text" name="full_name" class="form-control mb-3" value="<?= htmlspecialchars($user['full_name']) ?>" required>
        <input type="email" class="form-control mb-3" value="<?= $user['email'] ?>" disabled>
        <button class="btn btn-warning">Update</button>
        <a href="/GSC-Movie-ticket-Online-Booking-System/changepassword.php" class="btn btn-outline-dark ms-2">Change Password</a>
    </form>
</div>
</body>
</html>