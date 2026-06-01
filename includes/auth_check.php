<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

if ($_SESSION['role'] !== 'customer') {
    header("Location: " . BASE_URL . "/index.php");
    exit();
}