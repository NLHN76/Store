<?php
session_start();

// ✅ Kiểm tra người dùng đã login chưa
if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Bạn cần đăng nhập để lưu giỏ hàng!";
    exit;
}

// Chỉ xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart = json_decode(file_get_contents('php://input'), true);
    $_SESSION['cart'] = $cart; // Lưu giỏ hàng vào session
    http_response_code(200);
    echo "Đã lưu giỏ hàng thành công!";
}
?>
