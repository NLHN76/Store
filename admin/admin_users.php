<?php
require_once "../db.php";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #495057;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        h1 {
            color: #343a40;
            margin-bottom: 20px;
            text-align: center;
            font-size: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
            font-size: 1rem;
        }

        th {
            background-color: #343a40;
            color: white;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #e9ecef;
            transition: background-color 0.2s ease-in-out;
        }

        .search-box {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .search-box input {
            padding: 10px;
            width: 70%;
            border: 1px solid #ced4da;
            border-radius: 4px;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .search-box input:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .search-box button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.2s ease-in-out;
        }

        .search-box button:hover {
            background-color: #0056b3;
        }

        .back-button {
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }

        .back-button img {
            width: 40px;
            height: 40px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <a class="back-button" href="admin_interface.php">
            <img src="uploads/exit.jpg" alt="Quay lại">
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