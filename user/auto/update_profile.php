<?php
require_once '../../db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user_code'])){
    echo json_encode(['success'=>false, 'message'=>'Chưa đăng nhập']);
    exit();
}

$user_code = $_SESSION['user_code'];
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';

$stmt = $conn->prepare("UPDATE user_profile SET phone=?, address=? WHERE user_code=?");
$stmt->bind_param("sss", $phone, $address, $user_code);

if($stmt->execute()){
    echo json_encode(['success'=>true, 'message'=>'Cập nhật thành công']);
} else {
    echo json_encode(['success'=>false, 'message'=>'Cập nhật thất bại']);
}
?>
