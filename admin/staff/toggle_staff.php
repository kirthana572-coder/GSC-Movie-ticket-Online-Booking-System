<?php

require_once '../../includes/admin_auth.php';
require_once '../../config/db.php';

header('Content-Type: application/json');

$user_id = intval($_POST['id'] ?? 0);

if (!$user_id) {

    echo json_encode([
        'success' => false
    ]);

    exit();
}

if ($_SESSION['user_id'] == $user_id) {

    echo json_encode([
        'success' => false,
        'message' => 'You cannot change your own status.'
    ]);

    exit();
}

$stmt = $conn->prepare("
    SELECT status, role
    FROM users
    WHERE id = ?
    AND role IN ('staff', 'admin')
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    echo json_encode([
        'success' => false
    ]);

    exit();
}

$user = $result->fetch_assoc();

$new_status =
    ($user['status'] === 'active')
    ? 'inactive'
    : 'active';

$update = $conn->prepare("
    UPDATE users
    SET status = ?
    WHERE id = ?
");

$update->bind_param(
    "si",
    $new_status,
    $user_id
);

$update->execute();

echo json_encode([
    'success' => true,
    'new_status' => $new_status
]);

exit();