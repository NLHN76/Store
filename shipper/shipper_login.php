<?php
session_start();
$conn = new mysqli("localhost","root","","store");
if($conn->connect_error) die("Kết nối thất bại: ".$conn->connect_error);

$error = '';
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM shipper WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()){
        if(password_verify($password,$row['password'])){
            $_SESSION['shipper_id'] = $row['id'];
            $_SESSION['shipper_name'] = $row['name'];
            header("Location: shipper_dashboard.php");
            exit;
        } else {
            $error = "Sai mật khẩu!";
        }
    } else {
        $error = "Email không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Shipper Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #f0f2f5;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
.card-login {
    width: 100%;
    max-width: 400px;
    padding: 30px;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.card-login h3 {
    margin-bottom: 25px;
}
</style>
</head>
<body>
<div class="card-login">
    <h3 class="text-center">Đăng nhập Shipper</h3>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" placeholder="Nhập email" required>
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Đăng nhập</button>
        <p class="mt-3 text-center">Chưa có tài khoản? <a href="shipper_register.php">Đăng ký</a></p>
    </form>
</div>
</body>
</html>
