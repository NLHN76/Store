<?php
require_once __DIR__ . "/../../../../db.php";

$sql = "SELECT product_code, name, category, price FROM products ORDER BY id DESC";
$result = $conn->query($sql);

$products_db = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products_db[] = $row;
    }
}

function format_price($price) {
    return number_format($price, 0, ',', '.') . " VNĐ";
}
