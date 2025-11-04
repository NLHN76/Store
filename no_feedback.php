<?php
// ====== Kết nối CSDL ======
$conn = new mysqli("localhost", "root", "", "store"); 
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// ====== Lấy mã sản phẩm từ URL ======
$code = isset($_GET['code']) ? $_GET['code'] : '';

$sql = "
SELECT p.*, d.description, d.material, d.compatibility, d.warranty, d.origin, d.features
FROM products p
LEFT JOIN product_details d ON p.id = d.product_id
WHERE p.product_code = ? AND p.is_active = 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    echo "Không tìm thấy sản phẩm!";
    exit;
}

// ====== Lấy gợi ý sản phẩm khác (cùng loại) ======
$sql_suggested = "
    SELECT * FROM products 
    WHERE product_code != ? 
      AND category = ? 
      AND is_active = 1 
    ORDER BY RAND() 
    LIMIT 4
";
$stmt_suggested = $conn->prepare($sql_suggested);
$stmt_suggested->bind_param("ss", $product['product_code'], $product['category']);
$stmt_suggested->execute();
$result_suggested = $stmt_suggested->get_result();

// ====== Lấy danh sách đánh giá ======
$stmt_fb = $conn->prepare("
    SELECT f.*, u.name AS user_name 
    FROM feedback f 
    JOIN users u ON f.user_id = u.id 
    WHERE f.product_code = ?
    ORDER BY f.created_at DESC
");
$stmt_fb->bind_param("s", $product['product_code']);
$stmt_fb->execute();
$result_fb = $stmt_fb->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Chi tiết sản phẩm - <?php echo $product['name']; ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background: #f5f5f5;
      color: #333;
    }

    .product-detail {
      max-width: 1000px;
      margin: 30px auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .product-detail h1, .product-detail h2 {
      margin-bottom: 20px;
      font-size: 28px;
      color: #222;
    }

    .product-detail img {
      max-width: 300px;
      border-radius: 8px;
      display: block;
      margin-bottom: 15px;
    }

    .product-detail p {
      font-size: 16px;
      margin: 8px 0;
    }

    .product-detail strong {
      color: #444;
    }

    .related-products {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-top: 15px;
    }

    .related-item {
      background: #fff;
      border-radius: 8px;
      text-align: center;
      padding: 10px;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .related-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }

    .related-item img {
      max-width: 100%;
      height: 150px;
      object-fit: contain;
      margin-bottom: 10px;
    }

    .related-item p {
      margin: 5px 0;
      font-size: 15px;
    }

  

    .feedback-item {
      border-bottom:1px solid #ddd; 
      margin:10px 0; 
      padding-bottom:8px;
    }

    .feedback-item p {
      margin: 4px 0;
    }

    .feedback-item small {
      color: #666;
    }
  </style>
</head>
<body>
  <div class="product-detail">
    <h1><?php echo $product['name']; ?></h1>
    <img src="admin/uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
    <p><strong>Mã sản phẩm:</strong> <?php echo $product['product_code']; ?></p>
    <p><strong>Loại sản phẩm:</strong> <?php echo $product['category']; ?></p>
    <p><strong>Giá:</strong> <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
    <p><strong>Mô tả:</strong> <?php echo !empty($product['description']) ? $product['description'] : "Chưa có mô tả"; ?></p>
    <p><strong>Chất liệu:</strong> <?php echo !empty($product['material']) ? $product['material'] : "Chưa có thông tin"; ?></p>
    <p><strong>Tương thích:</strong> <?php echo !empty($product['compatibility']) ? $product['compatibility'] : "Chưa có thông tin"; ?></p>
    <p><strong>Bảo hành:</strong> <?php echo !empty($product['warranty']) ? $product['warranty'] : "Chưa có thông tin"; ?></p>
    <p><strong>Xuất xứ:</strong> <?php echo !empty($product['origin']) ? $product['origin'] : "Chưa có thông tin"; ?></p>
    <p><strong>Tính năng:</strong> <?php echo !empty($product['features']) ? $product['features'] : "Chưa có thông tin"; ?></p>
  </div>

  <?php if ($result_suggested->num_rows > 0): ?>
    <div class="product-detail" style="margin-top:20px;">
      <h2>Gợi ý sản phẩm khác</h2>
      <div class="related-products">
        <?php while ($rel = $result_suggested->fetch_assoc()): ?>
          <div class="related-item">
            <a href="product_detail.php?code=<?php echo $rel['product_code']; ?>">
              <img src="admin/uploads/<?php echo $rel['image']; ?>" alt="<?php echo $rel['name']; ?>">
              <p><?php echo $rel['name']; ?></p>
              <p class="price"><?php echo number_format($rel['price'], 0, ',', '.'); ?> VNĐ</p>
            </a>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="product-detail" style="margin-top:30px;">
    <h2>Đánh giá sản phẩm</h2>
    <?php if ($result_fb->num_rows > 0): ?>
        <?php while ($fb = $result_fb->fetch_assoc()): ?>
            <div class="feedback-item">
                <p><strong><?php echo htmlspecialchars($fb['user_name']); ?></strong> 
                - <?php echo str_repeat("⭐", $fb['rating']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
                <small><i><?php echo $fb['created_at']; ?></i></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Chưa có đánh giá nào cho sản phẩm này.</p>
    <?php endif; ?>
  </div>
</body>
</html>
<?php
$conn->close();
?>
