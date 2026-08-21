<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Liên hệ</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="css/contact.css">
</head>

<body>

<div class="container">

    <a class="back-button" href="../admin_interface.php">
        <img src="../uploads/exit.jpg" alt="Quay lại">
    </a>

    <h1>
        <i class="fas fa-envelope"></i>
        Danh Sách Người Dùng Liên Hệ
    </h1>

    <table>
        <thead>
            <tr>
                <th>Tên</th>
                <th>Email</th>
                <th>Điện thoại</th>
                <th>Nội dung</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>

        <tbody id="contactList">

        </tbody>
    </table>

</div>

<script src="js/contact.js"></script>

</body>
</html>