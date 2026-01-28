<?php
require_once "../../db.php";


/* ===== LOGIN ===== */
if (!isset($_SESSION['user_code'])) {
    exit("Vui lòng đăng nhập");
}
$user_code = $_SESSION['user_code'];

/* ===== PAYMENT ID ===== */
$payment_id = (int)($_GET['payment_id'] ?? 0);
if ($payment_id <= 0) {
    exit("Đơn hàng không hợp lệ");
}

/* ===== GET PAYMENT ===== */
$stmt = $conn->prepare("
    SELECT product_name, product_code, category, image, color,
           product_quantity, total_price
    FROM payment
    WHERE id = ? AND user_code = ?
");
$stmt->bind_param("is", $payment_id, $user_code);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payment) {
    exit("Không có quyền truy cập đơn hàng");
}

/* ===== SPLIT DATA ===== */
$names      = array_map('trim', explode(',', $payment['product_name']));
$codes      = array_map('trim', explode(',', $payment['product_code']));
$categories = array_map('trim', explode(',', $payment['category']));
$colors     = array_map('trim', explode(',', $payment['color'] ?? ''));
$images     = array_map('trim', explode(',', $payment['image'] ?? ''));

$quantities = [];

if (count($names) === 1) {

    $quantities[] = (int)$payment['product_quantity'];
} else {

    foreach ($names as $_) {
        $quantities[] = 1;
    }
}

/* ===== CLEAR CART  ===== */
$_SESSION['cart'] = [];

/* ===== ADD TO CART ===== */
foreach ($codes as $i => $code) {

    /* lấy giá hiện tại */
    $stmtP = $conn->prepare("
        SELECT price, is_active
        FROM products
        WHERE product_code = ?
    ");
    $stmtP->bind_param("s", $code);
    $stmtP->execute();
    $product = $stmtP->get_result()->fetch_assoc();
    $stmtP->close();

    if ($product && (int)$product['is_active'] === 0) {
        continue; // bỏ SP đã ngừng bán
    }

    $qty = $quantities[$i] ?? 1;


    $unitPrice = $product['price']
        ?? ((float)$payment['total_price'] / max(array_sum($quantities), 1));

    $_SESSION['cart'][] = [
        'product_code' => $code,
        'name'         => preg_replace('/\s*\(x\d+\)$/i', '', $names[$i]),
        'category'     => $categories[$i] ?? '',
        'color'        => $colors[$i] ?? '',
        'image'        => '../../admin/uploads/' . ($images[$i] ?? ''),
        'price'        => (float)$unitPrice,
        'quantity'     => (int)$qty
    ];
}

/* ===== REDIRECT ===== */
header("Location: ../pay/no_cart.php");
exit;
