<?php
// Kết nối với cơ sở dữ liệu MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý biểu mẫu khi người dùng gửi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $message = $_POST['message'];

    // Chuẩn bị câu lệnh SQL để chèn dữ liệu vào bảng contact
    $stmt = $conn->prepare("INSERT INTO contact (name, email, phone, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $phone, $message);

    // Thực hiện câu lệnh và kiểm tra xem có thành công không
    if ($stmt->execute()) {
        echo "Thông tin đã được gửi thành công!";
    } else {
        echo "Lỗi: " . $stmt->error;
    }

    // Đóng câu lệnh
    $stmt->close();
}

// Đóng kết nối
$conn->close();
?>