<?php

require_once "../db.php";

// Xử lý form POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['login-email']);
    $name = trim($_POST['login-name']);
    $password = $_POST['login-password'];

    // Chuẩn bị truy vấn người dùng theo email + name
    $stmt = $conn->prepare("
        SELECT id, name, password, user_code 
        FROM users 
        WHERE email = ? AND name = ? 
        LIMIT 1
    ");

  
    $stmt->bind_param("ss", $email, $name);
    $stmt->execute();
    $stmt->store_result();

    // Nếu tìm thấy user
    if ($stmt->num_rows === 1) {

        $stmt->bind_result($id, $db_name, $hashed_password, $user_code);
        $stmt->fetch();

        // Kiểm tra mật khẩu
        if (password_verify($password, $hashed_password)) {

            $_SESSION['user_id']   = $id;
            $_SESSION['user_name'] = $db_name;
            $_SESSION['user_code'] = $user_code;

            echo "<script>
            alert('Đăng nhập thành công');
           window.location.href = 'user_login.html';
         </script>";


        } else {
            echo "<div style='color:red;'>Sai mật khẩu. Vui lòng thử lại.</div>";
        }

    } else {
        echo "<div style='color:red;'>Email hoặc tên không đúng.</div>";
    }

    $stmt->close();
}

$conn->close();
?>
