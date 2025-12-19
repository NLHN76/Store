<?php
// Lấy thông tin user
$name = $email = $phone = $address = $user_code = '';
$isPaymentConfirmed = false;

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_code'])) {
    echo "Vui lòng đăng nhập để tiếp tục.";
    exit;
}

$user_code = $_SESSION['user_code'];

// Lấy thông tin từ bảng user_profile
$stmt = $conn->prepare("SELECT name, email, phone, address FROM user_profile WHERE user_code = ?");
$stmt->bind_param("s", $user_code);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $address);
if (!$stmt->fetch()) {
    echo "Không tìm thấy thông tin người dùng.";
    exit;
}
$stmt->close();

// Kiểm tra giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Giỏ hàng của bạn trống.";
    exit;
}

$cart = $_SESSION['cart'];
$totalPrice = 0;
$itemCount = 0;

// Chuẩn hóa và gộp giỏ hàng theo product_code + color
$itemsGrouped = [];
foreach ($cart as $item) {
    if (empty($item['product_code'])) {
       $stmtProd = $conn->prepare( "SELECT product_code, category, image FROM products WHERE name = ?");
        $stmtProd->bind_param("s", $item['name']);
        $stmtProd->execute();
       $stmtProd->bind_result($db_product_code, $db_category, $db_image);
        if ($stmtProd->fetch()) {
            $item['product_code'] = $db_product_code;
            $item['category'] = $db_category ?: 'N/A';
            $item['image'] = '../../admin/uploads/' . $db_image;
        } else {
            throw new Exception("Sản phẩm " . htmlspecialchars($item['name']) . " không tồn tại trong hệ thống.");
        }
        $stmtProd->close();
    }

    $color = $item['color'] ?? 'Mặc định';
    $key = $item['product_code'] . '||' . $color;

    if (!isset($itemsGrouped[$key])) {
        $itemsGrouped[$key] = [
            'name' => $item['name'],
            'product_code' => $item['product_code'],
            'category' => $item['category'] ?? 'N/A',
            'image' => $item['image'] ?? '../../admin/uploads/',
            'color' => $color,
            'price' => (float)$item['price'],
            'quantity' => (int)$item['quantity']
        ];
    } else {
        $itemsGrouped[$key]['quantity'] += (int)$item['quantity'];
    }

    $totalPrice += $item['price'] * $item['quantity'];
    $itemCount += (int)$item['quantity'];
}

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
                throw new Exception("Sản phẩm " . htmlspecialchars($item['name']) . " màu " . htmlspecialchars($item['color']) . " không tồn tại trong kho.");
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

// Hàm gửi email
function sendConfirmationEmail($name, $email, $totalPrice, $itemsGrouped, $address, $user_code) {
    require '../password/vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'Namdo2003hp@gmail.com';
        $mail->Password = 'pdkd ujkn piok qrnu';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('Namdo2003hp@gmail.com', 'Mobile Gear');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = mb_encode_mimeheader('Xác nhận đơn hàng thành công - Mobile Gear', 'UTF-8');
        $mail->Body = generateEmailBody($name, $totalPrice, $itemsGrouped, $address, $user_code);

        $mail->send();
    } catch (Exception $e) {
        error_log("Email không thể gửi. Lỗi: {$mail->ErrorInfo}");
    }
}

// Nội dung email
function generateEmailBody($name, $totalPrice, $itemsGrouped, $address, $user_code) {
    $body = "<html><body><h1>Xác Nhận Đơn Hàng</h1>";
    $body .= "<p>Khách hàng: <strong>" . htmlspecialchars($name) . "</strong></p>";
    $body .= "<p>Mã KH: <strong>" . htmlspecialchars($user_code) . "</strong></p>";
    $body .= "<p>Địa chỉ: " . htmlspecialchars($address) . "</p>";
    $body .= "<p>Tổng tiền: <strong>" . number_format($totalPrice, 0, ',', '.') . " VNĐ</strong></p>";
    $body .= "<h3>Chi tiết sản phẩm:</h3><ul>";

    foreach ($itemsGrouped as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $body .= "<li style='margin-bottom:8px;'>
                    <strong>Tên:</strong> " . htmlspecialchars($item['name']) . " (x{$item['quantity']})<br>
                    <strong>Mã sản phẩm:</strong> " . htmlspecialchars($item['product_code']) . "<br>
                    <strong>Màu sắc:</strong> " . htmlspecialchars($item['color']) . "<br>
                    <strong>Loại:</strong> " . htmlspecialchars($item['category']) . "<br>
                    <strong>Thành tiền:</strong> " . number_format($itemTotal, 0, ',', '.') . " VNĐ
                  </li>";
    }

    $body .= "</ul><p>Cảm ơn bạn đã mua sắm tại <strong>Mobile Gear</strong>!</p></body></html>";
    return $body;
}

$conn->close();

?>