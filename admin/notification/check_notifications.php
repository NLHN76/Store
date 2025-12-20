<?php
require_once "../../db.php";

header("Content-Type: application/json");

/* ===== ĐƠN HÀNG ===== */
$orders = $conn->query("
    SELECT COUNT(*) AS c 
    FROM payment 
    WHERE status='Chờ xử lý'
")->fetch_assoc()['c'] ?? 0;

/* ===== LIÊN HỆ MỚI ===== */
$res_new = $conn->query("SELECT id FROM contact WHERE is_new = 1 ORDER BY id ASC");
$new_ids = [];
while ($row = $res_new->fetch_assoc()) {
    $new_ids[] = $row['id'];
}

// Kiểm tra có liên hệ mới
$contact = !empty($new_ids) ? 1 : 0;


/* ===== CHAT ===== */
$chat = $conn->query("
    SELECT COUNT(*) AS c
    FROM message m
    LEFT JOIN user_last_seen_message u ON m.user_id = u.user_id
    WHERE m.sender_role = 'user'
      AND m.id > COALESCE(u.last_seen_id, 0)
")->fetch_assoc()['c'] ?? 0;

/* ===== TỒN KHO THẤP ===== */
$lowStock = $conn->query("
    SELECT COUNT(*) AS c
    FROM product_inventory
    WHERE quantity < 10
")->fetch_assoc()['c'] ?? 0;

/* ===== TRẢ VỀ JSON ===== */
echo json_encode([
    'orders'     => $orders,
    'contact'    => $contact,
    'chat'       => $chat,
    'lowStock'   => $lowStock
]);
