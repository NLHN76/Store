<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

// Kết nối tới cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Ghi log lỗi thay vì chỉ die() - tốt hơn cho production
    error_log("Database Connection Failed: " . $conn->connect_error);
    // Trả về lỗi JSON để client có thể xử lý
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Kết nối cơ sở dữ liệu thất bại.']);
    exit; // Dừng thực thi script
}

 
$conn->set_charset("utf8");


$sql = "SELECT product_code, name, price, image, category, is_active
        FROM products
        WHERE is_active = 1"; 

$result = $conn->query($sql);

$products = [];
// Kiểm tra xem truy vấn có thành công không
if ($result === false) {
     // Ghi log lỗi SQL
    error_log("SQL Error: " . $conn->error);
    // Trả về lỗi JSON
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Lỗi truy vấn dữ liệu sản phẩm.']);
    $conn->close(); // Đóng kết nối trước khi thoát
    exit;
}

if ($result->num_rows > 0) {
    // Lưu dữ liệu sản phẩm vào mảng
    while ($row = $result->fetch_assoc()) {
     
      
        if (!empty($row['image'])) {
            $row['image'] = 'admin/uploads/' . $row['image'];
        } else {
           
             $row['image'] = ''; 
            
        }

        // Chuyển đổi kiểu dữ liệu nếu cần (ví dụ: price và is_active)
        $row['price'] = floatval($row['price']); 
        $products[] = $row;
    }
}

// Đóng kết nối
$conn->close();

// Trả dữ liệu dưới dạng JSON
header('Content-Type: application/json');
echo json_encode($products);
?>