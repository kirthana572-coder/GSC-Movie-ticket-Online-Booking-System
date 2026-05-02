<?php
// config/db.php
$host = "localhost";
$user = "root";
$password = "";        // XAMPP默认密码为空
$database = "gsc_booking_db";

// 创建连接
$conn = new mysqli($host, $user, $password, $database);

// 检查连接
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>