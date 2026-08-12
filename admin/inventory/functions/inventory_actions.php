<?php
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