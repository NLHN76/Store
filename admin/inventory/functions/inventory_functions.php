<?php
function deductStockFromDeliveredPayment($conn) {

    $res = $conn->query("
        SELECT id, order_code, product_name, color, product_code
        FROM payment
        WHERE status = 'Đã giao hàng'
        AND is_deducted = 0
    ");

    while ($row = $res->fetch_assoc()) {

        $conn->begin_transaction();

        try {
            $product_names = explode(',', $row['product_name'] ?? '');
            $colors = explode(',', $row['color'] ?? '');
            $product_codes = explode(',', $row['product_code'] ?? '');

            foreach ($product_codes as $i => $product_code) {

                $product_code = trim($product_code);
                $color = trim($colors[$i] ?? '');
                $qty = 1;

                if (isset($product_names[$i])) {
                    preg_match('/\(\s*x\s*(\d+)\s*\)/i', $product_names[$i], $matches);
                    $qty = isset($matches[1]) ? (int)$matches[1] : 1;
                }

                if ($product_code === '' || $color === '' || $qty <= 0) {
                    continue;
                }

                // Lấy đúng tồn kho của mã sản phẩm và màu sản phẩm
                $stmt = $conn->prepare("
                    SELECT id, product_id, quantity, import_price, sale_price
                    FROM product_inventory
                    WHERE product_code = ? AND color = ?
                    LIMIT 1
                    FOR UPDATE
                ");

                $stmt->bind_param("ss", $product_code, $color);
                $stmt->execute();

                $inventory = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$inventory) {
                    throw new Exception(
                        "Không tìm thấy sản phẩm {$product_code} màu {$color}"
                    );
                }

                if ($inventory['quantity'] < $qty) {
                    throw new Exception(
                        "Sản phẩm {$product_code} màu {$color} không đủ tồn kho"
                    );
                }

                $product_id = (int)$inventory['product_id'];
                $import_price = (float)$inventory['import_price'];
                $sale_price = (float)$inventory['sale_price'];

                // Trừ đúng quantity của sản phẩm và màu tương ứng
                $stmt = $conn->prepare("
                    UPDATE product_inventory
                    SET quantity = quantity - ?
                    WHERE product_code = ? AND color = ?
                ");

                $stmt->bind_param(
                    "iss",
                    $qty,
                    $product_code,
                    $color
                );

                if (!$stmt->execute()) {
                    throw new Exception("Không thể trừ tồn kho");
                }

                $stmt->close();

                // Ghi lịch sử
                $note = "Trừ tồn kho khi đơn {$row['order_code']} đã giao hàng";

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
                    VALUES (?, ?, ?, ?, ?, ?, 'Bán hàng', ?)
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

            // Đánh dấu đơn đã trừ kho
            $stmt = $conn->prepare("
                UPDATE payment
                SET is_deducted = 1
                WHERE id = ?
            ");

            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

        } catch (Exception $e) {

            $conn->rollback();

            error_log(
                "Lỗi trừ tồn kho đơn {$row['order_code']}: "
                . $e->getMessage()
            );
        }
    }
}


// Đồng bộ giá bán product_inventory với products 
function syncSalePrice($conn) {
    $sql = "
        UPDATE product_inventory pi
        JOIN products p ON pi.product_id = p.id
        SET pi.sale_price = p.price
        WHERE pi.sale_price <> p.price
    ";
    $conn->query($sql);
}

syncSalePrice($conn);


//Tính tổng số đã bán 
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

//Đồng bộ tồn kho
$soldQuantities = calculateSoldQuantity($conn);
deductStockFromDeliveredPayment($conn);
?>
