<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';

// Kiểm tra nếu form được gửi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Kết nối đến cơ sở dữ liệu (cập nhật thông tin kết nối của bạn)
    $conn = new mysqli('localhost', 'root', '', 'store');

    // Kiểm tra kết nối
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    // Kiểm tra xem email có tồn tại trong cơ sở dữ liệu không
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Tạo token reset mật khẩu
        $token = bin2hex(random_bytes(50));

        // Lưu token và email vào cơ sở dữ liệu hoặc bảng password_resets
        $sql = "INSERT INTO password_resets (email, token) VALUES ('$email', '$token')";
        if ($conn->query($sql) === TRUE) {
            // Gửi email đặt lại mật khẩu
            $reset_link = "http://localhost/store/user/password/reset_password.php?token=" . $token;

            $mail = new PHPMailer(true);
            try {
                // Cấu hình máy chủ
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Địa chỉ SMTP
                $mail->SMTPAuth = true;
                $mail->Username = 'Namdo2003hp@gmail.com'; // Tài khoản email của bạn
                $mail->Password = 'pdkd ujkn piok qrnu'; // Mật khẩu email của bạn
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Thiết lập charset để hiển thị tiếng Việt
                $mail->CharSet = 'UTF-8';

                // Người gửi và người nhận
                $mail->setFrom('Namdo2003hp@gmail.com', 'Nam');
                $mail->addAddress($email);

                // Nội dung email
                $mail->isHTML(true);
                $mail->Subject = 'Yêu cầu đặt lại mật khẩu';
                $mail->Body = 'Click vào link này để đặt lại mật khẩu của bạn: <a href="' . $reset_link . '">' . $reset_link . '</a>';
                $mail->AltBody = 'Click vào link này để đặt lại mật khẩu của bạn: ' . $reset_link;

                $mail->send();
                echo "Kiểm tra email của bạn để đặt lại mật khẩu!";
            } catch (Exception $e) {
                echo "Đã xảy ra lỗi khi gửi email: {$mail->ErrorInfo}";
            }
        } else {
            echo "Đã xảy ra lỗi khi lưu thông tin reset mật khẩu!";
        }
    } else {
        echo "Email không tồn tại trong hệ thống!";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
</head>

<body>
    <h2>Khôi Phục Mật Khẩu</h2>
    <form method="POST" action="forgot_password.php">
        <div class="form-group">
            <label for="email">Nhập email của bạn:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit">Gửi Yêu Cầu</button>
    </form>
</body>

</html>