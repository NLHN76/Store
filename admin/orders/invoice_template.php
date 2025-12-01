<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Hóa Đơn - Mã <?= htmlspecialchars($row["id"]) ?></title>
<style>
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    line-height: 1.5;
    margin: 0;
    padding: 15px;
    font-size: 11px;
    background-color: #fff;
    color: #333;
}
.invoice-box {
    width: 100%;
    max-width: 700px;
    margin: auto;
    padding: 20px;
    border: 1px solid #eee;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
    background-color: #fff;
}
h1 {
    text-align: center;
    color: #222;
    font-size: 1.8em;
    margin-bottom: 15px;
    text-transform: uppercase;
}
.label {
    font-weight: bold;
    display: inline-block;
    min-width: 120px;
}
.line {
    border-top: 1px dashed #ccc;
    margin: 15px 0;
}
.total-section p {
    font-size: 1.1em;
    font-weight: bold;
    text-align:right;
}
.company-name {
    font-size: 1.4em;
    font-weight: bold;
    text-align: center;
    margin-bottom: 5px;
}
.footer {
    text-align: center;
    margin-top: 25px;
    font-size: 0.95em;
    color: #666;
    border-top: 1px solid #eee;
    padding-top: 15px;
}
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
        <p><span class='label'>Xuất Hóa Đơn Lúc:</span> <?= date('d/m/Y H:i') ?></p>
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
        <p><span class='label'>Màu Sắc:</span> <?= htmlspecialchars($row["color"] ?: '-') ?></p>
        <p><span class='label'>Số Lượng:</span> <?= htmlspecialchars($row["product_quantity"]) ?></p>
    </div>

    <div class='line'></div>

    <div class='total-section'>
        <p><span class='label'>Tổng Tiền Thanh Toán:</span> <?= number_format($row["total_price"], 0, ',', '.') ?> VNĐ</p>
    </div>

    <div class='footer'>
        <p>Xin chân thành cảm ơn Quý khách đã tin tưởng và mua hàng!</p>
    </div>
</div>
</body>
</html>
