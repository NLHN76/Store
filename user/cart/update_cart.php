<?php
header('Content-Type: application/json');
require_once '../../db.php';

$data = json_decode(file_get_contents('php://input'), true);

$product_id = $data['product_id'] ?? 0;
$color = $data['color'] ?? '';
$action = $data['action'] ?? '';

if (!$product_id || !$color) {
    echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Lấy số lượng hiện tại trong giỏ
$stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE product_id=? AND color=?");
$stmt->bind_param("is", $product_id, $color);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$currentQty = $item['quantity'] ?? 0;

// Lấy tồn kho thực tế từ product_inventory
$stmt = $conn->prepare("SELECT quantity FROM product_inventory WHERE product_id=? AND color=?");
$stmt->bind_param("is", $product_id, $color);
$stmt->execute();
$inv = $stmt->get_result()->fetch_assoc();
$stockQty = $inv['quantity'] ?? 0;

switch ($action) {
    case 'increase':
        if ($currentQty + 1 > $stockQty) {
            echo json_encode(['success' => false, 'error' => 'Số lượng vượt quá tồn kho!']);
            exit;
        }
        $sql = "UPDATE cart_items SET quantity = quantity + 1 WHERE product_id=? AND color=?";
        break;

    case 'decrease':
        $sql = "UPDATE cart_items SET quantity = quantity - 1 WHERE product_id=? AND color=? AND quantity > 1";
        break;

    case 'remove':
        $sql = "DELETE FROM cart_items WHERE product_id=? AND color=?";
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Hành động không hợp lệ']);
        exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $product_id, $color);
$stmt->execute();

echo json_encode(['success' => true]);
