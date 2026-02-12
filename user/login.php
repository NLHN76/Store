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

            // ✅ CHUYỂN TRANG 
            header("Location: user.html");
            exit;

        } else {
            $error = "Sai mật khẩu.";
        }

    } else {
        $error = "Email hoặc tên không đúng.";
    }

    $stmt->close();
}

$conn->close();
?>
