<?php
// Kết nối đến cơ sở dữ liệu
$dsn = 'mysql:host=localhost;dbname=store;charset=utf8';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Kết nối không thành công: ' . $e->getMessage());
}

// Thêm thông tin về trang chủ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $title = $_POST['home_title'];
    $description = $_POST['home_description'];
    $image = $_FILES['home_image']['name'];
    $target = 'uploads/' . basename($image);

    // Kiểm tra định dạng file
    $imageFileType = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if ($imageFileType != 'jpg') {
        die("Chỉ hỗ trợ định dạng hình ảnh .jpg.");
    }

    if (move_uploaded_file($_FILES['home_image']['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO home (title, description, image) VALUES (:title, :description, :image)");
        $stmt->execute(['title' => $title, 'description' => $description, 'image' => $image]);
        echo "<script>alert('Thông tin trang chủ đã được thêm thành công!');</script>";
    } else {
        echo "<script>alert('Đã xảy ra lỗi khi tải lên hình ảnh. Vui lòng kiểm tra quyền truy cập thư mục uploads.');</script>";
    }
}


// Sửa thông tin về trang chủ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['home_id'];
    $title = $_POST['home_title'];
    $description = $_POST['home_description'];

    // Kiểm tra xem có tải hình ảnh mới không
    if (!empty($_FILES['home_image']['name'])) {
        $image = $_FILES['home_image']['name'];
        $target = 'uploads/' . basename($image);

        // Kiểm tra định dạng file
        $imageFileType = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        if ($imageFileType != 'jpg') {
            die("Chỉ hỗ trợ định dạng hình ảnh .jpg.");
        }

        if (move_uploaded_file($_FILES['home_image']['tmp_name'], $target)) {
            // Cập nhật thông tin trang chủ với hình ảnh mới
            $stmt = $pdo->prepare("UPDATE home SET title = :title, description = :description, image = :image WHERE id = :id");
            $stmt->execute(['title' => $title, 'description' => $description, 'image' => $image, 'id' => $id]);
             echo "<script>alert('Thông tin trang chủ đã được sửa thành công!');</script>";
        } else {
            echo "<script>alert('Đã xảy ra lỗi khi tải lên hình ảnh. Vui lòng kiểm tra quyền truy cập thư mục uploads.');</script>";
        }
    } else {
        // Nếu không có hình ảnh mới, chỉ cập nhật tiêu đề và mô tả
        $stmt = $pdo->prepare("UPDATE home SET title = :title, description = :description WHERE id = :id");
        $stmt->execute(['title' => $title, 'description' => $description, 'id' => $id]);
         echo "<script>alert('Thông tin trang chủ đã được sửa thành công!');</script>";
    }
}



// Xóa thông tin về trang chủ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['home_id'];

    // Xóa thông tin
    $stmt = $pdo->prepare("DELETE FROM home WHERE id = :id");
    $stmt->execute(['id' => $id]);

   echo "<script>alert('Thông tin đã được xóa thành công!');</script>";
}

// Lấy danh sách thông tin về trang chủ
$stmt = $pdo->query("SELECT * FROM home");
$homes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>




<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUẢN LÝ TRANG CHỦ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
   
  
  <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #495057;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        h2 {
            color: #343a40;
            margin-bottom: 20px;
            text-align: center;
            font-size: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        input[type="text"],
        textarea {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: calc(50% - 10px);
            transition: border-color 0.2s ease-in-out;
        }

        textarea {
            height: 100px;
            width: 100%;
        }

        input[type="file"] {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 100%;
        }

        input[type="file"]:focus,
        input[type="text"]:focus,
        textarea:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
            width: auto;
            font-size: 1rem;
        }

        button:hover {
            background-color: #0056b3;
        }

        img {
            max-width: 100px;
            height: auto;
            border-radius: 4px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        img:hover {
            transform: scale(1.1);
        }

        .add-form {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .add-form h3 {
            color: #343a40;
            margin-bottom: 15px;
            text-align: center;
            font-size: 1.5rem;
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

        @media (max-width: 768px) {
            input[type="text"],
            textarea {
                width: 100%;
            }
        }

        .center-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh; 
}

.toggle-btn {
    padding: 10px 20px;
    font-size: 16px;
    cursor: pointer;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 8px;
    margin-bottom: 20px;
    transition: background-color 0.3s ease;
}

.toggle-btn:hover {
    background-color: #0056b3;
}

.add-form {
    background-color: #f9f9f9;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 500px;
}

.add-form input[type="text"],
.add-form textarea,
.add-form input[type="file"],
.add-form button {
    width: 100%;
    margin-bottom: 15px;
    padding: 10px;
    font-size: 15px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

.add-form button {
    background-color: #28a745;
    color: white;
    border: none;
}

.add-form button:hover {
    background-color: #218838;
}


.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4); 
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999;
}


.add-form {
    background: #fff;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.3);
    width: 100%;
    max-width: 500px;
}


    </style>
</head>

<body>
    <div class="container">
        <a class="back-button" href="admin_interface.php">
            <img src="uploads/exit.jpg" alt="Quay lại">
        </a>
        <h2>Danh Sách Thông Tin Trang Chủ</h2>

        <div class="center-wrapper">
    <button onclick="toggleForm()" class="toggle-btn">
        <i class="fas fa-plus-circle"></i> Thêm Thông Tin Trang Chủ
    </button>
        <table>
            <thead>
                <tr>
                    <th>Mục</th>
                    <th>Tiêu Đề</th>
                    <th>Nội Dung</th>
                    <th>Hình Ảnh</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($homes as $home): ?>
                    <tr>
                        <td><?= $home['id'] ?></td>
                        <td><?= htmlspecialchars($home['title']) ?></td>
                        <td><?= htmlspecialchars($home['description']) ?></td>
                        <td>
                            <?php if (!empty($home['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($home['image']) ?>"
                                    alt="<?= htmlspecialchars($home['title']) ?>">
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="home_id" value="<?= $home['id'] ?>">
                                <input type="text" name="home_title" value="<?= htmlspecialchars($home['title']) ?>"
                                    required placeholder="Tiêu đề">
                                <textarea name="home_description" required placeholder="Nội dung"><?= htmlspecialchars($home['description']) ?></textarea>
                                <input type="file" name="home_image" accept=".jpg">
                                <button type="submit" name="action" value="edit"><i class="fas fa-edit"></i> Sửa</button>
                                <button type="submit" name="action" value="delete"><i class="fas fa-trash"></i> Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>



        <div id="formModal" class="modal-overlay" style="display: none;">
    <div class="add-form" id="homeForm">
        <h3>Thêm Thông Tin Trang Chủ</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <input type="text" name="home_title" required placeholder="Tiêu đề">
            <textarea name="home_description" required placeholder="Nội dung"></textarea>
            <input type="file" name="home_image" accept=".jpg" required>
            <button type="submit"><i class="fas fa-plus"></i> Thêm</button>
        </form>
    </div>
</div>

</div>

<script>
function toggleForm() {
    const modal = document.getElementById('formModal');
    modal.style.display = (modal.style.display === 'none' || modal.style.display === '') ? 'flex' : 'none';
}

// Tắt form nếu người dùng click ra ngoài form
window.addEventListener('click', function(event) {
    const modal = document.getElementById('formModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});
</script>


    </div>
</body>

</html>