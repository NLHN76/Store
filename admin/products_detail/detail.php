<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý chi tiết sản phẩm</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="product_detail.css">
</head>
<body>

<div class="container mt-4 mb-4">
  <!-- Header -->
  <div class="page-header mb-3">
    <h5>Quản lý chi tiết sản phẩm</h5>
    <a href="../admin_interface.php" class="btn btn-light btn-sm">
      <img src="../uploads/exit.jpg" alt="Quay lại" style="width:20px; height:20px; border-radius:4px;">
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
