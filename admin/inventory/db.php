<?php
session_start();

/* ----------------- Kết nối database ----------------- */
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

/* ----------------- Tiện ích ----------------- */
function getProductCode($conn, $product_id) {
    $stmt = $conn->prepare("SELECT product_code FROM products WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res['product_code'] ?? '';
}
?>
