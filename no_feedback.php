<?php
session_start(); // ✅ Phải đặt ở đầu tiên để session hoạt động

// ====== Kết nối CSDL ======
$conn = new mysqli("localhost", "root", "", "store");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// ====== Kiểm tra đăng nhập ======
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : null;
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

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

// ====== Nếu gửi form đánh giá ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'])) {
    if ($is_logged_in) {
        $rating = intval($_POST['rating']);
        $message = trim($_POST['message']);

        // Kiểm tra xem người dùng đã đánh giá sản phẩm này chưa
        $check_stmt = $conn->prepare("SELECT id FROM feedback WHERE product_code = ? AND user_id = ?");
        $check_stmt->bind_param("si", $product['product_code'], $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "<script>alert('Bạn đã đánh giá sản phẩm này rồi!'); window.location.reload();</script>";
        } else {
            // Nếu chưa đánh giá thì thêm mới
            if ($rating >= 1 && $rating <= 5 && !empty($message)) {
                $stmt = $conn->prepare("INSERT INTO feedback (product_code, user_id, rating, message) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siis", $product['product_code'], $user_id, $rating, $message);
                $stmt->execute();
                $stmt->close();
                echo "<script>alert('Cảm ơn bạn đã gửi đánh giá!'); window.location.reload();</script>";
            } else {
                echo "<script>alert('Vui lòng nhập đủ nội dung và chọn số sao.');</script>";
            }
        }
        $check_stmt->close();
    } else {
        // ✅ Chặn người chưa đăng nhập
        echo "<script>alert('Vui lòng đăng nhập để gửi đánh giá.'); window.location.href='login.php';</script>";
        exit;
    }
}

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
  <title>Chi tiết sản phẩm - <?php echo htmlspecialchars($product['name']); ?></title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; color: #333; margin: 0; padding: 0; }
    .product-detail { max-width: 1000px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .product-detail h1 { margin-bottom: 20px; font-size: 28px; color: #222; }
    .product-detail img { max-width: 300px; border-radius: 8px; display: block; margin-bottom: 15px; }
    .product-detail p { font-size: 16px; margin: 8px 0; }
    .product-detail strong { color: #444; }
    h2 { margin-top: 10px; font-size: 22px; color: #222; }
    button { background: #007bff; border: none; color: white; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px; }
    button:hover { background: #0056b3; }
    textarea { width: 100%; height: 80px; margin-top: 10px; border-radius: 5px; padding: 10px; border: 1px solid #ccc; resize: none; }
    .stars label { font-size: 25px; margin-right: 5px; cursor: pointer; }
  </style>
</head>
<body>

<div class="product-detail">
  <h1><?php echo htmlspecialchars($product['name']); ?></h1>
  <img src="admin/uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
  <p><strong>Mã sản phẩm:</strong> <?php echo htmlspecialchars($product['product_code']); ?></p>
  <p><strong>Loại sản phẩm:</strong> <?php echo htmlspecialchars($product['category']); ?></p>
  <p><strong>Giá:</strong> <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</p>
  <p><strong>Mô tả:</strong> <?php echo !empty($product['description']) ? htmlspecialchars($product['description']) : "Chưa có mô tả"; ?></p>
  <p><strong>Chất liệu:</strong> <?php echo !empty($product['material']) ? htmlspecialchars($product['material']) : "Chưa có thông tin"; ?></p>
  <p><strong>Tương thích:</strong> <?php echo !empty($product['compatibility']) ? htmlspecialchars($product['compatibility']) : "Chưa có thông tin"; ?></p>
  <p><strong>Bảo hành:</strong> <?php echo !empty($product['warranty']) ? htmlspecialchars($product['warranty']) : "Chưa có thông tin"; ?></p>
  <p><strong>Xuất xứ:</strong> <?php echo !empty($product['origin']) ? htmlspecialchars($product['origin']) : "Chưa có thông tin"; ?></p>
  <p><strong>Tính năng:</strong> <?php echo !empty($product['features']) ? htmlspecialchars($product['features']) : "Chưa có thông tin"; ?></p>
</div>

<!-- Form đánh giá -->
<div class="product-detail" style="margin-top:30px;">
    <h2>Đánh giá sản phẩm</h2>

    <?php if ($result_fb->num_rows > 0): ?>
        <?php while ($fb = $result_fb->fetch_assoc()): ?>
            <div style="border-bottom:1px solid #ddd; margin:10px 0; padding-bottom:8px;">
                <p><strong><?php echo htmlspecialchars($fb['user_name']); ?></strong> - 
                <?php echo str_repeat("⭐", $fb['rating']); ?></p>
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
