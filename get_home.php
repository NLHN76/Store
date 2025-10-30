<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

// Kết nối tới cơ sở dữ liệu
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Truy vấn bảng home
$sql = "SELECT title, description, image FROM home";
$result = $conn->query($sql);

$homeData = [];
if ($result->num_rows > 0) {
    // Lưu dữ liệu vào mảng
    while ($row = $result->fetch_assoc()) {
        $row['image'] = 'admin/uploads/' . $row['image']; 
        $homeData[] = $row;
    }
}

// Đóng kết nối
$conn->close();

// Trả dữ liệu dưới dạng JSON
header('Content-Type: application/json');
echo json_encode($homeData);
?>


