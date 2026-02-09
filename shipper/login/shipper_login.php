<?php
require_once "../../db.php";
require_once "login.php";
$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM shipper WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['shipper_id'] = $row['id'];
            $_SESSION['shipper_name'] = $row['name'];

            header("Location: ../shipper_dashboard.php");
            exit;
        }
    }

    $error = "⚠️ Email hoặc mật khẩu không chính xác!";
}
