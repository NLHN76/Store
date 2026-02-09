<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Shipper Login</title>
 <link rel="stylesheet" href="css/login.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


</head>
<body>

<div class="card-login">
    <h3 class="text-center">Đăng nhập Shipper</h3>

      <?php if (!empty($error)) : ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>


    <form method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" name="login" class="btn btn-primary w-100">
            Đăng nhập
        </button>

        <p class="mt-3 text-center">
            Chưa có tài khoản?
            <a href="../register/shipper_register.php">Đăng ký</a>
        </p>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/login.js"></script>
</body>
</html>