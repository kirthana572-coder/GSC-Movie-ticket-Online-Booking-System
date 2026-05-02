<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../register.php");
    exit();
}

$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$confirm   = $_POST['confirm_password'] ?? '';

if (empty($full_name) || empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: ../register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email.";
    header("Location: ../register.php");
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters.";
    header("Location: ../register.php");
    exit();
}

if ($password !== $confirm) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: ../register.php");
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $_SESSION['error'] = "Email already registered.";
    $stmt->close();
    header("Location: ../register.php");
    exit();
}
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'customer';

$stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $full_name, $email, $hash, $role);

if ($stmt->execute()) {
    $_SESSION['success'] = "Registration successful! Please sign in.";
    header("Location: ../signin.php");
} else {
    $_SESSION['error'] = "Registration failed.";
    header("Location: ../register.php");
}

$stmt->close();
$conn->close();
?>