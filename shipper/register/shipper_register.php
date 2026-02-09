<?php

require_once "../../db.php";
require_once "register.php";

if (isset($_POST['register'])) {

    $name  = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob   = $_POST['dob'];
    $cmt   = $_POST['cmt'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Kiểm tra email đã tồn tại
    $check = $conn->prepare("SELECT id FROM shipper WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Email này đã được sử dụng. Vui lòng dùng email khác.";
    } else {

        // Avatar để NULL hoặc ảnh mặc định
        $avatar = NULL; 
 

        $stmt = $conn->prepare("
            INSERT INTO shipper (name, email, phone, dob, cmt, avatar, password)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssssss",
            $name,
            $email,
            $phone,
            $dob,
            $cmt,
            $avatar,
            $password
        );

        if ($stmt->execute()) {
            $_SESSION['shipper_id'] = $stmt->insert_id;
            $_SESSION['shipper_name'] = $name;

            header("Location: ../shipper_dashboard.php");
            exit;
        } else {
            $error = $stmt->error;
        }
    }
}
?>
