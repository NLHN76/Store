<?php

/* LẤY CHI TIẾT SẢN PHẨM */
$sql = "
    SELECT
        p.id AS product_id,
        p.name,
        d.*
    FROM products p
    LEFT JOIN product_details d
        ON p.id = d.product_id
";

$result = $conn->query($sql);


/* LẤY DANH SÁCH SẢN PHẨM */
$products = $conn->query("
    SELECT id, name
    FROM products
    ORDER BY name ASC
");

?>