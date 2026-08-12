<?php

/* THÊM CHI TIẾT SẢN PHẨM */
if (isset($_POST['add'])) {

    $product_id = intval($_POST['product_id']);
    $description = $_POST['description'] ?? '';
    $material = $_POST['material'] ?? '';
    $compatibility = $_POST['compatibility'] ?? '';
    $warranty = $_POST['warranty'] ?? '';
    $origin = $_POST['origin'] ?? '';
    $features = $_POST['features'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO product_details
        (product_id, description, material, compatibility, warranty, origin, features)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issssss",
        $product_id,
        $description,
        $material,
        $compatibility,
        $warranty,
        $origin,
        $features
    );

    $stmt->execute();
    $stmt->close();
}


/* SỬA CHI TIẾT SẢN PHẨM */
if (isset($_POST['update'])) {

    $id = intval($_POST['detail_id']);
    $description = $_POST['description'] ?? '';
    $material = $_POST['material'] ?? '';
    $compatibility = $_POST['compatibility'] ?? '';
    $warranty = $_POST['warranty'] ?? '';
    $origin = $_POST['origin'] ?? '';
    $features = $_POST['features'] ?? '';

    $stmt = $conn->prepare("
        UPDATE product_details
        SET description=?, material=?, compatibility=?, warranty=?, origin=?, features=?
        WHERE detail_id=?
    ");

    $stmt->bind_param(
        "ssssssi",
        $description,
        $material,
        $compatibility,
        $warranty,
        $origin,
        $features,
        $id
    );

    $stmt->execute();
    $stmt->close();
}


/* XÓA CHI TIẾT SẢN PHẨM */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    if ($id > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM product_details WHERE detail_id=?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

?>