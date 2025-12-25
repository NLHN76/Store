<?php


require_once "checkout_init.php";
require_once "checkout_mail.php";

$isPaymentConfirmed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_post    = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email_post   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone_post   = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $address_post = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

    if (!$name_post || !$email_post || !$phone_post || !$address_post) {
        echo "Vui lòng điền đủ thông tin.";
        exit;
    }

    // Bắt đầu transaction
    $conn->begin_transaction();
    try {
        // **Step 1: Kiểm tra tồn kho và khóa dòng**
        foreach ($itemsGrouped as $item) {
            $stmtStock = $conn->prepare("
                SELECT quantity 
                FROM product_inventory 
                WHERE product_code = ? AND color = ? FOR UPDATE
            ");
            $stmtStock->bind_param("ss", $item['product_code'], $item['color']);
            $stmtStock->execute();
            $stmtStock->bind_result($stock);
            if (!$stmtStock->fetch()) {
                throw new Exception("Sản phẩm " . htmlspecialchars($item['name']) . " màu " . htmlspecialchars($item['color']) . " không còn trong kho.");
            }
            $stmtStock->close();

            if ($stock < $item['quantity']) {
                throw new Exception("Sản phẩm " . htmlspecialchars($item['name']) . " màu " . htmlspecialchars($item['color']) . " chỉ còn " . $stock . " sản phẩm khả dụng.");
            }
        }

      // **Step 2: Trừ kho + Ghi lịch sử tồn kho**
foreach ($itemsGrouped as $item) {

    // Trừ kho
    $stmtUpdate = $conn->prepare("
        UPDATE product_inventory 
        SET quantity = quantity - ? 
        WHERE product_code = ? AND color = ?
    ");
    $stmtUpdate->bind_param("iss", $item['quantity'], $item['product_code'], $item['color']);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    // Lấy product_id từ products
    $stmtProdId = $conn->prepare("SELECT id FROM products WHERE product_code = ? LIMIT 1");
    $stmtProdId->bind_param("s", $item['product_code']);
    $stmtProdId->execute();
    $resProd = $stmtProdId->get_result()->fetch_assoc();
    $stmtProdId->close();

    if ($resProd) {
        $product_id = $resProd['id'];

        // Ghi lịch sử
        $note = "Trừ tồn kho khi đặt hàng (User: {$user_code})";
        $stmtHist = $conn->prepare("
            INSERT INTO inventory_history (product_id, product_code, color, quantity_change, import_price, type, note)
            VALUES (?, ?, ?, ?, 0, 'Bán hàng', ?)
        ");
        $stmtHist->bind_param("issis", $product_id, $item['product_code'], $item['color'], $item['quantity'], $note);
        $stmtHist->execute();
        $stmtHist->close();
    }
}


        // **Step 3: Lưu đơn hàng**
       // Gom dữ liệu sản phẩm
$productCodesString = implode(', ', array_map(
    fn($i) => $i['product_code'],
    $itemsGrouped
));

$productDetailsString = implode(', ', array_map(
    fn($i) => $i['name'] . " (x" . $i['quantity'] . ")",
    $itemsGrouped
));

// ⭐ Gom IMAGE (chỉ lấy tên file)
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

// Chuẩn bị câu INSERT (CÓ CỘT IMAGE)
$stmt = $conn->prepare("
    INSERT INTO payment 
    (customer_name, customer_email, customer_phone, customer_address, user_code,
     product_code, product_name, image, product_quantity, total_price, category, color)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Bind đúng số lượng & đúng thứ tự
$stmt->bind_param(
    "ssssssssiiss",
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

// Thực thi
if (!$stmt->execute()) {
    throw new Exception("Lỗi lưu đơn hàng: " . $stmt->error);
}

$stmt->close();

        // **Step 4: Gửi email xác nhận**
        sendConfirmationEmail($name_post, $email_post, $totalPrice, $itemsGrouped, $address_post, $user_code);

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