<?php
require_once "../../db.php";

// Kiểm tra token
if (isset($_GET['token'])) {

    $token = $_GET['token'];

    // Kiểm tra token
    $stmt = $conn->prepare("
        SELECT email 
        FROM password_resets 
        WHERE token=?
    ");

    $stmt->bind_param("s", $token);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        // Lấy email từ token
        $row = $result->fetch_assoc();
        $email = $row['email'];

        // Xử lý cập nhật
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $new_password = $_POST['new_password'] ?? '';

            // Kiểm tra dữ liệu
            if ($new_password == '') {

                echo "Vui lòng nhập mật khẩu mới!";
                exit;
            }

            // Mã hóa mật khẩu
            $hashed_password = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );

            // Chỉ cập nhật mật khẩu
            $stmt_update = $conn->prepare("
                UPDATE users
                SET password=?
                WHERE email=?
            ");

            $stmt_update->bind_param(
                "ss",
                $hashed_password,
                $email
            );

            if ($stmt_update->execute()) {

                // Xóa token sau khi cập nhật thành công
                $stmt_delete = $conn->prepare("
                    DELETE FROM password_resets
                    WHERE token=?
                ");

                $stmt_delete->bind_param("s", $token);
                $stmt_delete->execute();

                echo "Mật khẩu đã được cập nhật thành công!";

            } else {

                echo "Đã xảy ra lỗi khi cập nhật mật khẩu!";
                echo "<br>Lỗi: " . $stmt_update->error;
            }
        }

    } else {

        echo "Token không hợp lệ hoặc đã hết hạn!";
    }

} else {

    echo "Không có token nào được cung cấp!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Đặt lại mật khẩu</title>
</head>

<body>

    <h2>Đặt lại mật khẩu</h2>

    <form method="POST"
          action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>">

        <div class="form-group">
            <label for="new_password">Nhập mật khẩu mới:</label>

            <input type="password"
                   id="new_password"
                   name="new_password"
                   required>
        </div>

        <button type="submit">
            Cập nhật mật khẩu
        </button>

        <button type="button"
                onclick="window.location.href='http://localhost:8080/store/user/user.html';">
            Quay lại Trang Chủ
        </button>

    </form>

</body>

</html>