<?php
require_once "../../db.php";

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Người dùng chưa đăng nhập']);
    exit;
}


// Lấy ID người dùng từ session
$userId = $_SESSION['user_id'];

// Lấy thông tin người dùng
$query = "SELECT name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

// Trả về dữ liệu dưới dạng JSON
if ($user) {
    echo json_encode($user);
} else {
    echo json_encode(['error' => 'Không tìm thấy người dùng']);
}
?>