<?php

require_once "quote_data.php";
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
    <meta charset="UTF-8">
    <title>Báo giá sản phẩm</title>

    <style>
        <?php
        include __DIR__ . '/../css/quote.css';
        ?>
    </style>
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