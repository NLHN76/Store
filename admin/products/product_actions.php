<?php
require_once "product_functions.php";

$available_colors = load_colors();

$add_error = "";
$edit_error = "";

// =================== XỬ LÝ MÀU ===================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === "add_color") {
        $new = trim($_POST['new_color']);
        if ($new !== "" && !in_array($new, $available_colors)) {
            $available_colors[] = $new;
            save_colors($available_colors);
        }
        header("Location: admin_products.php?color=added");
        exit;
    }

    if ($_POST['action'] === "delete_color") {
        $del = $_POST['delete_color'] ?? "";
        if (($i = array_search($del, $available_colors)) !== false) {
            unset($available_colors[$i]);
            $available_colors = array_values($available_colors);
            save_colors($available_colors);
        }
        header("Location: admin_products.php?color=deleted");
        exit;
    }
}

// ====================== THÊM SẢN PHẨM =====================
if (isset($_POST['action']) && $_POST['action'] === "add") {

    $name = trim($_POST['product_name']);
    $category = trim($_POST['product_category']);
    $price = clean_price($_POST['product_price']);

    $allowed = ['Tai nghe', 'Cáp sạc', 'Ốp lưng', 'Kính cường lực'];

    if ($name === "" || $price < 0 || !in_array($category, $allowed)) {
        $add_error = "Dữ liệu thêm không hợp lệ.";
        return;
    }

    // upload ảnh
    $image_name = null;
    if (!empty($_FILES['product_image']['name'])) {
        $image_name = uniqid("prod_") . "." . strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        move_uploaded_file($_FILES['product_image']['tmp_name'], "../uploads/" . $image_name);
    }

    $colors = implode(",", $_POST['product_colors'] ?? []);
    $code = generate_product_code($category, $conn); // đổi sang mysqli

    $stmt = $conn->prepare("INSERT INTO products(name, price, color, category, image, product_code) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdssss", $name, $price, $colors, $category, $image_name, $code);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_products.php?status=added");
    exit;
}

// ====================== SỬA SẢN PHẨM =====================
if (isset($_POST['action']) && $_POST['action'] === "edit") {

    $id = intval($_POST['product_id']);
    $name = trim($_POST['product_name']);
    $category = trim($_POST['product_category']);
    $price = clean_price($_POST['product_price']);
    $colors = implode(",", $_POST['product_colors'] ?? []);

    $allowed = ['Tai nghe', 'Cáp sạc', 'Ốp lưng', 'Kính cường lực'];

    if ($name === "" || $price < 0 || !in_array($category, $allowed)) {
        $edit_error = "Dữ liệu sửa không hợp lệ.";
        return;
    }

    // Lấy sản phẩm cũ
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old = $result->fetch_assoc();
    $stmt->close();

    if (!$old) {
        $edit_error = "Không tìm thấy sản phẩm.";
        return;
    }

    // ========== Xử lý upload ảnh mới ==========
    $image_name = $old['image'];  // giữ ảnh cũ

    if (!empty($_FILES['product_image']['name'])) {
        if ($old['image'] && file_exists("../uploads/" . $old['image'])) {
            unlink("../uploads/" . $old['image']);
        }

        $image_name = uniqid("prod_") . "." . strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        move_uploaded_file($_FILES['product_image']['tmp_name'], "../uploads/" . $image_name);
    }

    // ========== Update DB ==========
    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, color=?, category=?, image=? WHERE id=?");
    $stmt->bind_param("sdsssi", $name, $price, $colors, $category, $image_name, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_products.php?status=edited");
    exit;
}

// ====================== XÓA =====================
if (isset($_POST['action']) && $_POST['action'] === "delete") {
    $id = intval($_POST['product_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_products.php?status=deleted");
    exit;
}

// ====================== BẬT/TẮT =====================
if (isset($_POST['action']) && $_POST['action'] === "toggle_status") {
    $id = intval($_POST['product_id']);
    $conn->query("UPDATE products SET is_active = 1 - is_active WHERE id = $id");
    header("Location: admin_products.php?status=toggled");
    exit;
}

// ====================== TÌM KIẾM =====================
$search = trim($_GET['search'] ?? "");
$sql = "SELECT * FROM products";
$params = [];

if ($search !== "") {
    $sql .= " WHERE name LIKE ? OR product_code LIKE ? OR category LIKE ?";
    $search_param = "%$search%";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $result = $conn->query($sql);
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
