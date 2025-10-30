<?php
session_start();

// Kết nối đến cơ sở dữ liệu MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Xử lý tìm kiếm
$search_query = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search_query = trim($_POST['search_query']);

    // Tìm kiếm theo tên hoặc số điện thoại
    $sql = "SELECT * FROM contact WHERE name LIKE ? OR phone LIKE ?";
    $stmt = $conn->prepare($sql);
    $like_query = "%" . $search_query . "%";
    $stmt->bind_param("ss", $like_query, $like_query);

    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Truy vấn mặc định lấy toàn bộ dữ liệu
    $sql = "SELECT * FROM contact";
    $result = $conn->query($sql);
}

// Xử lý xóa dữ liệu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = $_POST['id'];
    $delete_sql = "DELETE FROM contact WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Đã xóa thành công!'); window.location.href = '';</script>";
    } else {
        echo "<script>alert('Xóa không thành công.');</script>";
    }
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

        .search-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-form input[type="text"] {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 70%;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .search-form input[type="text"]:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .search-form button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.2s ease-in-out;
        }

        .search-form button:hover {
            background-color: #0056b3;
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

        .delete-form {
            display: inline;
        }

        .delete-button {
            background-color: #dc3545;
            color: white;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.2s ease-in-out;
        }

        .delete-button:hover {
            background-color: #c82333;
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