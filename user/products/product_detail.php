<?php
require_once "../../db.php";
require_once "function.php";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="css/product_detail.css">
<title>Chi tiết sản phẩm - <?= htmlspecialchars($product['name']) ?></title>
</head>
<body>

<div class="product-detail">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <img src="../../admin/uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="max-width:300px;">
    <p><strong>Mã sản phẩm:</strong> <?= htmlspecialchars($product['product_code']) ?></p>
    <p><strong>Giá:</strong> <?= number_format($product['price'],0,',','.') ?> VNĐ</p>
    <?php
$colors = array_filter(array_map('trim', explode(',', $product['color'] ?? '')));
?>

<?php if (!empty($colors)): ?>
<div style="margin-top:15px;">
    <label><strong>Màu sắc:</strong></label>
    <select id="productColor">
        <?php foreach ($colors as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>">
                <?= htmlspecialchars($c) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
<?php endif; ?>

    <div style="margin-top:20px; display:flex; gap:15px; align-items:center;">
    <button onclick="addToCartDetail()"
            style="padding:10px 16px; background:#28a745; color:white; border:none; cursor:pointer;">
        🛒 Thêm vào giỏ hàng
    </button>

</div>

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
                <a href="product_detail.php?code=<?= htmlspecialchars($rel['product_code']) ?>">
                    <img src="../../admin/uploads/<?= htmlspecialchars($rel['image']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" style="width:100%; height:auto;">
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
  <?php if ($is_logged_in): ?>
    <form method="POST" style="display:flex; gap:20px; align-items:flex-start;">
      <div style="flex:1;">
        <label>Chọn số sao:</label>
        <div style="font-size:25px; margin-top:5px;">
          <?php for($i=5; $i>=1; $i--): ?>
            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" required>
            <label for="star<?= $i ?>">⭐<?= $i ?></label>
          <?php endfor; ?>
        </div>
        <textarea name="message" placeholder="Nhập nội dung đánh giá..." rows="3" style="margin-top:10px; width:100%;"></textarea>
      </div>
      <div style="display:flex; flex-direction:column; gap:10px;">
        <button type="submit" class="btn-submit">Gửi đánh giá</button>
        <a href="../user_logout.php" class="btn-back">⬅ Quay về</a>
      </div>
    </form>
    
  <?php else: ?>
    <p style="color:red;">Bạn cần đăng nhập để gửi đánh giá.</p>
  <?php endif; ?>

  <h3 style="margin-top:20px;">Đánh giá gần đây</h3>
  <?php if ($feedbacks->num_rows > 0): ?>
    <?php while($fb = $feedbacks->fetch_assoc()): ?>
      <div class="feedback-item">
        <p><strong><?= htmlspecialchars($fb['user_name']) ?></strong> - <?= str_repeat("⭐",$fb['rating']) ?></p>
        <p><?= nl2br(htmlspecialchars($fb['message'])) ?></p>
        <small><i><?= $fb['created_at'] ?></i></small>
        <?php if ($is_logged_in && $fb['user_id'] == $user_id): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="delete_id" value="<?= $fb['id'] ?>">
            <button type="submit" name="delete_feedback" onclick="return confirm('Bạn có chắc muốn xóa đánh giá này không?')" style="background:red; color:white; border:none; padding:3px 6px; cursor:pointer;">Xóa</button>
          </form>
        <?php endif; ?>
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


<script>
function addToCartDetail() {
    const colorSelect = document.getElementById('productColor');
    const color = colorSelect ? colorSelect.value : null;

    fetch('../cart/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            product_code: "<?= $product['product_code'] ?>",
            color: color,
            quantity: 1,
            price: <?= $product['price'] ?>
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // ✅ chuyển sang giỏ + reload data
            window.location.href = "../user_logout.php#cart";
        } else {
            alert(data.error || '❌ Thêm thất bại');
        }
    })
    .catch(() => alert('❌ Lỗi kết nối'));
}


</script>


</body>
</html>


<?php $conn->close(); ?>
