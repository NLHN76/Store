<?php

//BANNER

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

        if (!move_uploaded_file(
            $_FILES['banner_image']['tmp_name'],
            $target
        )) {
            die("Tải ảnh banner thất bại");
        }
    }

    $res = $conn->query(
        "SELECT id FROM home WHERE id = 1"
    );

    if ($res->num_rows > 0) {

        if ($image) {

            $stmt = $conn->prepare(
                "UPDATE home
                 SET title=?, description=?, image=?
                 WHERE id=1"
            );

            $stmt->bind_param(
                "sss",
                $title,
                $description,
                $image
            );

        } else {

            $stmt = $conn->prepare(
                "UPDATE home
                 SET title=?, description=?
                 WHERE id=1"
            );

            $stmt->bind_param(
                "ss",
                $title,
                $description
            );
        }

        $stmt->execute();
        $stmt->close();

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO home
             (id, title, description, image)
             VALUES (1, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sss",
            $title,
            $description,
            $image
        );

        $stmt->execute();
        $stmt->close();
    }

    echo "
        <script>
            alert('Cập nhật banner thành công');
            location='admin_home.php';
        </script>
    ";

    exit;
}


//Lấy banner 

$banner = $conn
    ->query("SELECT * FROM home WHERE id=1")
    ->fetch_assoc();

?>