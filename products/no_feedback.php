<?php
require_once "../db.php";
require_once "function.php";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="../css/product_detail.css">
<title>Chi tiết sản phẩm - <?= htmlspecialchars($product['name']) ?></title>
</head>
<body>

<div class="product-detail">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <img src="../admin/uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="max-width:300px;">
    <p><strong>Mã sản phẩm:</strong> <?= htmlspecialchars($product['product_code']) ?></p>
    <p><strong>Loại sản phẩm:</strong> <?= htmlspecialchars($product['category']) ?></p>
    <p><strong>Giá:</strong> <?= number_format($product['price'],0,',','.') ?> VNĐ</p>
    <p><strong>Mô tả:</strong> <?= htmlspecialchars($product['description'] ?: "Chưa có mô tả") ?></p>
    <p><strong>Chất liệu:</strong> <?= htmlspecialchars($product['material'] ?: "Chưa có thông tin") ?></p>
    <p><strong>Tương thích:</strong> <?= htmlspecialchars($product['compatibility'] ?: "Chưa có thông tin") ?></p>
    <p><strong>Bảo hành:</strong> <?= htmlspecialchars($product['warranty'] ?: "Chưa có thông tin") ?></p>
    <p><strong>Xuất xứ:</strong> <?= htmlspecialchars($product['origin'] ?: "Chưa có thông tin") ?></p>
    <p><strong>Tính năng:</strong> <?= htmlspecialchars($product['features'] ?: "Chưa có thông tin") ?></p>
</div>

<?php if ($related_products->num_rows): ?>
<div class="product-detail" style="margin-top:20px;">
    <h2>Gợi ý sản phẩm khác</h2>
    <div class="related-products">
        <?php while($rel = $related_products->fetch_assoc()): ?>
            <div class="related-item">
                <a href="no_feedback.php?code=<?= htmlspecialchars($rel['product_code']) ?>">
                    <img src="../admin/uploads/<?= htmlspecialchars($rel['image']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" style="width:100%; height:auto;">
                    <p><?= htmlspecialchars($rel['name']) ?></p>
                    <p><?= number_format($rel['price'],0,',','.') ?> VNĐ</p>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<!-- Đánh giá sản phẩm -->
<div class="product-detail" style="margin-top:30px;">
  <a id="reviews"></a>
  <h2>Đánh giá sản phẩm</h2>

  <!-- Nút quay về -->
  <a href="../user/user.html" class="btn-back">⬅ Quay về</a>

  <h3 style="margin-top:20px;">Đánh giá gần đây</h3>
  <?php if ($feedbacks->num_rows > 0): ?>
    <?php while($fb = $feedbacks->fetch_assoc()): ?>
      <div class="feedback-item">
        <p><strong><?= htmlspecialchars($fb['user_name']) ?></strong> - <?= str_repeat("⭐",$fb['rating']) ?></p>
        <p><?= nl2br(htmlspecialchars($fb['message'])) ?></p>
        <small><i><?= $fb['created_at'] ?></i></small>
      </div>
    <?php endwhile; ?>

    <!-- PHÂN TRANG -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination" style="margin-top:20px; text-align:center;">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?code=<?= urlencode($product['product_code']) ?>&page=<?= $i ?>#reviews"
               style="padding:8px 12px; margin:4px; border:1px solid #ccc; text-decoration:none;
                      <?= ($i == $page) ? 'background:#333; color:white;' : 'background:white; color:black;' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

  <?php else: ?>
    <p>Chưa có đánh giá nào cho sản phẩm này.</p>
  <?php endif; ?>
  
</div>


</body>
</html>

<?php $conn->close(); ?>
