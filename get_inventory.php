<?php
require_once "db.php";

$product_code = $_GET['product_code'] ?? '';
$color = $_GET['color'] ?? '';

if (!$product_code || !$color) {
    echo json_encode(['quantity' => 0]);
    exit;
}

// Lấy product_id
$stmt = $conn->prepare("SELECT id FROM products WHERE product_code=? LIMIT 1");
$stmt->bind_param("s", $product_code);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Nếu không tìm thấy sản phẩm
if (!$product) {
    echo json_encode(['quantity' => 0]);
    exit;
}

// Lấy tồn kho theo màu
$stmt2 = $conn->prepare("SELECT quantity FROM product_inventory WHERE product_id=? AND color=? LIMIT 1");
$stmt2->bind_param("is", $product['id'], $color);
$stmt2->execute();
$inventory = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

// Số lượng tồn kho (default = 0)
$quantity = (int)($inventory['quantity'] ?? 0);

// Trả JSON
echo json_encode(['quantity' => $quantity]);
