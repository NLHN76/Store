<?php
require_once "../db.php";

$product_code = $_GET['product_code'] ?? '';
$color = $_GET['color'] ?? '';

if (!$product_code || !$color) {
    echo json_encode(['quantity' => 0]);
    exit;
}


$stmt = $conn->prepare("SELECT id FROM products WHERE product_code=? LIMIT 1");
$stmt->bind_param("s", $product_code);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();


if (!$product) {
    echo json_encode(['quantity' => 0]);
    exit;
}


$stmt2 = $conn->prepare("SELECT quantity FROM product_inventory WHERE product_id=? AND color=? LIMIT 1");
$stmt2->bind_param("is", $product['id'], $color);
$stmt2->execute();
$inventory = $stmt2->get_result()->fetch_assoc();
$stmt2->close();


$quantity = (int)($inventory['quantity'] ?? 0);


echo json_encode(['quantity' => $quantity]);
?>