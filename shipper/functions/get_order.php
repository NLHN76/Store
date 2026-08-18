<?php

// --- Lấy danh sách đơn ---
$stmt_orders = $conn->prepare("
    SELECT p.*, 
           s.name AS shipper_name, 
           s.avatar AS shipper_avatar
    FROM payment p
    LEFT JOIN shipper s ON p.shipper_id = s.id
    WHERE p.shipper_id = ?
       OR (p.status = 'Đang xử lý' AND p.shipper_id IS NULL)
    ORDER BY (p.status = 'Đang xử lý' AND p.shipper_id IS NULL) DESC,
             p.order_date DESC
");

$stmt_orders->bind_param("i", $shipper_id);
$stmt_orders->execute();
$orders = $stmt_orders->get_result();



// Lấy ID đơn lớn nhất từ bảng payment
$result_last = $conn->query("
    SELECT MAX(id) as last_id 
    FROM payment
");

$lastOrderId = ($row = $result_last->fetch_assoc())
    ? intval($row['last_id'])
    : 0;

?>