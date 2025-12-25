<?php

require_once "../../db.php";

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_code'])) {
    die("Vui lòng đăng nhập để tiếp tục.");
}

$user_code = $_SESSION['user_code'];

// Lấy thông tin user
$stmt = $conn->prepare("
    SELECT name, email, phone, address 
    FROM user_profile 
    WHERE user_code = ?
");
$stmt->bind_param("s", $user_code);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $address);
if (!$stmt->fetch()) {
    die("Không tìm thấy thông tin người dùng.");
}
$stmt->close();

// Kiểm tra giỏ hàng
if (empty($_SESSION['cart'])) {
    die("Giỏ hàng của bạn trống.");
}

$cart = $_SESSION['cart'];
$itemsGrouped = [];
$totalPrice = 0;
$itemCount = 0;

// Gom giỏ theo product_code + color
foreach ($cart as $item) {

    // Nếu thiếu product_code → lấy từ DB
    if (empty($item['product_code'])) {
        $stmtProd = $conn->prepare("
            SELECT product_code, category, image 
            FROM products WHERE name = ?
        ");
        $stmtProd->bind_param("s", $item['name']);
        $stmtProd->execute();
        $stmtProd->bind_result($pCode, $category, $image);

        if (!$stmtProd->fetch()) {
            die("Sản phẩm {$item['name']} không tồn tại.");
        }

        $item['product_code'] = $pCode;
        $item['category'] = $category ?? 'N/A';
        $item['image'] = '../../admin/uploads/' . $image;
        $stmtProd->close();
    }

    $color = $item['color'] ?? 'Mặc định';
    $key = $item['product_code'] . '|' . $color;

    if (!isset($itemsGrouped[$key])) {
        $itemsGrouped[$key] = [
            'name' => $item['name'],
            'product_code' => $item['product_code'],
            'category' => $item['category'],
            'image' => $item['image'],
            'color' => $color,
            'price' => (float)$item['price'],
            'quantity' => (int)$item['quantity']
        ];
    } else {
        $itemsGrouped[$key]['quantity'] += $item['quantity'];
    }

    $totalPrice += $item['price'] * $item['quantity'];
    $itemCount += $item['quantity'];
}
