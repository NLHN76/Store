<?php
require_once "../../db.php";
session_start();

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
$names  = array_map('trim', explode(',', $payment['product_name']));
$codes  = array_map('trim', explode(',', $payment['product_code']));
$cats   = array_map('trim', explode(',', $payment['category']));
$colors = array_map('trim', explode(',', $payment['color'] ?? ''));
$images = array_map('trim', explode(',', $payment['image'] ?? ''));

/*
  QUY ƯỚC:
  - 1 sản phẩm → dùng product_quantity
  - nhiều sản phẩm → mỗi sản phẩm = 1
*/
$quantities = [];

if (count($names) === 1) {
    $quantities[] = (int)$payment['product_quantity'];
} else {
    foreach ($names as $_) {
        $quantities[] = 1;
    }
}

/* ===== CLEAR REORDER DATA ===== */
$_SESSION['reorder_items'] = [];

/* ===== ADD PRODUCTS ===== */
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
        continue; // bỏ SP ngừng bán
    }

    $qty = $quantities[$i];

    // giá fallback an toàn
    $unitPrice = $product['price']
        ?? ((float)$payment['total_price'] / max(array_sum($quantities), 1));

    $_SESSION['reorder_items'][] = [
        'product_code' => $code,
        'name'         => preg_replace('/\s*\(x\d+\)$/i', '', $names[$i]),
        'category'     => $cats[$i] ?? '',
        'color'        => $colors[$i] ?? '',
        'image'        => '../../admin/uploads/' . ($images[$i] ?? ''),
        'price'        => (float)$unitPrice,
        'quantity'     => (int)$qty
    ];
}

/* ===== REDIRECT ===== */
header("Location: ../pay/user_pay.php");
exit;
