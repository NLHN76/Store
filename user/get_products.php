<?php
require_once "../db.php";

// ✅ Truy vấn: lấy sản phẩm + màu sắc + điểm trung bình + số lượt đánh giá
$sql = "
    SELECT 
        p.product_code, 
        p.name, 
        p.price, 
        p.color,              
        p.image, 
        p.category, 
        p.is_active,
        IFNULL(ROUND(AVG(f.rating), 1), 0) AS avg_rating, 
        COUNT(f.id) AS total_reviews                    
    FROM products p
    LEFT JOIN feedback f ON p.product_code = f.product_code
    WHERE p.is_active = 1
    GROUP BY p.product_code, p.name, p.price, p.color, p.image, p.category, p.is_active
";

$result = $conn->query($sql);
$products = [];

// Kiểm tra truy vấn
if ($result === false) {
    error_log("SQL Error: " . $conn->error);
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Lỗi truy vấn dữ liệu sản phẩm.']);
    $conn->close();
    exit;
}

// Xử lý dữ liệu trả về
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['price'] = number_format((float)$row['price'], 0, ',', '.');
        $row['image'] = !empty($row['image']) ? '../admin/uploads/' . $row['image'] : '';
        $row['color'] = $row['color'] ?? ''; 
        $row['avg_rating'] = floatval($row['avg_rating']);
        $row['total_reviews'] = intval($row['total_reviews']);

        $products[] = $row;
    }
}

// Đóng kết nối
$conn->close();

// Xuất JSON
header('Content-Type: application/json');
echo json_encode($products, JSON_UNESCAPED_UNICODE);
?>
