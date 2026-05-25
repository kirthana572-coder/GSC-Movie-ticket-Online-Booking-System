<?php
// 自動開啟 Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================
// 1. 自動偵測環境並設定資料庫連線
// ==========================================
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
    // 🏠 本地 XAMPP 環境
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "gsc_booking_db"; 
} else {
    // 🌐 InfinityFree 線上環境 (⚠️這裡必須改成你InfinityFree後台的資料⚠️)
$host = "sql213.infinityfree.com";
$user = "if0_41981522";
$password = "Xiang7556";       
$database = "if0_41981522_gsc";    // 替換為你的資料庫全名
}

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("資料庫連線失敗: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ==========================================
// 2. 自動計算專案根目錄路徑 (BASE_URL)
// ==========================================
if (!defined('BASE_URL')) {
    $project_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // 在 InfinityFree 根目錄時會自動清空，解決 404 問題
    $base_url = ($project_dir === '/' || $project_dir === '\\') ? '' : rtrim($project_dir, '/');
    define('BASE_URL', $base_url);
}
?>