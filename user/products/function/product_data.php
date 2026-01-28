<?php
   // ====== Lấy mã sản phẩm từ URL ======
$code = $_GET['code'] ?? '';

$stmt = $conn->prepare("
    SELECT p.*, d.description, d.material, d.compatibility, d.warranty, d.origin, d.features
    FROM products p
    LEFT JOIN product_details d ON p.id = d.product_id
    WHERE p.product_code = ? AND p.is_active = 1
");
$stmt->bind_param("s", $code);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) die("Không tìm thấy sản phẩm!");


// ====== Lấy sản phẩm gợi ý ======
$stmt_suggested = $conn->prepare("SELECT * FROM products WHERE product_code != ? AND category = ? AND is_active = 1 ORDER BY RAND() LIMIT 4");
$stmt_suggested->bind_param("ss", $product['product_code'], $product['category']);
$stmt_suggested->execute();
$related_products = $stmt_suggested->get_result();
$stmt_suggested->close();


?>