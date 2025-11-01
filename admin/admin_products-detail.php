<?php


$conn = new mysqli("localhost", "root", "", "store");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);


// Thêm
if (isset($_POST['add'])) {
    $product_id   = $_POST['product_id'];
    $description  = $_POST['description'];
    $material     = $_POST['material'];
    $compatibility= $_POST['compatibility'];
    $warranty     = $_POST['warranty'];
    $origin       = $_POST['origin'];
    $features     = $_POST['features'];

    $conn->query("INSERT INTO product_details 
                  (product_id, description, material, compatibility, warranty, origin, features) 
                  VALUES ('$product_id', '$description', '$material', '$compatibility', '$warranty', '$origin', '$features')");
}

// Sửa
if (isset($_POST['update'])) {
    $id           = $_POST['detail_id'];
    $description  = $_POST['description'];
    $material     = $_POST['material'];
    $compatibility= $_POST['compatibility'];
    $warranty     = $_POST['warranty'];
    $origin       = $_POST['origin'];
    $features     = $_POST['features'];

    $conn->query("UPDATE product_details 
                  SET description='$description', material='$material', compatibility='$compatibility',
                      warranty='$warranty', origin='$origin', features='$features'
                  WHERE detail_id=$id");
}

// Xóa
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM product_details WHERE detail_id=$id");
}

// ================= LẤY DỮ LIỆU =================
$sql = "SELECT p.id as product_id, p.name, d.* 
        FROM products p 
        LEFT JOIN product_details d ON p.id = d.product_id";
$result = $conn->query($sql);

// Lấy danh sách sản phẩm để thêm chi tiết
$products = $conn->query("SELECT id, name FROM products");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý chi tiết sản phẩm</title>
<style>
  body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 20px; }
  table { border-collapse: collapse; width: 100%; background: #fff; }
  th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
  th { background: #007bff; color: #fff; }
  form { margin: 0; }
  input, textarea, select { width: 100%; padding: 5px; }
  .btn { padding: 6px 10px; border: none; cursor: pointer; border-radius: 4px; }
  .btn-add { background: #28a745; color: #fff; }
  .btn-update { background: #ffc107; color: #000; }
  .btn-delete { background: #dc3545; color: #fff; text-decoration: none; }
</style>
</head>
<body>

<h2>Quản lý chi tiết sản phẩm</h2>

<a class="back-button" href="admin_interface.php" title="Quay lại trang quản trị">
  <img src="uploads/exit.jpg" alt="Quay lại" style="width:30px; height:50px; object-fit:cover; border-radius:5px;">
</a>

<!-- Form thêm chi tiết -->
<h3>Thêm chi tiết sản phẩm</h3>
<form method="POST">
  <label>Sản phẩm:</label>
  <select name="product_id" required>
    <?php while($p = $products->fetch_assoc()): ?>
      <option value="<?php echo $p['id']; ?>"><?php echo $p['name']; ?></option>
    <?php endwhile; ?>
  </select><br><br>

  <textarea name="description" placeholder="Mô tả"></textarea><br>
  <input type="text" name="material" placeholder="Chất liệu"><br>
  <input type="text" name="compatibility" placeholder="Tương thích"><br>
  <input type="text" name="warranty" placeholder="Bảo hành"><br>
  <input type="text" name="origin" placeholder="Xuất xứ"><br>
  <textarea name="features" placeholder="Tính năng"></textarea><br>
  <button type="submit" name="add" class="btn btn-add">+ Thêm</button>
</form>

<hr>

<!-- Danh sách -->
<h3>Danh sách sản phẩm & chi tiết</h3>
<table>
  <tr>
    <th>ID</th>
    <th>Tên sản phẩm</th>
    <th>Mô tả</th>
    <th>Chất liệu</th>
    <th>Tương thích</th>
    <th>Bảo hành</th>
    <th>Xuất xứ</th>
    <th>Tính năng</th>
    <th>Hành động</th>
  </tr>
  <?php while($row = $result->fetch_assoc()): ?>
  <tr>
    <form method="POST">
      <td><?php echo $row['detail_id']; ?></td>
      <td><?php echo $row['name']; ?></td>
      <td><textarea name="description"><?php echo $row['description']; ?></textarea></td>
      <td><input type="text" name="material" value="<?php echo $row['material']; ?>"></td>
      <td><input type="text" name="compatibility" value="<?php echo $row['compatibility']; ?>"></td>
      <td><input type="text" name="warranty" value="<?php echo $row['warranty']; ?>"></td>
      <td><input type="text" name="origin" value="<?php echo $row['origin']; ?>"></td>
      <td><textarea name="features"><?php echo $row['features']; ?></textarea></td>
      <td>
        <input type="hidden" name="detail_id" value="<?php echo $row['detail_id']; ?>">
        <button type="submit" name="update" class="btn btn-update">Sửa</button>
        <a href="?delete=<?php echo $row['detail_id']; ?>" class="btn btn-delete" onclick="return confirm('Xóa thật không?')">Xóa</a>
      </td>
    </form>
  </tr>
  <?php endwhile; ?>
</table>

</body>
</html>
