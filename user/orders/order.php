<?php

require_once "../../db.php";

if (!isset($_SESSION['user_code'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Chưa đăng nhập'
    ]);
    exit;
}

$user_code = $_SESSION['user_code'];

$sql = "
    SELECT
        id,
        order_code,
        customer_name,
        customer_email,
        customer_phone,
        customer_address,
        user_code,
        product_name,
        image,
        category,
        color,
        product_quantity,
        total_price,
        DATE_FORMAT(order_date, '%d/%m/%Y %H:%i:%s') AS order_date,
        status
    FROM payment
    WHERE user_code = ?
    ORDER BY order_date DESC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $user_code);

$stmt->execute();

$result = $stmt->get_result();

$orders = [];

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode(
    $orders,
    JSON_UNESCAPED_UNICODE
);

$stmt->close();
$conn->close();

?>