<?php
require_once "../../db.php";
require_once "inventory_functions.php";

?>


<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý tồn kho</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
        <link rel="stylesheet" href="css/inventory.css">
</head>
<body>
<div class="container mt-4 mb-5">

<!-- Cảnh báo tồn kho thấp -->
<?php if (!empty($lowStockWarnings)): ?>
<div class="alert alert-warning">
    <strong>Cảnh báo tồn kho thấp!</strong>
    <ul class="mb-0">
        <?php foreach ($lowStockWarnings as $warn): ?>
            <li>
                <a href="#"
                   class="low-stock-link"
                   data-target="<?= htmlspecialchars($warn['row_id']) ?>">
                    <?= htmlspecialchars($warn['text']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>



<div class="d-flex justify-content-between align-items-center mb-3">
<h3><i class="fa-solid fa-boxes-stacked"></i> Quản lý tồn kho</h3>
<a href="../admin_interface.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
</div>

<ul class="nav nav-tabs" id="tabMenu" role="tablist">
<li class="nav-item" role="presentation">
    <button class="nav-link <?= $tab_active=='stock'?'active':'' ?>" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button">Tồn kho</button>
</li>
<li class="nav-item" role="presentation">
    <button class="nav-link <?= $tab_active=='history'?'active':'' ?>" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button">Lịch sử</button>
</li>
</ul>

<div class="tab-content mt-3">

<!-- Tab Tồn kho -->
<div class="tab-pane fade <?= $tab_active=='stock'?'show active':'' ?>" id="stock" role="tabpanel">
    <div class="row">

        <!-- Form nhập hàng -->
<div class="col-lg-4 mb-3">
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-cart-plus"></i> Nhập hàng mới
        </div>
        <div class="card-body">
            <form method="post">
                <!-- Chọn sản phẩm -->
                <div class="mb-2">
                    <label class="form-label">Sản phẩm</label>
                    <select name="product_id" id="product_id" class="form-select" required onchange="loadColors(this.value)">
                        <option value="">-- Chọn sản phẩm --</option>
                        <?php foreach($productOptions as $p): 
                            // Chuyển mảng màu thành chuỗi, ví dụ "Đỏ,Xanh,Vàng"
                            $colorsStr = htmlspecialchars($p['color']);
                        ?>
                        <option value="<?= (int)$p['id'] ?>" data-colors="<?= $colorsStr ?>">
                            <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['product_code']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Chọn màu -->
                <div class="mb-2">
                    <label class="form-label">Màu sắc</label>
                    <select name="color" id="color" class="form-select" required>
                        <option value="">Chọn màu</option>
                    </select>
                </div>

                <!-- Số lượng -->
                <div class="mb-2">
                    <label class="form-label">Số lượng nhập</label>
                    <input type="number" name="quantity" class="form-control" min="1" required>
                </div>

                <!-- Giá nhập  -->
                <div class="mb-2">
                    <label class="form-label">Giá nhập</label>
                    <input type="number" name="import_price" class="form-control" min="0" required>
                </div>

                <button type="submit" name="add_stock" class="btn btn-success w-100">
                    <i class="fa-solid fa-plus"></i> Thêm / Cập nhật
                </button>
            </form>
        </div>
    </div>
</div>

        <!-- Bảng tồn kho -->
        <div class="col-lg-8">
        <table class="table table-bordered table-hover text-center align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Sản phẩm</th>
                    <th>Màu</th>
                    <th>Mã SP</th>
                    <th>Tồn kho</th>
                    <th>Đã bán</th>
                    <th>Giá nhập trung bình</th>
                    <th>Giá bán/1 sản phẩm</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>

            <?php if(empty($inventoryData)): ?>
                <tr>
                    <td colspan="8">Chưa có dữ liệu tồn kho</td>
                </tr>
            <?php else: ?>

            <?php foreach($inventoryData as $productName => $items):
                $collapseId = 'collapse_' . md5($productName);
            ?>
                <!-- Dòng cha -->
                <tr class="parent-row" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                    <td colspan="8" class="text-start fw-bold">
                        <i class="fa-solid fa-box"></i>
                        <?= htmlspecialchars($productName) ?> — Nhấn để xem
                    </td>
                </tr>

                <?php foreach($items as $item):
                    $colorKey = preg_replace('/[^a-zA-Z0-9_-]/','_',$item['color']);
                ?>
                <!-- Dòng con -->
              <tr class="collapse" id="<?= $collapseId ?>">
    <td class="text-start"><?= htmlspecialchars($item['product_name']) ?></td>
    <td><?= htmlspecialchars($item['color']) ?></td>
    <td class="fw-bold text-primary"><?= htmlspecialchars($item['product_code']) ?></td>

    <!-- Tồn kho hiển thị trực tiếp, không cho chỉnh sửa -->
    <td><?= (int)$item['actual_stock'] ?></td>

    <td><?= (int)$item['sold'] ?></td>
    <td><?= number_format($item['import_price'],0,',','.') ?></td>
    <td><?= number_format($item['sale_price'],0,',','.') ?></td>

    <!-- Xóa màu -->
    <td>
        <form method="post" onsubmit="return confirm('Bạn có chắc muốn xóa màu này?');">
            <input type="hidden" name="delete_product_id" value="<?= (int)$item['product_id'] ?>">
            <input type="hidden" name="delete_color" value="<?= htmlspecialchars($item['color']) ?>">
            <button type="submit" name="delete_stock" class="btn btn-sm btn-danger">
                <i class="fa-solid fa-trash"></i> Xóa
            </button>
        </form>
    </td>
</tr>

                <?php endforeach; ?>

            <?php endforeach; ?>
            <?php endif; ?>

            </tbody>
        </table>
        </div>
    </div>
</div>


<!-- Tab Lịch sử -->
<div class="tab-pane fade <?= $tab_active=='history'?'show active':'' ?>" id="history" role="tabpanel">
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="history">
                <div class="col-auto">
                    <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($from_date) ?>" placeholder="Từ ngày">
                </div>
                <div class="col-auto">
                    <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($to_date) ?>" placeholder="Đến ngày">
                </div>
                <div class="col-auto">
                    <input type="text" name="product_code" class="form-control" value="<?= htmlspecialchars($product_code_filter) ?>" placeholder="Mã sản phẩm">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Lọc</button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle table-fixed text-center">
            <thead class="table-secondary">
                <tr>
                    <th>Thời gian</th>
                    <th class="text-start">Sản phẩm</th>
                    <th>Mã SP</th>
                    <th>Màu</th>
                    <th>SL</th>
                    <th>Giá nhập</th>
                    <th>Giá bán/1 sản phẩm</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($history)): ?>
                    <tr><td colspan="8">Không có bản ghi lịch sử</td></tr>
                <?php else: ?>
                    <?php foreach($history as $h): ?>
                    <tr>
                        <td><?= htmlspecialchars($h['created_at']) ?></td>
                        <td class="text-start"><?= htmlspecialchars($h['product_name'] ?? '') ?></td>
                        <td class="fw-bold text-primary"><?= htmlspecialchars($h['product_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($h['color']) ?></td>
                        <td><?= (int)$h['quantity_change'] ?></td>
                        <td><?= number_format($h['import_price'] ?? 0,0,',','.') ?></td>
                        <td><?= number_format($h['sale_price'] ?? 0,0,',','.') ?></td>
                        <td class="text-start"><?= htmlspecialchars($h['note'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>


<script src="inventory.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
