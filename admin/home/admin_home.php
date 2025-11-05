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

// ---------------- Banner -----------------
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

// ---------------- Promotions -----------------
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
body{font-family:Segoe UI,Tahoma,Verdana,sans-serif;background:#f8f9fa;margin:0;padding:0;}
.container{max-width:1200px;margin:20px auto;padding:20px;background:#fff;border-radius:8px;box-shadow:0 0 15px rgba(0,0,0,0.05);}
h2{text-align:center;margin-bottom:20px;}
table{width:100%;border-collapse:collapse;margin-bottom:20px;}
th,td{padding:10px;border-bottom:1px solid #ddd;}
th{background:#343a40;color:white;}
img{max-width:100px;border-radius:4px;}
form input, form textarea, form button{margin-bottom:10px;padding:8px;width:100%;}
button{cursor:pointer;}
</style>
</head>
<body>
<div class="container">
<h2>Banner Trang Chủ</h2>
<?php if(!empty($banner['title']) || !empty($banner['description']) || !empty($banner['image'])): ?>
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
<?php endif; ?>

<h2>Danh Sách Khuyến Mãi</h2>
<button onclick="document.getElementById('promoForm').style.display='block'">Thêm Khuyến Mãi</button>
<div id="promoForm" style="display:none;">
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="promo_action" value="add">
<input type="text" name="promo_title" placeholder="Tiêu đề">
<textarea name="promo_description" placeholder="Mô tả"></textarea>
<input type="text" name="promo_link" placeholder="Link chi tiết">
<input type="file" name="promo_image" accept=".jpg">
<button type="submit">Thêm Khuyến Mãi</button>
</form>
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
</body>
</html>
