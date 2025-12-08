<?php
require_once "../db.php" ;
// Kiểm tra khách hàng đã đăng nhập chưa
if(!isset($_SESSION['user_code'])){
    echo json_encode(['status'=>'error','message'=>'Chưa đăng nhập']);
    exit;
}

$user_code = $_SESSION['user_code'];

// Lấy các đơn hàng của khách hàng đang đăng nhập
$sql = "SELECT id, customer_name, customer_email, customer_phone, customer_address,
        user_code, product_name, category, color, product_quantity, total_price, 
        order_date, status
        FROM payment 
        WHERE user_code = ? 
        ORDER BY order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_code);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while($row = $result->fetch_assoc()){
    $orders[] = $row;
}

echo json_encode($orders);

$stmt->close();
$conn->close();
?>
