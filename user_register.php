<?php

require_once "db.php";

// Hàm tạo mã user_code ngẫu nhiên
function generateUserCode($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        try {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        } catch (Exception $e) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }
    }
    return $randomString;
}

// Hàm gửi email xác nhận đăng ký
function sendConfirmationEmail($name, $email) {

   require 'pay/vendor/phpmailer/phpmailer/src/PHPMailer.php';
   require 'pay/vendor/phpmailer/phpmailer/src/SMTP.php';
   require 'pay/vendor/phpmailer/phpmailer/src/Exception.php';
   require 'pay/vendor/autoload.php';

    
    $mail = new PHPMailer\PHPMailer\PHPMailer();
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'Namdo2003hp@gmail.com';  // Email gửi đi
        $mail->Password = 'pdkd ujkn piok qrnu';    // Mật khẩu ứng dụng Gmail
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('Namdo2003hp@gmail.com', 'Mobile Gear');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Đăng ký tài khoản thành công - Mobile Gear';
        $mail->Body    = "
            <h3>Chào $name,</h3>
            <p>Bạn đã đăng ký tài khoản thành công với email <strong>$email</strong>.</p>
            <p>Cảm ơn bạn đã tham gia cùng <strong>Mobile Gear</strong>!</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Không thể gửi email xác nhận: {$mail->ErrorInfo}");
    }
}




// Xử lý form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['register-name'] ?? '';
    $email = $_POST['register-email'] ?? '';
    $password = $_POST['register-password'] ?? '';

    // Kiểm tra đầu vào
    if (empty($name) || empty($email) || empty($password)) {
        echo "Vui lòng điền đầy đủ thông tin.";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Email không hợp lệ.";
        exit;
    }

    if (strlen($password) < 6) {
        echo "Mật khẩu phải có ít nhất 6 ký tự.";
        exit;
    }

    // Kiểm tra trùng tên hoặc email
    $check_sql = "SELECT * FROM users WHERE email = ? OR name = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("ss", $email, $name);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        echo "Tên hoặc email đã được sử dụng, vui lòng chọn tên hoặc email khác.";
        $stmt_check->close();
    } else {
        $stmt_check->close();

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $max_attempts = 5;
        $attempt = 0;
        $inserted = false;

        while ($attempt < $max_attempts && !$inserted) {
            $user_code = generateUserCode(8);
            $insert_sql = "INSERT INTO users (name, email, password, user_code) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("ssss", $name, $email, $hashed_password, $user_code);

            if ($stmt_insert->execute()) {
                echo "Đăng ký thành công. Mã khách hàng của bạn là: " . htmlspecialchars($user_code);
                sendConfirmationEmail($name, $email);
                $inserted = true;
            } elseif ($conn->errno == 1062) {
                // Trùng user_code
                $attempt++;
                $stmt_insert->close();
            } else {
                echo "Lỗi khi đăng ký: " . $stmt_insert->error;
                $stmt_insert->close();
                break;
            }
        }

        if (!$inserted && $attempt >= $max_attempts) {
            echo "Không thể tạo mã người dùng duy nhất sau nhiều lần thử. Vui lòng thử lại sau.";
        }
    }
}

$conn->close();
?>
