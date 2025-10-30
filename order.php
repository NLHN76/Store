<?php
session_start(); 


// Lấy user_code từ session (người dùng đã đăng nhập)
$user_code = $_SESSION['user_code'];

// Kết nối cơ sở dữ liệu 
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'store';
$conn = new mysqli($host, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Truy vấn lấy đơn hàng của người dùng
$sql = "SELECT * FROM payment WHERE user_code = '$user_code'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Lấy tất cả các đơn hàng và trả về dưới dạng JSON
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    echo json_encode($orders);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Không có đơn hàng nào']);
}

$conn->close();
?>
