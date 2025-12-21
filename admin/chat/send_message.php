<?php
require_once "../../db.php";
$message = trim($_POST['message'] ?? '');
$user_id = intval($_POST['user_id'] ?? 0);

if (!$message || !$user_id) {
    exit("Tin nhắn trống hoặc thiếu user!");
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

$stmt = $conn->prepare("
    INSERT INTO message (user_id, user_name, sender_role, content)
    VALUES (?, ?, 'admin', ?)
");
$stmt->bind_param("iss", $user_id, $admin_name, $message);
$stmt->execute();
$stmt->close();

exit("OK");
