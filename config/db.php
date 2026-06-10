<?php
// 自动开启 Session（如果尚未开启）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 错误报告（本地开启，线上可注释）
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ==========================================
// 1. 自动侦测环境并设定数据库连接
// ==========================================
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
    // 本地 XAMPP
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "gsc_booking_db";
} else {
    // 线上 InfinityFree（请改为你的实际数据库信息）
    $host = "sql213.infinityfree.com";
    $user = "if0_41981522";
    $password = "Xiang7556";
    $database = "if0_41981522_gsc";
}

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ==========================================
// 2. 手动设置 BASE_URL（根据环境）
// ==========================================
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
    // 本地 XAMPP：你的项目在 htdocs 下的子目录名
    define('BASE_URL', '/GSC-Movie-ticket-Online-Booking-System');
} else {
    // 线上环境：如果项目在根目录，留空；否则填子目录名（如 /gsc）
    define('BASE_URL', '');
}

// ==========================================
// 3. 邮件 SMTP 配置（请务必修改成你自己的）
// ==========================================
define('SMTP_HOST', 'smtp.gmail.com');      // 你的 SMTP 服务器
define('SMTP_PORT', 587);
define('SMTP_USER', 'gscmovieticketonlinebookingsys@gmail.com'); // 发件邮箱（替换）
define('SMTP_PASS', 'dfdajqrfbilgylme');    // 邮箱密码或授权码（替换）
define('SMTP_FROM', 'gscmovieticketonlinebookingsys@gmail.com');
define('SMTP_FROM_NAME', 'GSC Cinema');
?>