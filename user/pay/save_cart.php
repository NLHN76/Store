<?php
session_start();


if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Bạn cần đăng nhập để lưu giỏ hàng!";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart = json_decode(file_get_contents('php://input'), true);
    $_SESSION['cart'] = $cart; 
    http_response_code(200);
    echo "Đã lưu giỏ hàng thành công!";
}
?>
