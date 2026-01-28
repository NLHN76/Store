<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
     <link rel="stylesheet" href="admin_users.css">
</head>

<body>
    <div class="container">
        <a class="back-button" href="../admin_interface.php">
            <img src="../uploads/exit.jpg" alt="Quay lại">
        </a>

        <h1><i class="fas fa-users"></i> Danh Sách Khách hàng</h1>

        <form method="GET" class="search-box">
            <input type="text" name="keyword"
                placeholder="Nhập mã khách hàng ,tên hoặc email..."
                value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>">
            <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Mã Khách Hàng</th>
                    <th>Tên</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>

                <?php
               

                // Lấy từ khóa tìm kiếm nếu có
                $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

                // Tạo truy vấn SQL
                $sql = "SELECT user_code, name, email FROM users";
                if (!empty($keyword)) {
                    $sql .= " WHERE user_code LIKE '%" . $conn->real_escape_string($keyword) . "%' 
                                  OR name LIKE '%" . $conn->real_escape_string($keyword) . "%' 
                                  OR email LIKE '%" . $conn->real_escape_string($keyword) . "%'";
                }

                $result = $conn->query($sql);

                // Hiển thị dữ liệu
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row["user_code"]) . "</td>
                                <td>" . htmlspecialchars($row["name"]) . "</td>
                                <td>" . htmlspecialchars($row["email"]) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Không có người dùng nào.</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>