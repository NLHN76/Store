<?php
// Kết nối CSDL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý form POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['login-email'];
    $password = $_POST['login-password'];

    // Chuẩn bị truy vấn người dùng theo email
    $stmt = $conn->prepare("SELECT id, name, password, user_code FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Nếu tìm thấy user
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password, $user_code);
        $stmt->fetch();

        // Kiểm tra mật khẩu
        if (password_verify($password, $hashed_password)) {
            // Đăng nhập thành công
            session_start();
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_code'] = $user_code;

            // Chuyển hướng đến trang khác sau khi login
            header("Location: user_logout.html"); 
            exit();
        } else {
            echo "<div style='color:red;'>Sai mật khẩu. Vui lòng thử lại.</div>";
        }
    } else {
        echo "<div style='color:red;'>Email không tồn tại. Vui lòng đăng ký tài khoản trước.</div>";
    }

    $stmt->close();
}

$conn->close();
?>
