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

/* ----------------- Nhận tham số lọc lịch sử ----------------- */
$tab_active = $_GET['tab'] ?? 'stock'; 
$today = date('Y-m-d'); 

if($tab_active == 'history'){
    $from_date = $_GET['from_date'] ?? $today;
    $to_date   = $_GET['to_date']   ?? $today;
} else {
    $from_date = $_GET['from_date'] ?? '';
    $to_date   = $_GET['to_date']   ?? '';
}
$product_code_filter = $_GET['product_code'] ?? '';

// Lọc lịch sử
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
    $history_where .= " AND ih.product_code=?";
    $params[] = $product_code_filter;
    $types .= "s";
}

/* ----------------- Lấy lịch sử tồn kho ----------------- */
$history=[];
$sql="SELECT ih.*,p.name as product_name FROM inventory_history ih JOIN products p ON ih.product_id=p.id WHERE 1=1 $history_where ORDER BY ih.created_at DESC";
$stmt_hist=$conn->prepare($sql);
if(!empty($params)) $stmt_hist->bind_param($types,...$params);
$stmt_hist->execute();
$resH=$stmt_hist->get_result();
while($r=$resH->fetch_assoc()) $history[]=$r;
$stmt_hist->close();



/* ----------------- Lấy tồn kho thực tế và số đã bán ----------------- */
$inventoryData=[];
$resInv = $conn->query("SELECT * FROM product_inventory ORDER BY product_code ASC,color ASC");
while($row=$resInv->fetch_assoc()){
    $stmt = $conn->prepare("SELECT name,price FROM products WHERE id=? LIMIT 1");
    $stmt->bind_param("i",$row['product_id']);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc()??['name'=>'N/A','price'=>0];
    $stmt->close();

    $row['product_name'] = $p['name'];
    $row['sale_price'] = $p['price'];
    $row['actual_stock'] = (int)$row['quantity'];
    $row['sold'] = $soldQuantities[$row['product_code']][$row['color']] ?? 0;
    $row['profit'] = ($row['sale_price']*$row['sold']) - ($row['import_price']*$row['sold']);
    $row['_row_id'] = "row_" . $row['product_id'] . "_" . preg_replace('/[^a-zA-Z0-9]/','',$row['color']);

    $inventoryData[$row['product_name']][]=$row;
}

/* ----------------- Cảnh báo tồn kho thấp ----------------- */
$lowStockWarnings=[];
foreach($inventoryData as $productName=>$items){
    foreach($items as $item){
        if($item['actual_stock']<10){
            $lowStockWarnings[]=[
                'text'=>$productName." (Màu: ".$item['color'].") - Tồn: ".$item['actual_stock'],
                'row_id'=>$item['_row_id']
            ];
        }
    }
}



/* ----------------- Xử lý thêm hàng ----------------- */
if (isset($_POST['add_stock'])) {

    $product_id   = (int)($_POST['product_id'] ?? 0);
    $color        = trim($_POST['color'] ?? '');
    $quantity     = (int)($_POST['quantity'] ?? 0);
    $import_price = (float)($_POST['import_price'] ?? 0);

    if ($product_id && $color !== '' && $quantity > 0) {

        $stmt = $conn->prepare("SELECT product_code FROM products WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$p) {
            die("Không tìm thấy sản phẩm");
        }

        $product_code = $p['product_code'];

        /* 🔹 KIỂM TRA TỒN KHO THEO MÀU */
        $stmt = $conn->prepare("
            SELECT quantity, import_price
            FROM product_inventory
            WHERE product_id=? AND color=? LIMIT 1
        ");
        $stmt->bind_param("is", $product_id, $color);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {

            $new_qty = $existing['quantity'] + $quantity;

            $new_price = (
                $existing['quantity'] * $existing['import_price']
                + $quantity * $import_price
            ) / $new_qty;

            $stmt = $conn->prepare("
                UPDATE product_inventory
                SET quantity=?, import_price=?
                WHERE product_id=? AND color=?
            ");
            $stmt->bind_param("idis", $new_qty, $new_price, $product_id, $color);
            $stmt->execute();
            $stmt->close();

            $note = "Cập nhật tồn kho: Thêm $quantity SL";

        } else {

            $stmt = $conn->prepare("
                INSERT INTO product_inventory
                (product_id, product_code, color, quantity, import_price)
                VALUES (?,?,?,?,?)
            ");
            $stmt->bind_param("issid", $product_id, $product_code, $color, $quantity, $import_price);
            $stmt->execute();
            $stmt->close();

            $note = "Thêm màu mới";
        }

        /* 🔹 GHI LỊCH SỬ */
        $stmt_hist = $conn->prepare("
            INSERT INTO inventory_history
            (product_id, product_code, color, quantity_change, import_price, type, note)
            VALUES (?,?,?,?,?,'Nhập hàng',?)
        ");
        $stmt_hist->bind_param(
            "issids",
            $product_id,
            $product_code,
            $color,
            $quantity,
            $import_price,
            $note
        );
        $stmt_hist->execute();
        $stmt_hist->close();

        header("Location: admin_inventory.php");
        exit();
    }
}


/* ----------------- Xử lý cập nhật tồn kho theo dòng ----------------- */
if(isset($_POST['update_stock']) && !empty($_POST['adjust_stock'])){
    foreach($_POST['adjust_stock'] as $product_id=>$colors){
        foreach($colors as $color=>$new_qty){

            $product_id = (int)$product_id;
            $color = trim($color);
            $new_qty = max(0, (int)$new_qty);

            // Lấy dữ liệu cũ
            $stmt = $conn->prepare("
                SELECT quantity, import_price, product_code
                FROM product_inventory
                WHERE product_id=? AND color=? LIMIT 1
            ");
            $stmt->bind_param("is", $product_id, $color);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if(!$row) continue;

            $old_qty = (int)$row['quantity'];
            $diff = $new_qty - $old_qty;
            if($diff === 0) continue;

            // Cập nhật tồn kho
            $stmt = $conn->prepare("
                UPDATE product_inventory SET quantity=?
                WHERE product_id=? AND color=?
            ");
            $stmt->bind_param("iis", $new_qty, $product_id, $color);
            $stmt->execute();
            $stmt->close();

            // Ghi log lịch sử
            $note = $diff > 0 ? "Tăng tồn kho +$diff" : "Giảm tồn kho $diff";

            // Tạo biến để tránh lỗi bind_param()
            $product_code = $row['product_code'];
            $import_price = (float)$row['import_price'];

            $stmt_hist = $conn->prepare("
                INSERT INTO inventory_history
                (product_id, product_code, color, quantity_change, import_price, type, note)
                VALUES (?,?,?,?,?, 'Điều chỉnh', ?)
            ");

            $stmt_hist->bind_param(
                "issids",
                $product_id,
                $product_code,
                $color,
                $diff,
                $import_price,
                $note
            );

            $stmt_hist->execute();
            $stmt_hist->close();
        }
    }

    header("Location: admin_inventory.php?tab=stock");
    exit();
}


/* ----------------- Xử lý xóa màu sản phẩm ----------------- */
if(isset($_POST['delete_stock'])){
    $product_id = (int)($_POST['delete_product_id'] ?? 0);
    $color = trim($_POST['delete_color'] ?? '');

    if($product_id && $color!==''){
        $stmt = $conn->prepare("SELECT quantity, import_price, product_code FROM product_inventory WHERE product_id=? AND color=? LIMIT 1");
        $stmt->bind_param("is",$product_id,$color);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if($row){

            // Xóa màu
            $stmt = $conn->prepare("DELETE FROM product_inventory WHERE product_id=? AND color=?");
            $stmt->bind_param("is",$product_id,$color);
            $stmt->execute();
            $stmt->close();

            // ------- Sửa đúng lỗi bind_param() -------
            $qty_change   = -(int)$row['quantity'];
            $import_price = (float)$row['import_price'];
            $product_code = $row['product_code'];
            $note         = "Xóa toàn bộ màu này";
            // -----------------------------------------

            $stmt_hist = $conn->prepare("
                INSERT INTO inventory_history(product_id, product_code, color, quantity_change, import_price, type, note)
                VALUES (?,?,?,?,?, 'Xóa hàng', ?)
            ");

            // Tất cả tham số đều là biến → KHÔNG lỗi
            $stmt_hist->bind_param(
                "issids",
                $product_id,
                $product_code,
                $color,
                $qty_change,
                $import_price,
                $note
            );

            $stmt_hist->execute();
            $stmt_hist->close();
        }
    }

    header("Location: admin_inventory.php?tab=stock");
    exit();
}


?>
