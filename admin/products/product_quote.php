<?php
require_once "../../db.php";

// Lấy sản phẩm từ CSDL
$sql = "SELECT product_code, name, category, price FROM products ORDER BY id DESC";
$result = $conn->query($sql);

$products_db = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products_db[] = $row;
    }
}

function format_price($price) {
    return number_format($price, 0, ',', '.') . " VNĐ";
}


// Xử lý form submit để trả về file HTML báo giá download
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['products'])) {
    $customer_name = $_POST['customer_name'] ?? '';
    $quote_date = $_POST['quote_date'] ?? date('Y-m-d');
    $terms = $_POST['terms'] ?? '';
    $products_json = $_POST['products'];

    $products_post = json_decode($products_json, true);
    if (!is_array($products_post)) $products_post = [];

    $total_price = 0;
    foreach ($products_post as &$p) {
        $p['quantity'] = intval($p['quantity'] ?? 1);
        $p['price'] = floatval($p['price'] ?? 0);
        $p['subtotal'] = $p['price'] * $p['quantity'];
        $total_price += $p['subtotal'];
    }

    // Gửi header để trình duyệt tải file HTML báo giá về
    header('Content-Type: text/html; charset=utf-8');
    header('Content-Disposition: attachment; filename="bao_gia.html"');
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8" />
        <title>Báo giá sản phẩm</title>
         <link rel="stylesheet" href="css/quote.css">
    </head>
    <body>
        <h1>BÁO GIÁ SẢN PHẨM</h1>

        <div style="text-align: center;">
            <p><strong>MOBILE GEAR</strong></p>
            <p>Địa chỉ: Số 254 Tây Sơn - P. Trung Liệt - Q. Đống Đa - TP. Hà Nội</p>
            <p>Điện thoại: 0587.911.287</p>
            <p>Email: mobilegear@gmail.com</p>
        </div>

        <p class="info"><strong>Khách hàng:</strong> <?= htmlspecialchars($customer_name) ?></p>
        <p class="info"><strong>Ngày báo giá:</strong> <?= date('d/m/Y', strtotime($quote_date)) ?></p>

        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Tên sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá (VNĐ)</th>
                    <th>Thành tiền (VNĐ)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products_post as $i => $product): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td><?= $product['quantity'] ?></td>
                        <td><?= format_price($product['price']) ?></td>
                        <td><?= format_price($product['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4"><strong>Tổng cộng</strong></td>
                    <td><strong><?= format_price($total_price) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <p class="note"><strong>Điều khoản:</strong></p>
        <p><?= nl2br(htmlspecialchars($terms)) ?></p>
    </body>
    </html>
    <?php
    exit;
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Chỉnh sửa Báo giá sản phẩm</title>
    <link rel ="stylesheet" href="css/edit_quote.css">
</head>
<body>
    <h1>Chỉnh sửa Báo giá sản phẩm</h1>
    <form method="post" id="quote-form">
        <label for="customer_name">Tên khách hàng:</label>
        <input type="text" name="customer_name" id="customer_name" required>

        <label for="quote_date">Ngày báo giá:</label>
        <input type="date" name="quote_date" id="quote_date" value="<?= date('Y-m-d') ?>" required>
         
        <p class="note"><strong>Điều khoản:</strong></p>
        <textarea name="terms" id="terms" rows="4">Báo giá có hiệu lực trong 7 ngày kể từ ngày lập. 
Đã bao gồm VAT và chưa tính thêm phí vận chuyển.</textarea>

        <h3>Chọn sản phẩm báo giá:</h3>
        <table>
            <thead>
                <tr>
                    <th>Chọn</th>
                    <th>Mã SP</th>
                    <th>Tên sản phẩm</th>
                    <th>Loại</th>
                    <th>Giá (VNĐ)</th>
                    <th>Số lượng</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products_db as $prod): ?>
                <tr>
                    <td><input type="checkbox" class="product-checkbox" data-name="<?= htmlspecialchars($prod['name'], ENT_QUOTES) ?>" data-price="<?= $prod['price'] ?>"></td>
                    <td><?= htmlspecialchars($prod['product_code']) ?></td>
                    <td><?= htmlspecialchars($prod['name']) ?></td>
                    <td><?= htmlspecialchars($prod['category']) ?></td>
                    <td><?= format_price($prod['price']) ?></td>
                    <td><input type="number" class="product-qty" value="1" min="1" disabled></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <input type="hidden" name="products" id="products-input">

        <button type="submit">Xuất Báo giá </button>
    </form>

    
<script src="js/product_quote.js"> </script>
</body>
</html>
