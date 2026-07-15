<?php
require_once "config.php";

if (!$user_id) {
    exit("Chưa đăng nhập!");
}

$message = trim($_POST['message'] ?? '');
if ($message === '') {
    exit("Tin nhắn trống!");
}

$stmt = $conn->prepare("
    INSERT INTO message (user_id, user_name, sender_role, content)
    VALUES (?, ?, 'user', ?)
");
$stmt->bind_param("iss", $user_id, $user_name, $message);
$stmt->execute();
$stmt->close();

echo "OK";
$conn->close();
