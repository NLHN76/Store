<?php
require_once "../db.php";


if(!isset($_SESSION['shipper_id'])){
    echo json_encode(['success'=>false]);
    exit;
}

$last_checked_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

// Lấy các đơn có trạng thái "Đang xử lý"
$sql = "SELECT * FROM payment WHERE id > ? AND status = 'Đang xử lý' ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $last_checked_id);
$stmt->execute();
$result = $stmt->get_result();

$new_orders = [];
while($row = $result->fetch_assoc()){
    $new_orders[] = $row;
}

echo json_encode([
    'success' => true,
    'new_orders' => $new_orders
]);
