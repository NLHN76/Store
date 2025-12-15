<?php
require_once "../../db.php";
require_once "function.php";

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin Liên Hệ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            <input type="text" name="search_query"
                placeholder="Nhập tên hoặc số điện thoại"
                value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" name="search"><i class="fas fa-search"></i> Tìm kiếm</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Nội dung</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['phone']}</td>
                                <td>{$row['message']}</td>
                                <td>
                                    <form class='delete-form' action='' method='POST'>
                                        <input type='hidden' name='id' value='{$row['id']}'>
                                        <button class='delete-button' type='submit' name='delete' onclick='return confirm(\"Bạn có chắc chắn muốn xóa không?\")'><i class='fas fa-trash-alt'></i> Xóa</button>
                                    </form>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Không tìm thấy kết quả phù hợp.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <?php $conn->close(); ?>
    </div>
</body>

</html>