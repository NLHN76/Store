<?php
require_once "../../db.php";
header("Content-Type: application/json");

/* ===== ĐƠN HÀNG ===== */
$orders = $conn->query("
    SELECT COUNT(*) c 
    FROM payment 
    WHERE status='Chờ xử lý'
")->fetch_assoc()['c'] ?? 0;

/* ===== LIÊN HỆ MỚI ===== */
// Lấy ID liên hệ cuối cùng admin đã xem
$res = $conn->query("SELECT MAX(id) max_id FROM contact");
$max_contact_id = $res->fetch_assoc()['max_id'] ?? 0;

$last_seen = $_SESSION['last_seen_contact_id'] ?? 0;
$contact = ($max_contact_id > $last_seen) ? 1 : 0;

/* ===== CHAT ===== */
$chat = $conn->query("
    SELECT COUNT(*) c
    FROM message m
    LEFT JOIN user_last_seen_message u ON m.user_id=u.user_id
    WHERE m.sender_role='user'
      AND m.id > COALESCE(u.last_seen_id,0)
")->fetch_assoc()['c'] ?? 0;

echo json_encode([
    'orders'  => $orders,
    'contact' => $contact,
    'chat'    => $chat
]);
