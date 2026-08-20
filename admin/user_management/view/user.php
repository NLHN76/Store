<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Quản Lý Người Dùng</title>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer">

    <link rel="stylesheet" href="css/admin_users.css">
</head>

<body>

    <div class="container">

        <a class="back-button" href="../admin_interface.php">
            <img src="../uploads/exit.jpg" alt="Quay lại">
        </a>

        <h1>
            <i class="fas fa-users"></i>
            Danh Sách Khách Hàng
        </h1>

        <form method="GET" class="search-box">

            <input type="text"
                name="keyword"
                placeholder="Nhập mã khách hàng, tên hoặc email..."
                value="<?= htmlspecialchars($keyword) ?>">

            <button type="submit">
                <i class="fas fa-search"></i> Tìm kiếm
            </button>

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

                <?php if ($result->num_rows > 0): ?>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>
                            <td><?= htmlspecialchars($row['user_code']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="3">Không có người dùng nào.</td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</body>
</html>

