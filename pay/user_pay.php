<?php
require_once "../db.php";

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

// Chuẩn hóa và gộp giỏ hàng theo product_code + color
$itemsGrouped = [];
foreach ($cart as $item) {
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

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thanh Toán</title>
<link rel="stylesheet" href="css/user_pay.css">
</head>
<body>
<div class="container">
<h1>Thông Tin Đặt Hàng</h1>

<?php if (!$isPaymentConfirmed): ?>
<form method="POST" action="">
<label for="name">Tên người nhận:</label>
<input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
<label for="user_code">Mã Khách Hàng:</label>
<input type="text" id="user_code" name="user_code" value="<?php echo htmlspecialchars($user_code); ?>" readonly>
<label for="email">Email:</label>
<input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
<label for="phone">Số Điện Thoại:</label>
<input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required pattern="[0-9]{10,11}" title="Số điện thoại gồm 10-11 chữ số">
<label for="address">Địa Chỉ Nhận Hàng:</label>
<input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
<button type="submit">Xác Nhận Đặt Hàng</button>
</form>

<div class="cart-summary">
<h2>Xem Lại Giỏ Hàng</h2>
<p><strong>Tổng Số Lượng Sản Phẩm:</strong> <?php echo $itemCount; ?></p>
<p><strong>Tổng Tiền:</strong> <?php echo number_format($totalPrice, 0, ',', '.'); ?> VNĐ</p>
<h3>Chi Tiết Sản Phẩm:</h3>
<ul>
<?php foreach ($itemsGrouped as $item): ?>
<li>
<?php
$itemTotal = $item['price'] * $item['quantity'];
echo "<strong>Mã:</strong> " . htmlspecialchars($item['product_code']) . "<br>";
echo "<strong>Tên:</strong> " . htmlspecialchars($item['name']) . " (x" . htmlspecialchars($item['quantity']) . ")<br>";
echo "<strong>Màu:</strong> " . htmlspecialchars($item['color']) . "<br>";
echo "<strong>Loại:</strong> " . htmlspecialchars($item['category']) . "<br>";
echo "<strong>Giá:</strong> " . number_format($itemTotal, 0, ',', '.') . " VNĐ";
?>
</li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<?php if ($isPaymentConfirmed): ?>
<div id="qr-code">
    <h3>Quét Mã QR Để Thanh Toán</h3>
    <img src="qr.png" alt="Mã QR Thanh Toán" style="width: 100%; max-width: 300px;">
    <p>Cảm ơn bạn đã đặt hàng! Vui lòng kiểm tra email xác nhận. Khi thanh toán bằng chuyển khoản, ghi rõ Mã Khách Hàng (<?php echo htmlspecialchars($user_code); ?>) trong nội dung chuyển khoản.</p>
</div>
<?php endif; ?>


<a href="../user_logout.html" class="back-button">Quay lại trang chủ</a>
</div>
</body>
</html>         