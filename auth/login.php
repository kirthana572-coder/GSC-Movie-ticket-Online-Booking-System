<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Email and password required.";
    header("Location: ../login.php");
    exit();
}

// Get user from database
$stmt = $conn->prepare("
    SELECT id, full_name, email, password_hash, role 
    FROM users 
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit();
}

$user = $result->fetch_assoc();

//Check password
if (!password_verify($password, $user['password_hash'])) {
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit();
}

session_regenerate_id(true);

// Success
$_SESSION['user_id']   = $user['id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email']     = $user['email'];
$_SESSION['role']      = $user['role'];

$stmt->close();
$conn->close();

header("Location: ../index.php");
exit();
?>