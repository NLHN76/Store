<?php

if(!isset($_SESSION['shipper_id'])){
    header("Location: login/shipper_login.php");
    exit;
}

$shipper_id = $_SESSION['shipper_id'];
$shipper_name = $_SESSION['shipper_name'];

// Lấy ID đơn lớn nhất từ bảng payment
$result_last = $conn->query("
    SELECT MAX(id) as last_id 
    FROM payment
");

$lastOrderId = ($row = $result_last->fetch_assoc())
    ? intval($row['last_id'])
    : 0;

// --- Lấy thông tin shipper ---
$shipper = $conn->query("
    SELECT * 
    FROM shipper 
    WHERE id=$shipper_id
")->fetch_assoc();

$avatar_login = $shipper['avatar']
    ?? 'https://via.placeholder.com/40';

?>