<?php
require_once "../../db.php";
require_once "function.php"; 
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Liên hệ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../css/contact.css">
 
</head>
<body>
<div class="container">
    <a class="back-button" href="../admin_interface.php">
        <img src="../uploads/exit.jpg" alt="Quay lại">
    </a>
    <h1><i class="fas fa-envelope"></i> Danh Sách Người Dùng Liên Hệ</h1>

    <!-- Form tìm kiếm -->
    <form class="search-form" method="POST">
        <input type="text" name="search_query" placeholder="Nhập id, tên hoặc số điện thoại" value="<?= htmlspecialchars($search_query) ?>">
        <button type="submit" name="search"><i class="fas fa-search"></i> Tìm kiếm</button>
    </form>

    <!-- Bảng hiển thị liên hệ -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Điện thoại</th>
                <th>Nội dung</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $row_class = in_array($row['id'], $new_ids) ? 'new-contact' : 'old-contact';
                    echo "<tr class='$row_class' data-id='{$row['id']}'>
                            <td>{$row['id']}</td>
                            <td>".htmlspecialchars($row['name'])."</td>
                            <td>".htmlspecialchars($row['email'])."</td>
                            <td>".htmlspecialchars($row['phone'])."</td>
                            <td>".htmlspecialchars($row['message'])."</td>
                            <td>".htmlspecialchars($row['created_at'])."</td>
                            <td>
                                <form class='delete-form' method='POST' onsubmit='return confirm(\"Bạn có chắc chắn muốn xóa không?\")'>
                                    <input type='hidden' name='id' value='{$row['id']}'>
                                    <button class='delete-button' type='submit' name='delete'><i class='fas fa-trash-alt'></i> Xóa</button>
                                </form>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Không tìm thấy kết quả phù hợp.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php $conn->close(); ?>
</div>


<script src="js/contact.js"> </script>
</body>
</html>
