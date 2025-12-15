<?php
require_once "../db.php";
// Kiểm tra xem token có được truyền vào không
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Kiểm tra xem token có hợp lệ không
    $sql = "SELECT * FROM password_resets WHERE token='$token'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Token hợp lệ, hiển thị biểu mẫu để người dùng nhập tên và mật khẩu mới
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $new_name = $_POST['new_name'];
            $new_password = $_POST['new_password'];

            // Cập nhật tên và mật khẩu cho người dùng
            $row = $result->fetch_assoc();
            $email = $row['email'];

            // Mã hóa mật khẩu trước khi lưu vào cơ sở dữ liệu
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET name='$new_name', password='$hashed_password' WHERE email='$email'";
            if ($conn->query($sql) === TRUE) {
                // Xóa token sau khi đã đặt lại tên và mật khẩu
                $conn->query("DELETE FROM password_resets WHERE token='$token'");
                echo "Tên và mật khẩu đã được cập nhật thành công!";
            } else {
                echo "Đã xảy ra lỗi khi cập nhật thông tin!";
            }
        }
    } else {
        echo "Token không hợp lệ hoặc đã hết hạn!";
    }
} else {
    echo "Không có token nào được cung cấp!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại tên và mật khẩu</title>
</head>

<body>
    <h2>Đặt lại tên và mật khẩu</h2>
    <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">
        <div class="form-group">
            <label for="new_name">Nhập tên mới:</label>
            <input type="text" id="new_name" name="new_name" required>
        </div>
        <div class="form-group">
            <label for="new_password">Nhập mật khẩu mới:</label>
            <input type="password" id="new_password" name="new_password" required>
        </div>
        <button type="submit">Cập nhật thông tin</button>
        <button onclick="window.location.href='http://localhost/store/user/user.html';">Quay lại Trang Chủ</button>
    </form>
</body>

</html>