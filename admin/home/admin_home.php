<?php
$dsn = 'mysql:host=localhost;dbname=store;charset=utf8';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn,$username,$password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Kết nối thất bại: " . $e->getMessage());
}

// Banner xử lý
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['banner_action'])){
    $title = $_POST['banner_title'];
    $description = $_POST['banner_description'];
    $image = null;

    if(!empty($_FILES['banner_image']['name'])){
        $image = $_FILES['banner_image']['name'];
        $target = 'uploads/' . basename($image);
        if(strtolower(pathinfo($target, PATHINFO_EXTENSION)) != 'jpg') die("Chỉ hỗ trợ .jpg");
        if(!move_uploaded_file($_FILES['banner_image']['tmp_name'], $target)){
            die("Tải ảnh thất bại. Vui lòng kiểm tra thư mục uploads.");
        }
    }

    $stmtCheck = $pdo->query("SELECT COUNT(*) FROM home WHERE id=1");
    $exists = $stmtCheck->fetchColumn();

    if($exists){
        if($image){
            $stmt = $pdo->prepare("UPDATE home SET title=:title, description=:description, image=:image WHERE id=1");
            $stmt->execute(['title'=>$title,'description'=>$description,'image'=>$image]);
        } else {
            $stmt = $pdo->prepare("UPDATE home SET title=:title, description=:description WHERE id=1");
            $stmt->execute(['title'=>$title,'description'=>$description]);
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO home (id,title,description,image) VALUES (1,:title,:description,:image)");
        $stmt->execute(['title'=>$title,'description'=>$description,'image'=>$image]);
    }
    echo "<script>alert('Banner cập nhật thành công!'); window.location='admin_home.php';</script>";
}

// Lấy banner
$stmt = $pdo->query("SELECT * FROM home WHERE id=1");
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

// Khuyến mãi xử lý
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promo_action'])){
    $action = $_POST['promo_action'];
    $id = $_POST['promo_id'] ?? null;
    $title = $_POST['promo_title'];
    $description = $_POST['promo_description'];
    $link = $_POST['promo_link'] ?? null;

    if($action === 'add'){
        if(!empty($_FILES['promo_image']['name'])){
            $image = $_FILES['promo_image']['name'];
            $target = 'uploads/' . basename($image);
            if(strtolower(pathinfo($target, PATHINFO_EXTENSION)) != 'jpg') die("Chỉ hỗ trợ .jpg");
            if(!move_uploaded_file($_FILES['promo_image']['tmp_name'], $target)) die("Tải ảnh thất bại.");
            $stmt = $pdo->prepare("INSERT INTO promotions (title,description,image,link) VALUES (:title,:description,:image,:link)");
            $stmt->execute(['title'=>$title,'description'=>$description,'image'=>$image,'link'=>$link]);
        } else die("Vui lòng chọn ảnh khuyến mãi.");
    } elseif($action==='edit' && $id){
        if(!empty($_FILES['promo_image']['name'])){
            $image = $_FILES['promo_image']['name'];
            $target = 'uploads/' . basename($image);
            move_uploaded_file($_FILES['promo_image']['tmp_name'], $target);
            $stmt = $pdo->prepare("UPDATE promotions SET title=:title, description=:description, link=:link, image=:image WHERE id=:id");
            $stmt->execute(['title'=>$title,'description'=>$description,'link'=>$link,'image'=>$image,'id'=>$id]);
        } else {
            $stmt = $pdo->prepare("UPDATE promotions SET title=:title, description=:description, link=:link WHERE id=:id");
            $stmt->execute(['title'=>$title,'description'=>$description,'link'=>$link,'id'=>$id]);
        }
    } elseif($action==='delete' && $id){
        $stmt = $pdo->prepare("DELETE FROM promotions WHERE id=:id");
        $stmt->execute(['id'=>$id]);
    }
    echo "<script>window.location='admin_home.php';</script>";
}

// Lấy tất cả khuyến mãi
$stmt = $pdo->query("SELECT * FROM promotions ORDER BY id DESC");
$promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý Banner & Khuyến Mãi</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body{
    font-family: "Segoe UI", Tahoma, Verdana, sans-serif;
    background: #e0e5ec;
    margin:0; padding:0;
    color:#333;
}
.container{
    max-width:800px; margin:30px auto; padding:20px;
}

/* Khung cửa sổ */
.window{
    background:#fff;
    border-radius:12px;
    padding:20px;
    margin-bottom:25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border:1px solid #ccc;
}

h2{text-align:center;margin-bottom:25px; font-size:1.8em;}
h3{margin-bottom:15px; font-size:1.4em; color:#222;}

form input, form textarea, form button{
    margin-bottom:12px; padding:10px; width:100%;
    border-radius:6px; border:1px solid #ccc; font-size:0.95em;
    box-sizing:border-box;
}
form input:focus, form textarea:focus{
    border-color:#4a90e2; outline:none;
    box-shadow:0 0 5px rgba(74,144,226,0.3);
}
form button{
    background:#4a90e2; color:#fff; border:none; font-weight:600;
    cursor:pointer; transition:0.3s;
}
form button:hover{background:#357ABD;}

table{width:100%; border-collapse:collapse; margin-top:15px;}
th,td{padding:10px; border-bottom:1px solid #e0e0e0; text-align:left; vertical-align:middle; word-wrap:break-word;}
th{background:#4a90e2; color:#fff; font-weight:600;}
img{max-width:80px; max-height:60px; border-radius:5px; object-fit:cover; display:block; margin:auto;}
textarea{resize:vertical; min-height:60px;}

/* Popup modal */
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0; top: 0; width: 100%; height: 100%; 
    overflow: auto; background-color: rgba(0,0,0,0.5); 
}
.modal-content {
    background-color: #fff; margin: 10% auto; padding: 20px;
    border-radius: 12px; width: 400px; box-shadow:0 5px 20px rgba(0,0,0,0.3);
    border:1px solid #ccc; position: relative;
}
.close {color:#aaa; float:right; font-size:24px; font-weight:bold; cursor:pointer;}
.close:hover{color:#000;}


/* Khung cửa sổ với scroll */
.window {
    background:#fff;
    border-radius:12px;
    padding:20px;
    margin-bottom:25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border:1px solid #ccc;
    max-height:400px; /* chiều cao tối đa */
    overflow-y:auto;   /* hiện thanh cuộn dọc khi vượt quá */
}

/* Tối ưu bảng bên trong scroll */
table {
    width:100%;
    border-collapse:collapse;
}
th, td {
    padding:10px;
    border-bottom:1px solid #e0e0e0;
    text-align:left;
}

</style>
</head>
<body>
<div class="container">
    
    <h2>Quản lý Trang Chủ</h2>

    <!-- Banner -->
    <div class="window">
        <h3>Banner Trang Chủ</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="banner_action" value="update">
            <input type="text" name="banner_title" value="<?= htmlspecialchars($banner['title']??'') ?>" placeholder="Tiêu đề banner">
            <textarea name="banner_description" placeholder="Mô tả banner"><?= htmlspecialchars($banner['description']??'') ?></textarea>
            <input type="file" name="banner_image" accept=".jpg">
            <?php if(!empty($banner['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($banner['image']) ?>" alt="Banner">
            <?php endif; ?>
            <button type="submit">Cập nhật Banner</button>
        </form>
    </div>

    <!-- Khuyến mãi -->
    <div class="window">
        <h3>Danh Sách Khuyến Mãi</h3>
        <button id="togglePromoForm">Thêm Khuyến Mãi</button>
        
        <!-- Popup Form -->
        <div id="promoFormModal" class="modal">
          <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Thêm Khuyến Mãi</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="promo_action" value="add">
                <input type="text" name="promo_title" placeholder="Tiêu đề">
                <textarea name="promo_description" placeholder="Mô tả"></textarea>
                <input type="text" name="promo_link" placeholder="Link chi tiết">
                <input type="file" name="promo_image" accept=".jpg">
                <button type="submit">Thêm Khuyến Mãi</button>
            </form>
          </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Tiêu đề</th><th>Mô tả</th><th>Hình ảnh</th><th>Link</th><th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($promotions as $p): ?>
                    <?php if(empty($p['title']) && empty($p['description']) && empty($p['image'])) continue; ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= !empty($p['title']) ? htmlspecialchars($p['title']) : '-' ?></td>
                        <td><?= !empty($p['description']) ? htmlspecialchars($p['description']) : '-' ?></td>
                        <td><?php if(!empty($p['image'])): ?><img src="uploads/<?= htmlspecialchars($p['image']) ?>" alt=""><?php endif; ?></td>
                        <td><?= !empty($p['link']) ? htmlspecialchars($p['link']) : '-' ?></td>
                        <td>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="promo_id" value="<?= $p['id'] ?>">
                                <input type="text" name="promo_title" value="<?= htmlspecialchars($p['title']) ?>">
                                <textarea name="promo_description"><?= htmlspecialchars($p['description']) ?></textarea>
                                <input type="text" name="promo_link" value="<?= htmlspecialchars($p['link']) ?>">
                                <input type="file" name="promo_image" accept=".jpg">
                                <button type="submit" name="promo_action" value="edit">Sửa</button>
                                <button type="submit" name="promo_action" value="delete" onclick="return confirm('Xóa khuyến mãi này?')">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const modal = document.getElementById("promoFormModal");
const btn = document.getElementById("togglePromoForm");
const span = document.getElementsByClassName("close")[0];

btn.onclick = function() { modal.style.display = "block"; }
span.onclick = function() { modal.style.display = "none"; }
window.onclick = function(event) { if(event.target == modal){ modal.style.display = "none"; } }
</script>
</body>
</html>
