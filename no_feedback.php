<?php
session_start();

// ====== Kết nối CSDL ======
$conn = new mysqli("localhost", "root", "", "store");
if ($conn->connect_error) die("Kết nối thất bại: " . $conn->connect_error);

// ====== Lấy mã sản phẩm từ URL ======
$code = $_GET['code'] ?? '';

$stmt = $conn->prepare("
    SELECT p.*, d.description, d.material, d.compatibility, d.warranty, d.origin, d.features
    FROM products p
    LEFT JOIN product_details d ON p.id = d.product_id
    WHERE p.product_code = ? AND p.is_active = 1
");
$stmt->bind_param("s", $code);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) die("Không tìm thấy sản phẩm!");

// ====== Xử lý người dùng ======
$is_logged_in = isset($_SESSION['user_id']);
$user_name = 'Khách';
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

if ($is_logged_in) {
    $stmt_user = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $res = $stmt_user->get_result();
    if ($res->num_rows) $user_name = $res->fetch_assoc()['name'];
    $stmt_user->close();
}

// ====== Xử lý đánh giá (chỉ gửi, không xóa) ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    if (!$is_logged_in) {
        echo "<script>alert('Vui lòng đăng nhập để thực hiện thao tác.');</script>";
    } else {
        $rating = intval($_POST['rating']);
        $message = trim($_POST['message']);
        $stmt = $conn->prepare("INSERT INTO feedback (product_code, user_id, rating, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siis", $product['product_code'], $user_id, $rating, $message);
        $stmt->execute();
        $stmt->close();
        echo "<script>alert('Cảm ơn bạn đã gửi đánh giá!'); window.location.href=window.location.href;</script>";
        exit;
    }
}

// ====== Lấy sản phẩm gợi ý ======
$stmt_suggested = $conn->prepare("SELECT * FROM products WHERE product_code != ? AND category = ? AND is_active = 1 ORDER BY RAND() LIMIT 4");
$stmt_suggested->bind_param("ss", $product['product_code'], $product['category']);
$stmt_suggested->execute();
$related_products = $stmt_suggested->get_result();
$stmt_suggested->close();

// ====== Lấy danh sách đánh giá ======
$limit = 6; // Số đánh giá mỗi trang
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Tổng số đánh giá
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM feedback WHERE product_code = ?");
$stmt_count->bind_param("s", $product['product_code']);
$stmt_count->execute();
$total_feedback = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

$total_pages = ceil($total_feedback / $limit);

// Lấy đánh giá cho từng trang
$stmt_fb = $conn->prepare("
    SELECT f.*, u.name AS user_name
    FROM feedback f 
    JOIN users u ON f.user_id = u.id
    WHERE f.product_code = ?
    ORDER BY f.created_at DESC
    LIMIT ?, ?
");
$stmt_fb->bind_param("sii", $product['product_code'], $offset, $limit);
$stmt_fb->execute();
$feedbacks = $stmt_fb->get_result();
$stmt_fb->close();
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
    <img src="admin/uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="max-width:300px;">
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
                    <img src="admin/uploads/<?= htmlspecialchars($rel['image']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" style="width:100%; height:auto;">
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
  <a href="user.html" class="btn-back">⬅ Quay về</a>

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
