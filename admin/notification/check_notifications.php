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
$res = $conn->query("SELECT MAX(id) AS max_id FROM contact");
$max_contact_id = $res->fetch_assoc()['max_id'] ?? 0;

$last_seen = $_SESSION['last_seen_contact_id'] ?? 0;
$contact = ($max_contact_id > $last_seen) ? 1 : 0;

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
