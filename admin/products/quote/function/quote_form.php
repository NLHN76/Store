<?php 
require_once "../../../db.php";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa Báo giá sản phẩm</title>
    <link rel="stylesheet" href="css/edit_quote.css">
</head>
<body>

<h1>Chỉnh sửa Báo giá sản phẩm</h1>

<form method="post"
      action="/store/admin/products/quote/function/quote_download.php"
      id="quote-form">

    <label>Tên khách hàng:</label>
    <input type="text" name="customer_name" required>

    <label>Ngày báo giá:</label>
    <input type="date" name="quote_date" value="<?= date('Y-m-d') ?>" required>

    <label>Điều khoản:</label>
    <textarea name="terms" rows="4">
Báo giá có hiệu lực trong 7 ngày.
Đã bao gồm VAT.
    </textarea>

    <h3>Chọn sản phẩm</h3>
    <table>
        <thead>
        <tr>
            <th>Chọn</th>
            <th>Mã SP</th>
            <th>Tên</th>
            <th>Loại</th>
            <th>Giá</th>
            <th>Số lượng</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products_db as $p): ?>
            <tr>
                <td>
                    <input type="checkbox"
                           class="product-checkbox"
                           data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                           data-price="<?= $p['price'] ?>">
                </td>
                <td><?= $p['product_code'] ?></td>
                <td><?= $p['name'] ?></td>
                <td><?= $p['category'] ?></td>
                <td><?= format_price($p['price']) ?></td>
                <td>
                    <input type="number" class="product-qty" value="1" min="1" disabled>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <input type="hidden" name="products" id="products-input">
    <button type="submit">Xuất báo giá</button>
</form>

<script src="js/product_quote.js"></script>
</body>
</html>
