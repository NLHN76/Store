<?php
require_once  "../db.php";

// ID đơn hàng đã xem lần cuối
$last_seen_order_id = $_SESSION['last_seen_order_id'] ?? 0;

// 🔔 Chỉ đếm đơn hàng MỚI + trạng thái "Chờ xử lý"
$sql = "SELECT COUNT(*) AS total 
        FROM payment 
        WHERE id > ? AND status = 'Chờ xử lý'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $last_seen_order_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$new_order_count = (int)$row['total'];
