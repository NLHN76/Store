<?php
session_start();

/* ----------------- Kết nối database ----------------- */
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);
$conn->set_charset("utf8mb4");

/* ----------------- Tiện ích ----------------- */
function getProductCode($conn, $product_id) {
    $stmt = $conn->prepare("SELECT product_code FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $res['product_code'] ?? '';
}

/* ----------------- Nhận tham số lọc lịch sử ----------------- */
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$product_code_filter = $_GET['product_code'] ?? '';
$tab_active = $_GET['tab'] ?? 'stock';

$history_where = "";
$params = [];
$types = "";

if($from_date){
    $history_where .= " AND ih.created_at >= ?";
    $params[] = $from_date . " 00:00:00";
    $types .= "s";
}
if($to_date){
    $history_where .= " AND ih.created_at <= ?";
    $params[] = $to_date . " 23:59:59";
    $types .= "s";
}
if($product_code_filter){
    $history_where .= " AND ih.product_code = ?";
    $params[] = $product_code_filter;
    $types .= "s";
}

/* ----------------- Hàm giảm tồn kho khi bán ----------------- */
function reduceStock($conn, $product_code, $color, $quantity_sold, $order_id){
    $stmt = $conn->prepare("SELECT pi.product_id, pi.import_price FROM product_inventory pi JOIN products p ON pi.product_id = p.id WHERE p.product_code = ? AND pi.color = ? LIMIT 1");
    $stmt->bind_param("ss", $product_code, $color);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($row){
        $product_id = $row['product_id'];
        $current_import_price = $row['import_price'];

        $stmt = $conn->prepare("UPDATE product_inventory SET quantity = quantity - ? WHERE product_id = ? AND color = ?");
        $stmt->bind_param("iis", $quantity_sold, $product_id, $color);
        $stmt->execute();
        $stmt->close();

        $note = "Khách hàng mua đơn #$order_id";
        $stmt_hist = $conn->prepare(
            "INSERT INTO inventory_history (product_id, product_code, color, quantity_change, import_price, type, note) 
             VALUES (?, ?, ?, ?, ?, 'Bán hàng', ?)"
        );
        $quantity_change = -$quantity_sold;
        $stmt_hist->bind_param("issids", $product_id, $product_code, $color, $quantity_change, $current_import_price, $note);
        $stmt_hist->execute();
        $stmt_hist->close();
    }
}

/* ----------------- Thêm / Nhập hàng ----------------- */
if(isset($_POST['add_stock'])){
    $product_id = (int)($_POST['product_id'] ?? 0);
    $color = trim($_POST['color'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $import_price = (float)($_POST['import_price'] ?? 0);

    if($product_id && $color !== '' && $quantity > 0){
        $product_code = getProductCode($conn, $product_id);

        $stmt = $conn->prepare("SELECT id, quantity, import_price FROM product_inventory WHERE product_id = ? AND color = ? LIMIT 1");
        $stmt->bind_param("is", $product_id, $color);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if($existing){
            $new_quantity = $existing['quantity'] + $quantity;
            $new_import_price = $new_quantity > 0 ? ((($existing['quantity'] * $existing['import_price']) + ($quantity * $import_price)) / $new_quantity) : $import_price;

            $stmt = $conn->prepare("UPDATE product_inventory SET quantity = ?, import_price = ? WHERE product_id = ? AND color = ?");
            $stmt->bind_param("idis", $new_quantity, $new_import_price, $product_id, $color);
            $stmt->execute();
            $stmt->close();

            $notes = [];
            if($quantity > 0) $notes[] = "Thêm $quantity SL";
            if($new_import_price != $existing['import_price']) $notes[] = "Sửa giá nhập";
            $note = implode(" và ", $notes);
            if($note === '') $note = "Cập nhật tồn kho";

        } else {
            $stmt = $conn->prepare("INSERT INTO product_inventory (product_id, product_code, color, quantity, import_price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issid", $product_id, $product_code, $color, $quantity, $import_price);
            $stmt->execute();
            $stmt->close();
            $note = "Thêm màu mới";
        }

        $stmt_hist = $conn->prepare(
            "INSERT INTO inventory_history (product_id, product_code, color, quantity_change, import_price, type, note) 
             VALUES (?, ?, ?, ?, ?, 'Nhập hàng', ?)"
        );
        $quantity_change = $quantity;
        $stmt_hist->bind_param("issids", $product_id, $product_code, $color, $quantity_change, $import_price, $note);
        $stmt_hist->execute();
        $stmt_hist->close();

        header("Location: admin_inventory.php");
        exit();
    }
}

/* ----------------- Cập nhật tồn kho thực tế theo dòng ----------------- */
if(isset($_POST['update_stock'])){
    $adjusts = $_POST['adjust_stock'] ?? [];
    foreach($adjusts as $product_id_str => $colors){
        $product_id = (int)$product_id_str;
        $product_code = getProductCode($conn, $product_id);

        foreach($colors as $color => $new_stock_raw){
            $new_stock = (int)$new_stock_raw;

            $stmt = $conn->prepare("SELECT quantity, import_price FROM product_inventory WHERE product_id = ? AND color = ?");
            $stmt->bind_param("is", $product_id, $color);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc() ?? ['quantity'=>0,'import_price'=>0];
            $stmt->close();

            $old_stock = (int)$row['quantity'];
            $old_price = (float)$row['import_price'];

            if($new_stock != $old_stock){
                $stmt = $conn->prepare("UPDATE product_inventory SET quantity = ? WHERE product_id = ? AND color = ?");
                $stmt->bind_param("iis", $new_stock, $product_id, $color);
                $stmt->execute();
                $stmt->close();

                $quantity_change = $new_stock - $old_stock;
                $notes = [];
                if($quantity_change != 0) $notes[] = ($quantity_change > 0 ? "Thêm $quantity_change SL" : "Giảm " . abs($quantity_change) . " SL");
                $note = implode(" và ", $notes);
                if($note === '') $note = "Điều chỉnh tồn kho";

                $stmt_hist = $conn->prepare(
                    "INSERT INTO inventory_history (product_id, product_code, color, quantity_change, import_price, type, note)
                     VALUES (?, ?, ?, ?, ?, 'Điều chỉnh', ?)"
                );
                $stmt_hist->bind_param("issids", $product_id, $product_code, $color, $quantity_change, $old_price, $note);
                $stmt_hist->execute();
                $stmt_hist->close();
            }
        }
    }
    header("Location: admin_inventory.php");
    exit();
}

/* ----------------- Xóa sản phẩm theo màu ----------------- */
if(isset($_POST['delete_stock'])){
    $product_id = (int)($_POST['delete_product_id'] ?? 0);
    $color = trim($_POST['delete_color'] ?? '');
    if($product_id && $color !== ''){
        $product_code = getProductCode($conn, $product_id);

        $stmt = $conn->prepare("DELETE FROM product_inventory WHERE product_id = ? AND color = ?");
        $stmt->bind_param("is", $product_id, $color);
        $stmt->execute();
        $stmt->close();

        $note = "Xóa màu '$color' khỏi sản phẩm ID #$product_id";

        $stmt_hist = $conn->prepare(
            "INSERT INTO inventory_history (product_id, product_code, color, quantity_change, import_price, type, note)
             VALUES (?, ?, ?, 0, 0, 'Xóa hàng', ?)"
        );
        $stmt_hist->bind_param("isss", $product_id, $product_code, $color, $note);
        $stmt_hist->execute();
        $stmt_hist->close();
    }
    header("Location: admin_inventory.php");
    exit();
}

/* ----------------- Lấy danh sách sản phẩm ----------------- */
$productOptions = [];
$resP = $conn->query("SELECT id, name, color, product_code FROM products ORDER BY name ASC");
while($r = $resP->fetch_assoc()){
    $productOptions[] = $r;
}
$resP->close();

/* ----------------- Lấy tồn kho ----------------- */
$inventoryData = [];
$resInv = $conn->query("SELECT * FROM product_inventory ORDER BY product_code ASC, color ASC");
while($row = $resInv->fetch_assoc()){
    $stmt2 = $conn->prepare("SELECT COALESCE(SUM(product_quantity),0) as sold 
                             FROM payment 
                             WHERE product_code = ? AND color = ? AND status = 'Đã giao hàng'");
    $stmt2->bind_param("ss", $row['product_code'], $row['color']);
    $stmt2->execute();
    $sold = (int)($stmt2->get_result()->fetch_assoc()['sold'] ?? 0);
    $stmt2->close();

    $stmt3 = $conn->prepare("SELECT name, price FROM products WHERE id = ? LIMIT 1");
    $stmt3->bind_param("i", $row['product_id']);
    $stmt3->execute();
    $p = $stmt3->get_result()->fetch_assoc() ?? ['name'=>'N/A','price'=>0];
    $stmt3->close();

    $row['product_name'] = $p['name'];
    $row['sale_price'] = $p['price'];
    $row['sold'] = $sold;
    $row['actual_stock'] = max((int)$row['quantity'] - $sold,0);
    $row['profit'] = ($row['sale_price']*$row['sold']) - ($row['import_price']*$row['sold']);

    $inventoryData[$row['product_name']][] = $row;
}
$resInv->close();

/* ----------------- Lấy lịch sử tồn kho ----------------- */
$history = [];
$sql = "SELECT ih.*, p.name as product_name FROM inventory_history ih JOIN products p ON ih.product_id = p.id WHERE 1=1 $history_where ORDER BY ih.created_at DESC";
$stmt_hist = $conn->prepare($sql);
if(!empty($params)){
    $stmt_hist->bind_param($types, ...$params);
}
$stmt_hist->execute();
$resH = $stmt_hist->get_result();
while($r = $resH->fetch_assoc()) $history[] = $r;
$stmt_hist->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý tồn kho</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
.table-fixed { table-layout:auto; width:100%; word-wrap:break-word; }
.parent-row { cursor:pointer; background-color:#f7fbff; font-weight:600; }
.child-row td { padding-left:40px; }
.table-fixed td.number, .table-fixed th.number { text-align:right; white-space:nowrap; }
.table-fixed td.text-start { white-space:normal; }
.table-fixed input[type=number]{ text-align:right; padding-right:4px; }
.badge-in{background:#0d6efd;color:#fff;padding:.35em .6em;border-radius:.35rem;}
.badge-adjust{background:#ffc107;color:#000;padding:.35em .6em;border-radius:.35rem;}
.badge-delete{background:#dc3545;color:#fff;padding:.35em .6em;border-radius:.35rem;}
.badge-sell{background:#198754;color:#fff;padding:.35em .6em;border-radius:.35rem;}
@media (max-width:768px){.child-row td{padding-left:20px;font-size:0.9rem;}.table-fixed th,.table-fixed td{font-size:0.9rem;}}
</style>
</head>
<body>
<div class="container mt-4 mb-5">
<div class="d-flex justify-content-between align-items-center mb-3">
<h3><i class="fa-solid fa-boxes-stacked"></i> Quản lý tồn kho</h3>
<a href="admin_interface.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
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
                <div class="card-header"><i class="fa-solid fa-cart-plus"></i> Nhập hàng mới</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-2">
                            <label class="form-label">Sản phẩm</label>
                            <select id="product_id" name="product_id" class="form-select" required onchange="loadColors(this.value)">
                                <option value="">-- Chọn sản phẩm --</option>
                                <?php foreach($productOptions as $p): ?>
                                    <option value="<?= htmlspecialchars($p['id']) ?>" data-colors="<?= htmlspecialchars($p['color']) ?>">
                                        <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['product_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Màu sắc</label>
                            <select id="color" name="color" class="form-select" required>
                                <option value="">Chọn màu</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Số lượng nhập</label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Giá nhập</label>
                            <input type="number" name="import_price" class="form-control" min="0" step="1" required>
                        </div>
                        <button type="submit" name="add_stock" class="btn btn-success w-100"><i class="fa-solid fa-plus"></i> Thêm / Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bảng tồn kho -->
        <div class="col-lg-8">
            <table class="table table-bordered table-hover table-fixed text-center align-middle">
                <thead class="table-primary">
                    <tr>
                        <th style="width:22%;">Sản phẩm</th>
                        <th style="width:10%;">Màu</th>
                        <th style="width:10%;" class="number">Mã SP</th>
                        <th style="width:12%;" class="number">Tồn thực tế</th>
                        <th style="width:8%;" class="number">Đã bán</th>
                        <th style="width:10%;" class="number">Giá nhập</th>
                        <th style="width:10%;" class="number">Giá bán</th>
                        <th style="width:8%;" class="number">Lợi nhuận</th>
                        <th style="width:10%;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($inventoryData)): ?>
                        <tr><td colspan="9">Chưa có dữ liệu tồn kho</td></tr>
                    <?php else: ?>
                        <?php foreach($inventoryData as $productName => $items): 
                            $collapseId = "collapse_" . md5($productName . rand(1,9999)); ?>
                            <tr class="parent-row" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>">
                                <td colspan="9"><i class="fa-solid fa-box"></i> <?= htmlspecialchars($productName) ?> — Nhấn để xem chi tiết</td>
                            </tr>
                            <?php foreach($items as $item): ?>
                                <tr class="collapse child-row" id="<?= $collapseId ?>">
                                    <td class="text-start"><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td><?= htmlspecialchars($item['color']) ?></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($item['product_code']) ?></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm number" min="0"
                                        id="stock_<?= (int)$item['product_id'] ?>_<?= htmlspecialchars($item['color']) ?>"
                                        value="<?= (int)$item['actual_stock'] ?>">
                                    </td>
                                    <td class="number"><?= (int)$item['sold'] ?></td>
                                    <td class="number"><?= number_format($item['import_price'],0,',','.') ?></td>
                                    <td class="number"><?= number_format($item['sale_price'],0,',','.') ?></td>
                                    <td class="number text-success fw-bold"><?= number_format($item['profit'],0,',','.') ?></td>
                                    <td>
                                        <!-- Nút lưu theo dòng -->
                                        <form method="post" style="display:inline">
                                            <input type="hidden" name="adjust_stock[<?= (int)$item['product_id'] ?>][<?= htmlspecialchars($item['color']) ?>]" 
                                                value="<?= (int)$item['actual_stock'] ?>" 
                                                id="hidden_<?= (int)$item['product_id'] ?>_<?= htmlspecialchars($item['color']) ?>">
                                            <button type="submit" name="update_stock" class="btn btn-primary btn-sm" 
                                                onclick="updateHidden(<?= (int)$item['product_id'] ?>,'<?= htmlspecialchars($item['color']) ?>')">
                                                <i class="fa-solid fa-save"></i>
                                            </button>
                                        </form>
                                        <!-- Nút xóa -->
                                        <form method="post" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn xóa màu này?');">
                                            <input type="hidden" name="delete_product_id" value="<?= (int)$item['product_id'] ?>">
                                            <input type="hidden" name="delete_color" value="<?= htmlspecialchars($item['color']) ?>">
                                            <button type="submit" name="delete_stock" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
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
                <div class="col text-end">
                    <a href="admin_inventory.php?tab=history" class="btn btn-outline-secondary">Reset</a>
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
                    <th class="number">Mã SP</th>
                    <th>Màu</th>
                    <th class="number">SL</th>
                    <th class="number">Giá nhập</th>
                    <th class="text-start">Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($history)): ?>
                    <tr><td colspan="7">Không có bản ghi lịch sử</td></tr>
                <?php else: ?>
                    <?php foreach($history as $h):
                        $badge = ($h['type'] ?? '') == 'Nhập hàng' ? 'badge-in' 
                                : (($h['type'] ?? '') == 'Điều chỉnh' ? 'badge-adjust' 
                                : (($h['type'] ?? '') == 'Xóa hàng' ? 'badge-delete' : 'badge-sell'));
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($h['created_at']) ?></td>
                        <td class="text-start"><?= htmlspecialchars($h['product_name']) ?></td>
                        <td class="number fw-bold text-primary"><?= htmlspecialchars($h['product_code'] ?? '') ?></td>
                        <td><?= htmlspecialchars($h['color']) ?></td>
                        <td class="number"><?= (int)$h['quantity_change'] ?></td>
                        <td class="number"><?= number_format($h['import_price'] ?? 0,0,',','.') ?></td>
                        <td class="text-start"><?= htmlspecialchars($h['note']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<script>
function loadColors(productId){
    const colorSelect = document.getElementById('color');
    colorSelect.innerHTML = '<option value="">Chọn màu</option>';
    if(!productId) return;
    const productOption = document.querySelector('#product_id option[value="'+productId+'"]');
    if(!productOption) return;
    let raw = productOption.dataset.colors || '';
    raw = raw.trim();
    if(raw==='') return;
    let parts = raw.split(/[,;\n]+/).map(s=>s.trim()).filter(s=>s!=='');

    const seen = new Set();
    parts.forEach(c=>{
        if(!seen.has(c)){
            const opt = document.createElement('option');
            opt.value = c; opt.text = c;
            colorSelect.appendChild(opt);
            seen.add(c);
        }
    });
}

// Cập nhật giá trị input vào hidden trước khi submit form
function updateHidden(productId,color){
    const val = document.getElementById(`stock_${productId}_${color}`).value;
    document.getElementById(`hidden_${productId}_${color}`).value = val;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
