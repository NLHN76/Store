<?php

require_once "../../../db.php";

header('Content-Type: application/json; charset=utf-8');

$keyword = trim($_GET['keyword'] ?? '');

$sql = "
    SELECT
        p.*,
        s.name AS shipper_name,
        s.email AS shipper_email,
        s.phone AS shipper_phone
    FROM payment p
    LEFT JOIN shipper s
        ON p.shipper_id = s.id
";

if ($keyword !== '') {
    $sql .= ctype_digit($keyword)
        ? " WHERE p.id = " . (int)$keyword
        : " WHERE 0";
}

$sql .= " ORDER BY p.order_date DESC";

$result = $conn->query($sql);

$orders = [];

while ($result && $row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode($orders, JSON_UNESCAPED_UNICODE);

$conn->close();

?>