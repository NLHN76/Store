<?php
require_once "../../db.php";


/* ----------------- Lấy danh sách sản phẩm ----------------- */
$productOptions=[];
$resP=$conn->query("SELECT id,name,color,product_code FROM products ORDER BY name ASC");
while($r=$resP->fetch_assoc()) $productOptions[]=$r;
$resP->close();


/* ----------------- Hoàn lại tồn kho khi hủy đơn ----------------- */
function restoreStockFromCancelledPayment($conn){
    $res = $conn->query("SELECT * FROM payment WHERE status='Đã hủy' AND is_restored IS NULL");
    while($row = $res->fetch_assoc()){
        $product_names = explode(',', $row['product_name']);
        $colors = explode(',', $row['color']);
        $product_codes = explode(',', $row['product_code']);

        foreach($product_codes as $i => $product_code){
            $product_code = trim($product_code);
            $color = trim($colors[$i] ?? '');
            $qty = 0;

            if(isset($product_names[$i])){
                preg_match('/\(x(\d+)\)/', $product_names[$i], $matches);
                $qty = isset($matches[1]) ? (int)$matches[1] : 1;
            }

            if($qty > 0 && $product_code && $color){
                $stmt_id = $conn->prepare("SELECT id FROM products WHERE product_code=? LIMIT 1");
                $stmt_id->bind_param("s", $product_code);
                $stmt_id->execute();
                $res_id = $stmt_id->get_result()->fetch_assoc();
                $stmt_id->close();
                if(!$res_id) continue;

                $product_id = $res_id['id'];

                // Cộng tồn kho
                $stmt = $conn->prepare("UPDATE product_inventory SET quantity = quantity + ? WHERE product_id=? AND color=?");
                $stmt->bind_param("iis", $qty, $product_id, $color);
                $stmt->execute();
                $stmt->close();

                $note = "Hoàn lại tồn kho từ đơn hủy (Payment ID: {$row['id']})";
                $stmt_hist = $conn->prepare("INSERT INTO inventory_history(product_id, product_code, color, quantity_change, import_price, type, note) VALUES (?, ?, ?, ?, 0, 'Hoàn trả', ?)");
                $stmt_hist->bind_param("issis", $product_id, $product_code, $color, $qty, $note);
                $stmt_hist->execute();
                $stmt_hist->close();
            }
        }

        // Đánh dấu đã xử lý
        $stmt2 = $conn->prepare("UPDATE payment SET is_restored=1 WHERE id=?");
        $stmt2->bind_param("i", $row['id']);
        $stmt2->execute();
        $stmt2->close();
    }
}

/* ----------------- Đồng bộ tồn kho ----------------- */
restoreStockFromCancelledPayment($conn);
$soldQuantities = calculateSoldQuantity($conn);




/* ----------------- Đồng bộ giá bán product_inventory với products ----------------- */
function syncSalePrice($conn) {
    $sql = "
        UPDATE product_inventory pi
        JOIN products p ON pi.product_id = p.id
        SET pi.sale_price = p.price
        WHERE pi.sale_price <> p.price
    ";
    $conn->query($sql);
}

// Gọi ngay sau khi kết nối DB và trước khi lấy dữ liệu tồn kho
syncSalePrice($conn);


/* ----------------- Tính tổng số đã bán ----------------- */
function calculateSoldQuantity($conn){
    $soldData = [];
    $res = $conn->query("SELECT product_name, color, product_code FROM payment WHERE status='Đã giao hàng'");
    while($row = $res->fetch_assoc()){
        $names = explode(',', $row['product_name']);
        $colors = explode(',', $row['color']);
        $codes = explode(',', $row['product_code']);

        foreach($codes as $i => $code){
            $code = trim($code);
            $color = trim($colors[$i] ?? '');
            $qty = 0;
            if(isset($names[$i])) preg_match('/\(x(\d+)\)/', $names[$i], $matches);
            $qty = isset($matches[1]) ? (int)$matches[1] : 1;

            if($code && $color && $qty > 0){
                if(!isset($soldData[$code])) $soldData[$code] = [];
                if(!isset($soldData[$code][$color])) $soldData[$code][$color] = 0;
                $soldData[$code][$color] += $qty;
            }
        }
    }
    return $soldData;
}

/* ----------------- Nhận tham số ----------------- */
$tab_active = $_GET['tab'] ?? 'stock';
$today = date('Y-m-d');

/* ----------------- Lọc theo tab ----------------- */
if ($tab_active === 'history') {
    $from_date = $_GET['from_date'] ?? '';
    $to_date   = $_GET['to_date'] ?? '';
} else {
    $from_date = '';
    $to_date   = '';
}

$product_code_filter = $_GET['product_code'] ?? '';

/* ----------------- Điều kiện lọc ----------------- */
$history_where = '';
$params = [];
$types  = '';

if (!empty($from_date)) {
    $history_where .= " AND ih.created_at >= ?";
    $params[] = $from_date . " 00:00:00";
    $types   .= "s";
}

if (!empty($to_date)) {
    $history_where .= " AND ih.created_at <= ?";
    $params[] = $to_date . " 23:59:59";
    $types   .= "s";
}

if (!empty($product_code_filter)) {
    // Lọc theo chứa chuỗi để linh hoạt
    $history_where .= " AND ih.product_code LIKE ?";
    $params[] = "%$product_code_filter%";
    $types   .= "s";
}

/* ----------------- Lấy lịch sử tồn kho ----------------- */
$history = [];

$sql = "
    SELECT 
        ih.*,
        p.name AS product_name,
        DATE_FORMAT(ih.created_at, '%d/%m/%Y %H:%i:%s') AS created_at
    FROM inventory_history ih
    JOIN products p ON ih.product_id = p.id
    WHERE 1=1 $history_where
    ORDER BY ih.created_at DESC
";

$stmt_hist = $conn->prepare($sql);

if (!empty($params)) {
    $stmt_hist->bind_param($types, ...$params);
}

$stmt_hist->execute();
$resH = $stmt_hist->get_result();

while ($row = $resH->fetch_assoc()) {
    // Tính tổng giá bán = giá bán * số lượng thay đổi
    $row['total_sale'] = ($row['sale_price'] ?? 0) * ($row['quantity_change'] ?? 0);
    $history[] = $row;
}

$stmt_hist->close();



/* ----------------- Lấy tồn kho thực tế và số đã bán ----------------- */
$inventoryData = [];
$resInv = $conn->query("SELECT * FROM product_inventory ORDER BY product_code ASC, color ASC");

while ($row = $resInv->fetch_assoc()) {

    $stmt = $conn->prepare("SELECT name, price FROM products WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $row['product_id']);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc() ?? ['name' => 'N/A', 'price' => 0];
    $stmt->close();

    $row['product_name'] = $p['name'];
    $row['sale_price']   = $p['price'];
    $row['actual_stock'] = (int)$row['quantity'];
    $row['sold']         = $soldQuantities[$row['product_code']][$row['color']] ?? 0;
    $row['_row_id'] = "row_" . $row['product_id'] . "_" . preg_replace('/[^a-zA-Z0-9]/', '', $row['color']);

    $inventoryData[$row['product_name']][] = $row;
}


/* ----------------- Cảnh báo tồn kho thấp ----------------- */
$lowStockWarnings = [];

foreach ($inventoryData as $productName => $items) {
    foreach ($items as $item) {
        if ($item['actual_stock'] < 10) {

            // Chuẩn hóa màu để tránh lỗi id
            $colorKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $item['color']);

            $lowStockWarnings[] = [
                'text'   => $productName . " (Màu: " . $item['color'] . ") - Tồn: " . $item['actual_stock'],
                'row_id' => 'row_' . $item['product_id'] . '_' . $colorKey
            ];
        }
    }
}


/* ----------------- Thêm / cập nhật hàng ----------------- */
if (isset($_POST['add_stock'])) {
    $product_id   = (int)($_POST['product_id'] ?? 0);
    $color        = trim($_POST['color'] ?? '');
    $quantity     = (int)($_POST['quantity'] ?? 0);
    $import_price = (float)($_POST['import_price'] ?? 0);

    if ($product_id && $color !== '' && $quantity > 0) {

        // Lấy mã sản phẩm và giá bán hiện tại
        $stmt = $conn->prepare("SELECT product_code, price FROM products WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$p) die("Không tìm thấy sản phẩm");

        $product_code = $p['product_code'];
        $sale_price   = (float)$p['price'];

        // Kiểm tra tồn kho màu
        $stmt = $conn->prepare("SELECT quantity, import_price, sale_price FROM product_inventory WHERE product_id=? AND color=? LIMIT 1");
        $stmt->bind_param("is", $product_id, $color);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            // Cập nhật tồn kho đã có
            $new_qty = $existing['quantity'] + $quantity;

            // Giá nhập trung bình
            $new_import_price = (
                $existing['quantity'] * $existing['import_price'] 
                + $quantity * $import_price
            ) / $new_qty;

            // Giá bán hiện tại của sản phẩm
            $new_sale_price = $sale_price;

            $stmt = $conn->prepare("
                UPDATE product_inventory 
                SET quantity=?, import_price=?, sale_price=? 
                WHERE product_id=? AND color=?
            ");
            $stmt->bind_param("iddis", $new_qty, $new_import_price, $new_sale_price, $product_id, $color);
            $stmt->execute();
            $stmt->close();

            // Tính tổng giá bán theo số lượng nhập
            $total_sale_value = $quantity * $new_sale_price;

            $note = "Cập nhật tồn kho: Thêm $quantity SL, giá nhập ".number_format($import_price,0,',','.')." VND, tổng giá bán ".number_format($total_sale_value,0,',','.')." VND";

        } else {
            // Thêm màu mới
            $stmt = $conn->prepare("
                INSERT INTO product_inventory 
                (product_id, product_code, color, quantity, import_price, sale_price)
                VALUES (?,?,?,?,?,?)
            ");
            $stmt->bind_param("issidd", $product_id, $product_code, $color, $quantity, $import_price, $sale_price);
            $stmt->execute();
            $stmt->close();

            $total_sale_value = $quantity * $sale_price;
            $note = "Thêm màu mới: $quantity SL, giá nhập ".number_format($import_price,0,',','.')." VND, tổng giá bán ".number_format($total_sale_value,0,',','.')." VND";
        }

        // Ghi lịch sử
        $stmt_hist = $conn->prepare("
            INSERT INTO inventory_history 
            (product_id, product_code, color, quantity_change, import_price, sale_price, type, note)
            VALUES (?,?,?,?,?,?, 'Nhập hàng', ?)
        ");
        $stmt_hist->bind_param("issidds", $product_id, $product_code, $color, $quantity, $import_price, $sale_price, $note);
        $stmt_hist->execute();
        $stmt_hist->close();

        header("Location: admin_inventory.php?tab=stock");
        exit();
    }
}



/* ----------------- Xóa màu sản phẩm ----------------- */
if (isset($_POST['delete_stock'])) {
    $product_id = (int)($_POST['delete_product_id'] ?? 0);
    $color = trim($_POST['delete_color'] ?? '');

    if ($product_id && $color !== '') {
        $stmt = $conn->prepare("SELECT quantity, import_price, sale_price, product_code FROM product_inventory WHERE product_id=? AND color=? LIMIT 1");
        $stmt->bind_param("is", $product_id, $color);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            // Xóa màu
            $stmt = $conn->prepare("DELETE FROM product_inventory WHERE product_id=? AND color=?");
            $stmt->bind_param("is", $product_id, $color);
            $stmt->execute();
            $stmt->close();

            $qty_change = -(int)$row['quantity'];
            $note = "Xóa toàn bộ màu này";

            $stmt_hist = $conn->prepare("
                INSERT INTO inventory_history 
                (product_id, product_code, color, quantity_change, import_price, sale_price, type, note)
                VALUES (?,?,?,?,?,?, 'Xóa hàng', ?)
            ");
            $stmt_hist->bind_param("issidds", $product_id, $row['product_code'], $color, $qty_change, $row['import_price'], $row['sale_price'], $note);
            $stmt_hist->execute();
            $stmt_hist->close();
        }
    }

    header("Location: admin_inventory.php?tab=stock");
    exit();
}


?>
