<?php

require_once "product_functions.php";

$available_colors = load_colors();
$add_error = "";
$edit_error = "";


// XỬ LÝ MÀU
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

   
    if ($_POST['action'] === 'add_color') {

        $new_color = trim($_POST['new_color'] ?? '');

        if ($new_color !== '' && !in_array($new_color, $available_colors)) {
            $available_colors[] = $new_color;
            save_colors($available_colors);
        }
    }

  
    if ($_POST['action'] === 'delete_color') {

        $delete_color = $_POST['delete_color'] ?? '';
        $index = array_search($delete_color, $available_colors);

        if ($index !== false) {
            unset($available_colors[$index]);
            $available_colors = array_values($available_colors);
            save_colors($available_colors);
        }
    }
}



// THÊM SẢN PHẨM
if (isset($_POST['action']) && $_POST['action'] === 'add') {

    $name = trim($_POST['product_name'] ?? '');
    $brand = trim($_POST['product_brand'] ?? '');
    $category = trim($_POST['product_category'] ?? '');
    $price = clean_price($_POST['product_price'] ?? '');

    $allowed_categories = [
        'Tai nghe',
        'Cáp sạc',
        'Ốp lưng',
        'Kính cường lực'
    ];

    if (
        $name === '' ||
        $brand === '' ||
        $price < 0 ||
        !in_array($category, $allowed_categories)
    ) {
        $add_error = "Dữ liệu thêm không hợp lệ.";
    } else {

        $image_name = null;

        if (!empty($_FILES['product_image']['name'])) {

            $extension = strtolower(
                pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION)
            );

            $image_name = basename($_FILES['product_image']['name']);

            move_uploaded_file(
                $_FILES['product_image']['tmp_name'],
                "../uploads/" . $image_name
            );
        }

        $colors = implode(',', $_POST['product_colors'] ?? []);

        $product_code = generate_product_code($category, $conn);

        $stmt = $conn->prepare("
            INSERT INTO products
                (name, brand, price, color, category, image, product_code)
            VALUES
                (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssdssss",
            $name,
            $brand,
            $price,
            $colors,
            $category,
            $image_name,
            $product_code
        );

        $stmt->execute();
        $stmt->close();
    }
}


// SỬA SẢN PHẨM
if (isset($_POST['action']) && $_POST['action'] === 'edit') {

    $id = intval($_POST['product_id'] ?? 0);
    $name = trim($_POST['product_name'] ?? '');
    $brand = trim($_POST['product_brand'] ?? '');
    $category = trim($_POST['product_category'] ?? '');
    $price = clean_price($_POST['product_price'] ?? '');
    $colors = implode(',', $_POST['product_colors'] ?? []);

    $allowed_categories = [
        'Tai nghe',
        'Cáp sạc',
        'Ốp lưng',
        'Kính cường lực'
    ];

    if (
        $name === '' ||
        $brand === '' ||
        $price < 0 ||
        !in_array($category, $allowed_categories)
    ) {
        $edit_error = "Dữ liệu sửa không hợp lệ.";
    } else {

        $stmt = $conn->prepare(
            "SELECT image FROM products WHERE id = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $old = $stmt->get_result()->fetch_assoc();

        $stmt->close();

        if (!$old) {

            $edit_error = "Không tìm thấy sản phẩm.";

        } else {

            $image_name = $old['image'];

            if (!empty($_FILES['product_image']['name'])) {

                if (
                    !empty($old['image']) &&
                    file_exists("../uploads/" . $old['image'])
                ) {
                    unlink("../uploads/" . $old['image']);
                }

                $extension = strtolower(
                    pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION)
                );

                $image_name = basename($_FILES['product_image']['name']);

                move_uploaded_file(
                    $_FILES['product_image']['tmp_name'],
                    "../uploads/" . $image_name
                );
            }

            $stmt = $conn->prepare("
                UPDATE products
                SET name=?, brand=?, price=?, color=?, category=?, image=?
                WHERE id=?
            ");

            $stmt->bind_param(
                "ssdsssi",
                $name,
                $brand,
                $price,
                $colors,
                $category,
                $image_name,
                $id
            );

            $stmt->execute();
            $stmt->close();
        }
    }
}

// XÓA SẢN PHẨM
if (isset($_POST['action']) && $_POST['action'] === 'delete') {

    $product_id = intval($_POST['product_id'] ?? 0);

    // Kiểm tra tồn kho
    $stmt = $conn->prepare("
        SELECT SUM(quantity) AS total_qty
        FROM product_inventory
        WHERE product_id = ?
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();

    $inventory = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (($inventory['total_qty'] ?? 0) > 0) {
        die("Không thể xóa sản phẩm này vì vẫn còn tồn kho.");
    }

    // Xóa lịch sử tồn kho
    $stmt = $conn->prepare("
        DELETE FROM inventory_history
        WHERE product_id = ?
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();

    // Xóa tồn kho
    $stmt = $conn->prepare("
        DELETE FROM product_inventory
        WHERE product_id = ?
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();

    // Xóa sản phẩm
    $stmt = $conn->prepare("
        DELETE FROM products
        WHERE id = ?
    ");

    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
}



// BẬT / TẮT SẢN PHẨM
if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {

    $id = intval($_POST['product_id'] ?? 0);

    $stmt = $conn->prepare("
        UPDATE products
        SET is_active = 1 - is_active
        WHERE id = ?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}


// Tìm kiếm sản phẩm
$search = trim($_GET['search'] ?? '');
$sql = "SELECT * FROM products";

if ($search !== '') {

    $sql .= " WHERE name LIKE ? OR product_code LIKE ? OR category LIKE ?";
    $search_param = "%$search%";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();

    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} else {

    $products = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

?>