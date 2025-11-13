<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

$product_code = $_GET['product_code'] ?? '';
$color = $_GET['color'] ?? '';

if($product_code && $color){
    // Lấy tồn kho
    $stmt = $conn->prepare("SELECT pi.quantity 
                            FROM product_inventory pi 
                            JOIN products p ON pi.product_id = p.id 
                            WHERE p.product_code=? AND pi.color=? LIMIT 1");
    $stmt->bind_param("ss", $product_code, $color);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $quantity = (int)($result['quantity'] ?? 0);
    $stmt->close();

    // Lấy đã bán (chỉ tính khi đã giao hàng)
    $stmt2 = $conn->prepare("SELECT SUM(product_quantity) as sold 
                             FROM payment 
                             WHERE product_code=? AND color=? AND status='Đã giao hàng'");
    $stmt2->bind_param("ss", $product_code, $color);
    $stmt2->execute();
    $result2 = $stmt2->get_result()->fetch_assoc();
    $sold = (int)($result2['sold'] ?? 0);
    $stmt2->close();

    // Tồn kho thực tế
    $actual_stock = max($quantity - $sold, 0); // tránh số âm

    echo json_encode(['quantity' => $actual_stock, 'sold' => $sold]);
} else {
    echo json_encode(['quantity'=>0,'sold'=>0]);
}
