<?php
$isPaymentConfirmed = false;

if (!isset($_SESSION['user_id'])) {
    echo "Vui lòng đăng nhập để tiếp tục.";
    exit;
}

$user_id = $_SESSION['user_id'];

// ID sản phẩm đang mua (bắt buộc phải có)
$current_product_id = $_POST['product_id'] ?? null;

if (!$current_product_id) {
    echo "Thiếu thông tin sản phẩm.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $isPaymentConfirmed = true;

    // 1️⃣ LẤY CART CỦA USER
    $stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $cart_id = $res->fetch_assoc()['id'];

        // 2️⃣ KIỂM TRA CART CÓ SẢN PHẨM KHÁC KHÔNG TRÙNG PRODUCT ĐANG MUA
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS total
            FROM cart_items
            WHERE cart_id = ?
              AND product_id != ?
        ");
        $stmt->bind_param("ii", $cart_id, $current_product_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        // 👉 Nếu tồn tại sản phẩm KHÁC sản phẩm đang mua
        if ($result['total'] > 0) {

            // 3️⃣ XÓA CART HIỆN TẠI
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();

            $stmt = $conn->prepare("DELETE FROM carts WHERE id = ?");
            $stmt->bind_param("i", $cart_id);
            $stmt->execute();
        }
    }
}
?>
