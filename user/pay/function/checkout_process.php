<?php

require_once "checkout_init.php";
require_once "checkout_mail.php";
require_once "bank_config.php";

$isPaymentConfirmed = false;

function generateOrderCode($conn) {
    do {
        $order_code = 'DH' . date('YmdHis') . strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));

        $stmt = $conn->prepare("SELECT id FROM payment WHERE order_code = ? LIMIT 1");
        $stmt->bind_param("s", $order_code);
        $stmt->execute();

        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    } while ($exists);

    return $order_code;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name_post = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email_post = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone_post = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $address_post = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

    if (!$name_post || !$email_post || !$phone_post || !$address_post) {
        echo "Vui lòng điền đủ thông tin.";
        exit;
    }

    if (empty($itemsGrouped)) {
        echo "Giỏ hàng đang trống.";
        exit;
    }

    $order_code = generateOrderCode($conn);

    $conn->begin_transaction();

    try {

        // Kiểm tra tồn kho, chưa trừ kho
        foreach ($itemsGrouped as $item) {
            $stmtStock = $conn->prepare("
                SELECT quantity FROM product_inventory
                WHERE product_code = ? AND color = ?
                LIMIT 1
            ");

            $stmtStock->bind_param("ss", $item['product_code'], $item['color']);
            $stmtStock->execute();

            $inventory = $stmtStock->get_result()->fetch_assoc();
            $stmtStock->close();

            if (!$inventory) {
                throw new Exception(
                    "Sản phẩm " . htmlspecialchars($item['name']) .
                    " màu " . htmlspecialchars($item['color']) .
                    " không còn trong kho."
                );
            }

            if ($inventory['quantity'] < $item['quantity']) {
                throw new Exception(
                    "Sản phẩm " . htmlspecialchars($item['name']) .
                    " màu " . htmlspecialchars($item['color']) .
                    " chỉ còn " . $inventory['quantity'] . " sản phẩm."
                );
            }
        }

        $productCodesString = implode(', ', array_map(
            fn($i) => $i['product_code'],
            $itemsGrouped
        ));

        $productDetailsString = implode(', ', array_map(
            fn($i) => $i['name'] . " (x" . $i['quantity'] . ")",
            $itemsGrouped
        ));

        $productImagesString = implode(', ', array_map(
            fn($i) => basename($i['image']),
            $itemsGrouped
        ));

        $productCategoriesString = implode(', ', array_unique(array_map(
            fn($i) => $i['category'],
            $itemsGrouped
        )));

        $colorsString = implode(', ', array_map(
            fn($i) => $i['color'],
            $itemsGrouped
        ));

        $status = "Chờ xử lý";

        $stmt = $conn->prepare("
            INSERT INTO payment (
                order_code, customer_name, customer_email,
                customer_phone, customer_address, user_code,
                product_code, product_name, image,
                product_quantity, total_price,
                category, color, status
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssssssiisss",
            $order_code,
            $name_post,
            $email_post,
            $phone_post,
            $address_post,
            $user_code,
            $productCodesString,
            $productDetailsString,
            $productImagesString,
            $itemCount,
            $totalPrice,
            $productCategoriesString,
            $colorsString,
            $status
        );

        if (!$stmt->execute()) {
            throw new Exception("Lỗi lưu đơn hàng: " . $stmt->error);
        }

        $stmt->close();

        $conn->commit();

        $qr_url = "https://img.vietqr.io/image/" 
            . $bank_id . "-" . $account_no 
            . "-compact2.png?"
            . "amount=" . (int)$totalPrice 
            . "&addInfo=" . urlencode($order_code) 
            . "&accountName=" . urlencode($account_name);

        sendConfirmationEmail(
            $name_post,
            $email_post,
            $totalPrice,
            $itemsGrouped,
            $address_post,
            $user_code,
            $order_code
        );

        $isPaymentConfirmed = true;
        unset($_SESSION['cart']);

    } catch (Exception $e) {
        $conn->rollback();
        echo "Đặt hàng thất bại: " . $e->getMessage();
        exit;
    }
}

?>