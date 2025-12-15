<?php
require_once '../db.php'; // Kết nối database và session

header('Content-Type: application/json');

// Kiểm tra user đã đăng nhập
if(!isset($_SESSION['user_code'])){
    echo json_encode(['error' => 'Chưa đăng nhập']);
    exit();
}

$user_code = $_SESSION['user_code'];

// Lấy thông tin user từ user_profile
$sql = "SELECT * FROM user_profile WHERE user_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_code);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if($user){
    echo json_encode($user);
} else {
    echo json_encode(['error' => 'Không tìm thấy thông tin người dùng']);
}
?>
