<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

// Kết nối DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['status'=>'error','message'=>'Kết nối CSDL thất bại']);
    exit;
}

// Lấy tất cả đơn hàng
$sql = "SELECT id, customer_name, customer_email, customer_phone, customer_address,
        user_code, product_name, category, color, product_quantity, total_price, 
        order_date, status
        FROM payment ORDER BY order_date DESC";

$result = $conn->query($sql);

$orders = [];
if($result){
    while($row = $result->fetch_assoc()){
        $orders[] = $row;
    }
    echo json_encode($orders);
} else {
    echo json_encode(['status'=>'error','message'=>'Lấy dữ liệu thất bại']);
}

$conn->close();
?>
