<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thanh Toán</title>
<link rel="stylesheet" href="css/user_pay.css">
</head>
<body>
<div class="container">
<h1>Thông Tin Đặt Hàng</h1>

<?php if (!$isPaymentConfirmed): ?>
<form method="POST" action="">
    <label for="name">Tên người nhận:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" readonly>

    <label for="user_code">Mã Khách Hàng:</label>
    <input type="text" id="user_code" name="user_code" value="<?php echo htmlspecialchars($user_code); ?>" readonly>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>

    <label for="phone">Số Điện Thoại:</label>
    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" readonly>

    <label for="address">Địa Chỉ Nhận Hàng:</label>
    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" readonly>

    <button type="submit">Xác Nhận Đặt Hàng</button>
</form>


<div class="cart-summary">
<h2>Xem Lại Giỏ Hàng</h2>
<p><strong>Tổng số sản phẩm:</strong> <?php echo $itemCount; ?></p>
<p><strong>Tổng giá trị:</strong> <?php echo number_format($totalPrice, 0, ',', '.'); ?> VNĐ</p>
<h3>Chi Tiết Sản Phẩm:</h3>

<ul>
<?php foreach ($itemsGrouped as $item): ?>
<li style="display:flex; gap:12px; margin-bottom:12px; align-items:flex-start;">

    <img 
        src="<?php echo htmlspecialchars($item['image']); ?>" 
        alt="<?php echo htmlspecialchars($item['name']); ?>"
        style="width:80px; height:80px; object-fit:cover; border-radius:6px;"
    >

   
    <div>
        <?php
        $itemTotal = $item['price'] * $item['quantity'];
        echo "<strong>Mã:</strong> " . htmlspecialchars($item['product_code']) . "<br>";
        echo "<strong>Tên:</strong> " . htmlspecialchars($item['name']) . " (x" . htmlspecialchars($item['quantity']) . ")<br>";
        echo "<strong>Màu:</strong> " . htmlspecialchars($item['color']) . "<br>";
        echo "<strong>Giá:</strong> " . number_format($itemTotal, 0, ',', '.') . " VNĐ";
        ?>
    </div>

</li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>


<?php if ($isPaymentConfirmed): ?>

<div id="qr-code">

    <h3>Quét Mã QR Để Thanh Toán</h3>

    <img
        src="<?= htmlspecialchars($qr_url) ?>"
        alt="Mã QR Thanh Toán"
        style="width:100%; max-width:300px;"
    >

    <p>
        <strong>Mã đơn hàng:</strong>
        <?= htmlspecialchars($order_code) ?>
    </p>

    <p>
        <strong>Mã khách hàng:</strong>
        <?= htmlspecialchars($user_code) ?>
    </p>

    <p>
        <strong>Số tiền:</strong>
        <?= number_format($totalPrice, 0, ',', '.') ?> VNĐ
    </p>

    <p>
        Quét mã QR bằng ứng dụng ngân hàng để thanh toán.
    </p>

</div>

<?php endif; ?>


<a href="../user.html" class="back-button">Quay lại trang chủ</a>
</div>
</body>
</html>         