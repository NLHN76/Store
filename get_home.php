<?php
$dsn = 'mysql:host=localhost;dbname=store;charset=utf8';
$username = 'root';
$password = '';

$pdo = new PDO($dsn,$username,$password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Lấy banner
$stmt = $pdo->query("SELECT * FROM home WHERE id=1");
$banner = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy khuyến mãi
$stmt = $pdo->query("SELECT * FROM promotions ORDER BY id DESC");
$promotions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thêm đường dẫn ảnh
if(!empty($banner['image'])) $banner['image'] = 'admin/home/uploads/'.$banner['image'];
foreach($promotions as &$p){
    if(!empty($p['image'])) $p['image'] = 'admin/home/uploads/'.$p['image'];
}

// Trả JSON
header('Content-Type: application/json');
echo json_encode(['banner'=>$banner,'promotions'=>$promotions]);
?>
