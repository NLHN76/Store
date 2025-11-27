<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

// Kết nối DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Lấy thông tin user
$name = $email = $phone = $address = $user_code = '';
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT name, email, user_code FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($name, $email, $user_code);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Vui lòng đăng nhập để tiếp tục.";
    exit;
}

// Kiểm tra giỏ hàng
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Giỏ hàng của bạn trống.";
    exit;
}

$cart = $_SESSION['cart'];
$totalPrice = 0;
$itemCount = 0;

// Bước 1: Chuẩn hóa và gộp giỏ hàng theo product_code + color
$itemsGrouped = [];
foreach ($cart as $item) {
    // Lấy product_code nếu chưa có
    if (empty($item['product_code'])) {
        $stmtProd = $conn->prepare("SELECT product_code, category FROM products WHERE name = ?");
        $stmtProd->bind_param("s", $item['name']);
        $stmtProd->execute();
        $stmtProd->bind_result($db_product_code, $db_category);
        if ($stmtProd->fetch()) {
            $item['product_code'] = $db_product_code;
            $item['category'] = $db_category ?: 'N/A';
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
            'color' => $color,
            'price' => (float)$item['price'],
            'quantity' => (int)$item['quantity']
        ];
    } else {
        // Cộng dồn số lượng nếu cùng product + color
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

    $conn->begin_transaction();
    try {
      // Bước 2: Kiểm tra tồn kho + update reserved_quantity an toàn
foreach ($itemsGrouped as $item) {
    $productCode = $item['product_code'];
    $color = $item['color'];
    $quantityNeeded = (int)$item['quantity'];

    // Lấy tồn kho và reserved_quantity, dùng IFNULL để tránh NULL
    $stmtStock = $conn->prepare("
        SELECT quantity, IFNULL(reserved_quantity,0) as reserved 
        FROM product_inventory 
        WHERE product_code = ? AND LOWER(color) = LOWER(?) FOR UPDATE
    ");
    $stmtStock->bind_param("ss", $productCode, $color);
    $stmtStock->execute();
    $stmtStock->bind_result($stock, $reserved);

    if (!$stmtStock->fetch()) {
        $stmtStock->close();
        throw new Exception("Sản phẩm " . htmlspecialchars($item['name']) . " màu " . htmlspecialchars($color) . " không tồn tại trong kho.");
    }
    $stmtStock->close();

    // Ép kiểu int
    $stock = (int)$stock;
    $reserved = (int)$reserved;

    // Kiểm tra số lượng khả dụng
    $available = $stock - $reserved;
    if ($available < $quantityNeeded) {
        throw new Exception("Sản phẩm " . htmlspecialchars($item['name']) . " màu " . htmlspecialchars($color) . " chỉ còn $available sản phẩm khả dụng.");
    }

    // Cập nhật reserved_quantity, đảm bảo không NULL
    $stmtUpdate = $conn->prepare("
        UPDATE product_inventory 
        SET reserved_quantity = IFNULL(reserved_quantity,0) + ? 
        WHERE product_code = ? AND LOWER(color) = LOWER(?)
    ");
    $stmtUpdate->bind_param("iss", $quantityNeeded, $productCode, $color);
    $stmtUpdate->execute();
    $stmtUpdate->close();
}


        // Bước 3: Lưu đơn hàng
        $productCodesString = implode(', ', array_map(fn($i) => $i['product_code'], $itemsGrouped));
        $productDetailsString = implode(', ', array_map(fn($i) => $i['name'] . " (x" . $i['quantity'] . ")", $itemsGrouped));
        $productCategoriesString = implode(', ', array_unique(array_map(fn($i) => $i['category'], $itemsGrouped)));
        $colorsString = implode(', ', array_map(fn($i) => $i['color'], $itemsGrouped));

        $stmt = $conn->prepare("INSERT INTO payment 
            (customer_name, customer_email, customer_phone, customer_address, user_code, product_code, product_name, product_quantity, total_price, category, color) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssssiiss",
            $name_post, $email_post, $phone_post, $address_post, $user_code,
            $productCodesString, $productDetailsString, $itemCount, $totalPrice, $productCategoriesString, $colorsString
        );
        if (!$stmt->execute()) {
            throw new Exception("Lỗi lưu đơn hàng: " . $stmt->error);
        }
        $stmt->close();

        // Bước 4: Gửi email xác nhận
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
    require 'vendor/autoload.php';
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