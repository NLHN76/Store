<?php


if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("Vui lòng đăng nhập để tiếp tục.");
}

$user_id = $_SESSION['user_id'];
$isPaymentConfirmed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $conn->begin_transaction();

    try {

        /* ===== 1. LẤY CART CỦA USER ===== */
        $stmt = $conn->prepare(
            "SELECT id FROM carts WHERE user_id = ? LIMIT 1"
        );
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cartResult = $stmt->get_result();

        if ($cartResult->num_rows === 0) {
            throw new Exception("Không tìm thấy giỏ hàng.");
        }

        $cart_id = $cartResult->fetch_assoc()['id'];

        /* ===== 2. XÓA ITEM TRONG CART (KHÔNG XÓA CART) ===== */
        $stmt = $conn->prepare(
            "DELETE FROM cart_items WHERE cart_id = ?"
        );
        $stmt->bind_param("i", $cart_id);
        $stmt->execute();

        /* ===== 3. COMMIT ===== */
        $conn->commit();
        $isPaymentConfirmed = true;

    } catch (Exception $e) {

        $conn->rollback();
        http_response_code(500);
        exit("Lỗi xử lý thanh toán: " . $e->getMessage());
    }
}
?>
