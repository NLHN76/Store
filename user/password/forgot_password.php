<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];


    $conn = new mysqli('localhost', 'root', '', 'store');


    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

  
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
  
        $token = bin2hex(random_bytes(50));

  
        $sql = "INSERT INTO password_resets (email, token) VALUES ('$email', '$token')";
        if ($conn->query($sql) === TRUE) {
          
            $reset_link = "http://localhost:8080/store/user/password/reset_password.php?token=" . $token;

            $mail = new PHPMailer(true);
            try {
                // Cấu hình máy chủ
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; 
                $mail->SMTPAuth = true;
                $mail->Username = 'Namdo2003hp@gmail.com'; 
                $mail->Password = 'pdkd ujkn piok qrnu'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

             
                $mail->CharSet = 'UTF-8';

             
                $mail->setFrom('Namdo2003hp@gmail.com', 'Nam');
                $mail->addAddress($email);

           
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