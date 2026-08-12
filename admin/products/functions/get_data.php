<?php

// =================== TÌM KIẾM ===================

$search = trim(
    $_GET['search'] ?? ''
);


$sql = "SELECT * FROM products";


if ($search !== "") {

    $sql .= "
        WHERE name LIKE ?
        OR product_code LIKE ?
        OR category LIKE ?
    ";

    $search_param = "%$search%";


    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sss",
        $search_param,
        $search_param,
        $search_param
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $products = $result->fetch_all(
        MYSQLI_ASSOC
    );

    $stmt->close();

} else {

    $result = $conn->query($sql);

    $products = $result->fetch_all(
        MYSQLI_ASSOC
    );
}

?>