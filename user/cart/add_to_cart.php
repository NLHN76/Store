<?php

header('Content-Type: application/json');
require_once '../../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Chưa đăng nhập']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$product_code = $data['product_code'] ?? '';
$color    = $data['color'] ?? null;
$quantity = (int)($data['quantity'] ?? 1);
$price    = (float)($data['price'] ?? 0);
$user_id  = $_SESSION['user_id'];

if (!$product_code || $quantity <= 0) {
    echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
    exit;
}

/* ===== LẤY PRODUCT ===== */
$stmt = $conn->prepare(
    "SELECT id, name, image FROM products WHERE product_code = ?"
);
$stmt->bind_param("s", $product_code);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'error' => 'Sản phẩm không tồn tại']);
    exit;
}

$product_id = $product['id'];
$name  = $product['name'];
$image = $product['image'];

/* ===== LẤY / TẠO CART ===== */
$stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $cart_id = $res->fetch_assoc()['id'];
} else {
    $stmt = $conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_id = $stmt->insert_id;
}

/* ===== KIỂM TRA ĐÃ CÓ TRONG GIỎ ===== */
$stmt = $conn->prepare(
    "SELECT id FROM cart_items 
     WHERE cart_id = ? AND product_id = ? AND color <=> ?"
);
$stmt->bind_param("iis", $cart_id, $product_id, $color);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

if ($item) {
    // tăng số lượng
    $stmt = $conn->prepare(
        "UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?"
    );
    $stmt->bind_param("i", $item['id']);
    $stmt->execute();
} else {
    // thêm mới
    $stmt = $conn->prepare(
        "INSERT INTO cart_items
        (cart_id, product_id, name, image, color, quantity, price)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "iisssid",
        $cart_id,
        $product_id,
        $name,
        $image,
        $color,
        $quantity,
        $price
    );
    $stmt->execute();
}

echo json_encode(['success' => true]);
