<?php

/* ----------------- Lấy danh sách sản phẩm ----------------- */
$productOptions=[];
$resP=$conn->query("SELECT id,name,color,product_code FROM products ORDER BY name ASC");
while($r=$resP->fetch_assoc()) $productOptions[]=$r;
$resP->close();


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

/* ----------------- Tìm kiếm theo tên sản phẩm ----------------- */
$search_product = trim($_GET['search_product'] ?? '');

$searchProductIds = [];

if ($search_product != '') {

    $stmtSearch = $conn->prepare("
        SELECT id
        FROM products
        WHERE name LIKE ?
        ORDER BY name ASC
    ");

    $keyword = "%" . $search_product . "%";

    $stmtSearch->bind_param("s", $keyword);
    $stmtSearch->execute();

    $resSearch = $stmtSearch->get_result();

    while ($product = $resSearch->fetch_assoc()) {
        $searchProductIds[] = (int)$product['id'];
    }

    $stmtSearch->close();
}

/* ----------------- Lấy tồn kho thực tế và số đã bán ----------------- */
$inventoryData = [];

$sqlInventory = "
    SELECT *
    FROM product_inventory
";

$paramsInventory = [];
$typesInventory = '';

/* Nếu có tìm kiếm thì chỉ lấy tồn kho của sản phẩm được tìm thấy */
if ($search_product != '') {

    if (!empty($searchProductIds)) {

        $placeholders = implode(',', array_fill(0, count($searchProductIds), '?'));

        $sqlInventory .= "
            WHERE product_id IN ($placeholders)
        ";

        $typesInventory = str_repeat('i', count($searchProductIds));
        $paramsInventory = $searchProductIds;

    } else {

        // Không tìm thấy sản phẩm
        $sqlInventory .= " WHERE 1 = 0";
    }
}

$sqlInventory .= "
    ORDER BY product_code ASC, color ASC
";


$stmtInventory = $conn->prepare($sqlInventory);

if (!empty($paramsInventory)) {

    $stmtInventory->bind_param(
        $typesInventory,
        ...$paramsInventory
    );
}

$stmtInventory->execute();

$resInv = $stmtInventory->get_result();


while ($row = $resInv->fetch_assoc()) {

    /** Lấy thông tin sản phẩm*/
    $stmtProduct = $conn->prepare("
        SELECT name, price
        FROM products
        WHERE id = ?
        LIMIT 1
    ");

    $stmtProduct->bind_param(
        "i",
        $row['product_id']
    );

    $stmtProduct->execute();

    $product = $stmtProduct
        ->get_result()
        ->fetch_assoc();

    $stmtProduct->close();


    /** Nếu không tìm thấy sản phẩm*/
    if (!$product) {

        $product = [
            'name'  => 'N/A',
            'price' => 0
        ];

    }


    /** Thông tin sản phẩm*/
    $row['product_name'] = $product['name'];
    $row['sale_price']   = $product['price'];


    /** Tồn kho thực tế*/
    $row['actual_stock'] = (int)$row['quantity'];


    /** Tổng số đã bán*/
    $productCode = trim($row['product_code']);
    $color       = trim($row['color']);

    $row['sold'] =
        $soldQuantities[$productCode][$color] ?? 0;


    /** ID của dòng tồn kho*/
    $row['_row_id'] =
        "row_" .
        $row['product_id'] .
        "_" .
        preg_replace(
            '/[^a-zA-Z0-9]/',
            '',
            $row['color']
        );


    /** Gom nhóm theo tên sản phẩm */
    $inventoryData[$row['product_name']][] = $row;
}


$stmtInventory->close();


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