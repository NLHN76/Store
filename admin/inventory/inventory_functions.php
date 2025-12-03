<?php
require_once "db.php";

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
