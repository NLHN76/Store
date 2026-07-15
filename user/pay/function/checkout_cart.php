<?php
$isPaymentConfirmed = false;

if (!isset($_SESSION['user_id'])) {
    echo "Vui lòng đăng nhập để tiếp tục.";
    exit;
}

$user_id = $_SESSION['user_id'];

/* ===== CHỈ XỬ LÝ KHI BẤM XÁC NHẬN (POST) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $isPaymentConfirmed = true;

    // LẤY CART
    $stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $cart_id = $res->fetch_assoc()['id'];

        // XÓA SẢN PHẨM TRONG GIỎ
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();

        // XÓA CART
        $stmt = $conn->prepare("DELETE FROM carts WHERE id = ?");
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();
    }
}
?>