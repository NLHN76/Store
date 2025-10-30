<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    $default_username = 'admin';
    $default_password = '123';

    if ($username === $default_username && $password === $default_password) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_interface.php');
        exit();
    } else {
        // Hiển thị thông báo lỗi
        echo "<script>alert('Tên đăng nhập hoặc mật khẩu không đúng!'); window.history.back();</script>";
        exit();
    }
}
?>
