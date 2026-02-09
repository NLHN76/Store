<?php

require_once "../db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST['login-email']);
    $name     = trim($_POST['login-name']);
    $password = $_POST['login-password'];

    $stmt = $conn->prepare("
        SELECT id, name, password, user_code 
        FROM users 
        WHERE email = ? AND name = ?
        LIMIT 1
    ");

    $stmt->bind_param("ss", $email, $name);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {

        $stmt->bind_result($id, $db_name, $hashed_password, $user_code);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {

            // ✅ LƯU SESSION
            $_SESSION['user_id']   = $id;
            $_SESSION['user_name'] = $db_name;
            $_SESSION['user_code'] = $user_code;

            // ✅ LƯU LOCALSTORAGE + CHUYỂN TRANG
            echo "<script>
                localStorage.setItem('isLoggedIn','true');
                localStorage.setItem('user_name','" . addslashes($db_name) . "');
                alert('Đăng nhập thành công');
                window.location.href = 'user.html';
            </script>";
            exit;

        } else {
            echo "<div style='color:red;'>Sai mật khẩu.</div>";
        }

    } else {
        echo "<div style='color:red;'>Email hoặc tên không đúng.</div>";
    }

    $stmt->close();
}

$conn->close();
