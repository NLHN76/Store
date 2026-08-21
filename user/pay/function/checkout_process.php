<?php

require_once "checkout_init.php";
require_once "checkout_mail.php";

$isPaymentConfirmed = false;

function generateOrderCode($conn)
{
    do {
        $order_code = 'DH' . date('YmdHis') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

        $stmt = $conn->prepare("
            SELECT id FROM payment
            WHERE order_code = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $order_code);
        $stmt->execute();

        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;

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

    $order_code = generateOrderCode($conn);

    $conn->begin_transaction();

    try {

        foreach ($itemsGrouped as $item) {
            $stmtStock = $conn->prepare("
                SELECT quantity
                FROM product_inventory
                WHERE product_code = ? AND color = ?
                FOR UPDATE
            ");

            $stmtStock->bind_param(
                "ss",
                $item['product_code'],
                $item['color']
            );

            $stmtStock->execute();
            $stmtStock->bind_result($stock);

            if (!$stmtStock->fetch()) {
                $stmtStock->close();

                throw new Exception(
                    "Sản phẩm " . htmlspecialchars($item['name']) .
                    " màu " . htmlspecialchars($item['color']) .
                    " không còn trong kho."
                );
            }

            $stmtStock->close();

            if ($stock < $item['quantity']) {
                throw new Exception(
                    "Sản phẩm " . htmlspecialchars($item['name']) .
                    " màu " . htmlspecialchars($item['color']) .
                    " chỉ còn " . $stock . " sản phẩm khả dụng."
                );
            }
        }

        foreach ($itemsGrouped as $item) {

            $stmtUpdate = $conn->prepare("
                UPDATE product_inventory
                SET quantity = quantity - ?
                WHERE product_code = ? AND color = ?
            ");

            $stmtUpdate->bind_param(
                "iss",
                $item['quantity'],
                $item['product_code'],
                $item['color']
            );

            $stmtUpdate->execute();
            $stmtUpdate->close();

            $stmtProd = $conn->prepare("
                SELECT id
                FROM products
                WHERE product_code = ?
                LIMIT 1
            ");

            $stmtProd->bind_param(
                "s",
                $item['product_code']
            );

            $stmtProd->execute();
            $product = $stmtProd->get_result()->fetch_assoc();
            $stmtProd->close();

            if ($product) {

                $product_id = $product['id'];

                $stmtPrice = $conn->prepare("
                    SELECT import_price, sale_price
                    FROM product_inventory
                    WHERE product_code = ? AND color = ?
                    LIMIT 1
                ");

                $stmtPrice->bind_param(
                    "ss",
                    $item['product_code'],
                    $item['color']
                );

                $stmtPrice->execute();
                $price = $stmtPrice->get_result()->fetch_assoc();
                $stmtPrice->close();

                $import_price = $price['import_price'] ?? 0;
                $sale_price = $price['sale_price'] ?? 0;

                $note = "Trừ tồn kho khi đặt hàng (User: {$user_code}, Mã đơn: {$order_code})";

                $stmtHist = $conn->prepare("
                    INSERT INTO inventory_history
                    (
                        product_id,
                        product_code,
                        color,
                        quantity_change,
                        import_price,
                        sale_price,
                        type,
                        note
                    )
                    VALUES (?, ?, ?, ?, ?, ?, 'Bán hàng', ?)
                ");

                $stmtHist->bind_param(
                    "issidds",
                    $product_id,
                    $item['product_code'],
                    $item['color'],
                    $item['quantity'],
                    $import_price,
                    $sale_price,
                    $note
                );

                $stmtHist->execute();
                $stmtHist->close();
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

        $stmt = $conn->prepare("
            INSERT INTO payment
            (
                order_code,
                customer_name,
                customer_email,
                customer_phone,
                customer_address,
                user_code,
                product_code,
                product_name,
                image,
                product_quantity,
                total_price,
                category,
                color
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssssssiiss",
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
            $colorsString
        );

        if (!$stmt->execute()) {
            throw new Exception("Lỗi lưu đơn hàng: " . $stmt->error);
        }

        $stmt->close();

        sendConfirmationEmail(
            $name_post,
            $email_post,
            $totalPrice,
            $itemsGrouped,
            $address_post,
            $user_code,
            $order_code
        );

        $conn->commit();

        $isPaymentConfirmed = true;
        unset($_SESSION['cart']);

    } catch (Exception $e) {

        $conn->rollback();

        echo "Đặt hàng thất bại: " . $e->getMessage();
        exit;
    }
}

?>