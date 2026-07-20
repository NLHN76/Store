<?php
require_once "../../db.php";


/* ----------------- Lấy danh sách sản phẩm ----------------- */
$productOptions=[];
$resP=$conn->query("SELECT id,name,color,product_code FROM products ORDER BY name ASC");
while($r=$resP->fetch_assoc()) $productOptions[]=$r;
$resP->close();


/* ----------------- Hoàn lại tồn kho khi hủy đơn ----------------- */
function restoreStockFromCancelledPayment($conn){
    $res = $conn->query("
        SELECT *
        FROM payment
        WHERE status='Đã hủy'
        AND is_restored IS NULL
    ");

    while($row = $res->fetch_assoc()){

        $product_names = explode(',', $row['product_name']);
        $colors        = explode(',', $row['color']);
        $product_codes = explode(',', $row['product_code']);

        foreach($product_codes as $i => $product_code){

            $product_code = trim($product_code);
            $color        = trim($colors[$i] ?? '');
            $qty          = 0;

            // Lấy số lượng từ chuỗi dạng: Tên sản phẩm (x2)
            if(isset($product_names[$i])){
                preg_match('/\(x(\d+)\)/', $product_names[$i], $matches);
                $qty = isset($matches[1]) ? (int)$matches[1] : 1;
            }

            if($qty > 0 && $product_code != '' && $color != ''){

                // Lấy ID sản phẩm
                $stmt = $conn->prepare("
                    SELECT id
                    FROM products
                    WHERE product_code=?
                    LIMIT 1
                ");
                $stmt->bind_param("s", $product_code);
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if(!$product){
                    continue;
                }

                $product_id = $product['id'];

                // Lấy giá nhập và giá bán hiện tại
                $stmt = $conn->prepare("
                    SELECT import_price, sale_price
                    FROM product_inventory
                    WHERE product_id=? AND color=?
                    LIMIT 1
                ");
                $stmt->bind_param("is", $product_id, $color);
                $stmt->execute();
                $inventory = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $import_price = $inventory['import_price'] ?? 0;
                $sale_price   = $inventory['sale_price'] ?? 0;

                // Cộng lại tồn kho
                $stmt = $conn->prepare("
                    UPDATE product_inventory
                    SET quantity = quantity + ?
                    WHERE product_id=? AND color=?
                ");
                $stmt->bind_param("iis", $qty, $product_id, $color);
                $stmt->execute();
                $stmt->close();

                // Ghi lịch sử
                $note = "Hoàn lại tồn kho từ đơn hủy (Payment ID: {$row['id']})";

                $stmt = $conn->prepare("
                    INSERT INTO inventory_history
                    (
                        product_id,
                        product_code,
                        color,
                        quantity_change,
                        import_price,
                        sale_price,
                        type,
                        note
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, 'Hoàn trả', ?)
                ");

                $stmt->bind_param(
                    "issidds",
                    $product_id,
                    $product_code,
                    $color,
                    $qty,
                    $import_price,
                    $sale_price,
                    $note
                );

                $stmt->execute();
                $stmt->close();
            }
        }

        // Đánh dấu đã hoàn kho
        $stmt = $conn->prepare("
            UPDATE payment
            SET is_restored = 1
            WHERE id=?
        ");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $stmt->close();
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


?>
