<?php
require_once "../../db.php";
header('Content-Type: application/json');

// Lấy dữ liệu POST JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Thiếu ID đơn hàng']);
    exit;
}

$id = intval($data['id']);


// Kiểm tra trạng thái hiện tại của đơn
$sql_check = "SELECT status FROM payment WHERE id = $id";
$result = $conn->query($sql_check);

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Đơn hàng không tồn tại']);
    $conn->close();
    exit;
}

$row = $result->fetch_assoc();

// Chỉ hủy đơn khi đang Chờ xử lý
if ($row['status'] !== 'Chờ xử lý') {
    echo json_encode(['status' => 'error', 'message' => 'Chỉ có đơn đang Chờ xử lý mới được hủy']);
    $conn->close();
    exit;
}

// Cập nhật trạng thái thành 'Đã hủy'
$sql_update = "UPDATE payment SET status = 'Đã hủy' WHERE id = $id";
if ($conn->query($sql_update)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}

$conn->close();
?>
