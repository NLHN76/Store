<?php

header('Content-Type: application/json');
require '../../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

/* ===== LẤY CART ===== */
$stmt = $conn->prepare("SELECT id FROM carts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode([]);
    exit;
}

$cart_id = $res->fetch_assoc()['id'];

/* ===== LẤY CART ITEMS ===== */
$stmt = $conn->prepare(
    "SELECT
        product_id,
        name,
        image,
        color,
        quantity,
        price
     FROM cart_items
     WHERE cart_id = ?"
);
$stmt->bind_param("i", $cart_id);
$stmt->execute();

$result = $stmt->get_result();
$cart = [];

while ($row = $result->fetch_assoc()) {
    $cart[] = $row;
}

echo json_encode($cart);
