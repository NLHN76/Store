<?php

require_once "../../../db.php";

date_default_timezone_set('Asia/Ho_Chi_Minh');

$order_id = filter_input(
    INPUT_POST,
    'export_html_id',
    FILTER_VALIDATE_INT
);

if (!$order_id) {
    exit("ID đơn hàng không hợp lệ");
}


// LẤY ĐƠN HÀNG
$stmt = $conn->prepare(
    "SELECT * FROM payment WHERE id = ?"
);

$stmt->bind_param("i", $order_id);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$row) {
    exit("Không tìm thấy đơn hàng");
}


// TẠO HTML
ob_start();

include "../view/invoice_template.php";

$html = ob_get_clean();


// TẢI FILE
header("Content-Type: application/octet-stream");
header(
    "Content-Disposition: attachment; filename=hoa_don_{$order_id}.html"
);

echo $html;

$conn->close();
exit;


?>