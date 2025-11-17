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
    // Lấy product_id từ product_code
    $stmt0 = $conn->prepare("SELECT id FROM products WHERE product_code=? LIMIT 1");
    $stmt0->bind_param("s",$product_code);
    $stmt0->execute();
    $res0 = $stmt0->get_result()->fetch_assoc();
    $stmt0->close();

    if(!$res0){
        echo json_encode(['quantity'=>0,'sold'=>0]);
        exit;
    }
    $product_id = $res0['id'];

    // Lấy tồn kho thực tế từ product_inventory (đã trừ theo payment)
    $stmt1 = $conn->prepare("SELECT quantity FROM product_inventory WHERE product_id=? AND color=? LIMIT 1");
    $stmt1->bind_param("is",$product_id,$color);
    $stmt1->execute();
    $res1 = $stmt1->get_result()->fetch_assoc();
    $quantity = (int)($res1['quantity'] ?? 0);
    $stmt1->close();

    // Lấy tổng đã bán từ inventory_history type='Bán hàng'
    $stmt2 = $conn->prepare("SELECT SUM(quantity_change) as sold FROM inventory_history WHERE product_id=? AND color=? AND type='Bán hàng'");
    $stmt2->bind_param("is",$product_id,$color);
    $stmt2->execute();
    $res2 = $stmt2->get_result()->fetch_assoc();
    $sold = (int)($res2['sold'] ?? 0);
    $stmt2->close();

    echo json_encode(['quantity'=>$quantity, 'sold'=>$sold]);
}else{
    echo json_encode(['quantity'=>0,'sold'=>0]);
}
