<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "store";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    error_log("Database Connection Error: " . $conn->connect_error);
    die("Lỗi kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}

if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
}

$user_message = "";

// Xử lý xóa đơn hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);

    if ($delete_id) {
        $stmt = $conn->prepare("DELETE FROM payment WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $delete_id);
            if ($stmt->execute()) {
                $user_message = "<p class='message success'>Đơn hàng " . htmlspecialchars($delete_id) . " đã được xóa thành công.</p>";
            } else {
                $user_message = "<p class='message error'>Lỗi khi xóa đơn hàng: " . htmlspecialchars($stmt->error) . "</p>";
                error_log("Error deleting order ID $delete_id: " . $stmt->error);
            }
            $stmt->close();
        } else {
            $user_message = "<p class='message error'>Lỗi khi chuẩn bị câu lệnh xóa.</p>";
            error_log("Error preparing delete statement: " . $conn->error);
        }
    } else {
        $user_message = "<p class='message error'>ID đơn hàng không hợp lệ để xóa.</p>";
    }
}

// Xử lý xuất hóa đơn HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_html_id'])) {
    $order_id = filter_input(INPUT_POST, 'export_html_id', FILTER_VALIDATE_INT);

    if ($order_id) {
        $sql = "SELECT * FROM payment WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {

                date_default_timezone_set('Asia/Ho_Chi_Minh');

                ob_start();

                ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn - Mã <?= htmlspecialchars($row["id"]) ?></title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; line-height: 1.5; margin: 0; padding: 15px; font-size: 11px; background-color: #fff; color: #333; }
        .invoice-box { width: 100%; max-width: 700px; margin: auto; padding: 20px; border: 1px solid #eee; box-shadow: 0 0 8px rgba(0,0,0,0.1); background-color: #ffffff; }
        h1 { text-align: center; color: #222; font-size: 1.8em; margin-bottom: 15px; text-transform: uppercase; }
        .contact-info, .invoice-details, .customer-details, .product-details { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .contact-info:last-child, .invoice-details:last-child, .customer-details:last-child, .product-details:last-child { border-bottom: none; }
        .contact-info p, .invoice-details p, .customer-details p, .product-details p { margin: 5px 0; }
        .label { font-weight: bold; display: inline-block; min-width: 120px; }
        .footer { text-align: center; margin-top: 25px; font-size: 0.95em; color: #666; border-top: 1px solid #eee; padding-top: 15px; }
        .line { border-top: 1px dashed #ccc; margin: 15px 0; }
        .total-section p { font-size: 1.1em; font-weight: bold; }
        .company-name { font-size: 1.4em; font-weight: bold; text-align: center; margin-bottom: 5px; }
    </style>
</head>

<body>
    <div class='invoice-box'>
        <div class='contact-info'>
            <p class='company-name'>MOBILE GEAR</p>
            <p style="text-align: center;">Địa chỉ: Số 254 Tây Sơn - P. Trung Liệt - Q. Đống Đa - TP. Hà Nội</p>
            <p style="text-align: center;">Điện thoại: 0587.911.287 | Email: mobilegear@gmail.com</p>
        </div>

        <h1>Hóa Đơn Thanh Toán</h1>

        <div class='invoice-details'>
            <p><span class='label'>Mã Hóa Đơn:</span> <?= htmlspecialchars($row["id"]) ?></p>
            <p><span class='label'>Ngày Đặt Hàng:</span> <?= date('d/m/Y H:i', strtotime($row["order_date"])) ?></p>
            <p><span class='label'>Xuất Hóa Đơn Lúc:</span> <?= date('d/m/Y H:i')  ?></p>
        </div>

        <div class='customer-details'>
            <p><span class='label'>Khách Hàng:</span> <?= htmlspecialchars($row["customer_name"]) ?></p>
            <p><span class='label'>Mã Khách Hàng:</span> <?= htmlspecialchars($row["user_code"]) ?: 'N/A' ?></p>
            <p><span class='label'>Email:</span> <?= htmlspecialchars($row["customer_email"]) ?></p>
            <p><span class='label'>Điện Thoại:</span> <?= htmlspecialchars($row["customer_phone"]) ?></p>
            <p><span class='label'>Địa Chỉ Giao Hàng:</span> <?= htmlspecialchars($row["customer_address"]) ?></p>
        </div>

        <div class='product-details'>
            <p><span class='label'>Mã Sản Phẩm:</span> <?= htmlspecialchars($row["product_code"]) ?></p>
            <p><span class='label'>Sản Phẩm:</span> <?= htmlspecialchars($row["product_name"]) ?></p>
            <p><span class='label'>Loại Sản Phẩm:</span> <?= htmlspecialchars($row["category"]) ?></p>
            <p><span class='label'>Số Lượng:</span> <?= htmlspecialchars($row["product_quantity"]) ?></p>
        </div>

        <div class='line'></div>

        <div class='total-section'>
            <p style="text-align: right;"><span class='label'>Tổng Tiền Thanh Toán:</span> <?= number_format($row["total_price"], 0, ',', '.') ?> VNĐ</p>
        </div>

        <div class='footer'>
            <p>Xin chân thành cảm ơn Quý khách đã tin tưởng và mua hàng!</p>
        </div>
    </div>
</body>
</html>



                <?php

                $html_content = ob_get_clean();

                header("Content-Type: application/octet-stream");
                header("Content-Disposition: attachment; filename=hoa_don_" . $order_id . ".html");

                echo $html_content;
                exit;

            } else {
                $user_message = "<p class='message error'>Không tìm thấy đơn hàng với ID " . htmlspecialchars($order_id) . ".</p>";
            }
            $stmt->close();
        } else {
            $user_message = "<p class='message error'>Lỗi khi chuẩn bị câu lệnh xuất hóa đơn.</p>";
            error_log("Error preparing export statement: " . $conn->error);
        }
    } else {
        $user_message = "<p class='message error'>ID đơn hàng không hợp lệ để xuất.</p>";
    }
}



// Xử lý cập nhật trạng thái đơn hànghàng
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status_id"], $_POST["new_status"])) {
    $update_id = intval($_POST["update_status_id"]);
    $new_status = $conn->real_escape_string($_POST["new_status"]);

    $update_sql = "UPDATE payment SET status = '$new_status' WHERE id = $update_id";
    if ($conn->query($update_sql)) {
        $user_message .= "<p class='message success'>Cập nhật trạng thái đơn hàng #$update_id thành công.</p>";
    } else {
        $user_message .= "<p class='message error'>Lỗi khi cập nhật trạng thái: " . htmlspecialchars($conn->error) . "</p>";
    }
}

//  Lấy Dữ Liệu Hiển Thị Danh Sách Đơn Hàng
$sql = "SELECT id, order_date, customer_name, customer_email, customer_phone, customer_address, product_code, product_name, category, product_quantity, total_price, user_code, status FROM payment ORDER BY order_date DESC";
$result = $conn->query($sql);

if (!$result) {
    $user_message .= "<p class='message error'>Lỗi khi truy vấn danh sách đơn hàng: " . htmlspecialchars($conn->error) . "</p>";
    error_log("Error fetching order list: " . $conn->error);
    $orders = [];
}


// Tìm kiếm mã đơn hàng 
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$sql = "SELECT * FROM payment";

if ($keyword !== '') {
    if (ctype_digit($keyword)) {
        // Tìm chính xác theo mã đơn (id)
        $sql .= " WHERE id = " . intval($keyword);
    } else {
        // Nếu nhập không phải số thì không trả về kết quả nào
        $sql .= " WHERE 0"; // luôn sai, không trả kết quả
    }
}

$result = $conn->query($sql);

?>



<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Quản Lý Đơn Hàng</title>
    <link rel="stylesheet" href="css/admin_online.css">
    <link rel="icon" href="uploads/favicon.ico" type="image/x-icon" />

 

    <script>
        function confirmDelete(orderId) {
            if (confirm('Bạn có chắc chắn muốn xóa đơn hàng #' + orderId + '?')) {
                document.getElementById('delete-form-' + orderId).submit();
            }
        }
        function goBack() {
            window.history.back();
        }
    </script>

</head>

<body>

<a class="back-button" href="admin_interface.php" title="Quay lại trang quản trị">
        <img src="uploads/exit.jpg" alt="Quay lại"> </a>

    <div class="container">
        <h1>Danh Sách Đơn Hàng</h1>

        <?php
            if (!empty($user_message)) {
                echo $user_message;
            }
        ?>
<form method="GET" class="search-form" style="margin-bottom: 20px;">
    <input type="text" name="keyword" placeholder="Tìm theo Mã đơn " value="<?= isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : '' ?>" />
    <button type="submit">Tìm kiếm</button>
</form>

       
<?php if (isset($result) && $result->num_rows > 0): ?>

        <table>
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày Đặt</th>
                    <th>Tên KH</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th>Địa Chỉ</th>
                    <th>Mã Sản Phẩm</th>
                    <th>Sản Phẩm</th>
                    <th>Loại SP</th>
                    <th>Số Lượng</th>
                    <th>Tổng Tiền (VNĐ)</th>
                    <th>Mã KH</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>


            <tbody>

                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td data-label="Mã đơn"><?= htmlspecialchars($row["id"]); ?></td>
                    <td data-label="Ngày Đặt"><?= date('d/m/Y H:i', strtotime($row["order_date"])); ?></td>
                    <td data-label="Tên KH"><?= htmlspecialchars($row["customer_name"]); ?></td>
                    <td data-label="Email"><?= htmlspecialchars($row["customer_email"]); ?></td>
                    <td data-label="SĐT"><?= htmlspecialchars($row["customer_phone"]); ?></td>
                    <td data-label="Địa Chỉ"><?= htmlspecialchars($row["customer_address"]); ?></td>
                    <td data-label="Mã Sản Phẩm"><?= htmlspecialchars($row["product_code"]); ?></td>
                    <td data-label="Sản Phẩm"><?= htmlspecialchars($row["product_name"]); ?></td>
                    <td data-label="Loại SP"><?= htmlspecialchars($row["category"]); ?></td>
                    <td data-label="Số Lượng" style="text-align: center;"><?= htmlspecialchars($row["product_quantity"]); ?></td>
                    <td data-label="Tổng Tiền" style="text-align: right; white-space: nowrap;"><?= number_format($row["total_price"], 0, ',', '.'); ?></td>
                    <td data-label="Mã KH"><?= htmlspecialchars($row["user_code"]) ?: '-'; ?></td>
                   
                   
                   
                    <td data-label="Trạng Thái">
              <form method="POST" style="margin:0;">
             <input type="hidden" name="update_status_id" value="<?= $row["id"]; ?>">
             <select name="new_status" onchange="this.form.submit()" style="padding: 4px 6px;">
            <?php
             $statuses = [
                "Chờ xử lý",
                "Chờ thanh toán",
                "Đã thanh toán",
                "Đang xử lý",
                "Đang giao hàng",
                "Đã giao hàng",
                "Đã hủy"
            ];
                foreach ($statuses as $status) {
                    $selected = ($row["status"] === $status) ? "selected" : "";
                    echo "<option value=\"$status\" $selected>$status</option>";
                }
            ?>
        </select>
    </form>
</td>


<td data-label="Hành Động" class="action action-buttons">
    <form id="delete-form-<?= $row['id']; ?>" method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="delete_id" value="<?= $row['id']; ?>">
        <button type="button" class="delete-btn" title="Xóa đơn hàng này" onclick="confirmDelete(<?= $row['id']; ?>)">Xóa</button>
    </form>
    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="export_html_id" value="<?= $row['id']; ?>">
        <button type="submit" class="export-btn" title="Xuất hóa đơn HTML">Xuất Bill</button>
    </form>
</td>
                </tr>
                <?php endwhile; ?>
            </tbody>


        </table>

        <?php else: ?>
            <p class="no-orders">Không có đơn hàng nào để hiển thị.</p>
        <?php endif; ?>

    </div>

</body>


<?php
$conn->close();
?>


</html>