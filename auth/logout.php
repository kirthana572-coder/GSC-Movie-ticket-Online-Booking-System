<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/db.php';   // 引入 BASE_URL 定义

session_destroy();
header("Location: " . BASE_URL . "/index.php");
exit();
?>