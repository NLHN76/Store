<?php
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
?>