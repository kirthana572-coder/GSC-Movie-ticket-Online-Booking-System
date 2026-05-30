<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

header('Content-Type: application/json');

// 只接受 POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// 获取数据
$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user ID'
    ]);
    exit;
}

// 查当前 status
$stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    exit;
}

$user = $result->fetch_assoc();
$current_status = $user['status'];

// oggle status
$new_status = ($current_status === 'active') ? 'inactive' : 'active';

//update DB
$update = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
$update->bind_param("si", $new_status, $id);
$update->execute();

// return JSON
echo json_encode([
    'success' => true,
    'new_status' => $new_status
]);