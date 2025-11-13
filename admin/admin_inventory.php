<?php
session_start();

// ----------------- Kết nối database -----------------
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// ----------------- Thêm / Nhập hàng -----------------
if(isset($_POST['add_stock'])){
    $product_id = $_POST['product_id'];
    $color = $_POST['color'];
    $quantity = (int)$_POST['quantity'];
    $import_price = (float)$_POST['import_price'];

    if($product_id && $color && $quantity > 0){
        $stmt = $conn->prepare("SELECT id, quantity, import_price FROM product_inventory WHERE product_id=? AND color=? LIMIT 1");
        $stmt->bind_param("is", $product_id, $color);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if($result){
            $new_quantity = $result['quantity'] + $quantity;
            $new_import_price = (($result['quantity'] * $result['import_price']) + ($quantity * $import_price)) / $new_quantity;

            $stmt = $conn->prepare("UPDATE product_inventory SET quantity=?, import_price=? WHERE product_id=? AND color=?");
            $stmt->bind_param("idis", $new_quantity, $new_import_price, $product_id, $color);
            $stmt->execute();
            $stmt->close();

            $stmt_hist = $conn->prepare(
                "INSERT INTO inventory_history (product_id, color, quantity_change, import_price, type, note) 
                 VALUES (?, ?, ?, ?, 'Nhập hàng', 'Cập nhật tồn kho')"
            );
            $stmt_hist->bind_param("isds", $product_id, $color, $quantity, $import_price);
            $stmt_hist->execute();
            $stmt_hist->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO product_inventory (product_id, color, quantity, import_price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isid", $product_id, $color, $quantity, $import_price);
            $stmt->execute();
            $stmt->close();

            $stmt_hist = $conn->prepare(
                "INSERT INTO inventory_history (product_id, color, quantity_change, import_price, type, note) 
                 VALUES (?, ?, ?, ?, 'Nhập hàng', 'Thêm màu mới')"
            );
            $stmt_hist->bind_param("isds", $product_id, $color, $quantity, $import_price);
            $stmt_hist->execute();
            $stmt_hist->close();
        }

        header("Location: admin_inventory.php");
        exit();
    }
}

// ----------------- Cập nhật tồn kho thực tế -----------------
if(isset($_POST['update_stock'])){
    foreach($_POST['adjust_stock'] as $product_id => $colors){
        foreach($colors as $color => $new_stock){
            $new_stock = (int)$new_stock;

            $stmt_old = $conn->prepare("SELECT quantity FROM product_inventory WHERE product_id=? AND color=?");
            $stmt_old->bind_param("is", $product_id, $color);
            $stmt_old->execute();
            $old_stock = $stmt_old->get_result()->fetch_assoc()['quantity'] ?? 0;
            $stmt_old->close();

            $stmt = $conn->prepare("UPDATE product_inventory SET quantity=? WHERE product_id=? AND color=?");
            $stmt->bind_param("iis", $new_stock, $product_id, $color);
            $stmt->execute();
            $stmt->close();

            $quantity_change = $new_stock - $old_stock;
            if($quantity_change != 0){
                $note = "Điều chỉnh tồn kho thực tế";
                $stmt_hist = $conn->prepare(
                    "INSERT INTO inventory_history (product_id, color, quantity_change, import_price, type, note)
                     VALUES (?, ?, ?, 0, 'Điều chỉnh tồn kho', ?)"
                );
                $stmt_hist->bind_param("iiss", $product_id, $color, $quantity_change, $note);
                $stmt_hist->execute();
                $stmt_hist->close();
            }
        }
    }

    header("Location: admin_inventory.php");
    exit();
}

// ----------------- Xóa sản phẩm theo màu -----------------
if(isset($_POST['delete_stock'])){
    $product_id = $_POST['delete_product_id'];
    $color = $_POST['delete_color'];

    if($product_id && $color){
        // Xóa trong bảng product_inventory
        $stmt = $conn->prepare("DELETE FROM product_inventory WHERE product_id=? AND color=?");
        $stmt->bind_param("is", $product_id, $color);
        $stmt->execute();
        $stmt->close();

        // Ghi lịch sử xóa
        $note = "Xóa màu '$color' khỏi sản phẩm ID #$product_id";
        $stmt_hist = $conn->prepare(
            "INSERT INTO inventory_history (product_id, color, quantity_change, import_price, type, note)
             VALUES (?, ?, 0, 0, 'Xóa hàng', ?)"
        );
        $stmt_hist->bind_param("iss", $product_id, $color, $note);
        $stmt_hist->execute();
        $stmt_hist->close();
    }

    header("Location: admin_inventory.php");
    exit();
}

// ----------------- Lọc lịch sử theo ngày -----------------
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
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

// ----------------- Giả lập bán hàng -----------------
function reduceStock($conn, $product_code, $color, $quantity_sold, $order_id){
    $stmt = $conn->prepare("SELECT id, import_price FROM products p JOIN product_inventory pi ON p.id = pi.product_id WHERE p.product_code=? AND pi.color=? LIMIT 1");
    $stmt->bind_param("ss", $product_code, $color);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($row){
        $product_id = $row['id'];
        $current_import_price = $row['import_price'];

        $stmt = $conn->prepare("UPDATE product_inventory SET quantity = quantity - ? WHERE product_id=? AND color=?");
        $stmt->bind_param("iis", $quantity_sold, $product_id, $color);
        $stmt->execute();
        $stmt->close();

        $note = "Khách hàng mua đơn #$order_id";
        $stmt_hist = $conn->prepare(
            "INSERT INTO inventory_history (product_id, color, quantity_change, import_price, type, note) 
             VALUES (?, ?, ?, ?, 'Bán hàng', ?)"
        );
        $stmt_hist->bind_param("iidss", $product_id, $color, -$quantity_sold, $current_import_price, $note);
        $stmt_hist->execute();
        $stmt_hist->close();
    }
}

// ----------------- Lấy danh sách sản phẩm -----------------
$products = $conn->query("SELECT id, name, price, color, product_code FROM products ORDER BY name ASC");

// ----------------- Lấy tồn kho -----------------
$inventoryData = [];
$result = $conn->query("SELECT pi.*, p.name as product_name, p.price as sale_price, p.product_code 
                        FROM product_inventory pi 
                        JOIN products p ON pi.product_id = p.id 
                        ORDER BY p.name ASC, pi.color ASC");
while($row = $result->fetch_assoc()){
    $stmt2 = $conn->prepare("SELECT SUM(product_quantity) as sold 
                             FROM payment 
                             WHERE product_code=? AND color=? AND status IN ('Đã thanh toán','Đã giao hàng')");
    $stmt2->bind_param("ss", $row['product_code'], $row['color']);
    $stmt2->execute();
    $sold = $stmt2->get_result()->fetch_assoc()['sold'] ?? 0;
    $stmt2->close();

    $row['sold'] = (int)$sold;
    $row['actual_stock'] = max((int)$row['quantity'] - (int)$sold, 0);
    $row['profit'] = ($row['sale_price'] * $row['sold']) - ($row['import_price'] * $row['sold']);

    $inventoryData[$row['product_name']][] = $row;
}

// ----------------- Lịch sử tồn kho -----------------
$history = [];
$sql = "SELECT ih.*, p.name as product_name 
        FROM inventory_history ih 
        JOIN products p ON ih.product_id = p.id
        WHERE 1=1 $history_where
        ORDER BY ih.created_at DESC";
$stmt_hist = $conn->prepare($sql);

if(!empty($params)){
    $stmt_hist->bind_param($types, ...$params);
}

$stmt_hist->execute();
$result_hist = $stmt_hist->get_result();
while($row = $result_hist->fetch_assoc()){
    $history[] = $row;
}
$stmt_hist->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quản lý tồn kho</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.parent-row { cursor: pointer; background-color: #f8f9fa; font-weight: bold; }
.child-row td { padding-left: 40px; }
</style>
</head>
<body>
<div class="container mt-4">
    <a class="back-button" href="admin_interface.php" title="Quay lại trang quản trị">
        <img src="uploads/exit.jpg" alt="Quay lại" style="width:30px; height:50px; object-fit:cover; border-radius:5px;">
    </a>
    <h2>Quản lý tồn kho sản phẩm</h2>

    <!-- Tabs -->
    <ul class="nav nav-tabs mt-3" id="tabMenu" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="stock-tab" data-bs-toggle="tab" data-bs-target="#stock" type="button" role="tab">Tồn kho</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">Lịch sử</button>
        </li>
    </ul>

    <div class="tab-content mt-3">
        <!-- Tab Tồn kho -->
        <div class="tab-pane fade show active" id="stock" role="tabpanel">
            <div class="row">
                <!-- Form nhập hàng -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">Nhập hàng mới</div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label>Sản phẩm</label>
                                    <select class="form-select" name="product_id" id="product_id" required onchange="loadColors(this.value)">
                                        <option value="">Chọn sản phẩm</option>
                                        <?php 
                                        $products->data_seek(0);
                                        while($row = $products->fetch_assoc()):
                                        ?>
                                            <option value="<?= $row['id'] ?>" data-colors="<?= htmlspecialchars($row['color']) ?>">
                                                <?= $row['name'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Màu sắc</label>
                                    <select class="form-select" name="color" id="color" required>
                                        <option value="">Chọn màu</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Số lượng nhập</label>
                                    <input type="number" class="form-control" name="quantity" id="quantity" required>
                                </div>
                                <div class="mb-3">
                                    <label>Giá nhập</label>
                                    <input type="number" step="1" class="form-control" name="import_price" id="import_price" required>
                                </div>
                                <button type="submit" name="add_stock" class="btn btn-success">Cập nhật tồn kho</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Bảng tồn kho + chỉnh sửa thực tế -->
                <div class="col-md-8">
                    <form method="post">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Màu sắc</th>
                                <th>Tồn kho thực tế</th>
                                <th>Đã bán</th>
                                <th>Giá nhập</th>
                                <th>Giá bán</th>
                                <th>Lợi nhuận</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($inventoryData as $productName => $items): ?>
                            <?php $collapseId = "collapse_".md5($productName); ?>
                            <tr class="parent-row" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false">
                                <td><?= $productName ?></td>
                                <td colspan="7">[▼] Nhấn để xem chi tiết</td>
                            </tr>
                            <?php foreach($items as $item): ?>
                                <tr class="collapse child-row" id="<?= $collapseId ?>">
                                    <td></td>
                                    <td><?= $item['color'] ?></td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" 
                                               name="adjust_stock[<?= $item['product_id'] ?>][<?= $item['color'] ?>]" 
                                               value="<?= $item['actual_stock'] ?>">
                                    </td>
                                    <td><?= $item['sold'] ?></td>
                                    <td><?= number_format($item['import_price'],0,'','.') ?></td>
                                    <td><?= number_format($item['sale_price'],0,'','.') ?></td>
                                    <td><?= number_format($item['profit'],0,'','.') ?></td>
                                    <td>
                                        <form method="post" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa màu này không?');">
                                            <input type="hidden" name="delete_product_id" value="<?= $item['product_id'] ?>">
                                            <input type="hidden" name="delete_color" value="<?= $item['color'] ?>">
                                            <button type="submit" name="delete_stock" class="btn btn-danger btn-sm">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="update_stock" class="btn btn-primary mt-2">Sửa</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tab Lịch sử -->
        <div class="tab-pane fade" id="history" role="tabpanel">
            <form method="get" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="date" class="form-control" name="from_date" value="<?= htmlspecialchars($from_date) ?>" placeholder="Từ ngày">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" name="to_date" value="<?= htmlspecialchars($to_date) ?>" placeholder="Đến ngày">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">Lọc</button>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Thời gian</th>
                        <th>Sản phẩm</th>
                        <th>Màu sắc</th>
                        <th>Số lượng thay đổi</th>
                        <th>Giá nhập</th>
                        <th>Loại thao tác</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($history as $h): ?>
                    <tr>
                        <td><?= $h['created_at'] ?></td>
                        <td><?= $h['product_name'] ?></td>
                        <td><?= $h['color'] ?></td>
                        <td><?= $h['quantity_change'] ?></td>
                        <td><?= number_format($h['import_price'],0,'','.') ?></td>
                        <td><?= $h['type'] ?></td>
                        <td><?= htmlspecialchars($h['note']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function loadColors(productId){
    const colorSelect = document.getElementById('color');
    colorSelect.innerHTML = '<option value="">Chọn màu</option>';
    const productOption = document.querySelector('#product_id option[value="'+productId+'"]');
    if(productOption){
        const colors = productOption.getAttribute('data-colors').split(',');
        colors.forEach(c => {
            c = c.trim();
            if(c !== ''){
                const option = document.createElement('option');
                option.value = c;
                option.text = c;
                colorSelect.appendChild(option);
            }
        });
    }
}
</script>

<script>
document.addEventListener("DOMContentLoaded", function(){
    const hash = window.location.hash;
    if(hash){
        const triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]');
        if(triggerEl){
            const tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }
});
</script>
</body>
</html>
