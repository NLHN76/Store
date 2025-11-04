<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

// Kết nối tới cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Kết nối cơ sở dữ liệu thất bại.']);
    exit;
}

$conn->set_charset("utf8");

// ✅ Lấy sản phẩm + điểm trung bình + số lượt đánh giá
$sql = "
    SELECT 
        p.id,
        p.product_code, 
        p.name, 
        p.price, 
        p.image, 
        p.category, 
        p.color,       -- 👈 Thêm dòng này để lấy màu trực tiếp
        p.is_active,
        IFNULL(ROUND(AVG(f.rating), 1), 0) AS avg_rating,
        COUNT(f.id) AS total_reviews
    FROM products p
    LEFT JOIN feedback f ON p.product_code = f.product_code
    WHERE p.is_active = 1
    GROUP BY p.id, p.product_code, p.name, p.price, p.image, p.category, p.color, p.is_active
";

$result = $conn->query($sql);
$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // 🟢 Xử lý màu sắc (tách thành mảng nếu có nhiều màu, ngăn cách bằng dấu phẩy)
        $colors = [];
        if (!empty($row['color'])) {
            $colors = array_map('trim', explode(',', $row['color']));
        }

        // 🟢 Format lại dữ liệu
        $row['colors'] = $colors;
        $row['price'] = number_format((float)$row['price'], 0, ',', '.');
        $row['avg_rating'] = floatval($row['avg_rating']);
        $row['total_reviews'] = intval($row['total_reviews']);
        $row['image'] = !empty($row['image']) ? 'admin/uploads/' . $row['image'] : '';

        // Xóa cột color gốc để frontend chỉ dùng colors[]
        unset($row['color']);

        $products[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($products, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$conn->close();
?>
