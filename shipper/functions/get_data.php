<?php

if(!isset($_SESSION['shipper_id'])){
    header("Location: login/shipper_login.php");
    exit;
}

$shipper_id = $_SESSION['shipper_id'];
$shipper_name = $_SESSION['shipper_name'];

// --- Lấy thông tin shipper ---
$shipper = $conn->query("
    SELECT * 
    FROM shipper 
    WHERE id=$shipper_id
")->fetch_assoc();

$avatar_login = $shipper['avatar']
    ?? 'https://via.placeholder.com/40';

?>