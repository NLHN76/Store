<?php
require_once "../db.php";

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $cmt = $_POST['cmt'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Kiểm tra email đã tồn tại chưa
    $check = $conn->prepare("SELECT id FROM shipper WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if($check->num_rows > 0){
        $error = "Email này đã được sử dụng. Vui lòng dùng email khác.";
    } else {
        // Upload avatar
        $avatar = null;
        if(isset($_FILES['avatar']) && $_FILES['avatar']['error']==0){
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatar = 'uploads/'.time()."_".$name.".".$ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar);
        }

        $stmt = $conn->prepare("INSERT INTO shipper (name,email,phone,dob,cmt,avatar,password) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("sssssss",$name,$email,$phone,$dob,$cmt,$avatar,$password);
        if($stmt->execute()){
            $shipper_id = $stmt->insert_id;

            $_SESSION['shipper_id'] = $shipper_id;
            $_SESSION['shipper_name'] = $name;

            header("Location: shipper_dashboard.php");
            exit;
        } else {
            $error = $stmt->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng ký Shipper</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: #f5f5f5;
}
.card {
    max-width: 500px;
    margin: 50px auto;
    padding: 20px 30px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
img#avatarPreview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 10px;
}
</style>
</head>
<body>
<div class="card">
    <h3 class="text-center mb-4">Đăng ký Shipper</h3>
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3 text-center">
            <img id="avatarPreview" src="https://via.placeholder.com/100" alt="Avatar">
        </div>
        <div class="mb-3">
            <label class="form-label">Họ tên</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Số điện thoại</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Ngày sinh</label>
            <input type="date" name="dob" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">CMND/CCCD</label>
            <input type="text" name="cmt" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Ảnh đại diện</label>
            <input type="file" name="avatar" class="form-control" accept="image/*" onchange="previewAvatar(this)">
        </div>
        <div class="mb-3">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary w-100">Đăng ký</button>
    </form>
</div>

<script>
function previewAvatar(input){
    if(input.files && input.files[0]){
        var reader = new FileReader();
        reader.onload = function(e){
            document.getElementById('avatarPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
