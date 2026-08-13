<?php


/* =================== KHUYẾN MÃI ======================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['promo_action'])) {

    $action = $_POST['promo_action'];
    $id = $_POST['promo_id'] ?? null;

    $title = $_POST['promo_title'];
    $description = $_POST['promo_description'];
    $link = $_POST['promo_link'] ?? null;


    /* ================= ADD ================= */

    if ($action === 'add') {

        if (empty($_FILES['promo_image']['name'])) {
            die("Vui lòng chọn ảnh khuyến mãi");
        }

        $image = $_FILES['promo_image']['name'];

        $target = 'uploads/' . basename($image);

        if (
            strtolower(
                pathinfo($target, PATHINFO_EXTENSION)
            ) != 'jpg'
        ) {
            die("Chỉ hỗ trợ ảnh JPG");
        }

        move_uploaded_file(
            $_FILES['promo_image']['tmp_name'],
            $target
        );

        $stmt = $conn->prepare(
            "INSERT INTO promotions
             (title, description, image, link)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $title,
            $description,
            $image,
            $link
        );

        $stmt->execute();
        $stmt->close();
    }


    /* ================= EDIT ================= */

    elseif ($action === 'edit' && $id) {

        if (!empty($_FILES['promo_image']['name'])) {

            $image = $_FILES['promo_image']['name'];

            $target = 'uploads/' . basename($image);

            move_uploaded_file(
                $_FILES['promo_image']['tmp_name'],
                $target
            );

            $stmt = $conn->prepare(
                "UPDATE promotions
                 SET title=?,
                     description=?,
                     link=?,
                     image=?
                 WHERE id=?"
            );

            $stmt->bind_param(
                "ssssi",
                $title,
                $description,
                $link,
                $image,
                $id
            );

        } else {

            $stmt = $conn->prepare(
                "UPDATE promotions
                 SET title=?,
                     description=?,
                     link=?
                 WHERE id=?"
            );

            $stmt->bind_param(
                "sssi",
                $title,
                $description,
                $link,
                $id
            );
        }

        $stmt->execute();
        $stmt->close();
    }


    /* ================= DELETE ================= */

    elseif ($action === 'delete' && $id) {

        $stmt = $conn->prepare(
            "DELETE FROM promotions WHERE id=?"
        );

        $stmt->bind_param("i", $id);

        $stmt->execute();
        $stmt->close();
    }


    echo "
        <script>
            location='admin_home.php';
        </script>
    ";

    exit;
}


/* Lấy khuyến mãi */

$promotions = $conn
    ->query(
        "SELECT *
         FROM promotions
         ORDER BY id DESC"
    )
    ->fetch_all(MYSQLI_ASSOC);

?>