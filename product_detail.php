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

session_start(); // Đảm bảo session hoạt động
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : 'Khách';
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

// ====== Xử lý xóa đánh giá ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feedback'])) {
    if ($is_logged_in) {
        $delete_id = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $delete_id, $user_id);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Đánh giá của bạn đã được xóa!'); window.location.href = window.location.href;</script>";
        exit;
    } else {
        echo "<script>alert('Bạn cần đăng nhập để xóa đánh giá.');</script>";
    }
}

// ====== Xử lý gửi đánh giá ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating']) && !isset($_POST['delete_feedback'])) {
    if ($is_logged_in) {
        $rating = intval($_POST['rating']);
        $message = trim($_POST['message']);
        $stmt = $conn->prepare("INSERT INTO feedback (product_code, user_id, rating, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siis", $product['product_code'], $user_id, $rating, $message);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Cảm ơn bạn đã gửi đánh giá!'); window.location.href = window.location.href;</script>";
        exit;
    } else {
        echo "<script>alert('Vui lòng đăng nhập để gửi đánh giá.');</script>";
    }
}
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

<?php
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
?>

<?php if ($result_suggested->num_rows > 0): ?>
  <div class="product-detail" style="margin-top:20px;">
    <h2>Gợi ý sản phẩm khác</h2>
    <div class="related-products">
      <?php while ($rel = $result_suggested->fetch_assoc()): ?>
        <div class="related-item">
          <a href="product_detail.php?code=<?php echo $rel['product_code']; ?>">
            <img src="admin/uploads/<?php echo $rel['image']; ?>" alt="<?php echo $rel['name']; ?>">
            <p><?php echo $rel['name']; ?></p>
            <p><?php echo number_format($rel['price'], 0, ',', '.'); ?> VNĐ</p>
          </a>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
<?php endif; ?>

<?php
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

<div class="product-detail" style="margin-top:30px;">
    <h2>Đánh giá sản phẩm</h2>

    <?php if ($is_logged_in): ?>
        <p><strong>Người dùng:</strong> <?php echo htmlspecialchars($user_name); ?></p>
        <form method="POST">
            <label>Chọn số sao:</label>
            <div style="font-size:25px;">
                <input type="radio" name="rating" value="5" id="star5"><label for="star5">⭐5</label>
                <input type="radio" name="rating" value="4" id="star4"><label for="star4">⭐4</label>
                <input type="radio" name="rating" value="3" id="star3"><label for="star3">⭐3</label>
                <input type="radio" name="rating" value="2" id="star2"><label for="star2">⭐2</label>
                <input type="radio" name="rating" value="1" id="star1" required><label for="star1">⭐1</label>
            </div>
            <textarea name="message" placeholder="Nhập nội dung đánh giá..." rows="3" style="width:100%;margin-top:10px;"></textarea>
            <button type="submit" style="margin-top:10px;">Gửi đánh giá</button>
        </form>
    <?php else: ?>
        <p style="color:red;">Bạn cần 
          <a href="#login" onclick="showSection('login')">
            <i class="fas fa-right-to-bracket" style="color: white; margin-right: 6px;"></i>
            Đăng Nhập
          </a> để gửi đánh giá.
        </p>
    <?php endif; ?>

    <hr>
    <h3>Đánh giá gần đây</h3>
    <?php if ($result_fb->num_rows > 0): ?>
        <?php while ($fb = $result_fb->fetch_assoc()): ?>
            <div style="border-bottom:1px solid #ddd; margin:10px 0; padding-bottom:8px;">
                <p><strong><?php echo htmlspecialchars($fb['user_name']); ?></strong> 
                - <?php echo str_repeat("⭐", $fb['rating']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
                <small><i><?php echo $fb['created_at']; ?></i></small>

                <?php if ($is_logged_in && $fb['user_id'] == $user_id): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $fb['id']; ?>">
                        <button type="submit" name="delete_feedback"
                                onclick="return confirm('Bạn có chắc muốn xóa đánh giá này không?')"
                                style="background:red; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; margin-left:10px;">
                            Xóa
                        </button>
                    </form>
                <?php endif; ?>
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
