<?php
session_start();
// Xóa thông tin session và đăng xuất
session_unset();
session_destroy();
// Chuyển hướng về trang đăng nhập
header('Location: admin.html');
exit();
?>