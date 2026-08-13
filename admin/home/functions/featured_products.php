<?php

/* =============== SẢN PHẨM NỔI BẬT ===================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['featured_action'])) {

    $action = $_POST['featured_action'];

    $product_id = $_POST['product_id'] ?? null;


    /* ================= ADD ================= */

    if ($action === 'add' && $product_id) {

        // Kiểm tra sản phẩm đã tồn tại
        $check = $conn->prepare(
            "SELECT id
             FROM featured_products
             WHERE product_id=?"
        );

        $check->bind_param(
            "i",
            $product_id
        );

        $check->execute();
        $check->store_result();


        // Nếu chưa có thì thêm
        if ($check->num_rows == 0) {

            $stmt = $conn->prepare(
                "INSERT INTO featured_products
                 (product_id)
                 VALUES (?)"
            );

            $stmt->bind_param(
                "i",
                $product_id
            );

            $stmt->execute();
            $stmt->close();
        }

        $check->close();
    }


    /* ================= DELETE ================= */

    elseif ($action === 'delete' && $product_id) {

        $stmt = $conn->prepare(
            "DELETE FROM featured_products
             WHERE product_id=?"
        );

        $stmt->bind_param(
            "i",
            $product_id
        );

        $stmt->execute();
        $stmt->close();
    }


    /*
     * Lưu vị trí scroll trước khi reload
     */

    echo "
        <script>
            const y = window.scrollY;

            sessionStorage.setItem(
                'scrollY',
                y
            );

            location='admin_home.php';
        </script>
    ";

    exit;
}


/* ================= DANH SÁCH SẢN PHẨM ================= */

$products = $conn
    ->query(
        "SELECT id, name, price, image
         FROM products
         WHERE is_active = 1
         ORDER BY id DESC"
    )
    ->fetch_all(MYSQLI_ASSOC);


/* ================= SẢN PHẨM NỔI BẬT ================= */

$featured_products = $conn
    ->query(
        "SELECT
            p.id,
            p.name,
            p.price,
            p.image
         FROM featured_products f
         JOIN products p
             ON f.product_id = p.id
         WHERE p.is_active = 1
         ORDER BY f.id DESC"
    )
    ->fetch_all(MYSQLI_ASSOC);

?>