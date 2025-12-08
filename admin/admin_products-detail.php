<?php
require_once "../db.php" ;

/* ============================
   🟩 THÊM CHI TIẾT SẢN PHẨM
   ============================ */
if (isset($_POST['add'])) {

    $product_id    = intval($_POST['product_id']);
    $description   = $_POST['description'] ?? '';
    $material      = $_POST['material'] ?? '';
    $compatibility = $_POST['compatibility'] ?? '';
    $warranty      = $_POST['warranty'] ?? '';
    $origin        = $_POST['origin'] ?? '';
    $features      = $_POST['features'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO product_details 
        (product_id, description, material, compatibility, warranty, origin, features) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "issssss",
        $product_id,
        $description,
        $material,
        $compatibility,
        $warranty,
        $origin,
        $features
    );

    $stmt->execute();
    $stmt->close();
}

/* ============================
   🟦 SỬA CHI TIẾT SẢN PHẨM
   ============================ */
if (isset($_POST['update'])) {

    $id            = intval($_POST['detail_id']);
    $description   = $_POST['description'] ?? '';
    $material      = $_POST['material'] ?? '';
    $compatibility = $_POST['compatibility'] ?? '';
    $warranty      = $_POST['warranty'] ?? '';
    $origin        = $_POST['origin'] ?? '';
    $features      = $_POST['features'] ?? '';

    $stmt = $conn->prepare("
        UPDATE product_details
        SET description=?, material=?, compatibility=?, warranty=?, origin=?, features=?
        WHERE detail_id=?
    ");

    $stmt->bind_param(
        "ssssssi",
        $description,
        $material,
        $compatibility,
        $warranty,
        $origin,
        $features,
        $id
    );

    $stmt->execute();
    $stmt->close();
}

/* ============================
   🟥 XÓA CHI TIẾT SẢN PHẨM
   ============================ */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);  // tránh rỗng → lỗi SQL

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM product_details WHERE detail_id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

/* ============================
   🟨 LẤY DỮ LIỆU
   ============================ */
$sql = "SELECT p.id AS product_id, p.name, d.* 
        FROM products p 
        LEFT JOIN product_details d ON p.id = d.product_id";

$result = $conn->query($sql);
$products = $conn->query("SELECT id, name FROM products");
?>


<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý chi tiết sản phẩm</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body {
    background-color: #f8f9fa;
    font-size: 14px;
  }
  .page-header {
    background-color: #0d6efd;
    color: white;
    padding: 10px 16px;
    border-radius: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .page-header h5 {
    margin: 0;
    font-size: 18px;
  }
  .card {
    border: none;
    box-shadow: 0 0 6px rgba(0,0,0,0.05);
  }
  .btn-primary, .btn-success {
    background-color: #0d6efd;
    border: none;
  }
  .btn-primary:hover, .btn-success:hover {
    background-color: #0b5ed7;
  }
  textarea {
    resize: none;
    font-size: 13px;
  }
  .table-wrapper {
    max-height: 500px;
    overflow-y: auto;
  }
  .table thead th {
    position: sticky;
    top: 0;
    background-color: #0d6efd;
    color: white;
    z-index: 2;
  }
</style>
</head>
<body>

<div class="container mt-4 mb-4">
  <!-- Header -->
  <div class="page-header mb-3">
    <h5>Quản lý chi tiết sản phẩm</h5>
    <a href="admin_interface.php" class="btn btn-light btn-sm">
      <img src="uploads/exit.jpg" alt="Quay lại" style="width:20px; height:20px; border-radius:4px;">
      <span class="ms-1">Quay lại</span>
    </a>
  </div>

  <div class="row g-3">
    <!-- Form bên trái -->
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          <h6 class="text-primary mb-3">Thêm chi tiết sản phẩm</h6>
          <form method="POST" class="row g-2">
            <div class="col-12">
              <label class="form-label">Sản phẩm</label>
              <select name="product_id" class="form-select form-select-sm" required>
                <option value="">-- Chọn sản phẩm --</option>
                <?php while($p = $products->fetch_assoc()): ?>
                  <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-12"><input type="text" name="material" class="form-control form-control-sm" placeholder="Chất liệu"></div>
            <div class="col-12"><input type="text" name="compatibility" class="form-control form-control-sm" placeholder="Tương thích"></div>
            <div class="col-12"><input type="text" name="warranty" class="form-control form-control-sm" placeholder="Bảo hành"></div>
            <div class="col-12"><input type="text" name="origin" class="form-control form-control-sm" placeholder="Xuất xứ"></div>
            <div class="col-12"><textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Mô tả"></textarea></div>
            <div class="col-12"><textarea name="features" class="form-control form-control-sm" rows="2" placeholder="Tính năng"></textarea></div>
            <div class="col-12 text-end">
              <button type="submit" name="add" class="btn btn-primary btn-sm px-3">+ Thêm</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Bảng bên phải -->
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h6 class="text-primary mb-3">Danh sách chi tiết sản phẩm</h6>
          <div class="table-wrapper">
            <table class="table table-sm table-striped align-middle">
              <thead class="text-center">
                <tr>
                  <th>ID</th>
                  <th>Tên</th>
                  <th>Mô tả</th>
                  <th>Chất liệu</th>
                  <th>Tương thích</th>
                  <th>Bảo hành</th>
                  <th>Xuất xứ</th>
                  <th>Tính năng</th>
                  <th>Hành động</th>
                </tr>
              </thead>
              <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                  <form method="POST">
                    <td><?php echo $row['detail_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><textarea name="description" class="form-control form-control-sm" rows="2"><?php echo htmlspecialchars($row['description']); ?></textarea></td>
                    <td><input type="text" name="material" value="<?php echo htmlspecialchars($row['material']); ?>" class="form-control form-control-sm"></td>
                    <td><input type="text" name="compatibility" value="<?php echo htmlspecialchars($row['compatibility']); ?>" class="form-control form-control-sm"></td>
                    <td><input type="text" name="warranty" value="<?php echo htmlspecialchars($row['warranty']); ?>" class="form-control form-control-sm"></td>
                    <td><input type="text" name="origin" value="<?php echo htmlspecialchars($row['origin']); ?>" class="form-control form-control-sm"></td>
                    <td><textarea name="features" class="form-control form-control-sm" rows="2"><?php echo htmlspecialchars($row['features']); ?></textarea></td>
                    <td class="text-center text-nowrap">
                      <input type="hidden" name="detail_id" value="<?php echo $row['detail_id']; ?>">
                      <button type="submit" name="update" class="btn btn-success btn-sm">Sửa</button>
                      <a href="?delete=<?php echo $row['detail_id']; ?>" onclick="return confirm('Xóa thật không?')" class="btn btn-danger btn-sm">Xóa</a>
                    </td>
                  </form>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
