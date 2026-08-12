<?php
require_once "../../db.php";

/* ===================== BANNER ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['banner_action'])) {

    $title = $_POST['banner_title'];
    $description = $_POST['banner_description'];
    $image = null;

    if (!empty($_FILES['banner_image']['name'])) {
        $image = $_FILES['banner_image']['name'];
        $target = 'uploads/' . basename($image);

        if (strtolower(pathinfo($target, PATHINFO_EXTENSION)) != 'jpg') {
            die("Chỉ hỗ trợ ảnh JPG");
        }

        if (!move_uploaded_file($_FILES['banner_image']['tmp_name'], $target)) {
            die("Tải ảnh banner thất bại");
        }
    }

    $res = $conn->query("SELECT id FROM home WHERE id = 1");
    if ($res->num_rows > 0) {
        if ($image) {
            $stmt = $conn->prepare(
                "UPDATE home SET title=?, description=?, image=? WHERE id=1"
            );
            $stmt->bind_param("sss", $title, $description, $image);
        } else {
            $stmt = $conn->prepare(
                "UPDATE home SET title=?, description=? WHERE id=1"
            );
            $stmt->bind_param("ss", $title, $description);
        }
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO home (id, title, description, image) VALUES (1, ?, ?, ?)"
        );
        $stmt->bind_param("sss", $title, $description, $image);
        $stmt->execute();
        $stmt->close();
    }

    echo "<script>alert('Cập nhật banner thành công');location='admin_home.php';</script>";
    exit;
}

// Lấy banner
$banner = $conn->query("SELECT * FROM home WHERE id=1")->fetch_assoc();


/* =================== KHUYẾN MÃI ======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['promo_action'])) {

    $action = $_POST['promo_action'];
    $id = $_POST['promo_id'] ?? null;
    $title = $_POST['promo_title'];
    $description = $_POST['promo_description'];
    $link = $_POST['promo_link'] ?? null;

    // ADD
    if ($action === 'add') {

        if (empty($_FILES['promo_image']['name'])) {
            die("Vui lòng chọn ảnh khuyến mãi");
        }

        $image = $_FILES['promo_image']['name'];
        $target = 'uploads/' . basename($image);

        if (strtolower(pathinfo($target, PATHINFO_EXTENSION)) != 'jpg') {
            die("Chỉ hỗ trợ ảnh JPG");
        }

        move_uploaded_file($_FILES['promo_image']['tmp_name'], $target);

        $stmt = $conn->prepare(
            "INSERT INTO promotions (title, description, image, link)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $title, $description, $image, $link);
        $stmt->execute();
        $stmt->close();
    }

    // EDIT
    elseif ($action === 'edit' && $id) {

        if (!empty($_FILES['promo_image']['name'])) {
            $image = $_FILES['promo_image']['name'];
            $target = 'uploads/' . basename($image);
            move_uploaded_file($_FILES['promo_image']['tmp_name'], $target);

            $stmt = $conn->prepare(
                "UPDATE promotions
                 SET title=?, description=?, link=?, image=?
                 WHERE id=?"
            );
            $stmt->bind_param("ssssi", $title, $description, $link, $image, $id);
        } else {
            $stmt = $conn->prepare(
                "UPDATE promotions
                 SET title=?, description=?, link=?
                 WHERE id=?"
            );
            $stmt->bind_param("sssi", $title, $description, $link, $id);
        }

        $stmt->execute();
        $stmt->close();
    }

    // DELETE
    elseif ($action === 'delete' && $id) {
        $stmt = $conn->prepare("DELETE FROM promotions WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    echo "<script>location='admin_home.php';</script>";
    exit;
}

// Lấy khuyến mãi
$promotions = $conn
    ->query("SELECT * FROM promotions ORDER BY id DESC")
    ->fetch_all(MYSQLI_ASSOC);


/* =============== SẢN PHẨM NỔI BẬT =====================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['featured_action'])) {

    $action = $_POST['featured_action'];
    $product_id = $_POST['product_id'] ?? null;

    // ADD
    if ($action === 'add' && $product_id) {

        $check = $conn->prepare(
            "SELECT id FROM featured_products WHERE product_id=?"
        );
        $check->bind_param("i", $product_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows == 0) {
            $stmt = $conn->prepare(
                "INSERT INTO featured_products (product_id)
                 VALUES (?)"
            );
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->close();
        }

        $check->close();
    }

    // DELETE
    elseif ($action === 'delete' && $product_id) {
        $stmt = $conn->prepare(
            "DELETE FROM featured_products WHERE product_id=?"
        );
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();
    }

    // giữ nguyên trang + không cuộn lên đầu
    echo "<script>
        const y = window.scrollY;
        sessionStorage.setItem('scrollY', y);
        location='admin_home.php';
    </script>";
    exit;
}


// Lấy danh sách sản phẩm (select)
$products = $conn->query("
    SELECT id, name, price, image
    FROM products
    WHERE is_active = 1
    ORDER BY id DESC
")->fetch_all(MYSQLI_ASSOC);

// Lấy sản phẩm nổi bật
$featured_products = $conn->query("
    SELECT 
        p.id,
        p.name,
        p.price,
        p.image
    FROM featured_products f
    JOIN products p ON f.product_id = p.id
    WHERE p.is_active = 1
    ORDER BY f.id DESC
")->fetch_all(MYSQLI_ASSOC);

?>
