<?php
require_once "../db.php";

// Xử lý xóa dữ liệu trước (để load lại danh sách)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = $_POST['id'];
    $delete_sql = "DELETE FROM contact WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Đã xóa thành công!'); window.location.href = '';</script>";
        exit;
    } else {
        echo "<script>alert('Xóa không thành công.');</script>";
    }
}

// Xử lý tìm kiếm
$search_query = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_query = trim($_POST['search_query']);

    $sql = "SELECT id, user_id, name, email, phone, message, created_at 
            FROM contact 
            WHERE name LIKE ? 
               OR email LIKE ?
               OR phone LIKE ? 
            ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    $like_query = "%" . $search_query . "%";
    $stmt->bind_param("sss", $like_query, $like_query, $like_query);
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    // Truy vấn mặc định lấy toàn bộ dữ liệu
    $sql = "SELECT id, user_id, name, email, phone, message, created_at 
            FROM contact 
            ORDER BY id DESC";

    $result = $conn->query($sql);
}
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
           <link rel="stylesheet" href="css/contact.css">
</head>

<body>
    <div class="container">
        <a class="back-button" href="admin_interface.php">
            <img src="uploads/exit.jpg" alt="Quay lại">
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