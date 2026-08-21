<?php

require_once "../../../db.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);


//XÓA ĐƠN HÀNG

if (isset($_POST['delete_id'])) {

    $id = (int)$_POST['delete_id'];

    if ($id <= 0) {
        exit("invalid");
    }

    $stmt = $conn->prepare(
        "DELETE FROM payment WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    echo $stmt->execute() ? "success" : "error";

    $stmt->close();
    exit;
}


// CẬP NHẬT TRẠNG THÁI

if (
    isset($_POST['action']) &&
    $_POST['action'] === 'update_status'
) {

    $id = (int)($_POST['order_id'] ?? 0);
    $status = trim($_POST['new_status'] ?? '');

    if ($id <= 0 || $status === '') {
        exit("invalid");
    }

    $stmt = $conn->prepare(
        "UPDATE payment
         SET status = ?
         WHERE id = ?"
    );

    $stmt->bind_param("si", $status, $id);

    echo $stmt->execute() ? "success" : "error";

    $stmt->close();
    exit;
}




echo "no_action";


?>