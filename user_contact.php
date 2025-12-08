<?php
require_once "db.php";

// Kiểm tra đăng nhập (nếu muốn bắt buộc)
$userId = $_SESSION['user_id'] ?? null;

// Khi gửi form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("
        INSERT INTO contact (user_id, name, email, phone, message) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $userId, $name, $email, $phone, $message);

    if ($stmt->execute()) {
        http_response_code(200);
    } else {
        http_response_code(500);
        echo "Lỗi: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
