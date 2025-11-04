<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

$name = $email = $phone = $address = $user_code = '';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $query = "SELECT name, email, user_code FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($name, $email, $user_code);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Vui lòng đăng nhập để tiếp tục.";
    exit;
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "Giỏ hàng của bạn trống.";
    exit;
}

$cart = $_SESSION['cart'];
$totalPrice = $itemCount = 0;
$productCodes = $productCategories = $productDetailsList = $colorsList = [];

foreach ($cart as $item) {
    $quantity = (int)$item['quantity'];
    $price = (float)$item['price'];
    $totalPrice += $price * $quantity;
    $itemCount += $quantity;
    $productDetailsList[] = htmlspecialchars($item['name']) . " (x$quantity)";

    $color = $item['color'] ?? 'Không có màu';
    $colorsList[] = $color;

    $product_code = $item['product_code'] ?? 'N/A';
    $category = 'N/A';

    if ($product_code === 'N/A') {
        $queryProd = "SELECT product_code, category FROM products WHERE name = ?";
        $stmtProd = $conn->prepare($queryProd);
        $stmtProd->bind_param("s", $item['name']);
        $stmtProd->execute();
        $stmtProd->bind_result($db_product_code, $db_category);
        if ($stmtProd->fetch()) {
            $product_code = $db_product_code ?: 'N/A';
            $category = $db_category ?: 'N/A';
        }
        $stmtProd->close();
    } else {
        $queryCat = "SELECT category FROM products WHERE product_code = ?";
        $stmtCat = $conn->prepare($queryCat);
        $stmtCat->bind_param("s", $product_code);
        $stmtCat->execute();
        $stmtCat->bind_result($db_category);
        if ($stmtCat->fetch()) {
            $category = $db_category ?: 'N/A';
        }
        $stmtCat->close();
    }

    $productCodes[] = $product_code;
    $productCategories[] = $category;
}

$productDetailsString = implode(', ', $productDetailsList);
$productCodesString = implode(', ', $productCodes);
$productCategoriesString = implode(', ', array_unique(array_filter($productCategories)));
$colorsString = implode(', ', $colorsList);

$isPaymentConfirmed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_post = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email_post = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $phone_post = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $address_post = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

    if (!$name_post || !$email_post || !$phone_post || !$address_post) {
        echo "Vui lòng điền đủ thông tin.";
    } else {
        // Lưu đơn hàng vào bảng payment, bao gồm màu sắc
        $stmt = $conn->prepare("INSERT INTO payment 
            (customer_name, customer_email, customer_phone, customer_address, user_code, product_code, product_name, product_quantity, total_price, category, color) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssssssiiss",
            $name_post, $email_post, $phone_post, $address_post, $user_code,
            $productCodesString, $productDetailsString, $itemCount, $totalPrice, $productCategoriesString, $colorsString
        );

        if ($stmt->execute()) {
            sendConfirmationEmail($name_post, $email_post, $totalPrice, $cart, $address_post, $productCodes, $user_code, $productCategories, $productDetailsList, $colorsList);
            $isPaymentConfirmed = true;
            unset($_SESSION['cart']); // Xóa giỏ hàng
        } else {
            echo "Lỗi lưu đơn hàng: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

function sendConfirmationEmail($name, $email, $totalPrice, $cart, $address, $productCodes, $user_code, $productCategories, $productDetailsList, $colorsList) {
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
        $mail->Body = generateEmailBody($name, $totalPrice, $cart, $address, $productCodes, $user_code, $productCategories, $productDetailsList, $colorsList);

        $mail->send();
    } catch (Exception $e) {
        error_log("Email không thể gửi. Lỗi: {$mail->ErrorInfo}");
    }
}

function generateEmailBody($name, $totalPrice, $cart, $address, $productCodes, $user_code, $productCategories, $productDetailsList, $colorsList) {
    $body = "<html><body><h1>Xác Nhận Đơn Hàng</h1>";
    $body .= "<p>Khách hàng: <strong>" . htmlspecialchars($name) . "</strong></p>";
    $body .= "<p>Mã KH: <strong>" . htmlspecialchars($user_code) . "</strong></p>";
    $body .= "<p>Địa chỉ: " . htmlspecialchars($address) . "</p>";
    $body .= "<p>Tổng tiền: <strong>" . number_format($totalPrice, 0, ',', '.') . " VNĐ</strong></p>";
    $body .= "<h3>Chi tiết sản phẩm:</h3><ul>";

    foreach ($productDetailsList as $index => $detail) {
        $productCode = htmlspecialchars($productCodes[$index] ?? 'N/A');
        $category    = htmlspecialchars($productCategories[$index] ?? 'N/A');
        $color       = htmlspecialchars($colorsList[$index] ?? 'Không có màu');
        $itemTotal   = number_format($cart[$index]['price'] * $cart[$index]['quantity'], 0, ',', '.');

        $body .= "<li style='margin-bottom:8px;'>
                    <strong>Tên:</strong> {$detail}<br>
                    <strong>Mã sản phẩm:</strong> {$productCode}<br>
                    <strong>Màu sắc:</strong> {$color}<br>
                    <strong>Loại:</strong> {$category}<br>
                    <strong>Thành tiền:</strong> {$itemTotal} VNĐ
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
<style>
body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
.container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 700px; margin: auto; }
h1,h2,h3 { color: #333; }
form label { display: block; margin-bottom: 8px; font-weight: bold; }
form input[type="text"],form input[type="email"],form input[type="tel"] { width: calc(100% - 22px); padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
form input[readonly] { background-color: #eee; cursor: not-allowed; }
form button { background-color: #5cb85c; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; transition: background-color 0.3s ease; }
form button:hover { background-color: #4cae4c; }
.cart-summary { margin-top: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background-color: #f9f9f9; }
.cart-summary ul { list-style: none; padding: 0; }
.cart-summary li { margin-bottom: 10px; border-bottom: 1px dotted #ccc; padding-bottom: 5px; }
#qr-code { margin: 30px 0; text-align: center; display: <?php echo $isPaymentConfirmed ? 'block' : 'none'; ?>; background: #e9f5e9; padding: 20px; border-radius: 5px; border: 1px solid #c8e6c9; }
#qr-code img { width: 150px; height: auto; margin-bottom: 10px; }
.back-button { display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #f0ad4e; color: white; text-decoration: none; border-radius: 4px; transition: background-color 0.3s ease; }
.back-button:hover { background-color: #ec971f; }
</style>
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
<?php foreach ($cart as $index => $item): ?>
<li>
<?php
$pCode = htmlspecialchars($productCodes[$index] ?? 'N/A');
$pCat  = htmlspecialchars($productCategories[$index] ?? 'N/A');
$color = htmlspecialchars($colorsList[$index] ?? 'Không có màu');
$itemTotal = $item['price'] * $item['quantity'];
echo "<strong>Mã:</strong> {$pCode}<br>";
echo "<strong>Tên:</strong> " . htmlspecialchars($item['name']) . " (x" . htmlspecialchars($item['quantity']) . ")<br>";
echo "<strong>Màu:</strong> {$color}<br>";
echo "<strong>Loại:</strong> {$pCat}<br>";
echo "<strong>Giá:</strong> " . number_format($itemTotal, 0, ',', '.') . " VNĐ";
?>
</li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<div id="qr-code">
<h3>Quét Mã QR Để Thanh Toán</h3>
<img src="qr.png" alt="Mã QR Thanh Toán" style="width: 100%; max-width: 300px;">
<p>Cảm ơn bạn đã đặt hàng! Vui lòng kiểm tra email xác nhận. Khi thanh toán bằng chuyển khoản, ghi rõ Mã Khách Hàng (<?php echo htmlspecialchars($user_code); ?>) trong nội dung chuyển khoản.</p>
</div>

<a href="../user_logout.html" class="back-button">Quay lại trang chủ</a>
</div>
</body>
</html>
