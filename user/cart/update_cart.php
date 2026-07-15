<?php
header('Content-Type: application/json');

require_once '../../db.php';


$user_id = $_SESSION['user_id'] ?? 0;

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'] ?? 0;
$color = $data['color'] ?? '';
$action = $data['action'] ?? '';

if (!$user_id || !$product_id || !$color || !$action) {
    echo json_encode(['success' => false, 'error' => 'Dữ liệu không hợp lệ']);
    exit;
}

// Lấy cart_id của người dùng, nếu chưa có thì tạo mới
$stmt = $conn->prepare("SELECT id FROM carts WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result()->fetch_assoc();
$cart_id = $cart['id'] ?? 0;

if (!$cart_id) {
    // Tạo giỏ hàng mới
    $stmt = $conn->prepare("INSERT INTO carts(user_id) VALUES(?)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_id = $conn->insert_id;
}

// Lấy số lượng hiện tại trong giỏ của người dùng
$stmt = $conn->prepare("SELECT quantity FROM cart_items WHERE cart_id=? AND product_id=? AND color=?");
$stmt->bind_param("iis", $cart_id, $product_id, $color);
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
        if ($currentQty == 0) {
            // Thêm mới nếu chưa có trong giỏ
            $stmt = $conn->prepare("INSERT INTO cart_items(cart_id, product_id, name, quantity, color, price, image)
                                    SELECT ?, id, name, 1, ?, price, image FROM products WHERE id=?");
            $stmt->bind_param("iis", $cart_id, $color, $product_id);
            $stmt->execute();
        } else {
            // Kiểm tra tồn kho
            if ($currentQty + 1 > $stockQty) {
                echo json_encode(['success' => false, 'error' => 'Số lượng vượt quá tồn kho!']);
                exit;
            }
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE cart_id=? AND product_id=? AND color=?");
            $stmt->bind_param("iis", $cart_id, $product_id, $color);
            $stmt->execute();
        }
        break;

    case 'decrease':
        if ($currentQty > 1) {
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity - 1 WHERE cart_id=? AND product_id=? AND color=?");
            $stmt->bind_param("iis", $cart_id, $product_id, $color);
            $stmt->execute();
        } else {
            // Nếu giảm xuống 0 hoặc 1 thì xóa luôn
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id=? AND product_id=? AND color=?");
            $stmt->bind_param("iis", $cart_id, $product_id, $color);
            $stmt->execute();
        }
        break;

    case 'remove':
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id=? AND product_id=? AND color=?");
        $stmt->bind_param("iis", $cart_id, $product_id, $color);
        $stmt->execute();
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Hành động không hợp lệ']);
        exit;
}

echo json_encode(['success' => true]);
